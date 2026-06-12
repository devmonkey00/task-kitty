<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status')->default('active')->after('date');
            $table->unsignedInteger('total_minutes')->default(0)->after('hours_worked');
            $table->unsignedInteger('overtime_minutes')->default(0)->after('total_minutes');
            $table->foreignId('office_location_id')->nullable()->after('overtime_minutes')->constrained()->nullOnDelete();
            $table->string('clock_in_ip', 45)->nullable()->after('office_location_id');
            $table->string('clock_out_ip', 45)->nullable()->after('clock_in_ip');
            $table->string('browser_fingerprint', 64)->nullable()->after('clock_out_ip');
            $table->decimal('clock_in_latitude', 10, 7)->nullable()->after('browser_fingerprint');
            $table->decimal('clock_in_longitude', 10, 7)->nullable()->after('clock_in_latitude');
            $table->boolean('is_within_geofence')->nullable()->after('clock_in_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['office_location_id']);
            $table->dropColumn([
                'status',
                'total_minutes',
                'overtime_minutes',
                'office_location_id',
                'clock_in_ip',
                'clock_out_ip',
                'browser_fingerprint',
                'clock_in_latitude',
                'clock_in_longitude',
                'is_within_geofence',
            ]);
        });
    }
};
