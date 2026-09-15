<?php

use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileAttendanceController;
use App\Http\Controllers\Mobile\MobileBootstrapController;
use App\Http\Controllers\Mobile\MobileFeeController;
use App\Http\Controllers\Mobile\MobileNoticeController;
use App\Http\Controllers\Mobile\MobileProfileController;
use App\Http\Controllers\Mobile\MobileReportController;
use App\Http\Controllers\Mobile\MobileStudentController;
use App\Http\Controllers\Mobile\MobileExamController;
use App\Http\Controllers\Mobile\MobileDisciplineController;
use App\Http\Controllers\Mobile\MobileTransportController;
use App\Http\Controllers\Mobile\MobileHostelController;
use App\Http\Controllers\Mobile\MobileHomeworkController;
use App\Http\Controllers\Mobile\MobileMedicalController;
use App\Http\Controllers\Mobile\MobileStudentNoticeController;
use App\Http\Controllers\Mobile\MobileTimetableController;
use App\Http\Controllers\Mobile\MobileCalendarController;
use App\Http\Controllers\Mobile\MobileStaffAttendanceController;
use App\Http\Controllers\Mobile\MobileCommunicationController;
use App\Http\Controllers\Mobile\MobileStudentDashboardController;
use App\Http\Controllers\Mobile\MobileParentController;
use App\Http\Controllers\Mobile\MobileTeacherDashboardController;
use App\Http\Controllers\Mobile\MobileParentDashboardController;
use App\Http\Controllers\Mobile\MobileLibraryController;
use App\Http\Controllers\Mobile\MobilePayrollController;
use App\Http\Controllers\Mobile\MobileLogisticsController;
use App\Http\Controllers\Mobile\MobileTeacherClassController;
use App\Http\Controllers\Mobile\MobileAdminDashboardController;
use App\Http\Controllers\Mobile\MobileFinanceSummaryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| These routes serve the React Native mobile app. They use Sanctum
| token-based authentication (not session/cookie). The access token
| is validated via the "mobile:access" ability; refresh tokens use
| "mobile:refresh".
|
*/

// ── Public (no auth) ──────────────────────────────────────────────
Route::post('/auth/login', [MobileAuthController::class, 'login']);

