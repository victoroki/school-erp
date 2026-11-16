<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hostel;
use App\Models\HostelRoom;
use App\Models\HostelAllocation;
use App\Models\Student;
use App\Models\Staff;
use App\Models\AcademicYear;
use Carbon\Carbon;

class HostelSeeder extends Seeder
{
    public function run()
    {
        $teaching = Staff::where('staff_type', 'administration')->get();

        // 1. Hostels (Kenyan names). Warden resolved by Staff PK (staff_id).
        $hostels = [
            ['name' => 'Simba Boys Dormitory', 'type' => 'boys', 'address' => 'North Campus, Shujaa Academy', 'capacity' => 120, 'warden' => 0],
            ['name' => 'Twiga Boys Hostel', 'type' => 'boys', 'address' => 'North Campus, Shujaa Academy', 'capacity' => 90, 'warden' => 1],
            ['name' => 'Chui Girls Hostel', 'type' => 'girls', 'address' => 'South Campus, Shujaa Academy', 'capacity' => 110, 'warden' => 2],
            ['name' => 'Nyati Girls Dormitory', 'type' => 'girls', 'address' => 'South Campus, Shujaa Academy', 'capacity' => 85, 'warden' => 3],
        ];

        $createdHostels = [];
        foreach ($hostels as $index => $data) {
            $warden = $teaching->get($data['warden']);
            $hostel = Hostel::firstOrCreate(['name' => $data['name']], [
                'type' => $data['type'],
                'address' => $data['address'],
                'capacity' => $data['capacity'],
                'warden_id' => $warden ? $warden->staff_id : null,
            ]);
            $createdHostels[] = $hostel;
        }

        // 2. Rooms per hostel (boarders placed into gender-matched hostels)
        foreach ($createdHostels as $hostel) {
            foreach ($this->roomPlan() as $room) {
                HostelRoom::firstOrCreate(
                    ['hostel_id' => $hostel->hostel_id, 'room_number' => $room['room_number']],
                    [
                        'room_type' => $room['room_type'],
                        'capacity' => $room['capacity'],
                        'occupied' => 0,
                        'floor' => $room['floor'],
                        'status' => 'available',
                    ]
                );
            }
        }

        // 3. Allocate boarder students (is_hosteller) to a gender-matched hostel
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return;
        }

        $hostellers = Student::where('status', 'active')->where('is_hosteller', true)->get();
        if ($hostellers->isEmpty()) {
            return;
        }

        foreach ($hostellers as $student) {
            $type = $student->gender === 'female' ? 'girls' : 'boys';
            $hostel = Hostel::where('type', $type)->first();
            if (!$hostel) {
                continue;
            }

            $room = HostelRoom::where('hostel_id', $hostel->hostel_id)
                ->where('status', 'available')
                ->whereColumn('occupied', '<', 'capacity')
                ->orderBy('occupied')
                ->first();

            if (!$room) {
                continue;
            }

            $exists = HostelAllocation::where('student_id', $student->student_id)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                continue;
            }

            HostelAllocation::create([
                'student_id' => $student->student_id,
                'hostel_id' => $hostel->hostel_id,
                'room_id' => $room->room_id,
                'bed_number' => ($room->occupied ?? 0) + 1,
                'allocation_date' => Carbon::now()->subMonths(3),
                'vacating_date' => null,
                'status' => 'active',
                'academic_year_id' => $year->academic_year_id,
            ]);

            $room->increment('occupied');
            if ($room->fresh()->occupied >= $room->capacity) {
                $room->update(['status' => 'full']);
            }
        }
    }

    private function roomPlan(): array
    {
        $plan = [];
        // Ground: dormitories (4)
        for ($i = 1; $i <= 4; $i++) {
            $plan[] = ['room_number' => 'G-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT), 'room_type' => 'dormitory', 'capacity' => 8, 'floor' => 'Ground'];
        }
        // 1st: triples + doubles
        for ($i = 1; $i <= 5; $i++) {
            $plan[] = ['room_number' => '1-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT), 'room_type' => 'triple', 'capacity' => 3, 'floor' => '1st'];
        }
        for ($i = 6; $i <= 8; $i++) {
            $plan[] = ['room_number' => '1-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT), 'room_type' => 'double', 'capacity' => 2, 'floor' => '1st'];
        }
        // 2nd: doubles + singles
        for ($i = 1; $i <= 7; $i++) {
            $plan[] = ['room_number' => '2-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT), 'room_type' => 'double', 'capacity' => 2, 'floor' => '2nd'];
        }
        for ($i = 8; $i <= 10; $i++) {
            $plan[] = ['room_number' => '2-' . str_pad((string)$i, 2, '0', STR_PAD_LEFT), 'room_type' => 'single', 'capacity' => 1, 'floor' => '2nd'];
        }
        return $plan;
    }
}
