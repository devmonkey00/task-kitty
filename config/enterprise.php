<?php

return [

    'roles' => [
        'super_admin' => 'Super Admin',
        'hr_admin' => 'HR Admin',
        'project_manager' => 'Project Manager',
        'team_lead' => 'Team Lead',
        'employee' => 'Employee',
    ],

    'redis' => [
        'enabled' => env('REDIS_ENABLED', false),
    ],

    'broadcasting' => [
        'driver' => env('BROADCAST_CONNECTION', 'log'),
    ],

    'attendance' => [
        'standard_work_minutes' => (int) env('ATTENDANCE_STANDARD_MINUTES', 480),
        'require_geofence' => env('ATTENDANCE_REQUIRE_GEOFENCE', true),
    ],

];
