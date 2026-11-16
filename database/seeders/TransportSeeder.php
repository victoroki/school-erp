<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Vehicle;
use App\Models\TransportAssignment;
use App\Models\TransportRegistration;
use App\Models\Staff;
use App\Models\Student;
use App\Models\AcademicYear;
use Carbon\Carbon;

class TransportSeeder extends Seeder
{
    public function run()
    {
        // 1. Kenyan routes (Nairobi & satellite) in KES
        $routes = [
            ['name' => 'Westlands & Parklands Route', 'route_code' => 'RT-01', 'description' => 'Covers Westlands, Parklands and Riverside areas of Nairobi', 'start_point' => 'Shujaa Academy Gate', 'end_point' => 'Parklands Terminal', 'distance' => 12.5, 'route_fee' => 9000],
            ['name' => 'Thika Road & Ruiru Route', 'route_code' => 'RT-02', 'description' => 'Covers Thika Road corridor up to Ruiru', 'start_point' => 'Shujaa Academy Gate', 'end_point' => 'Ruiru Posta', 'distance' => 22.0, 'route_fee' => 11000],
            ['name' => 'South B & Langata Route', 'route_code' => 'RT-03', 'description' => 'Covers South B, South C, Langata and Karen', 'start_point' => 'Shujaa Academy Gate', 'end_point' => 'Galleria Mall', 'distance' => 18.4, 'route_fee' => 9500],
            ['name' => 'Eastlands Route', 'route_code' => 'RT-04', 'description' => 'Covers Buruburu, Donholm, Kayole and Embakasi', 'start_point' => 'Shujaa Academy Gate', 'end_point' => 'Donholm Terminal', 'distance' => 15.0, 'route_fee' => 8500],
        ];

        $routeIds = [];
        foreach ($routes as $routeData) {
            $route = Route::firstOrCreate(['route_code' => $routeData['route_code']], $routeData);
            $routeIds[$routeData['route_code']] = $route->route_id;
        }

        // 2. Kenyan route stops
        $stops = [
            ['route_code' => 'RT-01', 'stop_name' => 'Sarit Centre', 'landmark' => 'Westlands Roundabout', 'stop_time' => '07:30:00', 'sequence' => 1, 'stop_fee' => 9000],
            ['route_code' => 'RT-01', 'stop_name' => 'Valley Arcade', 'landmark' => 'Old Kitisuru Road', 'stop_time' => '07:45:00', 'sequence' => 2, 'stop_fee' => 9000],
            ['route_code' => 'RT-01', 'stop_name' => 'Parklands Baptist', 'landmark' => 'Parklands Road', 'stop_time' => '08:00:00', 'sequence' => 3, 'stop_fee' => 9000],
            ['route_code' => 'RT-02', 'stop_name' => 'Kenya School of Monetary Studies', 'landmark' => 'Thika Road', 'stop_time' => '07:35:00', 'sequence' => 1, 'stop_fee' => 11000],
            ['route_code' => 'RT-02', 'stop_name' => 'Juja City Mall', 'landmark' => 'Thika Superhighway', 'stop_time' => '07:55:00', 'sequence' => 2, 'stop_fee' => 11000],
            ['route_code' => 'RT-02', 'stop_name' => 'Ruiru Posta', 'landmark' => 'Ruiru Town', 'stop_time' => '08:10:00', 'sequence' => 3, 'stop_fee' => 11000],
            ['route_code' => 'RT-03', 'stop_name' => 'South C Estate', 'landmark' => 'Madaraka', 'stop_time' => '07:40:00', 'sequence' => 1, 'stop_fee' => 9500],
            ['route_code' => 'RT-03', 'stop_name' => 'Karen Hardy', 'landmark' => 'Karen', 'stop_time' => '08:00:00', 'sequence' => 2, 'stop_fee' => 9500],
            ['route_code' => 'RT-04', 'stop_name' => 'Buruburu Phase 1', 'landmark' => 'Buruburu', 'stop_time' => '07:25:00', 'sequence' => 1, 'stop_fee' => 8500],
            ['route_code' => 'RT-04', 'stop_name' => 'Donholm', 'landmark' => 'Donholm', 'stop_time' => '07:45:00', 'sequence' => 2, 'stop_fee' => 8500],
        ];

        foreach ($stops as $stopData) {
            if (!isset($routeIds[$stopData['route_code']])) {
                continue;
            }
            $stopData['route_id'] = $routeIds[$stopData['route_code']];
            unset($stopData['route_code']);

            RouteStop::firstOrCreate(
                ['route_id' => $stopData['route_id'], 'stop_name' => $stopData['stop_name']],
                $stopData
            );
        }

        // 3. School vehicles (Kenyan plates)
        $vehicles = [
            ['vehicle_number' => 'KDJ 210A', 'vehicle_type' => 'Mini Bus', 'model' => 'Coaster', 'make' => 'Toyota', 'year' => 2021, 'seating_capacity' => 30, 'status' => 'active', 'insurance_expiry_date' => '2026-12-31'],
            ['vehicle_number' => 'KDJ 311B', 'vehicle_type' => 'Standard Bus', 'model' => 'Scania K420', 'make' => 'Scania', 'year' => 2020, 'seating_capacity' => 60, 'status' => 'active', 'insurance_expiry_date' => '2027-03-15'],
            ['vehicle_number' => 'KCX 122C', 'vehicle_type' => 'Mini Bus', 'model' => 'Coaster', 'make' => 'Toyota', 'year' => 2022, 'seating_capacity' => 30, 'status' => 'active', 'insurance_expiry_date' => '2027-06-30'],
            ['vehicle_number' => 'KDJ 900D', 'vehicle_type' => 'Standard Bus', 'model' => 'Isuzu FTR', 'make' => 'Isuzu', 'year' => 2019, 'seating_capacity' => 55, 'status' => 'maintenance', 'insurance_expiry_date' => '2026-09-30'],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::firstOrCreate(['vehicle_number' => $vehicleData['vehicle_number']], $vehicleData);
        }

        // 4. Assign drivers and conductors to routes
        $drivers = Staff::whereIn('designation', ['Driver', 'Transport Manager / Driver', 'Conductor / Assistant'])->get();
        if ($drivers->count() >= 5) {
            $allVehicles = Vehicle::all();

            $assignments = [
                ['route_code' => 'RT-01', 'vehicle_number' => 'KDJ 210A', 'driver' => 0, 'assistant' => 3, 'departure' => '07:15:00', 'return' => '16:30:00', 'status' => 'active'],
                ['route_code' => 'RT-02', 'vehicle_number' => 'KDJ 311B', 'driver' => 1, 'assistant' => 4, 'departure' => '07:20:00', 'return' => '16:35:00', 'status' => 'active'],
                ['route_code' => 'RT-03', 'vehicle_number' => 'KCX 122C', 'driver' => 2, 'assistant' => 0, 'departure' => '07:10:00', 'return' => '16:25:00', 'status' => 'active'],
            ];

            foreach ($assignments as $a) {
                $route = Route::where('route_code', $a['route_code'])->first();
                $vehicle = Vehicle::where('vehicle_number', $a['vehicle_number'])->first();
                if (!$route || !$vehicle) {
                    continue;
                }

                TransportAssignment::firstOrCreate(
                    ['route_id' => $route->route_id, 'vehicle_id' => $vehicle->vehicle_id],
                    [
                        'driver_id' => $drivers->get($a['driver'])->staff_id ?? null,
                        'assistant_id' => $drivers->get($a['assistant'])->staff_id ?? null,
                        'departure_time' => $a['departure'],
                        'return_time' => $a['return'],
                        'status' => $a['status'],
                    ]
                );
            }
        }

        // 5. Register transport-using students
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return;
        }

        $transportStudents = Student::where('status', 'active')->where('uses_transport', true)->get();
        if ($transportStudents->isEmpty()) {
            return;
        }

        $routeCodes = array_keys($routeIds);
        $allStops = RouteStop::all();
        $i = 0;

        foreach ($transportStudents as $student) {
            $routeCode = $routeCodes[$i % count($routeCodes)];
            $routeId = $routeIds[$routeCode];
            $stop = $allStops->where('route_id', $routeId)->first();

            if (!$stop) {
                $i++;
                continue;
            }

            $route = Route::find($routeId);
            $fee = $route ? (float) $route->route_fee : 9000;

            TransportRegistration::firstOrCreate(
                ['student_id' => $student->student_id, 'route_id' => $routeId],
                [
                    'stop_id' => $stop->stop_id,
                    'fee_amount' => $fee,
                    'payment_status' => $i % 3 === 0 ? 'unpaid' : 'paid',
                    'academic_year_id' => $year->academic_year_id,
                ]
            );
            $i++;
        }
    }
}
