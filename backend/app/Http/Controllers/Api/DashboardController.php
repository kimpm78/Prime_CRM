<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $pipeline = DB::table('opportunity_stages as stages')
            ->leftJoin('opportunities as opportunities', function ($join): void {
                $join->on('opportunities.stage_id', '=', 'stages.id')
                    ->where('opportunities.is_deleted', false);
            })
            ->select('stages.id', 'stages.stage_name', 'stages.probability', 'stages.sort_order')
            ->selectRaw('COUNT(opportunities.id) AS deals_count')
            ->selectRaw('COALESCE(SUM(opportunities.amount), 0) AS amount')
            ->groupBy('stages.id', 'stages.stage_name', 'stages.probability', 'stages.sort_order')
            ->orderBy('stages.sort_order')
            ->get();

        $openTasks = DB::table('tasks')->where('is_deleted', false)->where('status', '!=', 'completed');
        $activeCases = DB::table('cases')
            ->join('case_statuses', 'cases.status_id', '=', 'case_statuses.id')
            ->where('cases.is_deleted', false)
            ->where('case_statuses.status_name', '!=', 'resolved');

        return response()->json([
            'metrics' => [
                'accounts' => DB::table('accounts')->where('is_deleted', false)->count(),
                'pipeline_amount' => DB::table('opportunities')
                    ->join('opportunity_stages', 'opportunities.stage_id', '=', 'opportunity_stages.id')
                    ->where('opportunities.is_deleted', false)
                    ->whereNotIn('opportunity_stages.stage_name', ['won', 'lost'])
                    ->sum('opportunities.amount'),
                'open_tasks' => (clone $openTasks)->count(),
                'active_cases' => (clone $activeCases)->count(),
            ],
            'pipeline' => $pipeline,
            'tasks' => DB::table('tasks')
                ->leftJoin('accounts', 'tasks.account_id', '=', 'accounts.id')
                ->leftJoin('users', 'tasks.assigned_user_id', '=', 'users.id')
                ->where('tasks.is_deleted', false)
                ->where('tasks.status', '!=', 'completed')
                ->orderByRaw('tasks.due_date IS NULL, tasks.due_date')
                ->limit(6)
                ->get(['tasks.id', 'tasks.title', 'tasks.due_date', 'tasks.status', 'tasks.priority', 'accounts.account_name', 'users.name as assignee']),
            'opportunities' => DB::table('opportunities')
                ->join('accounts', 'opportunities.account_id', '=', 'accounts.id')
                ->join('opportunity_stages', 'opportunities.stage_id', '=', 'opportunity_stages.id')
                ->where('opportunities.is_deleted', false)
                ->orderByDesc('opportunities.updated_at')
                ->limit(6)
                ->get(['opportunities.id', 'opportunities.opportunity_name', 'opportunities.amount', 'opportunities.expected_close_date', 'accounts.account_name', 'opportunity_stages.stage_name', 'opportunity_stages.probability']),
            'leads' => DB::table('leads')->where('is_deleted', false)->orderByDesc('score')->limit(6)
                ->get(['id', 'lead_name', 'contact_name', 'source', 'status', 'score']),
            'cases' => DB::table('cases')
                ->join('accounts', 'cases.account_id', '=', 'accounts.id')
                ->join('case_statuses', 'cases.status_id', '=', 'case_statuses.id')
                ->where('cases.is_deleted', false)
                ->orderByDesc('cases.opened_at')->limit(6)
                ->get(['cases.id', 'cases.subject', 'cases.priority', 'cases.opened_at', 'accounts.account_name', 'case_statuses.status_name']),
            'users' => DB::table('users')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
