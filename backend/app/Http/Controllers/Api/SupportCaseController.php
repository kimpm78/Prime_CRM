<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportCaseRequest;
use App\Models\SupportCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportCaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);
        $cases = SupportCase::query()
            ->active()
            ->with([
                'account:id,account_code,account_name',
                'contact:id,account_id,last_name,first_name,email',
                'status:id,status_name,sort_order',
                'owner:id,name',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $pattern = "%{$search}%";
                    $query->whereRaw('LOWER(subject) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(description) LIKE LOWER(?)', [$pattern])
                        ->orWhereHas('account', function ($query) use ($pattern): void {
                            $query->whereRaw('LOWER(account_name) LIKE LOWER(?)', [$pattern])
                                ->orWhereRaw('LOWER(account_code) LIKE LOWER(?)', [$pattern]);
                        })
                        ->orWhereHas('contact', function ($query) use ($pattern): void {
                            $query->whereRaw('LOWER(last_name) LIKE LOWER(?)', [$pattern])
                                ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', [$pattern]);
                        });
                });
            })
            ->orderByDesc('opened_at')
            ->paginate($perPage);

        $payload = $cases->toArray();
        $payload['meta'] = [
            'accounts' => DB::table('accounts')->where('is_deleted', false)
                ->orderBy('account_name')->limit(200)->get(['id', 'account_code', 'account_name']),
            'contacts' => DB::table('contacts')
                ->join('accounts', 'contacts.account_id', '=', 'accounts.id')
                ->where('accounts.is_deleted', false)
                ->orderBy('contacts.last_name')->orderBy('contacts.first_name')->limit(300)
                ->get(['contacts.id', 'contacts.account_id', 'contacts.last_name', 'contacts.first_name', 'contacts.email']),
            'statuses' => DB::table('case_statuses')
                ->orderBy('sort_order')->get(['id', 'status_name', 'sort_order']),
            'users' => DB::table('users')->where('is_active', true)
                ->orderBy('name')->limit(100)->get(['id', 'name']),
        ];

        return response()->json($payload);
    }

    public function store(SupportCaseRequest $request): JsonResponse
    {
        $case = DB::transaction(function () use ($request): SupportCase {
            $case = SupportCase::create($this->withClosedAt($request->validated()));
            $this->log($request, $case, 'INSERT', "問い合わせ「{$case->subject}」を登録");

            return $case;
        });

        return response()->json([
            'message' => '問い合わせを登録しました。',
            'data' => $this->loadRelations($case),
        ], 201);
    }

    public function show(SupportCase $case): JsonResponse
    {
        abort_if($case->is_deleted, 404);

        return response()->json(['data' => $this->loadRelations($case)]);
    }

    public function update(SupportCaseRequest $request, SupportCase $case): JsonResponse
    {
        abort_if($case->is_deleted, 404);

        DB::transaction(function () use ($request, $case): void {
            $case->update($this->withClosedAt($request->validated(), $case));
            $this->log($request, $case, 'UPDATE', "問い合わせ「{$case->subject}」を更新");
        });

        return response()->json([
            'message' => '問い合わせ情報を更新しました。',
            'data' => $this->loadRelations($case->fresh()),
        ]);
    }

    public function destroy(Request $request, SupportCase $case): JsonResponse
    {
        abort_if($case->is_deleted, 404);

        DB::transaction(function () use ($request, $case): void {
            $case->update(['is_deleted' => true]);
            $this->log($request, $case, 'DELETE', "問い合わせ「{$case->subject}」を論理削除");
        });

        return response()->json(['message' => '問い合わせを削除しました。']);
    }

    private function loadRelations(SupportCase $case): SupportCase
    {
        return $case->load([
            'account:id,account_code,account_name',
            'contact:id,account_id,last_name,first_name,email',
            'status:id,status_name,sort_order',
            'owner:id,name',
        ]);
    }

    private function withClosedAt(array $data, ?SupportCase $case = null): array
    {
        $statusName = DB::table('case_statuses')->where('id', $data['status_id'])->value('status_name');
        $data['closed_at'] = $statusName === 'resolved' ? ($case?->closed_at ?? now()) : null;

        return $data;
    }

    private function log(Request $request, SupportCase $case, string $action, string $description): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $request->user()?->id,
            'target_table' => 'cases',
            'target_id' => $case->id,
            'action_type' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
