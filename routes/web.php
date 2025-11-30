<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::resource('roles', App\Http\Controllers\RoleController::class);
Route::resource('permissions', App\Http\Controllers\PermissionController::class);
Route::resource('departments', App\Http\Controllers\DepartmentController::class);
Route::resource('academic-years', App\Http\Controllers\AcademicYearController::class);
Route::resource('school-classes', App\Http\Controllers\SchoolClassController::class);
Route::resource('sections', App\Http\Controllers\SectionController::class);
Route::resource('classrooms', App\Http\Controllers\ClassroomController::class);
Route::resource('subjects', App\Http\Controllers\SubjectController::class);
Route::resource('periods', App\Http\Controllers\PeriodController::class);
Route::resource('exam-types', App\Http\Controllers\ExamTypeController::class);
Route::resource('grading-scales', App\Http\Controllers\GradingScaleController::class);
Route::resource('fee-categories', App\Http\Controllers\FeeCategoryController::class);
Route::resource('book-categories', App\Http\Controllers\BookCategoryController::class);
Route::resource('inventory-categories', App\Http\Controllers\InventoryCategoryController::class);
Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
Route::resource('expense-categories', App\Http\Controllers\ExpenseCategoryController::class);
Route::resource('income-categories', App\Http\Controllers\IncomeCategoryController::class);
Route::resource('bank-accounts', App\Http\Controllers\BankAccountController::class);
Route::resource('job-positions', App\Http\Controllers\JobPositionController::class);
Route::resource('leave-types', App\Http\Controllers\LeaveTypeController::class);
Route::resource('sms-templates', App\Http\Controllers\SmsTemplateController::class);
Route::resource('email-templates', App\Http\Controllers\EmailTemplateController::class);
Route::resource('user-roles', App\Http\Controllers\UserRoleController::class);
Route::resource('role-permissions', App\Http\Controllers\RolePermissionController::class);
Route::resource('student-parent-relationships', App\Http\Controllers\StudentParentRelationshipController::class);
Route::resource('student-documents', App\Http\Controllers\StudentDocumentController::class);
Route::get('student-documents/{id}/download', [App\Http\Controllers\StudentDocumentController::class, 'download'])->name('student-documents.download');
Route::resource('staff-documents', App\Http\Controllers\StaffDocumentController::class);
Route::resource('class-sections', App\Http\Controllers\ClassSectionController::class);
Route::resource('class-subjects', App\Http\Controllers\ClassSubjectController::class);
Route::resource('teacher-subjects', App\Http\Controllers\TeacherSubjectController::class);
Route::resource('exams', App\Http\Controllers\ExamController::class);
Route::resource('exam-schedules', App\Http\Controllers\ExamScheduleController::class);
Route::resource('exam-results', App\Http\Controllers\ExamResultController::class);
Route::resource('fee-structures', App\Http\Controllers\FeeStructureController::class);
Route::resource('student-fee-discounts', App\Http\Controllers\StudentFeeDiscountController::class);
Route::resource('books', App\Http\Controllers\BookController::class);
Route::resource('book-issues', App\Http\Controllers\BookIssueController::class);
Route::resource('inventory-items', App\Http\Controllers\InventoryItemController::class);
Route::resource('routes', App\Http\Controllers\RouteController::class);
Route::resource('route-stops', App\Http\Controllers\RouteStopController::class);
Route::resource('vehicles', App\Http\Controllers\VehicleController::class);
Route::resource('transport-assignments', App\Http\Controllers\TransportAssignmentController::class);
Route::resource('transport-registrations', App\Http\Controllers\TransportRegistrationController::class);
Route::resource('notifications', App\Http\Controllers\NotificationController::class);
Route::resource('messages', App\Http\Controllers\MessageController::class);
Route::resource('parents', App\Http\Controllers\ParentsController::class);
Route::resource('hostels', App\Http\Controllers\HostelController::class);
Route::resource('hostel-allocations', App\Http\Controllers\HostelAllocationController::class);
Route::resource('hostel-rooms', App\Http\Controllers\HostelRoomController::class);
Route::resource('payrolls', App\Http\Controllers\PayrollController::class);
Route::resource('expenses', App\Http\Controllers\ExpensesController::class);
Route::resource('expense-categories', App\Http\Controllers\ExpenseCategoriesController::class);
Route::resource('library-members', App\Http\Controllers\LibraryMemberController::class);
Route::resource('students', App\Http\Controllers\StudentController::class);
Route::resource('student-class-enrollments', App\Http\Controllers\StudentClassEnrollmentController::class);
Route::resource('staff', App\Http\Controllers\StaffController::class);
Route::resource('timetables', App\Http\Controllers\TimetableController::class);
Route::get('fee-management', [App\Http\Controllers\FeeManagementController::class, 'index'])->name('fee-management.index');
Route::get('fee-management/{id}', [App\Http\Controllers\FeeManagementController::class, 'show'])->name('fee-management.show');
Route::get('fee-management/{id}/print', [App\Http\Controllers\FeeManagementController::class, 'print'])->name('fee-management.print');