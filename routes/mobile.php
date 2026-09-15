<?php

use App\Http\Controllers\Mobile\MobileAttendanceController;
use App\Http\Controllers\Mobile\MobileHomeworkController;
use App\Http\Controllers\Mobile\MobileStudentController;
use App\Http\Controllers\Mobile\MobileTeacherDashboardController;
use App\Http\Controllers\Mobile\MobileTimetableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes — Phase 2 (Teacher Daily Experience)
|--------------------------------------------------------------------------
|
| These routes serve the teacher-focused daily workflow of the React Native
| mobile app: the teacher dashboard, pending attendance registers, homework
| management and the timetable feed. They use Sanctum token-based auth, with
| the "mobile:access" ability required on every route.
|
*/

// ── Authenticated ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Teacher dashboard
    Route::get('/teacher/dashboard', [MobileTeacherDashboardController::class, '__invoke'])
        ->middleware('abilities:mobile:access');
    Route::get('/teacher/pending-registers', [MobileTeacherDashboardController::class, 'registers'])
        ->middleware('abilities:mobile:access');

    // Students
    Route::get('/students', [MobileStudentController::class, 'index'])
        ->middleware('abilities:mobile:access');

    // Attendance
    Route::get('/attendance', [MobileAttendanceController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::get('/attendance/mine', [MobileAttendanceController::class, 'mine'])
        ->middleware('abilities:mobile:access');
    Route::post('/attendance', [MobileAttendanceController::class, 'store'])
        ->middleware('abilities:mobile:access');

    // Homework
    Route::get('/homework', [MobileHomeworkController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::post('/homework', [MobileHomeworkController::class, 'store'])
        ->middleware('abilities:mobile:access');

    // Timetable
    Route::get('/timetable', [MobileTimetableController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::get('/timetable/today', [MobileTimetableController::class, 'today'])
        ->middleware('abilities:mobile:access');
});
