<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);
        $tasks = Task::query()
            ->active()
            ->with([
                'account:id,account_code,account_name',
                'opportunity:id,account_id,opportunity_name',
                'assignee:id,name',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $pattern = "%{$search}%";
                    $query->whereRaw('LOWER(title) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(description) LIKE LOWER(?)', [$pattern])
                        ->orWhereHas('account', function ($query) use ($pattern): void {
                            $query->whereRaw('LOWER(account_name) LIKE LOWER(?)', [$pattern]);
                        })
                        ->orWhereHas('opportunity', function ($query) use ($pattern): void {
                            $query->whereRaw('LOWER(opportunity_name) LIKE LOWER(?)', [$pattern]);
                        });
                });
            })
            ->orderByRaw('due_date IS NULL, due_date')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $payload = $tasks->toArray();
        $payload['meta'] = [
            'accounts' => DB::table('accounts')->where('is_deleted', false)
                ->orderBy('account_name')->limit(200)->get(['id', 'account_code', 'account_name']),
            'opportunities' => DB::table('opportunities')
                ->join('accounts', 'opportunities.account_id', '=', 'accounts.id')
                ->where('opportunities.is_deleted', false)
                ->where('accounts.is_deleted', false)
                ->orderBy('opportunities.opportunity_name')
                ->limit(300)
                ->get(['opportunities.id', 'opportunities.account_id', 'opportunities.opportunity_name']),
            'users' => DB::table('users')->where('is_active', true)
                ->orderBy('name')->limit(100)->get(['id', 'name']),
        ];

        return response()->json($payload);
    }

    public function store(TaskRequest $request): JsonResponse
    {
        $task = DB::transaction(function () use ($request): Task {
            $task = Task::create($request->validated());
            $this->log($request, $task, 'INSERT', "タスク「{$task->title}」を登録");

            return $task;
        });

        return response()->json([
            'message' => 'タスクを登録しました。',
            'data' => $this->loadRelations($task),
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        abort_if($task->is_deleted, 404);

        return response()->json(['data' => $this->loadRelations($task)]);
    }

    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        abort_if($task->is_deleted, 404);

        DB::transaction(function () use ($request, $task): void {
            $task->update($request->validated());
            $this->log($request, $task, 'UPDATE', "タスク「{$task->title}」を更新");
        });

        return response()->json([
            'message' => 'タスク情報を更新しました。',
            'data' => $this->loadRelations($task->fresh()),
        ]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        abort_if($task->is_deleted, 404);

        DB::transaction(function () use ($request, $task): void {
            $task->update(['is_deleted' => true]);
            $this->log($request, $task, 'DELETE', "タスク「{$task->title}」を論理削除");
        });

        return response()->json(['message' => 'タスクを削除しました。']);
    }

    private function loadRelations(Task $task): Task
    {
        return $task->load([
            'account:id,account_code,account_name',
            'opportunity:id,account_id,opportunity_name',
            'assignee:id,name',
        ]);
    }

    private function log(Request $request, Task $task, string $action, string $description): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $request->user()?->id,
            'target_table' => 'tasks',
            'target_id' => $task->id,
            'action_type' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
