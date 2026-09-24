<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $admin = DB::table('users')->where('email', 'admin@prime-crm.test')->first(['id', 'password']);
        $newPassword = config('prime_crm.admin.password');

        if ($admin
            && is_string($newPassword)
            && $newPassword !== ''
            && Hash::check('password', $admin->password)) {
            DB::table('users')->where('id', $admin->id)->update([
                'password' => Hash::make($newPassword),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // セキュリティ上、以前の初期パスワードへは戻さない。
    }
};
