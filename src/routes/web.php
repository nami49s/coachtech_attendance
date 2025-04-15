<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AdminRequestController;
use Carbon\Carbon;

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth', 'verified')->group(function () {
    //勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
    Route::get('/attendance/working', [AttendanceController::class, 'working'])->name('attendance.working');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::get('/attendance/checkout', [AttendanceController::class, 'checkoutView'])->name('attendance.checkout');
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break.start');
    Route::get('/attendance/break', [AttendanceController::class, 'breakView'])->name('attendance.break');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break.end');
    Route::get('/attendance/request/{attendanceId}', [RequestController::class, 'request'])->name('attendance.request');
    //勤怠一覧画面
    Route::get('/attendance/index', [AttendanceController::class, 'index'])->name('attendance.index');
    //勤怠詳細画面
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    //勤怠修正申請画面
    Route::post('/attendance/request', [RequestController::class, 'storeAttendance'])->name('attendance.request.submit');
    //勤怠修正申請一覧画面
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('index', [AdminAttendanceController::class, 'dashboard'])->name('index');
        Route::get('/staffs', [AdminAttendanceController::class, 'staffs'])->name('staffs');
        Route::get('/admin/show/{attendance}', [AdminAttendanceController::class, 'show'])->name('show');
        Route::get('/admin/monthlyattendance/{user}', [AdminAttendanceController::class, 'monthlyAttendance'])->name('monthlyAttendance');
        Route::post('/admin/attendance/{attendance}/update', [AdminAttendanceController::class, 'update'])->name('attendance.update');
        Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{id}', [AdminRequestController::class, 'show'])->name('requests.show');
        Route::post('/requests/{id}/approve', [AdminRequestController::class, 'approve'])->name('requests.approve');
    });
});
require __DIR__.'/auth.php';