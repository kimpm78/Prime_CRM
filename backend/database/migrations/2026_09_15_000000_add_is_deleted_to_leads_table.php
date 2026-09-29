<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->boolean('is_deleted')->default(false)->after('converted_account_id');
            $table->index(['is_deleted', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['is_deleted', 'updated_at']);
            $table->dropColumn('is_deleted');
        });
    }
};
