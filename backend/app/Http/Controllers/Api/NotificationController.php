<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecord;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->syncTaskReminders($user);

        $limit = min(max((int) $request->query('limit', 10), 1), 50);
        $query = NotificationRecord::query()->where('user_id', $user->id);
        $notifications = (clone $query)
            ->orderByRaw('read_at IS NOT NULL')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $notifications,
            'unread_count' => (clone $query)->whereNull('read_at')->count(),
        ]);
    }

    public function read(Request $request, NotificationRecord $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()?->id, 404);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => '通知を既読にしました。',
            'data' => $notification->fresh(),
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        NotificationRecord::query()
            ->where('user_id', $request->user()?->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return response()->json(['message' => 'すべての通知を既読にしました。']);
    }

    private function syncTaskReminders(User $user): void
    {
        $tasks = Task::query()
            ->active()
            ->with('account:id,account_name')
            ->where('assigned_user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today()->addDays(7))
            ->orderBy('due_date')
            ->get();

        DB::transaction(function () use ($tasks, $user): void {
            $this->removeInactiveTaskReminders($user, $tasks->pluck('id'));

            foreach ($tasks as $task) {
                $days = (int) today()->diffInDays($task->due_date, false);
                $timing = $days < 0 ? '期限を超過しています' : ($days === 0 ? '本日が期限です' : "期限まであと{$days}日です");
                $account = $task->account?->account_name;
                $message = $account ? "{$account}のタスクです。{$timing}。" : "{$timing}。";

                $notification = NotificationRecord::query()->firstOrNew([
                    'user_id' => $user->id,
                    'type' => 'task_due',
                    'target_table' => 'tasks',
                    'target_id' => $task->id,
                ]);
                $notification->fill([
                    'title' => $task->title,
                    'message' => $message,
                    'action_url' => '/tasks',
                ])->save();
            }
        });
    }

    /** @param Collection<int, int> $activeTaskIds */
    private function removeInactiveTaskReminders(User $user, Collection $activeTaskIds): void
    {
        $query = NotificationRecord::query()
            ->where('user_id', $user->id)
            ->where('type', 'task_due')
            ->where('target_table', 'tasks');

        if ($activeTaskIds->isEmpty()) {
            $query->delete();

            return;
        }

        $query->whereNotIn('target_id', $activeTaskIds)->delete();
    }
}
