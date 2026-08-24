<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->unique();
            $table->boolean('sms_opt_out')->default(false);
            $table->boolean('email_opt_out')->default(false);
            $table->json('opt_out_types')->nullable();    // ["fee_reminder", "attendance_absence"]
            $table->timestamps();

            $table->foreign('parent_id')->references('parent_id')->on('parents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_notification_preferences');
    }
};
