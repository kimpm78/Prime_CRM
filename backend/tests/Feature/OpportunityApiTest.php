<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OpportunityApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private int $stageId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        $this->account = Account::create([
            'account_code' => 'AC-OPP-01',
            'account_name' => '商談テスト株式会社',
        ]);
        $this->stageId = DB::table('opportunity_stages')->insertGetId([
            'stage_name' => 'proposal',
            'probability' => 30,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_opportunity_crud_uses_logical_delete_and_writes_audit_log(): void
    {
        $created = $this->postJson('/api/opportunities', [
            'account_id' => $this->account->id,
            'stage_id' => $this->stageId,
            'owner_user_id' => $this->user->id,
            'opportunity_name' => '新基幹システム導入',
            'amount' => 3500000,
            'expected_close_date' => '2026-12-20',
            'description' => '要件確認中',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.opportunity_name', '新基幹システム導入')
            ->assertJsonPath('data.account.id', $this->account->id)
            ->assertJsonPath('data.stage.id', $this->stageId);

        $opportunityId = $created->json('data.id');

        $this->putJson("/api/opportunities/{$opportunityId}", [
            'account_id' => $this->account->id,
            'stage_id' => $this->stageId,
            'owner_user_id' => $this->user->id,
            'opportunity_name' => '新基幹システム導入 更新',
            'amount' => 4200000,
            'expected_close_date' => '2027-01-15',
            'description' => '提案内容を調整中',
        ])->assertOk()
            ->assertJsonPath('data.opportunity_name', '新基幹システム導入 更新')
            ->assertJsonPath('data.amount', '4200000.00');

        $this->getJson('/api/opportunities?search=更新')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['meta' => ['accounts', 'stages', 'users']]);

        $this->deleteJson("/api/opportunities/{$opportunityId}")
            ->assertOk()
            ->assertJsonPath('message', '商談を削除しました。');

        $this->assertDatabaseHas('opportunities', ['id' => $opportunityId, 'is_deleted' => true]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'target_table' => 'opportunities',
            'target_id' => $opportunityId,
            'action_type' => 'DELETE',
        ]);
        $this->getJson('/api/opportunities')->assertJsonCount(0, 'data');
        $this->getJson("/api/opportunities/{$opportunityId}")->assertNotFound();
    }

    public function test_opportunity_validation_rejects_invalid_input(): void
    {
        $this->postJson('/api/opportunities', [
            'account_id' => 999999,
            'stage_id' => 999999,
            'opportunity_name' => '',
            'amount' => -1,
            'expected_close_date' => 'not-a-date',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'account_id', 'stage_id', 'opportunity_name', 'amount', 'expected_close_date',
        ]);
    }
}
