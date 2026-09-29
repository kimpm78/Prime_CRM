<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportCaseApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Contact $contact;

    private int $newStatusId;

    private int $resolvedStatusId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        $this->account = Account::create([
            'account_code' => 'AC-CASE-01',
            'account_name' => '問い合わせテスト株式会社',
        ]);
        $this->contact = Contact::create([
            'account_id' => $this->account->id,
            'last_name' => '山田',
            'first_name' => '太郎',
            'email' => 'yamada@example.com',
        ]);
        $this->newStatusId = $this->statusId('new', 1);
        $this->resolvedStatusId = $this->statusId('resolved', 3);
    }

    public function test_case_crud_sets_closed_at_uses_logical_delete_and_writes_audit_log(): void
    {
        $created = $this->postJson('/api/cases', [
            'account_id' => $this->account->id,
            'contact_id' => $this->contact->id,
            'status_id' => $this->newStatusId,
            'owner_user_id' => $this->user->id,
            'subject' => '管理画面にログインできない',
            'description' => 'ログイン時にエラーが表示される。',
            'priority' => 'high',
            'opened_at' => '2026-09-24 10:30:00',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.subject', '管理画面にログインできない')
            ->assertJsonPath('data.account.id', $this->account->id)
            ->assertJsonPath('data.contact.id', $this->contact->id)
            ->assertJsonPath('data.status.status_name', 'new')
            ->assertJsonPath('data.closed_at', null);

        $caseId = $created->json('data.id');

        $this->putJson("/api/cases/{$caseId}", [
            'account_id' => $this->account->id,
            'contact_id' => $this->contact->id,
            'status_id' => $this->resolvedStatusId,
            'owner_user_id' => $this->user->id,
            'subject' => '管理画面ログイン問題を解決',
            'description' => 'パスワード再設定により解決した。',
            'priority' => 'middle',
            'opened_at' => '2026-09-24 10:30:00',
        ])->assertOk()
            ->assertJsonPath('data.status.status_name', 'resolved')
            ->assertJsonPath('data.priority', 'middle')
            ->assertJsonPath('data.closed_at', fn ($value) => is_string($value));

        $this->getJson('/api/cases?search=ログイン問題')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['meta' => ['accounts', 'contacts', 'statuses', 'users']]);

        $this->deleteJson("/api/cases/{$caseId}")
            ->assertOk()
            ->assertJsonPath('message', '問い合わせを削除しました。');

        $this->assertDatabaseHas('cases', ['id' => $caseId, 'is_deleted' => true]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'target_table' => 'cases',
            'target_id' => $caseId,
            'action_type' => 'DELETE',
        ]);
        $this->getJson('/api/cases')->assertJsonCount(0, 'data');
        $this->getJson("/api/cases/{$caseId}")->assertNotFound();
    }

    public function test_case_validation_rejects_invalid_and_mismatched_relations(): void
    {
        $otherAccount = Account::create([
            'account_code' => 'AC-CASE-02',
            'account_name' => '別取引先株式会社',
        ]);

        $this->postJson('/api/cases', [
            'account_id' => $otherAccount->id,
            'contact_id' => $this->contact->id,
            'status_id' => 999999,
            'owner_user_id' => 999999,
            'subject' => '',
            'priority' => 'urgent',
            'opened_at' => 'not-a-date',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'contact_id', 'status_id', 'owner_user_id', 'subject', 'priority', 'opened_at',
        ]);
    }

    private function statusId(string $name, int $sortOrder): int
    {
        return DB::table('case_statuses')->insertGetId([
            'status_name' => $name,
            'sort_order' => $sortOrder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
