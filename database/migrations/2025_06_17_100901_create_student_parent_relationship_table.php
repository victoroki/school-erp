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
        Schema::create('student_parent_relationship', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('parent_id')->nullable()->index('parent_id');
            $table->boolean('is_primary_contact')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_parent_relationship');
    }
};
