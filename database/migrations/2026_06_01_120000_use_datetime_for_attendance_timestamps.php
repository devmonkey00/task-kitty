<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('clock_in')->change();
            $table->dateTime('clock_out')->nullable()->change();
        });

        Schema::table('attendance_segments', function (Blueprint $table) {
            $table->dateTime('started_at')->change();
            $table->dateTime('ended_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('clock_in')->change();
            $table->timestamp('clock_out')->nullable()->change();
        });

        Schema::table('attendance_segments', function (Blueprint $table) {
            $table->timestamp('started_at')->change();
            $table->timestamp('ended_at')->nullable()->change();
        });
    }
};
