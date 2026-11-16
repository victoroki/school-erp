<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Parents;

class ParentsSeeder extends Seeder
{
    // MUST stay identical to StudentParentRelationshipSeeder::GIVEN_NAMES so
    // generated parent emails can be resolved when relationships are created.
    private const GIVEN_NAMES = ['David', 'Mary', 'James', 'Grace', 'Peter', 'Faith', 'John', 'Esther', 'George', 'Sarah', 'Paul', 'Ruth', 'Stephen', 'Mercy', 'Daniel', 'Lucy', 'Samuel', 'Joyce', 'Michael', 'Agnes', 'Joseph', 'Rose', 'Brian', 'Janet', 'Charles', 'Catherine', 'Patrick', 'Maryanne', 'Kennedy', 'Veronica'];

    public function run(): void
    {
        $students = Student::all();
        if ($students->count() === 0) {
            return;
        }

        $givenNames = self::GIVEN_NAMES;

        $occupations = ['Business Owner', 'Teacher', 'Civil Servant', 'Farmer', 'Nurse', 'Police Officer',
            'Accountant', 'Driver', 'Entrepreneur', 'Doctor', 'Lecturer', 'Shopkeeper', 'Fashion Designer',
            'Mechanic', 'Banker', 'Farmer', 'Journalist', 'Pharmacist', 'Engineer', 'Lawyer'];

        foreach ($students as $student) {
            // Deterministic pseudo-random seed so a reseed keeps the same parents
            $idx = (int) substr(str_replace(['/', 'ADM'], '', $student->admission_no), -3);

            $first = $givenNames[$idx % count($givenNames)];
            $last = $student->last_name; // share the child's Kenyan surname
            $email = strtolower($first . '.' . $last . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT) . '@example.co.ke');

            $homeCounty = $student->county ?: 'Nairobi';

            $parents = [
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'relationship' => 'father',
                    'email' => $email,
                    'phone' => $this->phone(720, $idx),
                    'alternate_phone' => $this->phone(733, $idx),
                    'occupation' => $occupations[$idx % count($occupations)],
                ],
            ];

            // Give roughly half the students a second (mother) guardian
            if ($idx % 2 === 0) {
                $motherFirst = $givenNames[($idx + 3) % count($givenNames)];
                $parents[] = [
                    'first_name' => $motherFirst,
                    'last_name' => $last,
                    'relationship' => 'mother',
                    'email' => strtolower($motherFirst . '.' . $last . str_pad((string)($idx + 2), 3, '0', STR_PAD_LEFT) . '@example.co.ke'),
                    'phone' => $this->phone(721, $idx),
                    'alternate_phone' => $this->phone(734, $idx),
                    'occupation' => $occupations[($idx + 5) % count($occupations)],
                ];
            }

            foreach ($parents as $data) {
                Parents::firstOrCreate(['email' => $data['email']], $data);
            }
        }
    }

    private function phone(int $prefix, int $idx): string
    {
        $suffix = str_pad((string)(100000 + ($idx * 23) % 900000), 6, '0', STR_PAD_LEFT);
        return '0' . $prefix . $suffix;
    }
}
