<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider_type', 50);          // 'sms' or 'email'
            $table->string('provider_name', 50);           // 'africastalking', 'smtp'
            $table->boolean('is_active')->default(false);
            $table->text('credentials');                    // Encrypted via model accessor
            $table->string('settings_key', 100)->unique(); // 'sms_provider', 'email_provider'
            $table->timestamp('last_tested_at')->nullable();
            $table->enum('last_test_status', ['success', 'failed'])->nullable();
            $table->text('last_test_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_settings');
    }
};
