<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

/**
 * Creates User accounts for every active student so they can log in
 * to the mobile app. The email is their admission number (lowercased,
 * with "/" replaced by "-") and the password is the admission number
 * itself. Students can change their password after first login.
 */
class StudentUserSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::where('status', 'active')
            ->whereNull('user_id')
            ->get();

        $studentRole = Role::where('role_name', 'Student')->first();

        foreach ($students as $student) {
            // Email = admission number adapted for email format
            // ADM2026/001 -> adm2026-001@student.local
            $email = strtolower(str_replace('/', '-', $student->admission_no)) . '@student.local';

            // Password = admission number (student can change later)
            $password = $student->admission_no;

            $user = User::create([
                'name'      => $student->first_name . ' ' . $student->last_name,
                'email'     => $email,
                'password'  => Hash::make($password),
                'user_type' => 'student',
                'is_active' => true,
            ]);

            if ($studentRole) {
                $user->roles()->attach($studentRole);
            }

            $student->user_id = $user->id;
            $student->save();
        }
    }
}