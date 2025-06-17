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
        Schema::create('notifications', function (Blueprint $table) {
            $table->integer('notification_id', true);
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['announcement', 'alert', 'event', 'exam', 'fee', 'general']);
            $table->enum('recipient_type', ['all', 'students', 'parents', 'teachers', 'staff', 'specific']);
            $table->integer('sender_id')->nullable()->index('sender_id');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
