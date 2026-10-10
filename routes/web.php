<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MyActivityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index');
        }

        return redirect()->route('activities.index');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::resource('activities', ActivityController::class)->except('destroy');
    Route::patch('/activities/{activity}/cancel', [ActivityController::class, 'cancel'])->name('activities.cancel');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/my-activities', [MyActivityController::class, 'index'])->name('my-activities.index');

    // ระบบแจ้งเตือน (Notifications)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    Route::prefix('activities/{activity}')->group(function () {
        Route::post('/join', [ParticipationController::class, 'store'])->name('activities.join');
        Route::patch('/cancel-request', [ParticipationController::class, 'cancel'])->name('activities.cancel-request');
        Route::get('/requests', [ParticipationController::class, 'requests'])->name('activities.requests');
        Route::patch('/requests/{participant}/approve', [ParticipationController::class, 'approve'])->name('activities.requests.approve');
        Route::patch('/requests/{participant}/reject', [ParticipationController::class, 'reject'])->name('activities.requests.reject');
        Route::patch('/requests/{participant}/attendance', [ParticipationController::class, 'updateAttendance'])->name('activities.requests.attendance');

        // ส่วนที่ 5: รีวิวและรายงาน
        Route::post('/reviews', [ReviewController::class, 'store'])->name('activities.reviews.store');
        Route::post('/reports', [ReportController::class, 'store'])->name('activities.reports.store');
    });
});

Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/toggle-role', [AdminUserController::class, 'toggleRole'])->name('users.toggle-role');

    // ส่วนที่ 5: ตรวจรายงานและประวัติการดำเนินการ
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}/resolve', [AdminReportController::class, 'resolve'])->name('reports.resolve');
    Route::patch('/activities/{activity}/unhide', [AdminReportController::class, 'unhide'])->name('activities.unhide');
});
