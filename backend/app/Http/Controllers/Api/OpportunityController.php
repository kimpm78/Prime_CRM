<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpportunityRequest;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpportunityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);
        $opportunities = Opportunity::query()
            ->active()
            ->with([
                'account:id,account_code,account_name',
                'stage:id,stage_name,probability,sort_order',
                'owner:id,name',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $pattern = "%{$search}%";
                    $query->whereRaw('LOWER(opportunity_name) LIKE LOWER(?)', [$pattern])
                        ->orWhereHas('account', function ($query) use ($pattern): void {
                            $query->whereRaw('LOWER(account_name) LIKE LOWER(?)', [$pattern])
                                ->orWhereRaw('LOWER(account_code) LIKE LOWER(?)', [$pattern]);
                        });
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $payload = $opportunities->toArray();
        $payload['meta'] = [
            'accounts' => DB::table('accounts')->where('is_deleted', false)
                ->orderBy('account_name')->limit(200)->get(['id', 'account_code', 'account_name']),
            'stages' => DB::table('opportunity_stages')
                ->orderBy('sort_order')->get(['id', 'stage_name', 'probability', 'sort_order']),
            'users' => DB::table('users')->where('is_active', true)
                ->orderBy('name')->limit(100)->get(['id', 'name']),
        ];

        return response()->json($payload);
    }

    public function store(OpportunityRequest $request): JsonResponse
    {
        $opportunity = DB::transaction(function () use ($request): Opportunity {
            $opportunity = Opportunity::create($request->validated());
            $this->log($request, $opportunity, 'INSERT', "商談「{$opportunity->opportunity_name}」を登録");

            return $opportunity;
        });

        return response()->json([
            'message' => '商談を登録しました。',
            'data' => $this->loadRelations($opportunity),
        ], 201);
    }

    public function show(Opportunity $opportunity): JsonResponse
    {
        abort_if($opportunity->is_deleted, 404);

        return response()->json(['data' => $this->loadRelations($opportunity)]);
    }

    public function update(OpportunityRequest $request, Opportunity $opportunity): JsonResponse
    {
        abort_if($opportunity->is_deleted, 404);

        DB::transaction(function () use ($request, $opportunity): void {
            $opportunity->update($request->validated());
            $this->log($request, $opportunity, 'UPDATE', "商談「{$opportunity->opportunity_name}」を更新");
        });

        return response()->json([
            'message' => '商談情報を更新しました。',
            'data' => $this->loadRelations($opportunity->fresh()),
        ]);
    }

    public function destroy(Request $request, Opportunity $opportunity): JsonResponse
    {
        abort_if($opportunity->is_deleted, 404);

        DB::transaction(function () use ($request, $opportunity): void {
            $opportunity->update(['is_deleted' => true]);
            $this->log($request, $opportunity, 'DELETE', "商談「{$opportunity->opportunity_name}」を論理削除");
        });

        return response()->json(['message' => '商談を削除しました。']);
    }

    private function loadRelations(Opportunity $opportunity): Opportunity
    {
        return $opportunity->load([
            'account:id,account_code,account_name',
            'stage:id,stage_name,probability,sort_order',
            'owner:id,name',
        ]);
    }

    private function log(Request $request, Opportunity $opportunity, string $action, string $description): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $request->user()?->id,
            'target_table' => 'opportunities',
            'target_id' => $opportunity->id,
            'action_type' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
