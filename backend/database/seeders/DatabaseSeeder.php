<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $adminRole = $this->upsertId('roles', 'role_name', 'admin', [
                'description' => '全機能を利用可能', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->upsertId('roles', 'role_name', 'sales', [
                'description' => '顧客・商談・活動を利用可能', 'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->upsertId('roles', 'role_name', 'viewer', [
                'description' => '参照のみ可能', 'created_at' => $now, 'updated_at' => $now,
            ]);

            foreach ([
                ['lead', 10, 1],
                ['proposal', 30, 2],
                ['quote', 60, 3],
                ['won', 100, 4],
                ['lost', 0, 5],
            ] as [$name, $probability, $order]) {
                $this->upsertId('opportunity_stages', 'stage_name', $name, [
                    'probability' => $probability,
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ([['new', 1], ['in_progress', 2], ['resolved', 3]] as [$name, $order]) {
                $this->upsertId('case_statuses', 'status_name', $name, [
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->ensureAdministrator($adminRole);
        });
    }

    private function ensureAdministrator(int $adminRole): void
    {
        $email = (string) config('prime_crm.admin.email');
        $admin = User::query()->firstOrNew(['email' => $email]);

        if (! $admin->exists) {
            $password = config('prime_crm.admin.password');
            if (! is_string($password) || $password === '') {
                throw new RuntimeException('初期管理者を作成するにはPRIME_CRM_ADMIN_PASSWORDを設定してください。');
            }
            $admin->password = Hash::make($password);
        }

        $admin->fill([
            'role_id' => $adminRole,
            'name' => (string) config('prime_crm.admin.name'),
            'department' => (string) config('prime_crm.admin.department'),
            'is_active' => true,
        ])->save();
    }

    private function upsertId(string $table, string $key, mixed $value, array $data): int
    {
        DB::table($table)->updateOrInsert([$key => $value], $data);

        return (int) DB::table($table)->where($key, $value)->value('id');
    }
}
