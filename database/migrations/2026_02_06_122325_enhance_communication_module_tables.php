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
        // 1. Create Template Categories Table
        Schema::create('template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->default('Both'); // SMS, Email, Both
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        // 2. Enhance SMS Templates Table
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->integer('usage_count')->default(0)->after('content');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active')->after('usage_count');
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
        });

        // 3. Enhance Email Templates Table
        Schema::table('email_templates', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->integer('usage_count')->default(0)->after('content');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active')->after('usage_count');
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
        });

        // 4. Create Sent Messages Table
        Schema::create('sent_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('message_type', ['SMS', 'Email']);
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('subject')->nullable(); // For Emails
            $table->text('content');
            $table->string('recipient_type')->nullable(); // All Students, All Parents, Custom, etc.
            $table->integer('recipient_count')->default(0);
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->enum('status', ['Sent', 'Failed', 'Scheduled', 'Sending'])->default('Sending');
            $table->decimal('cost', 10, 2)->nullable(); // For SMS segment costs
            $table->timestamps();
        });

        // 5. Create Message Recipients Table
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sent_message_id');
            $table->string('recipient_type')->nullable(); // Student, Parent, Staff
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('contact'); // Phone number or Email address
            $table->string('recipient_name')->nullable();
            $table->enum('delivery_status', ['Sent', 'Failed', 'Pending'])->default('Pending');
            $table->timestamp('delivery_time')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('sent_message_id')->references('id')->on('sent_messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('sent_messages');
        Schema::dropIfExists('template_categories');

        Schema::table('sms_templates', function (Blueprint $table) {
            $table->dropColumn(['category', 'usage_count', 'status', 'created_by']);
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn(['category', 'usage_count', 'status', 'created_by']);
        });
    }
};
