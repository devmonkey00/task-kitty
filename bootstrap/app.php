<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        channels: __DIR__.'/../routes/channels.php',
        attributes: ['middleware' => ['web', 'auth']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return redirect()->back()->with(
                    'error',
                    'You are not authorized to perform this action.',
                );
            }
        });

        $businessExceptions = [
            \App\Exceptions\Attendance\AttendanceStateException::class,
            \App\Exceptions\Attendance\GeofenceViolationException::class,
            \App\Exceptions\Tasks\TaskDependencyBlockedException::class,
            \App\Exceptions\Leave\LeaveApprovalException::class,
        ];

        $exceptions->render(function (\Throwable $e, Request $request) use ($businessExceptions) {
            foreach ($businessExceptions as $type) {
                if ($e instanceof $type && ($request->expectsJson() || $request->header('X-Inertia'))) {
                    return redirect()->back()->with('error', $e->getMessage());
                }
            }
        });
    })->create();
