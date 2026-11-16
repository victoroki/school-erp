<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Term;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            [
                'name' => '2025', 'start_date' => '2025-01-06', 'end_date' => '2025-11-21', 'is_current' => false,
                'terms' => [
                    ['name' => 'Term 1', 'code' => 'T1', 'start_date' => '2025-01-06', 'end_date' => '2025-04-11', 'fee_due_date' => '2025-01-20', 'status' => 'completed', 'display_order' => 1],
                    ['name' => 'Term 2', 'code' => 'T2', 'start_date' => '2025-05-05', 'end_date' => '2025-08-08', 'fee_due_date' => '2025-05-15', 'status' => 'completed', 'display_order' => 2],
                    ['name' => 'Term 3', 'code' => 'T3', 'start_date' => '2025-08-25', 'end_date' => '2025-11-21', 'fee_due_date' => '2025-09-05', 'status' => 'completed', 'display_order' => 3],
                ],
            ],
            [
                'name' => '2026', 'start_date' => '2026-01-05', 'end_date' => '2026-11-20', 'is_current' => true,
                'terms' => [
                    ['name' => 'Term 1', 'code' => 'T1', 'start_date' => '2026-01-05', 'end_date' => '2026-04-03', 'fee_due_date' => '2026-01-19', 'status' => 'completed', 'display_order' => 1],
                    ['name' => 'Term 2', 'code' => 'T2', 'start_date' => '2026-05-04', 'end_date' => '2026-08-07', 'fee_due_date' => '2026-05-15', 'status' => 'active', 'display_order' => 2],
                    ['name' => 'Term 3', 'code' => 'T3', 'start_date' => '2026-08-24', 'end_date' => '2026-11-20', 'fee_due_date' => '2026-09-04', 'status' => 'upcoming', 'display_order' => 3],
                ],
            ],
        ];

        foreach ($years as $yearData) {
            $terms = $yearData['terms'];
            unset($yearData['terms']);

            $year = AcademicYear::firstOrCreate(
                ['name' => $yearData['name']],
                $yearData
            );

            foreach ($terms as $termData) {
                Term::firstOrCreate(
                    ['academic_year_id' => $year->academic_year_id, 'code' => $termData['code']],
                    $termData
                );
            }
        }
    }
}
