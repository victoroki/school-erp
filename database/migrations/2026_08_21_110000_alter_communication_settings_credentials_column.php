<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change credentials from json to text to support encrypted ciphertext.
        // NULL is allowed so that rows with no credentials yet (e.g. a provider
        // row inserted before credentials are configured) don't violate the
        // NOT NULL constraint — the model accessor already handles NULL gracefully.
        DB::statement('ALTER TABLE communication_settings MODIFY COLUMN credentials TEXT NULL DEFAULT NULL');

        // Drop the single-column unique on settings_key so we can have multiple
        // rows with the same key but different provider_name (e.g. 'sms_provider'
        // for both africastalking and sozuri)
        Schema::table('communication_settings', function ($table) {
            $table->dropIndex('communication_settings_settings_key_unique');
            $table->unique(['settings_key', 'provider_name']);
        });
    }

    public function down(): void
    {
        Schema::table('communication_settings', function ($table) {
            $table->dropUnique(['settings_key', 'provider_name']);
            $table->unique('settings_key');
        });

        DB::statement('ALTER TABLE communication_settings MODIFY COLUMN credentials JSON NOT NULL');
    }
};
