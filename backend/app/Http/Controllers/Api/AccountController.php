<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $accounts = Account::query()
            ->active()
            ->with('owner:id,name')
            ->withCount(['contacts', 'opportunities'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $pattern = "%{$search}%";
                    $query->whereRaw('LOWER(account_name) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(account_code) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(industry) LIKE LOWER(?)', [$pattern])
                        ->orWhereRaw('LOWER(phone_number) LIKE LOWER(?)', [$pattern]);
                });
            })
            ->latest()
            ->paginate(min((int) $request->query('per_page', 10), 50));

        return response()->json($accounts);
    }

    public function store(AccountRequest $request): JsonResponse
    {
        $account = DB::transaction(function () use ($request) {
            $account = Account::create($request->validated());
            $this->log($account, 'INSERT', "取引先「{$account->account_name}」を登録");

            return $account;
        });

        return response()->json(['message' => '取引先を登録しました。', 'data' => $account->load('owner:id,name')], 201);
    }

    public function show(Account $account): JsonResponse
    {
        abort_if($account->is_deleted, 404);

        return response()->json(['data' => $account->load(['owner:id,name', 'contacts'])]);
    }

    public function update(AccountRequest $request, Account $account): JsonResponse
    {
        abort_if($account->is_deleted, 404);
        DB::transaction(function () use ($request, $account) {
            $account->update($request->validated());
            $this->log($account, 'UPDATE', "取引先「{$account->account_name}」を更新");
        });

        return response()->json(['message' => '取引先情報を更新しました。', 'data' => $account->fresh()->load('owner:id,name')]);
    }

    public function destroy(Account $account): JsonResponse
    {
        abort_if($account->is_deleted, 404);
        DB::transaction(function () use ($account) {
            $account->update(['is_deleted' => true]);
            $this->log($account, 'DELETE', "取引先「{$account->account_name}」を論理削除");
        });

        return response()->json(['message' => '取引先を削除しました。']);
    }

    private function log(Account $account, string $action, string $description): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $account->owner_user_id,
            'target_table' => 'accounts',
            'target_id' => $account->id,
            'action_type' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
