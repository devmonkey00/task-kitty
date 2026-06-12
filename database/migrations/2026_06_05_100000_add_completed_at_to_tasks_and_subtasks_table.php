<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('status');
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('is_completed');
        });

        DB::table('tasks')
            ->where('status', 'done')
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('updated_at')]);

        DB::table('subtasks')
            ->where('is_completed', true)
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
