<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('tier');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['leave_request_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};
