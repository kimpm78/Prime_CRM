<?php

namespace Tests\Feature;

use App\Models\NotificationRecord;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_read_personal_task_reminders_and_mark_them_as_read(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Task::create([
            'assigned_user_id' => $user->id,
            'title' => '提案内容の最終確認',
            'due_date' => today()->addDays(2),
            'status' => 'not_started',
            'priority' => 'high',
        ]);
        Task::create([
            'assigned_user_id' => $user->id,
            'title' => '完了済みタスク',
            'due_date' => today(),
            'status' => 'completed',
            'priority' => 'low',
        ]);

        $response = $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.type', 'task_due')
            ->assertJsonPath('data.0.title', '提案内容の最終確認')
            ->assertJsonPath('data.0.action_url', '/tasks');

        $notificationId = $response->json('data.0.id');

        $this->patchJson("/api/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.id', $notificationId);

        $this->getJson('/api/notifications')->assertJsonPath('unread_count', 0);

        NotificationRecord::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'システム通知',
            'message' => '確認してください。',
        ]);
        $this->postJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('message', 'すべての通知を既読にしました。');
        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id, 'read_at' => null]);
    }

    public function test_user_cannot_read_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = NotificationRecord::create([
            'user_id' => $owner->id,
            'type' => 'system',
            'title' => '本人向け通知',
            'message' => '本人のみ閲覧できます。',
        ]);

        Sanctum::actingAs($otherUser);

        $this->patchJson("/api/notifications/{$notification->id}/read")->assertNotFound();
        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('unread_count', 0);
    }
}
