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
        Schema::create('library_members', function (Blueprint $table) {
            $table->integer('member_id', true);
            $table->integer('user_id')->nullable()->unique('user_id');
            $table->enum('member_type', ['student', 'staff']);
            $table->integer('reference_id')->comment('student_id or staff_id');
            $table->date('membership_date');
            $table->integer('max_allowed_books')->default(2);
            $table->enum('status', ['active', 'inactive', 'suspended'])->nullable()->default('active');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_members');
    }
};
