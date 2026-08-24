<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_confirmations', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_type', 50);
            $table->unsignedBigInteger('trigger_id')->nullable();
            $table->string('trigger_model', 150)->nullable();
            $table->string('recipient_type', 50);      // 'parent', 'student', 'staff'
            $table->unsignedBigInteger('recipient_id');
            $table->string('contact', 255);
            $table->string('recipient_name', 255)->nullable();
            $table->string('student_name', 255)->nullable();
            $table->enum('channel', ['sms', 'email']);
            $table->string('subject')->nullable();
            $table->text('rendered_body');
            $table->enum('status', ['pending', 'sent', 'discarded'])->default('pending');
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['trigger_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_confirmations');
    }
};
