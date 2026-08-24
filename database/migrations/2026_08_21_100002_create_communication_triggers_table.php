<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_type', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('requires_confirmation')->default(true);
            $table->unsignedBigInteger('default_template_id')->nullable();
            $table->enum('channel', ['sms', 'email', 'both'])->default('sms');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_triggers');
    }
};
