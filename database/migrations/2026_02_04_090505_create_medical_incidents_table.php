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
        Schema::create('medical_incidents', function (Blueprint $table) {
            $table->id('medical_incident_id');
            $table->integer('student_id')->index();
            $table->date('incident_date');
            $table->string('symptoms');
            $table->text('details')->nullable();
            $table->text('treatment_given')->nullable();
            $table->boolean('notified_parents')->default(false);
            $table->integer('marked_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_incidents');
    }
};
