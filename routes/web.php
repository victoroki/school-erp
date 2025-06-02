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
Route::resource('inventory-items', App\Http\Controllers\InventoryItemController::class);
Route::resource('routes', App\Http\Controllers\RouteController::class);
Route::resource('route-stops', App\Http\Controllers\RouteStopController::class);