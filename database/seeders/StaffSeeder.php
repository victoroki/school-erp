<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'employee_id' => 'T-1001',
                'first_name' => 'Alice',
                'last_name' => 'Nguyen',
                'date_of_birth' => '1990-05-10',
                'gender' => 'female',
                'joining_date' => '2020-08-15',
                'designation' => 'Senior Teacher',
                'qualification' => 'B.Ed, M.Ed',
                'experience' => 5,
                'email' => 'alice.nguyen@school.edu',
                'phone' => '555-1001',
                'address' => '123 School Street',
                'city' => 'Metro City',
                'country' => 'Country',
                'photo_url' => null,
                'staff_type' => 'teaching',
                'status' => 'active',
            ],
            [
                'employee_id' => 'T-1002',
                'first_name' => 'Bob',
                'last_name' => 'Khan',
                'date_of_birth' => '1988-03-20',
                'gender' => 'male',
                'joining_date' => '2019-08-01',
                'designation' => 'Mathematics Teacher',
                'qualification' => 'M.Sc, M.Ed',
                'experience' => 7,
                'email' => 'bob.khan@school.edu',
                'phone' => '555-1002',
                'address' => '456 Campus Avenue',
                'city' => 'Metro City',
                'country' => 'Country',
                'photo_url' => null,
                'staff_type' => 'teaching',
                'status' => 'active',
            ],
            [
                'employee_id' => 'T-1003',
                'first_name' => 'Cara',
                'last_name' => 'Lee',
                'date_of_birth' => '1992-09-12',
                'gender' => 'female',
                'joining_date' => '2021-09-01',
                'designation' => 'Science Teacher',
                'qualification' => 'B.Sc, B.Ed',
                'experience' => 4,
                'email' => 'cara.lee@school.edu',
                'phone' => '555-1003',
                'address' => '789 Learning Road',
                'city' => 'Metro City',
                'country' => 'Country',
                'photo_url' => null,
                'staff_type' => 'teaching',
                'status' => 'active',
            ],
            [
                'employee_id' => 'T-1004',
                'first_name' => 'David',
                'last_name' => 'Martinez',
                'date_of_birth' => '1985-11-25',
                'gender' => 'male',
                'joining_date' => '2018-01-10',
                'designation' => 'English Teacher',
                'qualification' => 'B.A, B.Ed',
                'experience' => 9,
                'email' => 'david.martinez@school.edu',
                'phone' => '555-1004',
                'address' => '321 Education Lane',
                'city' => 'Metro City',
                'country' => 'Country',
                'photo_url' => null,
                'staff_type' => 'teaching',
                'status' => 'active',
            ],
            [
                'employee_id' => 'T-1005',
                'first_name' => 'Emma',
                'last_name' => 'Johnson',
                'date_of_birth' => '1991-07-08',
                'gender' => 'female',
                'joining_date' => '2022-02-15',
                'designation' => 'Social Studies Teacher',
                'qualification' => 'M.A, B.Ed',
                'experience' => 3,
                'email' => 'emma.johnson@school.edu',
                'phone' => '555-1005',
                'address' => '654 Academic Street',
                'city' => 'Metro City',
                'country' => 'Country',
                'photo_url' => null,
                'staff_type' => 'teaching',
                'status' => 'active',
            ],
            [
                'employee_id' => 'T-1006',
                'first_name' => 'Frank',
                'last_name' => 'Chen',
                'date_of_birth' => '1987-04-14',
                'gender' => 'male',
                'joining_date' => '2020-09-05',
                'designation' => 'Physical Education Teacher',
                'qualification' => 'B.P.Ed, M.P.Ed',
                'experience' => 6,
                'email' => 'frank.chen@school.edu',
                'phone' => '555-1006',
                'address' => '987 Sports Complex',
                'city' => 'Metro City',
                'country' => 'Country',
                'photo_url' => null,
                'staff_type' => 'teaching',
                'status' => 'active',
            ],
        ];

        foreach ($teachers as $data) {
            Staff::firstOrCreate(
                ['employee_id' => $data['employee_id']],
                $data
            );
        }
    }
}