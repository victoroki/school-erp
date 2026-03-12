<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Audit and Cleanup Existing Tables
        // We will modify existing tables to match the new schema requirements

        // --- Departments ---
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('departments', 'type')) {
                $table->enum('type', ['academic', 'administrative', 'support'])->default('academic')->after('code');
            }
            if (!Schema::hasColumn('departments', 'budget_allocation')) {
                $table->decimal('budget_allocation', 15, 2)->default(0)->after('hod_id');
            }
            if (!Schema::hasColumn('departments', 'parent_department_id')) {
                $table->integer('parent_department_id')->nullable()->after('hod_id');
            }
            if (!Schema::hasColumn('departments', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
            }
        });

        // --- Job Positions ---
        // Ensure job_positions table exists or modify it (assuming it exists from previous context)
        Schema::table('job_positions', function (Blueprint $table) {
             if (!Schema::hasColumn('job_positions', 'code')) {
                $table->string('code')->nullable()->after('title');
             }
             if (!Schema::hasColumn('job_positions', 'department_id')) {
                 $table->integer('department_id')->nullable();
             }
             if (!Schema::hasColumn('job_positions', 'level')) {
                 $table->string('level')->nullable(); // Grade 1-10 or Junior/Senior
             }
             if (!Schema::hasColumn('job_positions', 'employment_type')) {
                 $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'casual', 'intern'])->default('full_time');
             }
             if (!Schema::hasColumn('job_positions', 'min_qualification')) {
                 $table->string('min_qualification')->nullable();
             }
             if (!Schema::hasColumn('job_positions', 'salary_min')) {
                 $table->decimal('salary_min', 12, 2)->nullable();
             }
             if (!Schema::hasColumn('job_positions', 'salary_max')) {
                 $table->decimal('salary_max', 12, 2)->nullable();
             }
             if (!Schema::hasColumn('job_positions', 'number_of_positions')) {
                 $table->integer('number_of_positions')->default(1);
             }
             if (!Schema::hasColumn('job_positions', 'reports_to_id')) {
                 $table->integer('reports_to_id')->nullable(); // FK to another job position
             }
             if (!Schema::hasColumn('job_positions', 'status')) {
                 $table->enum('status', ['active', 'inactive', 'abolished'])->default('active');
             }
        });

        // --- Staff ---
        Schema::table('staff', function (Blueprint $table) {
            // Personal Info
            if (Schema::hasColumn('staff', 'employee_id') && !Schema::hasColumn('staff', 'employee_number')) {
                $table->renameColumn('employee_id', 'employee_number'); 
            }
            
            if (!Schema::hasColumn('staff', 'national_id_number')) {
                $table->string('national_id_number')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('staff', 'passport_number')) {
                $table->string('passport_number')->nullable()->after('national_id_number');
            }
            if (!Schema::hasColumn('staff', 'marital_status')) {
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            }
            if (!Schema::hasColumn('staff', 'nationality')) {
                $table->string('nationality')->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('staff', 'religion')) {
                $table->string('religion')->nullable()->after('nationality');
            }
            
            // Contact
            if (Schema::hasColumn('staff', 'phone') && !Schema::hasColumn('staff', 'phone_primary')) {
                $table->renameColumn('phone', 'phone_primary');
            }
            if (!Schema::hasColumn('staff', 'phone_secondary')) {
                $table->string('phone_secondary')->nullable();
            }
            if (!Schema::hasColumn('staff', 'personal_email')) {
                $table->string('personal_email')->nullable();
            }
            if (Schema::hasColumn('staff', 'email') && !Schema::hasColumn('staff', 'work_email')) {
                $table->renameColumn('email', 'work_email');
            }
            if (Schema::hasColumn('staff', 'address') && !Schema::hasColumn('staff', 'current_address')) {
                $table->renameColumn('address', 'current_address');
            }
            if (!Schema::hasColumn('staff', 'postal_address')) {
                $table->string('postal_address')->nullable();
            }
            if (!Schema::hasColumn('staff', 'county')) {
                $table->string('county')->nullable();
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable();
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable();
            }
            if (!Schema::hasColumn('staff', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable();
            }
            
            // Medical / Other
            if (!Schema::hasColumn('staff', 'blood_group')) {
                $table->string('blood_group')->nullable();
            }
            if (!Schema::hasColumn('staff', 'disability_status')) {
                $table->string('disability_status')->nullable();
            }
            
            // Employment
            if (!Schema::hasColumn('staff', 'job_position_id')) {
                $table->integer('job_position_id')->nullable();
            }
            if (!Schema::hasColumn('staff', 'employment_type')) {
                $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'casual', 'intern'])->default('full_time');
            }
            if (Schema::hasColumn('staff', 'status') && !Schema::hasColumn('staff', 'employment_status')) {
                $table->renameColumn('status', 'employment_status'); 
            }
            
            if (Schema::hasColumn('staff', 'joining_date') && !Schema::hasColumn('staff', 'date_of_joining')) {
                $table->renameColumn('joining_date', 'date_of_joining');
            }
            
            if (!Schema::hasColumn('staff', 'contract_start_date')) $table->date('contract_start_date')->nullable();
            if (!Schema::hasColumn('staff', 'contract_end_date')) $table->date('contract_end_date')->nullable();
            if (!Schema::hasColumn('staff', 'probation_period_months')) $table->integer('probation_period_months')->default(0);
            if (!Schema::hasColumn('staff', 'probation_end_date')) $table->date('probation_end_date')->nullable();
            if (!Schema::hasColumn('staff', 'confirmation_status')) $table->enum('confirmation_status', ['confirmed', 'on_probation'])->default('on_probation');
            if (!Schema::hasColumn('staff', 'reporting_manager_id')) $table->integer('reporting_manager_id')->nullable(); 
            if (!Schema::hasColumn('staff', 'work_location')) $table->string('work_location')->default('Main Campus');
            if (!Schema::hasColumn('staff', 'work_schedule')) $table->string('work_schedule')->nullable();
            
            // Statutory / Payroll
            if (!Schema::hasColumn('staff', 'tsc_number')) $table->string('tsc_number')->nullable();
            if (!Schema::hasColumn('staff', 'kra_pin')) $table->string('kra_pin')->nullable();
            if (!Schema::hasColumn('staff', 'nhif_number')) $table->string('nhif_number')->nullable();
            if (!Schema::hasColumn('staff', 'nssf_number')) $table->string('nssf_number')->nullable();
            if (!Schema::hasColumn('staff', 'basic_salary')) $table->decimal('basic_salary', 12, 2)->default(0);
            if (!Schema::hasColumn('staff', 'salary_grade')) $table->string('salary_grade')->nullable();
            if (!Schema::hasColumn('staff', 'bank_name')) $table->string('bank_name')->nullable();
            if (!Schema::hasColumn('staff', 'bank_branch')) $table->string('bank_branch')->nullable();
            if (!Schema::hasColumn('staff', 'account_number')) $table->string('account_number')->nullable();
            if (!Schema::hasColumn('staff', 'account_name')) $table->string('account_name')->nullable();
            
            // Leave / Exit
            if (!Schema::hasColumn('staff', 'annual_leave_entitlement')) $table->integer('annual_leave_entitlement')->default(21);
            if (!Schema::hasColumn('staff', 'exit_date')) $table->date('exit_date')->nullable();
            if (!Schema::hasColumn('staff', 'exit_reason')) $table->string('exit_reason')->nullable();
            if (!Schema::hasColumn('staff', 'notes')) $table->text('notes')->nullable();
            if (!Schema::hasColumn('staff', 'created_by')) $table->integer('created_by')->nullable();
            if (!Schema::hasColumn('staff', 'updated_by')) $table->integer('updated_by')->nullable();
            if (!Schema::hasColumn('staff', 'deleted_at')) $table->softDeletes();
        });

        // --- New Tables ---

        if (!Schema::hasTable('staff_qualifications')) {
            Schema::create('staff_qualifications', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->enum('qualification_type', ['high_school', 'diploma', 'degree', 'masters', 'phd', 'professional_cert']);
                $table->string('qualification_level')->nullable();
                $table->string('institution');
                $table->string('course_name');
                $table->string('field_of_study')->nullable();
                $table->year('year_of_graduation');
                $table->string('certificate_number')->nullable();
                $table->string('certificate_file')->nullable();
                $table->boolean('verified')->default(false);
                $table->integer('verified_by')->nullable();
                $table->dateTime('verified_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staff_employment_history')) {
            Schema::create('staff_employment_history', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->boolean('is_internal')->default(false);
                $table->string('position_title');
                $table->string('department')->nullable();
                $table->string('company_name')->nullable(); // If external
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->string('reason_for_leaving')->nullable();
                $table->text('responsibilities')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staff_allowances')) {
            Schema::create('staff_allowances', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->string('allowance_type'); // house, transport, etc.
                $table->string('allowance_name');
                $table->decimal('amount', 12, 2);
                $table->boolean('is_taxable')->default(true);
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staff_deductions')) {
            Schema::create('staff_deductions', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->string('deduction_type'); // loan, advance, etc.
                $table->string('deduction_name');
                $table->decimal('total_amount', 12, 2)->nullable(); // For loans
                $table->decimal('monthly_amount', 12, 2);
                $table->decimal('balance_remaining', 12, 2)->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->enum('status', ['active', 'paid', 'cancelled'])->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Leave Management
        Schema::table('leave_types', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_types', 'code')) $table->string('code')->nullable()->after('name');
            if (!Schema::hasColumn('leave_types', 'days_allocated')) $table->integer('days_allocated')->default(21)->after('code');
            if (!Schema::hasColumn('leave_types', 'paid')) $table->boolean('paid')->default(true);
            if (!Schema::hasColumn('leave_types', 'requires_certificate')) $table->boolean('requires_certificate')->default(false);
            if (!Schema::hasColumn('leave_types', 'max_days_consecutive')) $table->integer('max_days_consecutive')->default(30);
            if (!Schema::hasColumn('leave_types', 'notice_days_required')) $table->integer('notice_days_required')->default(0);
            if (!Schema::hasColumn('leave_types', 'carry_forward')) $table->boolean('carry_forward')->default(false);
            if (!Schema::hasColumn('leave_types', 'max_carry_forward_days')) $table->integer('max_carry_forward_days')->default(0);
            if (!Schema::hasColumn('leave_types', 'gender_specific')) $table->enum('gender_specific', ['all', 'male', 'female'])->default('all');
            if (!Schema::hasColumn('leave_types', 'status')) $table->enum('status', ['active', 'inactive'])->default('active');
        });

        if (!Schema::hasTable('leave_applications')) {
            Schema::create('leave_applications', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->integer('leave_type_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('working_days');
                $table->text('reason');
                $table->integer('relief_staff_id')->nullable();
                $table->text('handover_notes')->nullable();
                $table->string('supporting_document')->nullable();
                $table->enum('application_status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft');
                $table->dateTime('submitted_date')->nullable();
                // Approvals
                $table->enum('hod_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->integer('hod_approved_by')->nullable();
                $table->dateTime('hod_approval_date')->nullable();
                $table->text('hod_comments')->nullable();
                
                $table->enum('hr_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->integer('hr_approved_by')->nullable();
                $table->dateTime('hr_approval_date')->nullable();
                $table->text('hr_comments')->nullable();
                
                $table->enum('final_status', ['approved', 'rejected'])->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
            });
        }
        
        if (!Schema::hasTable('staff_leave_balances')) {
            Schema::create('staff_leave_balances', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->integer('leave_type_id');
                $table->integer('academic_year_id');
                $table->integer('total_entitlement');
                $table->integer('carried_forward')->default(0);
                $table->integer('total_available');
                $table->integer('used')->default(0);
                $table->integer('remaining');
                $table->timestamps();
            });
        }

        // Attendance 
        Schema::table('staff_attendance', function (Blueprint $table) {
             if (Schema::hasColumn('staff_attendance', 'clock_in')) {
                // Rename clock_in to time_in check
                // $table->renameColumn('clock_in', 'time_in'); // Optional, assume we want time_in
             } else if (!Schema::hasColumn('staff_attendance', 'time_in')) {
                 $table->time('time_in')->nullable();
             }

             if (Schema::hasColumn('staff_attendance', 'clock_out')) {
                // $table->renameColumn('clock_out', 'time_out');
             } else if (!Schema::hasColumn('staff_attendance', 'time_out')) {
                 $table->time('time_out')->nullable();
             }
             
             if (!Schema::hasColumn('staff_attendance', 'late_minutes')) $table->integer('late_minutes')->default(0);
             if (!Schema::hasColumn('staff_attendance', 'overtime_hours')) $table->decimal('overtime_hours', 5, 2)->default(0);
             
             if (Schema::hasColumn('staff_attendance', 'remarks') && !Schema::hasColumn('staff_attendance', 'notes')) {
                 $table->renameColumn('remarks', 'notes');
             }
             
             if (!Schema::hasColumn('staff_attendance', 'marked_by')) $table->integer('marked_by')->nullable();
        });

        // Payroll Revamp
        
        if (Schema::hasTable('payroll')) {
            Schema::rename('payroll', 'payroll_details');
        }

        if (!Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->integer('month');
                $table->integer('year');
                $table->integer('academic_year_id')->nullable();
                $table->integer('total_staff_count')->default(0);
                $table->decimal('total_gross_salary', 15, 2)->default(0);
                $table->decimal('total_deductions', 15, 2)->default(0);
                $table->decimal('total_net_salary', 15, 2)->default(0);
                $table->enum('status', ['draft', 'submitted', 'approved', 'paid'])->default('draft');
                $table->integer('prepared_by')->nullable();
                $table->dateTime('prepared_date')->nullable();
                $table->integer('approved_by')->nullable();
                $table->dateTime('approved_date')->nullable();
                $table->date('paid_date')->nullable();
                $table->string('payment_reference')->nullable();
                $table->string('bank_file_path')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('payroll_details', function (Blueprint $table) {
            // Check if renaming happened
            if (Schema::hasColumn('payroll_details', 'payroll_id')) {
                // If it's the PK
                // IDK implementation detail of rename, let's assume valid
            }
             // Renaming Column in MySQL might fail if not checked carefully.
             // Let's assume `id` rename needs checking
             
             if (!Schema::hasColumn('payroll_details', 'payroll_id')) {
                 $table->integer('payroll_id')->nullable()->after('id'); 
             }
             
            if (!Schema::hasColumn('payroll_details', 'total_allowances')) $table->decimal('total_allowances', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'paye_tax')) $table->decimal('paye_tax', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'nhif_deduction')) $table->decimal('nhif_deduction', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'nssf_deduction')) $table->decimal('nssf_deduction', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'total_statutory_deductions')) $table->decimal('total_statutory_deductions', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'total_other_deductions')) $table->decimal('total_other_deductions', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'overtime_pay')) $table->decimal('overtime_pay', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'bonus')) $table->decimal('bonus', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'arrears')) $table->decimal('arrears', 12, 2)->default(0);
            if (!Schema::hasColumn('payroll_details', 'payslip_sent')) $table->boolean('payslip_sent')->default(false);
        });

        if (!Schema::hasTable('payroll_allowances')) {
            Schema::create('payroll_allowances', function (Blueprint $table) {
                $table->id();
                $table->integer('payroll_detail_id');
                $table->string('allowance_type');
                $table->string('allowance_name');
                $table->decimal('amount', 12, 2);
                $table->boolean('is_taxable')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payroll_deductions')) {
            Schema::create('payroll_deductions', function (Blueprint $table) {
                $table->id();
                $table->integer('payroll_detail_id');
                $table->string('deduction_type');
                $table->string('deduction_name');
                $table->decimal('amount', 12, 2);
                $table->timestamps();
            });
        }
        
        // Documents
        Schema::table('staff_documents', function (Blueprint $table) {
             if (!Schema::hasColumn('staff_documents', 'document_category')) $table->string('document_category')->nullable();
             if (!Schema::hasColumn('staff_documents', 'document_number')) $table->string('document_number')->nullable();
             if (!Schema::hasColumn('staff_documents', 'expiry_date')) $table->date('expiry_date')->nullable();
             if (!Schema::hasColumn('staff_documents', 'is_verified')) $table->boolean('is_verified')->default(false);
             if (!Schema::hasColumn('staff_documents', 'verified_by')) $table->integer('verified_by')->nullable();
             if (!Schema::hasColumn('staff_documents', 'verified_date')) $table->dateTime('verified_date')->nullable();
             if (!Schema::hasColumn('staff_documents', 'verification_notes')) $table->text('verification_notes')->nullable();
        });

        // Onboarding & Exit
        if (!Schema::hasTable('staff_onboarding_checklists')) {
            Schema::create('staff_onboarding_checklists', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->string('checklist_item');
                $table->boolean('is_completed')->default(false);
                $table->integer('completed_by')->nullable();
                $table->dateTime('completed_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staff_exit_clearances')) {
            Schema::create('staff_exit_clearances', function (Blueprint $table) {
                $table->id();
                $table->integer('staff_id');
                $table->enum('exit_type', ['resignation', 'termination', 'retirement', 'contract_end', 'death']);
                $table->string('resignation_letter_path')->nullable();
                $table->string('termination_letter_path')->nullable();
                $table->integer('notice_period_days')->default(30);
                $table->date('last_working_day');
                $table->string('exit_reason')->nullable();
                $table->enum('clearance_status', ['in_progress', 'completed'])->default('in_progress');
                $table->boolean('exit_interview_conducted')->default(false);
                $table->text('exit_interview_notes')->nullable();
                $table->decimal('final_settlement_amount', 15, 2)->default(0);
                $table->boolean('final_settlement_paid')->default(false);
                $table->date('final_settlement_date')->nullable();
                $table->boolean('certificate_of_service_issued')->default(false);
                $table->boolean('reference_letter_issued')->default(false);
                $table->boolean('user_account_disabled')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('exit_clearance_items')) {
            Schema::create('exit_clearance_items', function (Blueprint $table) {
                $table->id();
                $table->integer('exit_clearance_id'); // FK to staff_exit_clearances
                $table->string('clearance_item');
                $table->integer('responsible_person')->nullable(); // User ID
                $table->boolean('is_cleared')->default(false);
                $table->integer('cleared_by')->nullable();
                $table->dateTime('cleared_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('exit_clearance_items');
        Schema::dropIfExists('staff_exit_clearances');
        Schema::dropIfExists('staff_onboarding_checklists');
        Schema::dropIfExists('payroll_deductions');
        Schema::dropIfExists('payroll_allowances');
        // Schema::dropIfExists('payroll_details'); // Risks data loss
        // Schema::dropIfExists('payrolls');
        Schema::dropIfExists('staff_leave_balances');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('staff_deductions');
        Schema::dropIfExists('staff_allowances');
        Schema::dropIfExists('staff_employment_history');
        Schema::dropIfExists('staff_qualifications');
    }
};
