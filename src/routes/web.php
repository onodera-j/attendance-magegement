<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => true]);

Route::get('/', [AttendanceController::class, "index"]);

Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])->name('attendance.list');
    Route::post('/at_work', [AttendanceController::class, 'atWork']);
    Route::post('/leaving_work', [AttendanceController::class, 'leavingWork']);
    Route::post('/at_break', [AttendanceController::class, 'atBreak']);
    Route::post('/leaving_break', [AttendanceController::class, 'leavingBreak']);
    // Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('stamp_correction_request.list');
    Route::post('/stamp_correction_request', [AttendanceController::class, 'stampCorrectionRequest']);
});

Route::middleware(['auth:web,admin', 'verified'])->group(function () {

    Route::get('/attendance/{id}', function ($id) {
        if (Auth::guard('admin')->check()) {
            return app(App\Http\Controllers\AdminController::class)->attendanceDetail(request(), $id);
        }

        return app(App\Http\Controllers\AttendanceController::class)->attendanceDetail(request(), $id);
    })->name('attendance.detail');

    Route::get('/stamp_correction_request/list', function () {
        if (Auth::guard('admin')->check()) {
            return app(App\Http\Controllers\AdminController::class)->requestList(request());
        }

        return app(App\Http\Controllers\AttendanceController::class)->requestList(request());
    })->name('stamp_correction_request.list');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendance/list', [AdminController::class, 'attendanceList'])->name('attendance.list');
        Route::get('/staff/list', [AdminController::class, 'staffList'])->name('staff.list');
        Route::get('/attendance/staff/{id}', [AdminController::class, 'staffAttendanceList'])->name('staff.attendance.list');
        Route::post('/export', [AdminController::class, 'exportCsv'])->name('export_csv');
        Route::post('/attendance/correction', [AdminController::class, 'attendanceCorrection']);
    });

});

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/stamp_correction/approve/{attendance_correct_request}', [AdminController::class, 'approveRequest']);
    Route::post('/request/approve', [AdminController::class, 'requestApprove']);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
