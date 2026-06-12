<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('estimated_minutes')->nullable()->after('due_date');
            $table->unsignedInteger('actual_minutes')->default(0)->after('estimated_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['estimated_minutes', 'actual_minutes']);
        });
    }
};
