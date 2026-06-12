<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('minutes');
            $table->text('description')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['task_id', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
