<?php

use App\Services\LibrarySettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the library policy settings with the values that were previously
 * hardcoded in LibraryService, so the settings page opens on the numbers the
 * system has actually been using.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $now = now();

        $defaults = [
            [
                'setting_key' => LibrarySettings::FINE_PER_DAY,
                'setting_value' => (string) LibrarySettings::DEFAULT_FINE_PER_DAY,
                'field_type' => 'number',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'setting_key' => LibrarySettings::LOAN_PERIOD_DAYS,
                'setting_value' => (string) LibrarySettings::DEFAULT_LOAN_PERIOD_DAYS,
                'field_type' => 'number',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($defaults as $default) {
            $exists = DB::table('settings')->where('setting_key', $default['setting_key'])->exists();

            if (! $exists) {
                DB::table('settings')->insert($default);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('setting_key', [LibrarySettings::FINE_PER_DAY, LibrarySettings::LOAN_PERIOD_DAYS])
            ->delete();
    }
};
