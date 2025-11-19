
#!/bin/bash

# Complete School ERP Scaffolding Script for Laravel InfyOm Generator
# This script generates Models, Controllers, Views, Routes, and Tests

echo "=== Starting School ERP Scaffolding ==="
echo "This will generate Models, Controllers, Views, Routes, and Tests for all tables"
echo ""

# Function to run scaffold command with error handling
scaffold_table() {
    local model_name=$1
    local table_name=$2

    echo "Scaffolding $model_name from table $table_name..."

    # Try different command variations based on InfyOm version
    if php artisan infyom:scaffold $model_name --fromTable --table=$table_name 2>/dev/null; then
        echo "✓ Successfully scaffolded $model_name"
    elif php artisan make:scaffold $model_name --fromTable --table=$table_name 2>/dev/null; then
        echo "✓ Successfully scaffolded $model_name (using make:scaffold)"
    elif php artisan infyom:scaffold $model_name --fromTable --table=$table_name 2>/dev/null; then
        echo "✓ Successfully scaffolded $model_name (using --from-table)"
    else
        echo "✗ Failed to scaffold $model_name - trying manual approach"
        # Fallback to model generation only
        php artisan infyom:model $model_name --fromTable --table=$table_name 2>/dev/null || echo "✗ Model generation also failed for $model_name"
    fi

    echo ""
}

# Phase 1: Core Independent Tables
echo "=== PHASE 1: Core Independent Tables ==="

scaffold_table "Role" "roles"
scaffold_table "Permission" "permissions"
scaffold_table "Department" "departments"
scaffold_table "AcademicYear" "academic_years"
scaffold_table "SchoolClass" "classes"
scaffold_table "Section" "sections"
scaffold_table "Classroom" "classrooms"
scaffold_table "Subject" "subjects"
scaffold_table "Period" "periods"
scaffold_table "ExamType" "exam_types"
scaffold_table "GradingScale" "grading_scales"
scaffold_table "FeeCategory" "fee_categories"
scaffold_table "FeeDiscount" "fee_discounts"
scaffold_table "BookCategory" "book_categories"
scaffold_table "InventoryCategory" "inventory_categories"
scaffold_table "Supplier" "suppliers"
scaffold_table "ExpenseCategory" "expense_categories"
scaffold_table "IncomeCategory" "income_categories"
scaffold_table "BankAccount" "bank_accounts"
scaffold_table "JobPosition" "job_positions"
scaffold_table "LeaveType" "leave_types"
scaffold_table "Setting" "settings"
scaffold_table "SmsTemplate" "sms_templates"
scaffold_table "EmailTemplate" "email_templates"

# Phase 2: User Management Tables
echo "=== PHASE 2: User Management Tables ==="

scaffold_table "User" "users"
scaffold_table "UserRole" "user_roles"
scaffold_table "RolePermission" "role_permissions"
scaffold_table "Student" "students"
scaffold_table "ParentModel" "parents"
scaffold_table "Staff" "staff"
scaffold_table "StudentParentRelationship" "student_parent_relationship"
scaffold_table "StudentDocument" "student_documents"
scaffold_table "StaffDocument" "staff_documents"

# Phase 3: Academic Structure
echo "=== PHASE 3: Academic Structure ==="

scaffold_table "ClassSection" "class_sections"
scaffold_table "ClassSubject" "class_subjects"
scaffold_table "TeacherSubject" "teacher_subjects"
scaffold_table "StudentClassEnrollment" "student_class_enrollment"

# Phase 4: Attendance & Timetable
echo "=== PHASE 4: Attendance & Timetable ==="

scaffold_table "Timetable" "timetable"
scaffold_table "StudentAttendance" "student_attendance"
scaffold_table "StaffAttendance" "staff_attendance"

# Phase 5: Examination System
echo "=== PHASE 5: Examination System ==="

scaffold_table "Exam" "exams"
scaffold_table "ExamSchedule" "exam_schedules"
scaffold_table "ExamResult" "exam_results"

# Phase 6: Financial Management
echo "=== PHASE 6: Financial Management ==="

scaffold_table "FeeStructure" "fee_structures"
scaffold_table "StudentFeeDiscount" "student_fee_discounts"
scaffold_table "StudentFee" "student_fees"
scaffold_table "FeePayment" "fee_payments"
scaffold_table "Expense" "expenses"
scaffold_table "Income" "income"
scaffold_table "BankTransaction" "bank_transactions"

# Phase 7: Library Management
echo "=== PHASE 7: Library Management ==="

scaffold_table "Book" "books"
scaffold_table "LibraryMember" "library_members"
scaffold_table "BookIssue" "book_issues"

# Phase 8: Inventory Management
echo "=== PHASE 8: Inventory Management ==="

scaffold_table "InventoryItem" "inventory_items"
scaffold_table "InventoryTransaction" "inventory_transactions"

# Phase 9: Transport Management
echo "=== PHASE 9: Transport Management ==="

scaffold_table "Vehicle" "vehicles"
scaffold_table "Route" "routes"
scaffold_table "RouteStop" "route_stops"
scaffold_table "TransportAssignment" "transport_assignments"
scaffold_table "TransportRegistration" "transport_registrations"

# Phase 10: Hostel Management
echo "=== PHASE 10: Hostel Management ==="

scaffold_table "Hostel" "hostels"
scaffold_table "HostelRoom" "hostel_rooms"
scaffold_table "HostelAllocation" "hostel_allocations"
scaffold_table "HostelFee" "hostel_fee"

# Phase 11: Communication & Events
echo "=== PHASE 11: Communication & Events ==="

scaffold_table "Notification" "notifications"
scaffold_table "NotificationRecipient" "notification_recipients"
scaffold_table "Message" "messages"
scaffold_table "Event" "events"
scaffold_table "EventParticipant" "event_participants"

# Phase 12: Assignments & Learning
echo "=== PHASE 12: Assignments & Learning ==="

scaffold_table "Assignment" "assignments"
scaffold_table "AssignmentSubmission" "assignment_submissions"

# Phase 13: HR Management
echo "=== PHASE 13: HR Management ==="

scaffold_table "StaffLeave" "staff_leaves"
scaffold_table "StaffSalary" "staff_salary"
scaffold_table "Payroll" "payroll"

# Phase 14: System Logs
echo "=== PHASE 14: System Logs ==="

scaffold_table "ActivityLog" "activity_logs"
scaffold_table "SystemLog" "system_logs"

echo "=== SCAFFOLDING COMPLETED ==="
echo ""
echo "Next Steps:"
echo "1. Run 'php artisan migrate' if you haven't already"
echo "2. Check generated routes in routes/web.php"
echo "3. Update model relationships"
echo "4. Customize views and controllers as needed"
echo "5. Add proper validation rules"
echo "6. Test the generated CRUD operations"
echo ""
echo "Generated files will be in:"
echo "- Models: app/Models/"
echo "- Controllers: app/Http/Controllers/"
echo "- Views: resources/views/"
echo "- Routes: routes/web.php (check for new additions)"
echo "- Requests: app/Http/Requests/"
echo "- Tests: tests/"
echo ""
echo "=== SCAFFOLD COMPLETE ==="
