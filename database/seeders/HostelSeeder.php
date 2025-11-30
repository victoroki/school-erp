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
        // 1. Create Hostels
        $hostels = [
            [
                'name' => 'Boys Hostel - Block A',
                'type' => 'boys',
                'address' => 'North Campus, Building A, School Premises',
                'capacity' => 100,
                'warden_id' => Staff::first()->id ?? null,
            ],
            [
                'name' => 'Boys Hostel - Block B',
                'type' => 'boys',
                'address' => 'North Campus, Building B, School Premises',
                'capacity' => 80,
                'warden_id' => Staff::skip(1)->first()->id ?? null,
            ],
            [
                'name' => 'Girls Hostel - Block A',
                'type' => 'girls',
                'address' => 'South Campus, Building A, School Premises',
                'capacity' => 90,
                'warden_id' => Staff::skip(2)->first()->id ?? null,
            ],
            [
                'name' => 'Girls Hostel - Block B',
                'type' => 'girls',
                'address' => 'South Campus, Building B, School Premises',
                'capacity' => 75,
                'warden_id' => Staff::skip(3)->first()->id ?? null,
            ],
        ];

        foreach ($hostels as $hostelData) {
            Hostel::create($hostelData);
        }

        // 2. Create Rooms for each hostel
        $allHostels = Hostel::all();
        $roomTypes = ['single', 'double', 'triple', 'dormitory'];
        
        foreach ($allHostels as $hostel) {
            $totalRooms = 0;
            $hostelCapacity = 0;

            // Ground Floor - Dormitory style (4 rooms)
            for ($i = 1; $i <= 4; $i++) {
                HostelRoom::create([
                    'hostel_id' => $hostel->hostel_id,
                    'room_number' => 'G-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'room_type' => 'dormitory',
                    'capacity' => 8,
                    'occupied' => 0,
                    'floor' => 'Ground',
                    'status' => 'available'
                ]);
                $hostelCapacity += 8;
            }

            // First Floor - Mix of triple and double (8 rooms)
            for ($i = 1; $i <= 5; $i++) {
                HostelRoom::create([
                    'hostel_id' => $hostel->hostel_id,
                    'room_number' => '1-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'room_type' => 'triple',
                    'capacity' => 3,
                    'occupied' => 0,
                    'floor' => '1st',
                    'status' => 'available'
                ]);
                $hostelCapacity += 3;
            }
            
            for ($i = 6; $i <= 8; $i++) {
                HostelRoom::create([
                    'hostel_id' => $hostel->hostel_id,
                    'room_number' => '1-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'room_type' => 'double',
                    'capacity' => 2,
                    'occupied' => 0,
                    'floor' => '1st',
                    'status' => 'available'
                ]);
                $hostelCapacity += 2;
            }

            // Second Floor - Doubles and singles (10 rooms)
            for ($i = 1; $i <= 7; $i++) {
                HostelRoom::create([
                    'hostel_id' => $hostel->hostel_id,
                    'room_number' => '2-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'room_type' => 'double',
                    'capacity' => 2,
                    'occupied' => 0,
                    'floor' => '2nd',
                    'status' => 'available'
                ]);
                $hostelCapacity += 2;
            }

            for ($i = 8; $i <= 10; $i++) {
                HostelRoom::create([
                    'hostel_id' => $hostel->hostel_id,
                    'room_number' => '2-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'room_type' => 'single',
                    'capacity' => 1,
                    'occupied' => 0,
                    'floor' => '2nd',
                    'status' => 'available'
                ]);
                $hostelCapacity += 1;
            }
        }

        // 3. Create Student Allocations
        $currentYear = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
        
        if (!$currentYear) {
            return; // Skip allocations if no academic year exists
        }

        $students = Student::limit(50)->get();
        
        if ($students->isEmpty()) {
            return; // Skip if no students
        }

        $boysHostels = Hostel::where('type', 'boys')->get();
        $girlsHostels = Hostel::where('type', 'girls')->get();

        foreach ($students as $index => $student) {
            // Randomly assign gender (since we don't have gender field, we'll alternate)
            $isBoy = $index % 2 == 0;
            $availableHostels = $isBoy ? $boysHostels : $girlsHostels;

            if ($availableHostels->isEmpty()) continue;

            $hostel = $availableHostels->random();
            
            // Find a room with available space
            $availableRoom = HostelRoom::where('hostel_id', $hostel->hostel_id)
                ->where('status', 'available')
                ->whereRaw('occupied < capacity')
                ->orderBy('occupied', 'asc')
                ->first();

            if ($availableRoom) {
                HostelAllocation::create([
                    'student_id' => $student->id,
                    'hostel_id' => $hostel->hostel_id,
                    'room_id' => $availableRoom->room_id,
                    'bed_number' => ($availableRoom->occupied ?? 0) + 1,
                    'allocation_date' => Carbon::now()->subMonths(rand(1, 6)),
                    'vacating_date' => null,
                    'status' => 'active',
                    'academic_year_id' => $currentYear->id
                ]);

                // Update room occupied count
                $availableRoom->increment('occupied');
                
                // Update room status if full
                if ($availableRoom->fresh()->occupied >= $availableRoom->capacity) {
                    $availableRoom->update(['status' => 'full']);
                }
            }
        }
    }
}
