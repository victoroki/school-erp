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
        Schema::table('student_documents', function (Blueprint $table) {
            // Add document categorization
            $table->enum('document_category', [
                'academic', 
                'medical', 
                'identification', 
                'financial', 
                'legal', 
                'certificates', 
                'other'
            ])->default('other')->after('document_type');
            
            $table->boolean('is_verified')->default(false)->after('file_path');
            $table->integer('verified_by')->nullable()->after('is_verified');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->date('expiry_date')->nullable()->after('verified_at');
            $table->boolean('is_mandatory')->default(false)->after('expiry_date');
            $table->text('notes')->nullable()->after('is_mandatory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropColumn([
                'document_category',
                'is_verified',
                'verified_by',
                'verified_at',
                'expiry_date',
                'is_mandatory',
                'notes'
            ]);
        });
    }
};
