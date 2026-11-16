<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PHASE 5: notification_recipients becomes a LIVE table (per-user read
     * state for the personal notification feed). Two small fixes required
     * before Eloquent writes against it:
     *  - `updated_at` column (model timestamps expect it; the original
     *    generated migration only had created_at),
     *  - a UNIQUE index on (notification_id, recipient_id) so the lazy
     *    fan-out + marking can never race duplicate rows.
     */
    public function up(): void
    {
        Schema::table('notification_recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_recipients', 'updated_at')) {
                $table->dateTime('updated_at')->nullable()->after('read_at');
            }
        });

        // Collapse any pre-existing duplicates (seeder-era rows had no
        // writer, so in practice this is a no-op) before enforcing uniqueness.
        $dups = \DB::table('notification_recipients')
            ->select('notification_id', 'recipient_id')
            ->groupBy('notification_id', 'recipient_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($dups as $d) {
            \DB::table('notification_recipients')
                ->where('notification_id', $d->notification_id)
                ->where('recipient_id', $d->recipient_id)
                ->where('id', '!=', \DB::table('notification_recipients')
                    ->where('notification_id', $d->notification_id)
                    ->where('recipient_id', $d->recipient_id)
                    ->min('id'))
                ->delete();
        }

        $hasUnique = collect(Schema::getIndexes('notification_recipients'))
            ->contains(fn ($i) => ($i['flags'] ?? null) === 'UNIQUE');
        if (!$hasUnique) {
            Schema::table('notification_recipients', function (Blueprint $table) {
                $table->unique(['notification_id', 'recipient_id'], 'notification_recipients_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->dropUnique('notification_recipients_unique');
        });
        if (Schema::hasColumn('notification_recipients', 'updated_at')) {
            Schema::table('notification_recipients', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
};
