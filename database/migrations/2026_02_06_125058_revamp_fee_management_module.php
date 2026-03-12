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
        // 1. Enhance fee_categories
        Schema::table('fee_categories', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('fee_categories', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('fee_categories', 'type')) {
                $table->enum('type', ['mandatory', 'optional'])->default('mandatory')->after('code');
            }
            if (!Schema::hasColumn('fee_categories', 'income_category_id')) {
                $table->unsignedInteger('income_category_id')->nullable()->after('description');
            }
            if (!Schema::hasColumn('fee_categories', 'display_order')) {
                $table->integer('display_order')->default(0)->after('income_category_id');
            }
            if (!Schema::hasColumn('fee_categories', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('display_order');
            }
            if (!Schema::hasColumn('fee_categories', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
            }
        });

        // 2. Enhance fee_structures
        Schema::table('fee_structures', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_structures', 'term')) {
                $table->string('term')->nullable()->after('academic_year_id'); // Term 1, Term 2, etc.
            }
            if (!Schema::hasColumn('fee_structures', 'payment_frequency')) {
                $table->enum('payment_frequency', ['one-time', 'termly', 'monthly', 'custom'])->default('termly')->after('amount');
            }
            // amount existed, late fee is new
            if (!Schema::hasColumn('fee_structures', 'late_fee_amount')) {
                $table->decimal('late_fee_amount', 10, 2)->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('fee_structures', 'late_fee_type')) {
                $table->enum('late_fee_type', ['flat', 'percentage'])->nullable()->after('late_fee_amount');
            }
            if (!Schema::hasColumn('fee_structures', 'pro_rata_enabled')) {
                $table->boolean('pro_rata_enabled')->default(false)->after('late_fee_type');
            }
            if (!Schema::hasColumn('fee_structures', 'status')) {
                $table->enum('status', ['active', 'inactive', 'draft', 'archived'])->default('active')->after('pro_rata_enabled');
            }
            if (!Schema::hasColumn('fee_structures', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('fee_structures', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            }
        });

        // 3. Create discount_schemes (Enhancement/Replacement of fee_discounts)
        Schema::create('discount_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['percentage', 'fixed', 'full_waiver']);
            $table->decimal('value', 10, 2)->nullable(); // Null if full_waiver
            $table->enum('applies_to', ['all_fees', 'specific_categories', 'exclude_categories'])->default('all_fees');
            $table->json('applicable_fee_categories')->nullable(); // Store IDs of categories
            $table->enum('eligibility_criteria', ['staff_child', 'sibling', 'merit', 'financial_aid', 'custom'])->default('custom');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->integer('academic_year_id')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->boolean('auto_apply')->default(false);
            $table->integer('max_instances')->nullable();
            $table->decimal('budget_allocated', 15, 2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 4. Create student_fee_assignments (The Core)
        Schema::create('student_fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->unsigned(); // Matches students table PK type if possible, usually bigint, but scaffold might be int
            $table->integer('fee_structure_id')->unsigned();
            $table->integer('academic_year_id')->unsigned();
            $table->string('term')->nullable();
            $table->decimal('amount', 10, 2); // Base amount
            $table->unsignedBigInteger('discount_id')->nullable(); // Link to student_discounts or discount_schemes
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2); // Payable
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->dateTime('assigned_date')->useCurrent();
            $table->enum('status', ['active', 'inactive'])->default('active'); // Inactive if removed/waived
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['student_id', 'academic_year_id', 'term']);
            $table->index(['fee_structure_id']);
        });

        // 5. Create student_discounts (Tracking & Approval)
        Schema::create('student_discounts', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->unsigned();
            $table->unsignedBigInteger('discount_scheme_id');
            $table->integer('academic_year_id')->unsigned();
            $table->decimal('applied_amount', 10, 2);
            $table->text('justification')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->dateTime('requested_date')->useCurrent();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['student_id', 'academic_year_id']);
        });

        // 6. Create fee_invoices (Optional but requested)
        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->unsigned();
            $table->integer('academic_year_id')->unsigned();
            $table->string('term')->nullable();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('student_discounts');
        Schema::dropIfExists('student_fee_assignments');
        Schema::dropIfExists('discount_schemes');
        
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn(['term', 'payment_frequency', 'late_fee_amount', 'late_fee_type', 'pro_rata_enabled', 'status', 'notes', 'created_by']);
        });

        Schema::table('fee_categories', function (Blueprint $table) {
            $table->dropColumn(['code', 'type', 'income_category_id', 'display_order', 'status', 'created_by']);
        });
    }
};
