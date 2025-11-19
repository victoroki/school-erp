<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::query()->update(['is_current' => false]);

        AcademicYear::firstOrCreate(
            ['name' => '2023-2024'],
            [
                'start_date' => '2023-09-01',
                'end_date' => '2024-06-30',
                'is_current' => false,
            ]
        );

        AcademicYear::firstOrCreate(
            ['name' => '2024-2025'],
            [
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'is_current' => true,
            ]
        );
    }
}