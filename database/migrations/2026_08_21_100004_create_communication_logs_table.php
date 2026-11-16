<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_type', 50);
            $table->unsignedBigInteger('trigger_id')->nullable();
            $table->string('trigger_model', 150)->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->enum('channel', ['sms', 'email']);
            $table->string('recipient_type', 50);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('contact', 255);
            $table->string('contact_masked', 255)->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->string('provider_message_id', 255)->nullable();
            $table->text('failure_reason')->nullable();
            $table->decimal('cost', 10, 4)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['trigger_type', 'trigger_id']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
