<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeWorkReportController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/hr', [HrDashboardController::class, 'index'])->name('hr.dashboard');

    Route::get('/work-report', [EmployeeWorkReportController::class, 'index'])->name('work-report.index');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');

    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store'])->name('tasks.subtasks.store');
    Route::patch('/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update');
    Route::delete('/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->middleware('throttle:10,1')
        ->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->middleware('throttle:10,1')
        ->name('attendance.clock-out');
    Route::post('/attendance/pause', [AttendanceController::class, 'pause'])
        ->middleware('throttle:10,1')
        ->name('attendance.pause');
    Route::post('/attendance/resume', [AttendanceController::class, 'resume'])
        ->middleware('throttle:10,1')
        ->name('attendance.resume');

    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::patch('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::patch('/leave-requests/{leaveRequest}/deny', [LeaveRequestController::class, 'deny'])->name('leave-requests.deny');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
