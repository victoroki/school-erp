<?php

namespace Tests\Feature;

use App\Models\FeePayment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guards the seeders against the two defects found by running the real thing.
 *
 * 1. StaffSeeder omitted current_address, city and country, all NOT NULL with no
 *    default, so `php artisan db:seed` aborted on a fresh database.
 *
 * 2. FeeSeeder wrote payment_method = 'mpesa', which is not a member of
 *    enum('cash','check','card','bank_transfer','online'). DatabaseSeeder runs
 *    with sql_mode="" so MySQL stored the empty-string error value instead of
 *    raising, and every seeded payment silently lost its method. That is the
 *    origin of the 209 rows with payment_method = ''.
 *
 * Neither is detectable by reading the seeders, which is why this test runs the
 * seeder and inspects the result.
 */
class DatabaseSeedIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_full_seed_completes_and_writes_only_valid_enum_values(): void
    {
        // DatabaseSeeder relaxes these for the duration of the seed. Restore them
        // afterwards — session settings persist for the connection and would
        // otherwise leak into every later test in the same process.
        $previousMode = DB::selectOne('SELECT @@SESSION.sql_mode AS m')->m;

        try {
            $this->seed(DatabaseSeeder::class);
        } finally {
            DB::statement("SET SESSION sql_mode='" . $previousMode . "'");
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // The seed must actually have produced rows, or the assertions below pass
        // vacuously.
        $this->assertGreaterThan(0, DB::table('staff')->count(), 'StaffSeeder produced no rows.');
        $this->assertGreaterThan(0, DB::table('fee_payments')->count(), 'FeeSeeder produced no payments.');

        // Defect 1: the NOT NULL columns StaffSeeder used to omit.
        $this->assertSame(
            0,
            DB::table('staff')->whereNull('current_address')->orWhereNull('city')->orWhereNull('country')->count(),
            'Seeded staff must carry current_address, city and country.'
        );

        // Defect 2: every method must be a member the ENUM can actually hold.
        $invalid = DB::table('fee_payments')
            ->whereNotIn('payment_method', FeePayment::PAYMENT_METHODS)
            ->select('payment_method', DB::raw('COUNT(*) as occurrences'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($row) => var_export($row->payment_method, true) . " x{$row->occurrences}")
            ->all();

        $this->assertSame(
            [],
            $invalid,
            'Seeded payments use methods the ENUM cannot hold, which MySQL silently coerces to "": '
                . implode(', ', $invalid)
        );
    }

    public function test_the_seed_is_idempotent(): void
    {
        $previousMode = DB::selectOne('SELECT @@SESSION.sql_mode AS m')->m;

        try {
            $this->seed(DatabaseSeeder::class);

            $staffAfterFirst = DB::table('staff')->count();
            $paymentsAfterFirst = DB::table('fee_payments')->count();

            $this->seed(DatabaseSeeder::class);

            $this->assertSame($staffAfterFirst, DB::table('staff')->count(), 'Reseeding duplicated staff.');
            $this->assertSame($paymentsAfterFirst, DB::table('fee_payments')->count(), 'Reseeding duplicated payments.');
        } finally {
            DB::statement("SET SESSION sql_mode='" . $previousMode . "'");
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
