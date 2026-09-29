<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Opportunity $opportunity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        $this->account = Account::create([
            'account_code' => 'AC-TASK-01',
            'account_name' => 'タスクテスト株式会社',
        ]);
        $stageId = DB::table('opportunity_stages')->insertGetId([
            'stage_name' => 'proposal',
            'probability' => 30,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->opportunity = Opportunity::create([
            'account_id' => $this->account->id,
            'stage_id' => $stageId,
            'opportunity_name' => 'タスク関連商談',
            'amount' => 1000000,
        ]);
    }

    public function test_task_crud_uses_logical_delete_and_writes_audit_log(): void
    {
        $created = $this->postJson('/api/tasks', [
            'account_id' => $this->account->id,
            'opportunity_id' => $this->opportunity->id,
            'assigned_user_id' => $this->user->id,
            'title' => '提案資料を作成',
            'description' => '初回提案用の資料を作成する。',
            'due_date' => '2026-10-10',
            'status' => 'not_started',
            'priority' => 'high',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.title', '提案資料を作成')
            ->assertJsonPath('data.account.id', $this->account->id)
            ->assertJsonPath('data.opportunity.id', $this->opportunity->id);

        $taskId = $created->json('data.id');

        $this->putJson("/api/tasks/{$taskId}", [
            'account_id' => $this->account->id,
            'opportunity_id' => $this->opportunity->id,
            'assigned_user_id' => $this->user->id,
            'title' => '提案資料を更新',
            'description' => 'レビュー結果を反映する。',
            'due_date' => '2026-10-12',
            'status' => 'in_progress',
            'priority' => 'middle',
        ])->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.priority', 'middle');

        $this->getJson('/api/tasks?search=更新')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['meta' => ['accounts', 'opportunities', 'users']]);

        $this->deleteJson("/api/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonPath('message', 'タスクを削除しました。');

        $this->assertDatabaseHas('tasks', ['id' => $taskId, 'is_deleted' => true]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'target_table' => 'tasks',
            'target_id' => $taskId,
            'action_type' => 'DELETE',
        ]);
        $this->getJson('/api/tasks')->assertJsonCount(0, 'data');
        $this->getJson("/api/tasks/{$taskId}")->assertNotFound();
    }

    public function test_task_validation_rejects_invalid_and_mismatched_relations(): void
    {
        $otherAccount = Account::create([
            'account_code' => 'AC-TASK-02',
            'account_name' => '別取引先株式会社',
        ]);

        $this->postJson('/api/tasks', [
            'account_id' => $otherAccount->id,
            'opportunity_id' => $this->opportunity->id,
            'assigned_user_id' => 999999,
            'title' => '',
            'due_date' => 'not-a-date',
            'status' => 'unknown',
            'priority' => 'urgent',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'opportunity_id', 'assigned_user_id', 'title', 'due_date', 'status', 'priority',
        ]);
    }
}
