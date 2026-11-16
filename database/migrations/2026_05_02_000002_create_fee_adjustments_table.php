<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_fee_assignment_id');
            $table->integer('student_id');
            $table->decimal('original_amount', 10, 2);
            $table->decimal('new_amount', 10, 2);
            $table->decimal('adjustment_amount', 10, 2);
            $table->enum('adjustment_type', ['reduction', 'increase', 'waiver']);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('requested_by')->nullable();
            $table->dateTime('requested_at')->useCurrent();
            $table->integer('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('student_fee_assignment_id')->references('id')->on('student_fee_assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['student_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('fee_adjustment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_adjustment_id');
            $table->string('action');
            $table->integer('user_id')->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('fee_adjustment_id')->references('id')->on('fee_adjustments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['fee_adjustment_id', 'created_at']);
        });

        if (!Schema::hasColumn('student_fee_assignments', 'term_id')) {
            Schema::table('student_fee_assignments', function (Blueprint $table) {
                $table->unsignedBigInteger('term_id')->nullable()->after('term');
                $table->foreign('term_id')->references('id')->on('terms')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_fee_assignments', 'term_id')) {
            Schema::table('student_fee_assignments', function (Blueprint $table) {
                $table->dropForeign(['term_id']);
                $table->dropColumn('term_id');
            });
        }

        Schema::dropIfExists('fee_adjustment_audit_logs');
        Schema::dropIfExists('fee_adjustments');
    }
};
