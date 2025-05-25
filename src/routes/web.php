<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;

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

Route::get('/', [AttendanceController::class, "index"]);

Route::prefix('admin')->group(function () {
    Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm']);
    Route::post('/admin/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout']);
});


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'attendanceDetail'])->name('attendance.detail');
    Route::post('/at_work', [AttendanceController::class, 'atWork']);
    Route::post('/leaving_work', [AttendanceController::class, 'leavingWork']);
    Route::post('/at_break', [AttendanceController::class, 'atBreak']);
    Route::post('/leaving_break', [AttendanceController::class, 'leavingBreak']);
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('stamp_correction_request.list');
    Route::post('/stamp_correction_request', [AttendanceController::class, 'stampCorrectionRequest']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm']);
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout']);
    Route::get('/attendance/list', [AdminController::class, 'attendanceList']);
});

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
