<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Period;
use Illuminate\Support\Facades\DB;

class PeriodSeeder extends Seeder
{
    /**
     * Sensible Kenyan-day default structure.
     *
     * 07:30-08:00  Registration / Morning Prep   (registration — no lessons)
     * 08:00-08:40  Period 1
     * 08:40-09:20  Period 2
     * 09:20-10:00  Period 3
     * 10:00-10:30  Morning Break                (break — no lessons)
     * 10:30-11:10  Period 4
     * 11:10-11:50  Period 5
     * 11:50-12:30  Period 6
     * 12:30-13:30  Lunch                        (break — no lessons)
     * 13:30-14:10  Period 7
     * 14:10-14:50  Period 8
     * 14:50-15:30  Period 9
     *
     * The `periods.type` column is an enum of only 'period' and 'break', and
     * the timetable grid + generator both key off those two values, so the
     * non-teaching slots (registration, break, lunch) all map to 'break' and
     * carry the distinction in their names. Administrators can rename, retime
     * or re-order everything afterwards through Periods in Settings.
     */
    public function run(): void
    {
        $periods = [
            ['name' => 'Registration / Morning Prep', 'start_time' => '07:30:00', 'end_time' => '08:00:00', 'type' => 'break'],
            ['name' => 'Period 1',   'start_time' => '08:00:00', 'end_time' => '08:40:00', 'type' => 'period'],
            ['name' => 'Period 2',   'start_time' => '08:40:00', 'end_time' => '09:20:00', 'type' => 'period'],
            ['name' => 'Period 3',   'start_time' => '09:20:00', 'end_time' => '10:00:00', 'type' => 'period'],
            ['name' => 'Morning Break', 'start_time' => '10:00:00', 'end_time' => '10:30:00', 'type' => 'break'],
            ['name' => 'Period 4',   'start_time' => '10:30:00', 'end_time' => '11:10:00', 'type' => 'period'],
            ['name' => 'Period 5',   'start_time' => '11:10:00', 'end_time' => '11:50:00', 'type' => 'period'],
            ['name' => 'Period 6',   'start_time' => '11:50:00', 'end_time' => '12:30:00', 'type' => 'period'],
            ['name' => 'Lunch',      'start_time' => '12:30:00', 'end_time' => '13:30:00', 'type' => 'break'],
            ['name' => 'Period 7',   'start_time' => '13:30:00', 'end_time' => '14:10:00', 'type' => 'period'],
            ['name' => 'Period 8',   'start_time' => '14:10:00', 'end_time' => '14:50:00', 'type' => 'period'],
            ['name' => 'Period 9',   'start_time' => '14:50:00', 'end_time' => '15:30:00', 'type' => 'period'],
        ];

        foreach ($periods as $data) {
            Period::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        // A school that already ran the old 4-period seeder keeps those rows —
        // firstOrCreate never overwrites — but the legacy slots sit at the same
        // times as the new Period 2..5 rows. De-duplicate by exact time overlap
        // so the grid never shows two names for one slot. Rows referenced by
        // timetable entries are left alone; the FK would refuse anyway and the
        // school clearly uses them.
        $legacy = Period::where('name', 'like', 'Period %')
            ->whereIn('start_time', ['08:30:00', '09:20:00', '10:10:00', '11:00:00'])
            ->get();

        foreach ($legacy as $row) {
            $hasTimetable = DB::table('timetable')->where('period_id', $row->period_id)->exists();

            if (! $hasTimetable) {
                $row->delete();
            }
        }
    }
}
