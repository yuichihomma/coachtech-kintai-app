<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StampCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| トップページ
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect('/login');
    }

    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.attendance.list')
        : redirect()->route('attendance.index');
});


/*
|--------------------------------------------------------------------------
| スタッフ用
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'staff'])->group(function () {

    // 打刻画面
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 出勤・退勤
    Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/clock-out', [AttendanceController::class, 'clockOut']);

    // 休憩
    Route::post('/break-start', [AttendanceController::class, 'breakStart']);
    Route::post('/break-end', [AttendanceController::class, 'breakEnd']);

    // 月別一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.show');

    // 修正申請送信
    Route::post('/attendance/{attendance}/apply', [AttendanceController::class, 'apply'])
        ->name('attendance.apply');

    // 自分の申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])
        ->name('attendance.request');
});


/*
|--------------------------------------------------------------------------
| 管理者用
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 管理者ログイン
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest')->group(function () {

        Route::get('/login', [AuthController::class, 'showLogin'])
            ->name('admin.login');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('admin.login.submit');
    });


    /*
    |--------------------------------------------------------------------------
    | 管理画面（auth + admin）
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'admin'])->group(function () {

        // ===== 勤怠一覧 =====
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('admin.attendance.list');

        // ===== スタッフ一覧 =====
        Route::get('/staff/list', [StaffController::class, 'index'])
            ->name('admin.staff.list');

        // ===== スタッフ別勤怠 =====
        Route::get('/attendance/staff/{user}', [AdminAttendanceController::class, 'staff'])
            ->name('admin.attendance.staff');

        // ===== スタッフ別勤怠CSV =====
        Route::get('/attendance/{user}/csv', [AdminAttendanceController::class, 'csv'])
            ->name('admin.attendance.csv');

        // ===== 勤怠詳細 =====
        Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
            ->name('admin.attendance.show');

        // ===== 勤怠更新 =====
        Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
            ->name('admin.attendance.update');


        /*
        |--------------------------------------------------------------------------
        | 打刻修正申請（管理者）
        |--------------------------------------------------------------------------
        */

        // 一覧
        Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
            ->name('admin.stamp_correction_request.list');

        // 詳細（承認画面）
        Route::get('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'show'])
            ->name('admin.stamp_correction_request.show');

        // 承認
        Route::post('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'approve'])
            ->name('admin.stamp_correction_request.approve');

        // 却下
        Route::post('/stamp_correction_request/reject/{id}', [StampCorrectionRequestController::class, 'reject'])
            ->name('admin.stamp_correction_request.reject');
    });
});
