<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_lead_crud_uses_logical_delete_and_writes_audit_log(): void
    {
        $created = $this->postJson('/api/leads', [
            'lead_name' => 'テストリード株式会社',
            'contact_name' => '山田 太郎',
            'email' => 'lead@example.com',
            'phone_number' => '03-1234-5678',
            'source' => 'Web',
            'status' => 'new',
            'score' => 65,
            'owner_user_id' => $this->user->id,
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.lead_name', 'テストリード株式会社')
            ->assertJsonPath('data.owner.id', $this->user->id);

        $leadId = $created->json('data.id');

        $this->putJson("/api/leads/{$leadId}", [
            'lead_name' => 'テストリード株式会社 更新',
            'contact_name' => '山田 太郎',
            'email' => 'lead@example.com',
            'phone_number' => '03-1234-5678',
            'source' => '紹介',
            'status' => 'qualified',
            'score' => 90,
            'owner_user_id' => $this->user->id,
        ])->assertOk()
            ->assertJsonPath('data.status', 'qualified')
            ->assertJsonPath('data.score', 90);

        $this->getJson('/api/leads?search=更新')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/leads/{$leadId}")
            ->assertOk()
            ->assertJsonPath('message', 'リードを削除しました。');

        $this->assertDatabaseHas('leads', ['id' => $leadId, 'is_deleted' => true]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'target_table' => 'leads',
            'target_id' => $leadId,
            'action_type' => 'DELETE',
        ]);
        $this->getJson('/api/leads')->assertJsonCount(0, 'data');
        $this->getJson("/api/leads/{$leadId}")->assertNotFound();
    }

    public function test_lead_validation_rejects_invalid_input(): void
    {
        $this->postJson('/api/leads', [
            'lead_name' => '',
            'email' => 'invalid-email',
            'phone_number' => '電話番号',
            'status' => 'unknown',
            'score' => 101,
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'lead_name', 'email', 'phone_number', 'status', 'score',
        ]);
    }
}
