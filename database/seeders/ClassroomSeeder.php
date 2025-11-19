<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classroom;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['room_number' => '101', 'building' => 'Main', 'floor' => 1, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => '102', 'building' => 'Main', 'floor' => 1, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => '201', 'building' => 'Main', 'floor' => 2, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
        ];

        foreach ($rooms as $data) {
            Classroom::firstOrCreate(
                ['room_number' => $data['room_number']],
                $data
            );
        }
    }
}