<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);
        $leads = Lead::query()
            ->active()
            ->with('owner:id,name')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $pattern = "%{$search}%";
                    $query->whereRaw('LOWER(lead_name) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(contact_name) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(email) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(phone_number) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(source) LIKE LOWER(?)', [$pattern]);
                });
            })
            ->orderByDesc('score')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        return response()->json($leads);
    }

    public function store(LeadRequest $request): JsonResponse
    {
        $lead = DB::transaction(function () use ($request): Lead {
            $lead = Lead::create($request->validated());
            $this->log($request, $lead, 'INSERT', "リード「{$lead->lead_name}」を登録");

            return $lead;
        });

        return response()->json([
            'message' => 'リードを登録しました。',
            'data' => $lead->load('owner:id,name'),
        ], 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        abort_if($lead->is_deleted, 404);

        return response()->json(['data' => $lead->load('owner:id,name')]);
    }

    public function update(LeadRequest $request, Lead $lead): JsonResponse
    {
        abort_if($lead->is_deleted, 404);

        DB::transaction(function () use ($request, $lead): void {
            $lead->update($request->validated());
            $this->log($request, $lead, 'UPDATE', "リード「{$lead->lead_name}」を更新");
        });

        return response()->json([
            'message' => 'リード情報を更新しました。',
            'data' => $lead->fresh()->load('owner:id,name'),
        ]);
    }

    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        abort_if($lead->is_deleted, 404);

        DB::transaction(function () use ($request, $lead): void {
            $lead->update(['is_deleted' => true]);
            $this->log($request, $lead, 'DELETE', "リード「{$lead->lead_name}」を論理削除");
        });

        return response()->json(['message' => 'リードを削除しました。']);
    }

    private function log(Request $request, Lead $lead, string $action, string $description): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $request->user()?->id,
            'target_table' => 'leads',
            'target_id' => $lead->id,
            'action_type' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
