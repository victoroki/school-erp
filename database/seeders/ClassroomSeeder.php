<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classroom;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['room_number' => 'G-01', 'building' => 'Upper Block', 'floor' => 1, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'G-02', 'building' => 'Upper Block', 'floor' => 1, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'G-03', 'building' => 'Upper Block', 'floor' => 1, 'capacity' => 35, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'G-04', 'building' => 'Lower Block', 'floor' => 1, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'G-05', 'building' => 'Lower Block', 'floor' => 1, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'G-06', 'building' => 'Lower Block', 'floor' => 1, 'capacity' => 30, 'has_sockets' => false, 'has_whiteboard' => true],
            ['room_number' => '1-01', 'building' => 'Upper Block', 'floor' => 2, 'capacity' => 45, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => '1-02', 'building' => 'Upper Block', 'floor' => 2, 'capacity' => 45, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => '1-03', 'building' => 'Science Block', 'floor' => 2, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => '2-01', 'building' => 'Junior Wing', 'floor' => 2, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'SCI-01', 'building' => 'Science Laboratory', 'floor' => 1, 'capacity' => 30, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'COM-01', 'building' => 'ICT Laboratory', 'floor' => 1, 'capacity' => 40, 'has_sockets' => true, 'has_whiteboard' => true],
            ['room_number' => 'LIB-01', 'building' => 'Library', 'floor' => 1, 'capacity' => 60, 'has_sockets' => true, 'has_whiteboard' => false],
        ];

        foreach ($rooms as $data) {
            Classroom::firstOrCreate(['room_number' => $data['room_number']], $data);
        }
    }
}
