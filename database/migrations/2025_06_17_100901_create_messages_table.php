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
        Schema::create('messages', function (Blueprint $table) {
            $table->integer('message_id', true);
            $table->integer('sender_id')->nullable()->index('sender_id');
            $table->integer('receiver_id')->nullable()->index('receiver_id');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->nullable()->default(false);
            $table->dateTime('read_at')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
