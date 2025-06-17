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
        Schema::create('settings', function (Blueprint $table) {
            $table->integer('setting_id', true);
            $table->string('setting_key', 100)->unique('setting_key');
            $table->text('setting_value')->nullable();
            $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'select', 'file', 'checkbox'])->nullable()->default('text');
            $table->text('options')->nullable()->comment('For select fields, JSON array of options');
            $table->boolean('is_public')->nullable()->default(false);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
