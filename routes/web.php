<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

// Route to clear cache on deployment
Route::get('/clear-all-cache', function() {
    Artisan::call('optimize:clear');
    return "<h1>All Cache Cleared Successfully</h1>";
});

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Auth::routes();

// All authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [App\Http\Controllers\DashboardController::class, 'getData'])->name('dashboard.data');

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
    Route::post('fee-categories/generate-code', [App\Http\Controllers\FeeCategoryController::class, 'generateAutoCode'])->name('fee-categories.generate-code');
    Route::resource('book-categories', App\Http\Controllers\BookCategoryController::class);
    Route::resource('inventory-categories', App\Http\Controllers\InventoryCategoryController::class);
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    Route::resource('bank-accounts', App\Http\Controllers\BankAccountController::class);
    Route::resource('job-positions', App\Http\Controllers\JobPositionController::class);
    Route::resource('leave-types', App\Http\Controllers\LeaveTypeController::class);
    Route::resource('sms-templates', App\Http\Controllers\SmsTemplateController::class)->names('smsTemplates');
    Route::resource('email-templates', App\Http\Controllers\EmailTemplateController::class)->names('emailTemplates');
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('student-parent-relationships', App\Http\Controllers\StudentParentRelationshipController::class);
    Route::resource('student-documents', App\Http\Controllers\StudentDocumentController::class);
    Route::get('student-documents/{id}/download', [App\Http\Controllers\StudentDocumentController::class, 'download'])->name('student-documents.download');
    Route::resource('staff-documents', App\Http\Controllers\StaffDocumentController::class);
    Route::resource('class-sections', App\Http\Controllers\ClassSectionController::class);
    Route::post('class-subjects/bulk-delete', [App\Http\Controllers\ClassSubjectController::class, 'bulkDestroy'])->name('class-subjects.bulk-delete');
    Route::resource('class-subjects', App\Http\Controllers\ClassSubjectController::class);
    Route::resource('teacher-subjects', App\Http\Controllers\TeacherSubjectController::class);
    Route::resource('exams', App\Http\Controllers\ExamController::class);
    Route::resource('exam-schedules', App\Http\Controllers\ExamScheduleController::class);
    Route::get('exam-results/bulk', [App\Http\Controllers\ExamResultController::class, 'bulk'])->name('exam-results.bulk');
    Route::post('exam-results/bulk', [App\Http\Controllers\ExamResultController::class, 'postBulk'])->name('exam-results.bulk.store');
    Route::get('exam-results/import-template', [App\Http\Controllers\ExamResultController::class, 'importTemplate'])->name('exam-results.import-template');
    Route::post('exam-results/import', [App\Http\Controllers\ExamResultController::class, 'importStore'])->name('exam-results.import.store');
    Route::resource('exam-results', App\Http\Controllers\ExamResultController::class);
    Route::resource('fee-structures', App\Http\Controllers\FeeStructureController::class);
    Route::resource('books', App\Http\Controllers\BookController::class);
    Route::resource('book-issues', App\Http\Controllers\BookIssueController::class);
    Route::get('library/book-issues/{id}/return', [App\Http\Controllers\BookIssueController::class, 'returnModal'])->name('book-issues.return-modal');
    Route::post('library/book-issues/{id}/return', [App\Http\Controllers\BookIssueController::class, 'returnBook'])->name('book-issues.return');
    Route::get('library/dashboard', [App\Http\Controllers\LibraryDashboardController::class, 'index'])->name('library.dashboard');
    Route::resource('inventory-items', App\Http\Controllers\InventoryItemController::class);

    // Inventory Management Routes
    Route::name('inventory.')->prefix('inventory')->group(function () {
        Route::get('/', [App\Http\Controllers\InventoryController::class, 'dashboard'])->name('dashboard');
        Route::get('/add-stock', [App\Http\Controllers\InventoryController::class, 'showAddStockForm'])->name('add-stock.form');
        Route::post('/add-stock', [App\Http\Controllers\InventoryController::class, 'addStock'])->name('add-stock');
        Route::get('/issue-stock', [App\Http\Controllers\InventoryController::class, 'showIssueStockForm'])->name('issue-stock.form');
        Route::post('/issue-stock', [App\Http\Controllers\InventoryController::class, 'issueStock'])->name('issue-stock');
        Route::get('/adjust-stock', [App\Http\Controllers\InventoryController::class, 'showAdjustStockForm'])->name('adjust-stock.form');
        Route::post('/adjust-stock', [App\Http\Controllers\InventoryController::class, 'adjustStock'])->name('adjust-stock');
        Route::get('/stock-movement-history', [App\Http\Controllers\InventoryController::class, 'stockMovementHistory'])->name('stock-movement-history');
        
        // Requisitions
        Route::resource('requisitions', App\Http\Controllers\RequisitionController::class);
        Route::post('requisitions/{id}/approve', [App\Http\Controllers\RequisitionController::class, 'approve'])->name('requisitions.approve');
        
        // Purchase Orders
        Route::resource('purchase-orders', App\Http\Controllers\PurchaseOrderController::class);
        Route::post('purchase-orders/{id}/receive', [App\Http\Controllers\PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    });
    
    Route::resource('routes', App\Http\Controllers\RouteController::class);
    Route::resource('route-stops', App\Http\Controllers\RouteStopController::class)->names('routeStops');
    Route::resource('vehicles', App\Http\Controllers\VehicleController::class);
    Route::resource('transport-assignments', App\Http\Controllers\TransportAssignmentController::class);
    Route::resource('transport-registrations', App\Http\Controllers\TransportRegistrationController::class);
    Route::resource('student-transport-assignments', App\Http\Controllers\StudentTransportAssignmentController::class);
    Route::get('api/routes/{routeId}/stops', [App\Http\Controllers\StudentTransportAssignmentController::class, 'getStopsByRoute'])->name('api.routes.stops');
    
    Route::prefix('transportation')->name('transportation.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\TransportDashboardController::class, 'index'])->name('dashboard');
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\TransportReportController::class, 'index'])->name('index');
            Route::get('/route-wise', [App\Http\Controllers\TransportReportController::class, 'routeWiseStudentList'])->name('route-wise');
            Route::get('/occupancy', [App\Http\Controllers\TransportReportController::class, 'occupancyReport'])->name('occupancy');
        });
    });

    // Communication Management Routes
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\CommunicationDashboardController::class, 'index'])->name('dashboard');
        Route::get('/compose', [App\Http\Controllers\CommunicationController::class, 'compose'])->name('compose');
        Route::post('/send', [App\Http\Controllers\CommunicationController::class, 'send'])->name('send');
        Route::get('/history', [App\Http\Controllers\CommunicationController::class, 'history'])->name('history.index');
        Route::get('/history/{id}', [App\Http\Controllers\CommunicationController::class, 'showHistory'])->name('history.show');
        Route::get('/api/template/{type}/{id}', [App\Http\Controllers\CommunicationController::class, 'getTemplate']);
    });

    Route::resource('notifications', App\Http\Controllers\NotificationController::class);
    Route::resource('messages', App\Http\Controllers\MessageController::class);
    Route::resource('parents', App\Http\Controllers\ParentsController::class);
    Route::resource('hostels', App\Http\Controllers\HostelController::class);
    Route::prefix('hostel')->name('hostel.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\HostelDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [App\Http\Controllers\HostelReportController::class, 'index'])->name('reports');
        Route::get('/vacancy-report', [App\Http\Controllers\HostelReportController::class, 'vacancyReport'])->name('vacancy-report');
        Route::get('/student-list', [App\Http\Controllers\HostelReportController::class, 'studentList'])->name('student-list');
    });
    
    Route::resource('hostel-rooms', App\Http\Controllers\HostelRoomController::class);
    Route::resource('hostel-allocations', App\Http\Controllers\HostelAllocationController::class);
    Route::post('hostel-allocations/{id}/checkout', [App\Http\Controllers\HostelAllocationController::class, 'checkout'])->name('hostel-allocations.checkout');
    Route::get('hostel-allocations/bulk', [App\Http\Controllers\HostelAllocationController::class, 'bulkForm'])->name('hostel-allocations.bulk-form');
    Route::post('hostel-allocations/bulk', [App\Http\Controllers\HostelAllocationController::class, 'bulkStore'])->name('hostel-allocations.bulk-store');
    Route::get('hostel-allocations/{id}/transfer', [App\Http\Controllers\HostelAllocationController::class, 'transferForm'])->name('hostel-allocations.transfer-form');
    Route::post('hostel-allocations/{id}/transfer', [App\Http\Controllers\HostelAllocationController::class, 'transferStore'])->name('hostel-allocations.transfer-store');
    Route::resource('payrolls', App\Http\Controllers\PayrollController::class);
    Route::resource('expenses', App\Http\Controllers\ExpensesController::class);
    Route::get('expenses-pending', [App\Http\Controllers\ExpensesController::class, 'pending'])->name('expenses.pending');
    Route::post('expenses/{id}/approve', [App\Http\Controllers\ExpensesController::class, 'approve'])->name('expenses.approve');
    Route::post('expenses/{id}/pay', [App\Http\Controllers\ExpensesController::class, 'markAsPaid'])->name('expenses.pay');
    
    Route::resource('expense-categories', App\Http\Controllers\ExpenseCategoryController::class)->names('expenseCategories');
    Route::resource('income-categories', App\Http\Controllers\IncomeCategoryController::class)->names('incomeCategories');
    Route::resource('library-members', App\Http\Controllers\LibraryMemberController::class);
    // Import Routes
    Route::get('students/import', [App\Http\Controllers\StudentImportController::class, 'index'])->name('students.import');
    Route::post('students/import', [App\Http\Controllers\StudentImportController::class, 'store'])->name('students.import.store');

    // ID Card Routes
    Route::get('students/{id}/id-card', [App\Http\Controllers\StudentIdCardController::class, 'generate'])->name('students.id-card');
    Route::post('students/bulk-id-cards', [App\Http\Controllers\StudentIdCardController::class, 'bulk'])->name('students.bulk-id-cards');

    Route::resource('students', App\Http\Controllers\StudentController::class);
    Route::get('students/ajax/search', [App\Http\Controllers\StudentController::class, 'ajaxSearch'])->name('students.ajax.search');
    Route::post('students/{id}/siblings', [App\Http\Controllers\StudentController::class, 'addSibling'])->name('students.add-sibling');
    Route::get('student-transfer', [App\Http\Controllers\StudentTransferController::class, 'index'])->name('student-transfer.index');
    Route::post('student-transfer', [App\Http\Controllers\StudentTransferController::class, 'store'])->name('student-transfer.store');
    Route::resource('student-class-enrollments', App\Http\Controllers\StudentClassEnrollmentController::class);
    Route::resource('emergency-contacts', App\Http\Controllers\EmergencyContactController::class)->names([
        'index' => 'emergencyContacts.index',
        'create' => 'emergencyContacts.create',
        'store' => 'emergencyContacts.store',
        'show' => 'emergencyContacts.show',
        'edit' => 'emergencyContacts.edit',
        'update' => 'emergencyContacts.update',
        'destroy' => 'emergencyContacts.destroy',
    ]);
    Route::resource('staff', App\Http\Controllers\StaffController::class);

    // Financial Management Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\FinanceDashboardController::class, 'index'])->name('dashboard');
    });

    Route::resource('income', App\Http\Controllers\IncomeController::class);
    Route::resource('bank-transactions', App\Http\Controllers\BankTransactionController::class);
    Route::resource('bank-reconciliations', App\Http\Controllers\BankReconciliationController::class);
    Route::resource('budgets', App\Http\Controllers\BudgetController::class);
    Route::get('budget-vs-actual', [App\Http\Controllers\BudgetController::class, 'vsActual'])->name('budgets.vs-actual');
    
    Route::prefix('financial-reports')->name('financial-reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\FinancialReportController::class, 'index'])->name('index');
        Route::get('/cashflow', [App\Http\Controllers\FinancialReportController::class, 'cashflow'])->name('cashflow');
        Route::get('/p-and-l', [App\Http\Controllers\FinancialReportController::class, 'pAndL'])->name('p-and-l');
    });

    Route::resource('financial-years', App\Http\Controllers\FinancialYearController::class);
    Route::get('audit-trail', [App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit-trail.index');
    
    // Student Management Enhanced Routes
    Route::get('student-dashboard', [App\Http\Controllers\StudentDashboardController::class, 'index'])->name('student-dashboard.index');
    Route::resource('student-documents', App\Http\Controllers\StudentDocumentController::class);
    Route::resource('student-parent-relationships', App\Http\Controllers\StudentParentRelationshipController::class);

    // Attendance Routes
    Route::get('student-attendance', [App\Http\Controllers\StudentAttendanceController::class, 'index'])->name('student-attendance.index');
    Route::post('student-attendance', [App\Http\Controllers\StudentAttendanceController::class, 'store'])->name('student-attendance.store');
    Route::get('student-attendance/report', [App\Http\Controllers\StudentAttendanceController::class, 'report'])->name('student-attendance.report');

    // Promotion Routes
    Route::get('student-promotion', [App\Http\Controllers\StudentPromotionController::class, 'index'])->name('student-promotion.index');
    Route::post('student-promotion', [App\Http\Controllers\StudentPromotionController::class, 'store'])->name('student-promotion.store');



    // Report Routes
    Route::get('student-reports', [App\Http\Controllers\StudentReportController::class, 'index'])->name('student-reports.index');
    Route::get('student-reports/strength', [App\Http\Controllers\StudentReportController::class, 'studentStrength'])->name('student-reports.strength');
    Route::get('student-reports/gender', [App\Http\Controllers\StudentReportController::class, 'genderRatio'])->name('student-reports.gender');
    Route::get('student-reports/attendance', [App\Http\Controllers\StudentReportController::class, 'attendanceSummary'])->name('student-reports.attendance');


    
    // Logging & Incident Routes
    Route::get('disciplinary-records', [App\Http\Controllers\DisciplinaryController::class, 'index'])->name('disciplinary-records.index');
    Route::get('disciplinary-records/create', [App\Http\Controllers\DisciplinaryController::class, 'create'])->name('disciplinary-records.create');
    Route::post('disciplinary-records', [App\Http\Controllers\DisciplinaryController::class, 'store'])->name('disciplinary-records.store');
    Route::patch('disciplinary-records/{id}', [App\Http\Controllers\DisciplinaryController::class, 'update'])->name('disciplinary-records.update');
    
    Route::get('medical-incidents', [App\Http\Controllers\MedicalIncidentController::class, 'index'])->name('medical-incidents.index');
    Route::get('medical-incidents/create', [App\Http\Controllers\MedicalIncidentController::class, 'create'])->name('medical-incidents.create');
    Route::post('medical-incidents', [App\Http\Controllers\MedicalIncidentController::class, 'store'])->name('medical-incidents.store');
    
    // Academic Management Enhanced Routes
    Route::get('academic-dashboard', [App\Http\Controllers\AcademicDashboardController::class, 'index'])->name('academic-dashboard.index');
    Route::get('timetables/teacher', [App\Http\Controllers\TimetableController::class, 'teacherTimetable'])->name('timetables.teacher');
    Route::resource('academic-calendar', App\Http\Controllers\AcademicCalendarController::class);
    Route::get('class-teachers', [App\Http\Controllers\ClassTeacherController::class, 'index'])->name('class-teachers.index');
    Route::patch('class-teachers/{id}', [App\Http\Controllers\ClassTeacherController::class, 'update'])->name('class-teachers.update');
    Route::get('teacher-workload', [App\Http\Controllers\TeacherWorkloadController::class, 'index'])->name('teacher-workload.index');
    
    Route::resource('timetables', App\Http\Controllers\TimetableController::class);
    Route::get('api/subjects/{subjectId}/teachers', [App\Http\Controllers\TimetableController::class, 'getTeachersBySubject'])->name('api.subjects.teachers');
    Route::get('api/academic-years/{academicYearId}/class-sections', [App\Http\Controllers\TimetableController::class, 'getClassSectionsByYear'])->name('api.academic-years.class-sections');


    
    // Fee Management Revamped Routes
    Route::group(['prefix' => 'fees', 'as' => 'fees.'], function () {
        Route::get('/dashboard', [App\Http\Controllers\FeeDashboardController::class, 'index'])->name('dashboard');
        
        // Fee Assignments
        Route::get('/assignments', [App\Http\Controllers\StudentFeeAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [App\Http\Controllers\StudentFeeAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [App\Http\Controllers\StudentFeeAssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('/assignments/{id}', [App\Http\Controllers\StudentFeeAssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::get('/assignments/student/{id}', [App\Http\Controllers\StudentFeeAssignmentController::class, 'studentSummary'])->name('assignments.student-summary');
        Route::get('/assignments/unassigned', [App\Http\Controllers\StudentFeeAssignmentController::class, 'unassigned'])->name('assignments.unassigned');
        Route::get('/assignments/ajax/class-fees', [App\Http\Controllers\StudentFeeAssignmentController::class, 'getFeesByClass'])->name('assignments.ajax.class-fees');
        Route::get('/assignments/ajax/classes-fees', [App\Http\Controllers\StudentFeeAssignmentController::class, 'getFeesByClasses'])->name('assignments.ajax.classes-fees');
        Route::get('/assignments/ajax/auto-preview', [App\Http\Controllers\StudentFeeAssignmentController::class, 'getAutoAssignmentPreview'])->name('assignments.ajax.auto-preview');
        Route::get('/assignments/ajax/all-fees', [App\Http\Controllers\StudentFeeAssignmentController::class, 'getAllFeeStructures'])->name('assignments.ajax.all-fees');

        // Fee Adjustments
        Route::get('/adjustments', [App\Http\Controllers\FeeAdjustmentController::class, 'index'])->name('adjustments.index');
        Route::get('/adjustments/create', [App\Http\Controllers\FeeAdjustmentController::class, 'create'])->name('adjustments.create');
        Route::post('/adjustments', [App\Http\Controllers\FeeAdjustmentController::class, 'store'])->name('adjustments.store');
        Route::get('/adjustments/{id}', [App\Http\Controllers\FeeAdjustmentController::class, 'show'])->name('adjustments.show');
        Route::post('/adjustments/{id}/approve', [App\Http\Controllers\FeeAdjustmentController::class, 'approve'])->name('adjustments.approve');
        Route::post('/adjustments/{id}/reject', [App\Http\Controllers\FeeAdjustmentController::class, 'reject'])->name('adjustments.reject');
        Route::get('/adjustments/pending', [App\Http\Controllers\FeeAdjustmentController::class, 'pendingApprovals'])->name('adjustments.pending');
        Route::get('/adjustments/student/{studentId}', [App\Http\Controllers\FeeAdjustmentController::class, 'studentAdjustments'])->name('adjustments.student-adjustments');
        Route::get('/adjustments/{id}/audit-log', [App\Http\Controllers\FeeAdjustmentController::class, 'auditLog'])->name('adjustments.audit-log');
        Route::get('/adjustments/ajax/student-fees', [App\Http\Controllers\FeeAdjustmentController::class, 'getFeeAssignmentsForStudent'])->name('adjustments.ajax.student-fees');

        // Terms
        Route::get('/terms', [App\Http\Controllers\TermController::class, 'index'])->name('terms.index');
        Route::get('/terms/create', [App\Http\Controllers\TermController::class, 'create'])->name('terms.create');
        Route::post('/terms', [App\Http\Controllers\TermController::class, 'store'])->name('terms.store');
        Route::get('/terms/{id}', [App\Http\Controllers\TermController::class, 'show'])->name('terms.show');
        Route::get('/terms/{id}/edit', [App\Http\Controllers\TermController::class, 'edit'])->name('terms.edit');
        Route::put('/terms/{id}', [App\Http\Controllers\TermController::class, 'update'])->name('terms.update');
        Route::delete('/terms/{id}', [App\Http\Controllers\TermController::class, 'destroy'])->name('terms.destroy');
        Route::post('/terms/{id}/activate', [App\Http\Controllers\TermController::class, 'activate'])->name('terms.activate');

        // Discount Schemes
        Route::resource('discounts', App\Http\Controllers\DiscountSchemeController::class);
        
        // Reports
        Route::get('/reports/expected-revenue', [App\Http\Controllers\FeeReportsController::class, 'expectedRevenue'])->name('reports.expected-revenue');
        Route::get('/reports/assignment-status', [App\Http\Controllers\FeeReportsController::class, 'assignmentStatus'])->name('reports.assignment-status');
        Route::get('/reports/discount-summary', [App\Http\Controllers\FeeReportsController::class, 'discountSummary'])->name('reports.discount-summary');
        Route::get('/reports/export/expected-revenue/pdf', [App\Http\Controllers\FeeReportsController::class, 'exportExpectedRevenuePdf'])->name('reports.export.expected-revenue.pdf');
        Route::get('/reports/export/assignment-status/pdf', [App\Http\Controllers\FeeReportsController::class, 'exportAssignmentStatusPdf'])->name('reports.export.assignment-status.pdf');
        Route::get('/reports/export/discount-summary/pdf', [App\Http\Controllers\FeeReportsController::class, 'exportDiscountSummaryPdf'])->name('reports.export.discount-summary.pdf');
    });

    // Legacy Fee Management (Collection) - Kept/Modified for integration
    Route::get('fee-management', [App\Http\Controllers\FeeManagementController::class, 'index'])->name('fee-management.index');
    Route::get('fee-management/{id}', [App\Http\Controllers\FeeManagementController::class, 'show'])->name('fee-management.show');
    Route::get('fee-management/{id}/collect-payment', [App\Http\Controllers\FeeManagementController::class, 'collectPayment'])->name('fee-management.collect-payment');
    Route::post('fee-management/{id}/store-payment', [App\Http\Controllers\FeeManagementController::class, 'storePayment'])->name('fee-management.store-payment');
    Route::get('fee-management/{id}/print', [App\Http\Controllers\FeeManagementController::class, 'print'])->name('fee-management.print');
    Route::get('fee-management/export/pdf', [App\Http\Controllers\FeeManagementController::class, 'exportPdf'])->name('fee-management.export-pdf');
    Route::get('fee-management/export/excel', [App\Http\Controllers\FeeManagementController::class, 'exportExcel'])->name('fee-management.export-excel');

    // Examination Management Enhanced Routes
    Route::get('exam-dashboard', [App\Http\Controllers\ExamDashboardController::class, 'index'])->name('exam-dashboard.index');
    Route::resource('assessment-types', App\Http\Controllers\AssessmentTypeController::class);
    Route::resource('exam-rooms', App\Http\Controllers\ExamRoomController::class);
    Route::get('grade-book', [App\Http\Controllers\GradeBookController::class, 'index'])->name('grade-book.index');
    Route::get('mark-sheets', [App\Http\Controllers\MarkSheetController::class, 'index'])->name('mark-sheets.index');
    Route::get('marks-approval', [App\Http\Controllers\MarksApprovalController::class, 'index'])->name('marks-approval.index');
    Route::post('marks-approval/approve', [App\Http\Controllers\MarksApprovalController::class, 'approve'])->name('marks-approval.approve');
    Route::get('cbc-assessments', [App\Http\Controllers\CompetencyAssessmentController::class, 'index'])->name('cbc-assessments.index');
    Route::post('cbc-assessments', [App\Http\Controllers\CompetencyAssessmentController::class, 'store'])->name('cbc-assessments.store');
    Route::get('exam-reports/individual/{exam_id}/{student_id}', [App\Http\Controllers\ExamReportController::class, 'individual'])->name('exam-reports.individual');
    Route::get('exam-reports/generate', [App\Http\Controllers\ExamReportController::class, 'generate'])->name('exam-reports.generate');
    Route::resource('report-card-templates', App\Http\Controllers\ReportCardTemplateController::class);
    Route::get('bulk-report-cards', [App\Http\Controllers\ExamReportController::class, 'bulk'])->name('exam-reports.bulk');
    Route::get('exam-analysis/performance', [App\Http\Controllers\ExamAnalysisController::class, 'performance'])->name('exam-analysis.performance');
    Route::get('exam-analysis/subject', [App\Http\Controllers\ExamAnalysisController::class, 'subject'])->name('exam-analysis.subject');
    Route::get('exam-analysis/rankings', [App\Http\Controllers\ExamAnalysisController::class, 'rankings'])->name('exam-analysis.rankings');
    Route::resource('learning-areas', App\Http\Controllers\LearningAreaController::class);
    Route::resource('strands', App\Http\Controllers\StrandController::class);
    Route::resource('sub-strands', App\Http\Controllers\SubStrandController::class);
    Route::get('competency-assessment', [App\Http\Controllers\CompetencyAssessmentController::class, 'index'])->name('competency-assessment.index');
    // Human Resources Management Revamped Routes
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\HRDashboardController::class, 'index'])->name('dashboard');
        
        // Reports
        Route::get('/reports/headcount', [App\Http\Controllers\HRReportController::class, 'headcount'])->name('reports.headcount');
        Route::get('/reports/payroll', [App\Http\Controllers\HRReportController::class, 'payroll'])->name('reports.payroll');
        Route::get('/reports/leave', [App\Http\Controllers\HRReportController::class, 'leave'])->name('reports.leave');
        Route::get('/reports/attendance', [App\Http\Controllers\HRReportController::class, 'attendance'])->name('reports.attendance');
        
        // Onboarding
        Route::get('/onboarding', [App\Http\Controllers\StaffOnboardingController::class, 'index'])->name('onboarding');
        Route::get('/onboarding/{id}', [App\Http\Controllers\StaffOnboardingController::class, 'show'])->name('onboarding.show');
        Route::post('/onboarding/{id}/complete/{item}', [App\Http\Controllers\StaffOnboardingController::class, 'completeItem'])->name('onboarding.complete-item');

        // Exit
        Route::get('/exit', [App\Http\Controllers\StaffExitController::class, 'index'])->name('exit');
        Route::get('/exit/create/{staff}', [App\Http\Controllers\StaffExitController::class, 'create'])->name('exit.create');
        Route::post('/exit', [App\Http\Controllers\StaffExitController::class, 'store'])->name('exit.store');

        // Staff Directory
        Route::get('staff-directory', [App\Http\Controllers\StaffController::class, 'directory'])->name('staff.directory');
    });

    // Enhanced Resource Routes
    Route::resource('leave-applications', App\Http\Controllers\LeaveApplicationController::class);
    Route::post('leave-applications/{id}/approve', [App\Http\Controllers\LeaveApplicationController::class, 'approve'])->name('leave-applications.approve');
    Route::post('leave-applications/{id}/reject', [App\Http\Controllers\LeaveApplicationController::class, 'reject'])->name('leave-applications.reject');

    Route::resource('staff-attendance', App\Http\Controllers\StaffAttendanceController::class);
    
    // Payroll Processing Wizard
    Route::prefix('payroll-processing')->name('payroll-processing.')->group(function() {
        Route::get('/', [App\Http\Controllers\PayrollProcessingController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\PayrollProcessingController::class, 'create'])->name('create');
        Route::post('/calculate', [App\Http\Controllers\PayrollProcessingController::class, 'calculate'])->name('calculate');
        Route::get('/review/{payroll}', [App\Http\Controllers\PayrollProcessingController::class, 'review'])->name('review');
        Route::post('/finalize/{payroll}', [App\Http\Controllers\PayrollProcessingController::class, 'finalize'])->name('finalize');
    });

});

