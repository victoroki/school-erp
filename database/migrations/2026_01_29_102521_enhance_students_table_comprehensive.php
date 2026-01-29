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
        Schema::table('students', function (Blueprint $table) {
            // Personal Information Enhancements
            $table->string('nationality', 50)->nullable()->after('country');
            $table->string('religion', 50)->nullable()->after('nationality');
            $table->string('blood_group', 10)->nullable()->after('religion');
            $table->string('birth_certificate_no', 50)->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('country');
            $table->string('postal_code', 20)->nullable()->after('address');
            
            // Medical Information
            $table->text('medical_conditions')->nullable()->after('blood_group');
            $table->text('allergies')->nullable()->after('medical_conditions');
            $table->text('medications')->nullable()->after('allergies');
            $table->string('doctor_name', 100)->nullable()->after('medications');
            $table->string('doctor_phone', 20)->nullable()->after('doctor_name');
            
            // Emergency Contacts (Enhanced)
            $table->string('emergency_contact_name', 100)->nullable()->after('emergency_contact');
            $table->string('emergency_contact_relationship', 50)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_phone_2', 20)->nullable()->after('emergency_contact_relationship');
            
            // Previous School Information
            $table->string('previous_school', 200)->nullable()->after('admission_date');
            $table->string('previous_class', 50)->nullable()->after('previous_school');
            $table->date('transfer_date')->nullable()->after('previous_class');
            $table->text('transfer_reason')->nullable()->after('transfer_date');
            $table->string('transfer_certificate_no', 50)->nullable()->after('transfer_reason');
            
            // Transport Information
            $table->boolean('uses_transport')->default(false)->after('status');
            $table->integer('route_id')->nullable()->after('uses_transport');
            $table->string('pickup_point', 200)->nullable()->after('route_id');
            
            // Hostel Information
            $table->boolean('is_hosteller')->default(false)->after('pickup_point');
            
            // Additional Status Fields
            $table->enum('enrollment_status', ['enrolled', 'graduated', 'transferred', 'expelled', 'dropped_out', 'on_leave'])->default('enrolled')->after('status');
            $table->date('graduation_date')->nullable()->after('enrollment_status');
            $table->text('leaving_reason')->nullable()->after('graduation_date');
            
            // Academic Information
            $table->string('roll_number', 20)->nullable()->after('admission_no');
            $table->string('student_category', 50)->nullable()->after('roll_number')->comment('e.g., General, SC, ST, OBC');
            $table->boolean('is_scholarship_holder')->default(false)->after('student_category');
            $table->text('scholarship_details')->nullable()->after('is_scholarship_holder');
            
            // Behavioral & Disciplinary
            $table->integer('behavior_score')->default(100)->after('scholarship_details');
            $table->text('special_notes')->nullable()->after('behavior_score');
            
            // System Fields
            $table->timestamp('last_login_at')->nullable()->after('special_notes');
            $table->boolean('is_active')->default(true)->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nationality', 'religion', 'blood_group', 'birth_certificate_no', 'address', 'postal_code',
                'medical_conditions', 'allergies', 'medications', 'doctor_name', 'doctor_phone',
                'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_phone_2',
                'previous_school', 'previous_class', 'transfer_date', 'transfer_reason', 'transfer_certificate_no',
                'uses_transport', 'route_id', 'pickup_point',
                'is_hosteller',
                'enrollment_status', 'graduation_date', 'leaving_reason',
                'roll_number', 'student_category', 'is_scholarship_holder', 'scholarship_details',
                'behavior_score', 'special_notes',
                'last_login_at', 'is_active'
            ]);
        });
    }
};
