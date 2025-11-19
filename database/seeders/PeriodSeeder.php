<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Period;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            ['name' => 'Period 1', 'start_time' => '08:30:00', 'end_time' => '09:15:00'],
            ['name' => 'Period 2', 'start_time' => '09:20:00', 'end_time' => '10:05:00'],
            ['name' => 'Period 3', 'start_time' => '10:10:00', 'end_time' => '10:55:00'],
            ['name' => 'Period 4', 'start_time' => '11:00:00', 'end_time' => '11:45:00'],
        ];

        foreach ($periods as $data) {
            Period::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}