<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_crud_uses_logical_delete_and_writes_audit_log(): void
    {
        $created = $this->postJson('/api/accounts', [
            'account_code' => 'AC-TEST-01',
            'account_name' => 'テスト株式会社',
            'industry' => 'IT・通信',
            'phone_number' => '03-1234-5678',
            'postal_code' => '100-0001',
            'address' => '東京都千代田区',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.account_code', 'AC-TEST-01')
            ->assertJsonPath('data.account_name', 'テスト株式会社');

        $accountId = $created->json('data.id');
        $this->patchJson("/api/accounts/{$accountId}", [
            'account_code' => 'AC-TEST-01',
            'account_name' => 'テスト株式会社 更新',
        ])->assertOk()->assertJsonPath('data.account_name', 'テスト株式会社 更新');

        $this->getJson('/api/accounts?search=テスト')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/accounts/{$accountId}")
            ->assertOk()
            ->assertJsonPath('message', '取引先を削除しました。');

        $this->assertDatabaseHas('accounts', ['id' => $accountId, 'is_deleted' => true]);
        $this->assertDatabaseHas('activity_logs', ['target_id' => $accountId, 'action_type' => 'DELETE']);
        $this->getJson('/api/accounts')->assertJsonCount(0, 'data');
    }

    public function test_account_validation_rejects_invalid_input(): void
    {
        $this->postJson('/api/accounts', [
            'account_code' => 'invalid code',
            'account_name' => '',
            'phone_number' => 'invalid-phone',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'account_code', 'account_name', 'phone_number',
        ]);
    }

    public function test_dashboard_returns_crm_metrics(): void
    {
        Account::create(['account_code' => 'AC-01', 'account_name' => 'Prime', 'is_deleted' => false]);
        Account::create(['account_code' => 'AC-02', 'account_name' => 'Deleted', 'is_deleted' => true]);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('metrics.accounts', 1)
            ->assertJsonStructure(['metrics', 'pipeline', 'tasks', 'opportunities', 'leads', 'cases', 'users']);
    }
}
