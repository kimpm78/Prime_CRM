<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('closed_at');
            $table->index(['is_deleted', 'status_id', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['is_deleted', 'status_id', 'opened_at']);
            $table->dropColumn('is_deleted');
        });
    }
};
