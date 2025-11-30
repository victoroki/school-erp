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
        // 1. Create Routes
        $routes = [
            ['name' => 'North Route', 'description' => 'North side of the city', 'start_point' => 'School Gate', 'end_point' => 'Northern Terminal', 'distance' => 15.5],
            ['name' => 'South Route', 'description' => 'South side of the city', 'start_point' => 'School Gate', 'end_point' => 'Southern Terminal', 'distance' => 12.3],
            ['name' => 'East Route', 'description' => 'East side of the city', 'start_point' => 'School Gate', 'end_point' => 'Eastern Terminal', 'distance' => 8.7],
            ['name' => 'West Route', 'description' => 'West side of the city', 'start_point' => 'School Gate', 'end_point' => 'Western Terminal', 'distance' => 10.2],
        ];

        foreach ($routes as $routeData) {
            Route::firstOrCreate(['name' => $routeData['name']], $routeData);
        }

        // 2. Create Route Stops
        $allRoutes = Route::all();
        $stops = [
            // North Route stops
            ['route_id' => $allRoutes->firstWhere('name', 'North Route')->route_id, 'stop_name' => 'Central Park', 'stop_time' => '07:30:00', 'sequence' => 1],
            ['route_id' => $allRoutes->firstWhere('name', 'North Route')->route_id, 'stop_name' => 'Shopping Mall', 'stop_time' => '07:45:00', 'sequence' => 2],
            ['route_id' => $allRoutes->firstWhere('name', 'North Route')->route_id, 'stop_name' => 'University Area', 'stop_time' => '08:00:00', 'sequence' => 3],
            
            // South Route stops
            ['route_id' => $allRoutes->firstWhere('name', 'South Route')->route_id, 'stop_name' => 'Residential Zone', 'stop_time' => '07:35:00', 'sequence' => 1],
            ['route_id' => $allRoutes->firstWhere('name', 'South Route')->route_id, 'stop_name' => 'Hospital', 'stop_time' => '07:50:00', 'sequence' => 2],
            ['route_id' => $allRoutes->firstWhere('name', 'South Route')->route_id, 'stop_name' => 'Industrial Area', 'stop_time' => '08:10:00', 'sequence' => 3],
            
            // East Route stops
            ['route_id' => $allRoutes->firstWhere('name', 'East Route')->route_id, 'stop_name' => 'Business District', 'stop_time' => '07:40:00', 'sequence' => 1],
            ['route_id' => $allRoutes->firstWhere('name', 'East Route')->route_id, 'stop_name' => 'Airport', 'stop_time' => '07:55:00', 'sequence' => 2],
            
            // West Route stops
            ['route_id' => $allRoutes->firstWhere('name', 'West Route')->route_id, 'stop_name' => 'Suburban Area', 'stop_time' => '07:25:00', 'sequence' => 1],
            ['route_id' => $allRoutes->firstWhere('name', 'West Route')->route_id, 'stop_name' => 'Railway Station', 'stop_time' => '07:45:00', 'sequence' => 2],
        ];

        foreach ($stops as $stopData) {
            RouteStop::firstOrCreate([
                'route_id' => $stopData['route_id'], 
                'stop_name' => $stopData['stop_name']
            ], $stopData);
        }

        // 3. Create Vehicles
        $vehicles = [
            ['vehicle_number' => 'BUS-001', 'vehicle_type' => 'Mini Bus', 'model' => 'Transit', 'make' => 'Ford', 'year' => 2020, 'seating_capacity' => 25, 'status' => 'active', 'insurance_expiry_date' => '2026-06-30'],
            ['vehicle_number' => 'BUS-002', 'vehicle_type' => 'Standard Bus', 'model' => 'Sprinter', 'make' => 'Mercedes', 'year' => 2019, 'seating_capacity' => 40, 'status' => 'active', 'insurance_expiry_date' => '2025-12-31'],
            ['vehicle_number' => 'BUS-003', 'vehicle_type' => 'Mini Bus', 'model' => 'Transit', 'make' => 'Ford', 'year' => 2021, 'seating_capacity' => 25, 'status' => 'active', 'insurance_expiry_date' => '2027-03-15'],
            ['vehicle_number' => 'BUS-004', 'vehicle_type' => 'Standard Bus', 'model' => 'Sprinter', 'make' => 'Mercedes', 'year' => 2018, 'seating_capacity' => 40, 'status' => 'maintenance', 'insurance_expiry_date' => '2024-09-30'],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::firstOrCreate(['vehicle_number' => $vehicleData['vehicle_number']], $vehicleData);
        }

        // 4. Get Staff for Drivers and Assistants
        $staffMembers = Staff::limit(10)->get();
        if ($staffMembers->count() >= 8) {
            // 5. Create Transport Assignments
            $allVehicles = Vehicle::all();
            $assignments = [
                [
                    'route_id' => $allRoutes->firstWhere('name', 'North Route')->route_id,
                    'vehicle_id' => $allVehicles->firstWhere('vehicle_number', 'BUS-001')->vehicle_id,
                    'driver_id' => $staffMembers[0]->staff_id,
                    'assistant_id' => $staffMembers[1]->staff_id,
                    'departure_time' => '07:15:00',
                    'return_time' => '16:30:00',
                    'status' => 'active'
                ],
                [
                    'route_id' => $allRoutes->firstWhere('name', 'South Route')->route_id,
                    'vehicle_id' => $allVehicles->firstWhere('vehicle_number', 'BUS-002')->vehicle_id,
                    'driver_id' => $staffMembers[2]->staff_id,
                    'assistant_id' => $staffMembers[3]->staff_id,
                    'departure_time' => '07:20:00',
                    'return_time' => '16:35:00',
                    'status' => 'active'
                ],
                [
                    'route_id' => $allRoutes->firstWhere('name', 'East Route')->route_id,
                    'vehicle_id' => $allVehicles->firstWhere('vehicle_number', 'BUS-003')->vehicle_id,
                    'driver_id' => $staffMembers[4]->staff_id,
                    'assistant_id' => $staffMembers[5]->staff_id,
                    'departure_time' => '07:10:00',
                    'return_time' => '16:25:00',
                    'status' => 'active'
                ],
                [
                    'route_id' => $allRoutes->firstWhere('name', 'West Route')->route_id,
                    'vehicle_id' => $allVehicles->firstWhere('vehicle_number', 'BUS-004')->vehicle_id,
                    'driver_id' => $staffMembers[6]->staff_id,
                    'assistant_id' => $staffMembers[7]->staff_id,
                    'departure_time' => '07:05:00',
                    'return_time' => '16:20:00',
                    'status' => 'inactive'
                ],
            ];

            foreach ($assignments as $assignmentData) {
                TransportAssignment::firstOrCreate([
                    'route_id' => $assignmentData['route_id'],
                    'vehicle_id' => $assignmentData['vehicle_id']
                ], $assignmentData);
            }
        }

        // 6. Get Students for Registrations
        $students = Student::limit(20)->get();
        $academicYears = AcademicYear::all();
        $currentAcademicYear = $academicYears->first();
        
        if ($students->count() >= 15 && $currentAcademicYear) {
            // 7. Create Transport Registrations
            $allStops = RouteStop::all();
            $registrations = [];
            
            // Register students for North Route
            for ($i = 0; $i < 5; $i++) {
                if (isset($students[$i])) {
                    $registrations[] = [
                        'student_id' => $students[$i]->student_id,
                        'route_id' => $allRoutes->firstWhere('name', 'North Route')->route_id,
                        'stop_id' => $allStops->where('route_id', $allRoutes->firstWhere('name', 'North Route')->route_id)->first()->stop_id,
                        'fee_amount' => 150.00,
                        'payment_status' => 'paid',
                        'academic_year_id' => $currentAcademicYear->academic_year_id
                    ];
                }
            }
            
            // Register students for South Route
            for ($i = 5; $i < 10; $i++) {
                if (isset($students[$i])) {
                    $registrations[] = [
                        'student_id' => $students[$i]->student_id,
                        'route_id' => $allRoutes->firstWhere('name', 'South Route')->route_id,
                        'stop_id' => $allStops->where('route_id', $allRoutes->firstWhere('name', 'South Route')->route_id)->first()->stop_id,
                        'fee_amount' => 120.00,
                        'payment_status' => 'paid',
                        'academic_year_id' => $currentAcademicYear->academic_year_id
                    ];
                }
            }
            
            // Register students for East Route
            for ($i = 10; $i < 15; $i++) {
                if (isset($students[$i])) {
                    $registrations[] = [
                        'student_id' => $students[$i]->student_id,
                        'route_id' => $allRoutes->firstWhere('name', 'East Route')->route_id,
                        'stop_id' => $allStops->where('route_id', $allRoutes->firstWhere('name', 'East Route')->route_id)->first()->stop_id,
                        'fee_amount' => 100.00,
                        'payment_status' => 'unpaid',
                        'academic_year_id' => $currentAcademicYear->academic_year_id
                    ];
                }
            }

            foreach ($registrations as $registrationData) {
                TransportRegistration::firstOrCreate([
                    'student_id' => $registrationData['student_id'],
                    'route_id' => $registrationData['route_id']
                ], $registrationData);
            }
        }
    }
}