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
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id('disciplinary_record_id');
            $table->integer('student_id')->index();
            $table->date('incident_date');
            $table->string('incident_type');
            $table->text('description');
            $table->integer('reported_by')->nullable()->index();
            $table->text('action_taken')->nullable();
            $table->string('status')->default('closed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_records');
    }
};