// ── Authenticated ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Token management — refresh uses auth:sanctum only (the access token
    // in the Authorization header authenticates the user; the refresh token
    // is validated in the request body by the controller).
    Route::post('/auth/refresh', [MobileAuthController::class, 'refresh']);
    Route::post('/auth/logout', [MobileAuthController::class, 'logout']);

    // Bootstrap (user identity + roles + permissions + modules + school + term)
    Route::get('/bootstrap', MobileBootstrapController::class)
        ->middleware('abilities:mobile:access');

    // Dashboards
    Route::get('/dashboard', MobileStudentDashboardController::class)
        ->middleware('abilities:mobile:access');
    Route::get('/teacher/dashboard', [MobileTeacherDashboardController::class, '__invoke'])
        ->middleware('abilities:mobile:access');
    Route::get('/teacher/pending-registers', [MobileTeacherDashboardController::class, 'registers'])
        ->middleware('abilities:mobile:access');
    Route::get('/parent/dashboard', MobileParentDashboardController::class)
        ->middleware('abilities:mobile:access');

    // PHASE 3 — Admin "school today" briefing (Owner/Super Admin/Admin).
    Route::get('/admin/dashboard', MobileAdminDashboardController::class)
        ->middleware('abilities:mobile:access');

    // PHASE 3 — money snapshot for the Accountant/Admin home (fees.view).
    Route::get('/finance/summary', MobileFinanceSummaryController::class)
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

    // Fees
    Route::get('/fees/summary', [MobileFeeController::class, 'summary'])
        ->middleware('abilities:mobile:access');
    // Fee-workflow student search (identity + fee position only).
    // Static segment BEFORE the {studentId} wildcard so it can't be captured.
    Route::get('/fees/students', [MobileFeeController::class, 'studentSearch'])
        ->middleware('abilities:mobile:access');
    Route::get('/fees/student/{studentId}', [MobileFeeController::class, 'detailed'])
        ->middleware('abilities:mobile:access');
    Route::get('/fees/student/{studentId}/history', [MobileFeeController::class, 'paymentHistory'])
        ->middleware('abilities:mobile:access');
    Route::post('/fees/collect', [MobileFeeController::class, 'collect'])
        ->middleware('abilities:mobile:access');

    // Reports
    Route::get('/reports/report-cards', [MobileReportController::class, 'reportCards'])
        ->middleware('abilities:mobile:access');

    // Notices
    Route::get('/notices', [MobileNoticeController::class, 'index'])
        ->middleware('abilities:mobile:access');

    // Profile
    Route::get('/profile', [MobileProfileController::class, 'show'])
        ->middleware('abilities:mobile:access');

    // Exams & CBC
    Route::get('/exams', [MobileExamController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::get('/exams/{examId}/students', [MobileExamController::class, 'studentsForExam'])
        ->middleware('abilities:mobile:access');
    Route::get('/exams/my-marks', [MobileExamController::class, 'myMarks'])
        ->middleware('abilities:mobile:access');
    Route::post('/exams/marks', [MobileExamController::class, 'storeMarks'])
        ->middleware('abilities:mobile:access');

    // CBC specific
    Route::get('/exams/cbc/structure', [MobileExamController::class, 'cbcStructure'])
        ->middleware('abilities:mobile:access');
    Route::get('/exams/cbc/{studentId}', [MobileExamController::class, 'studentCbc'])
        ->middleware('abilities:mobile:access');
    Route::post('/exams/cbc', [MobileExamController::class, 'storeCbc'])
        ->middleware('abilities:mobile:access');

    // Discipline
    Route::get('/discipline', [MobileDisciplineController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::post('/discipline', [MobileDisciplineController::class, 'store'])
        ->middleware('abilities:mobile:access');

    // Transport
    Route::get('/transport', [MobileTransportController::class, 'index'])
        ->middleware('abilities:mobile:access');

    // Hostel
    Route::get('/hostel', [MobileHostelController::class, 'index'])
        ->middleware('abilities:mobile:access');

    // Logistics (Transport & Hostel)
    Route::get('/logistics/transport', [MobileLogisticsController::class, 'transport'])
        ->middleware('abilities:mobile:access');
    Route::get('/logistics/hostel', [MobileLogisticsController::class, 'hostel'])
        ->middleware('abilities:mobile:access');

    // Parent Management
    Route::get('/parent/children', [MobileParentController::class, 'children'])
        ->middleware('abilities:mobile:access');

// Homework
    Route::get('/homework', [MobileHomeworkController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::post('/homework', [MobileHomeworkController::class, 'store'])
        ->middleware('abilities:mobile:access');

    // Teacher assigned classes (homework/marks dropdowns)
    Route::get('/teacher/classes', MobileTeacherClassController::class)
        ->middleware('abilities:mobile:access');

    // Medical records
    Route::get('/medical', [MobileMedicalController::class, 'index'])
        ->middleware('abilities:mobile:access');

    // Timetable
    Route::get('/timetable', [MobileTimetableController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::get('/timetable/today', [MobileTimetableController::class, 'today'])
        ->middleware('abilities:mobile:access');

    // Calendar
    Route::get('/calendar', [MobileCalendarController::class, 'index'])
        ->middleware('abilities:mobile:access');

    // Staff Attendance
    Route::post('/attendance/staff/clock-in', [MobileStaffAttendanceController::class, 'clockIn'])
        ->middleware('abilities:mobile:access');
    Route::post('/attendance/staff/clock-out', [MobileStaffAttendanceController::class, 'clockOut'])
        ->middleware('abilities:mobile:access');
    Route::get('/attendance/staff/my-history', [MobileStaffAttendanceController::class, 'myHistory'])
        ->middleware('abilities:mobile:access');

    // Library
    Route::get('/library/my-books', [MobileLibraryController::class, 'myBorrowedBooks'])
        ->middleware('abilities:mobile:access');
    Route::get('/library/catalog', [MobileLibraryController::class, 'catalog'])
        ->middleware('abilities:mobile:access');

    // Payroll
    Route::get('/payroll/history', [MobilePayrollController::class, 'history'])
        ->middleware('abilities:mobile:access');
    Route::get('/payroll/latest', [MobilePayrollController::class, 'latest'])
        ->middleware('abilities:mobile:access');

    // Communication
    Route::get('/messages', [MobileCommunicationController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::post('/messages/send', [MobileCommunicationController::class, 'send'])
        ->middleware('abilities:mobile:access');
    Route::get('/notifications', [MobileCommunicationController::class, 'notifications'])
        ->middleware('abilities:mobile:access');

    // Student notices (teacher posts, parent/student views)
    Route::get('/student-notices', [MobileStudentNoticeController::class, 'index'])
        ->middleware('abilities:mobile:access');
    Route::post('/student-notices', [MobileStudentNoticeController::class, 'store'])
        ->middleware('abilities:mobile:access');
});