<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->boolean('is_deleted')->default(false)->after('description');
            $table->index(['is_deleted', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            $table->dropIndex(['is_deleted', 'updated_at']);
            $table->dropColumn('is_deleted');
        });
    }
};
