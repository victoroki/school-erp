<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Parents;
use App\Models\StudentParentRelationship;

class StudentParentRelationshipSeeder extends Seeder
{
    // MUST stay identical to the list in ParentsSeeder so the parent emails
    // generated here resolve to the parents already created there.
    private const GIVEN_NAMES = ['David', 'Mary', 'James', 'Grace', 'Peter', 'Faith', 'John', 'Esther', 'George', 'Sarah', 'Paul', 'Ruth', 'Stephen', 'Mercy', 'Daniel', 'Lucy', 'Samuel', 'Joyce', 'Michael', 'Agnes', 'Joseph', 'Rose', 'Brian', 'Janet', 'Charles', 'Catherine', 'Patrick', 'Maryanne', 'Kennedy', 'Veronica'];

    public function run(): void
    {
        $students = Student::all();
        if ($students->count() === 0) {
            return;
        }

        foreach ($students as $student) {
            $idx = (int) substr(str_replace(['/', 'ADM'], '', $student->admission_no), -3);
            $last = $student->last_name;
            $g = self::GIVEN_NAMES;

            $fatherEmail = strtolower($g[$idx % count($g)] . '.' . $last . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT) . '@example.co.ke');

            $this->link($student, $fatherEmail, true);

            // Mirror ParentsSeeder: even-indexed students get a mother guardian
            if ($idx % 2 === 0) {
                $motherEmail = strtolower($g[($idx + 3) % count($g)] . '.' . $last . str_pad((string)($idx + 2), 3, '0', STR_PAD_LEFT) . '@example.co.ke');
                $this->link($student, $motherEmail, false);
            }
        }
    }

    private function link(Student $student, string $email, bool $primary): void
    {
        $parent = Parents::where('email', $email)->first();
        if (!$parent) {
            return;
        }

        StudentParentRelationship::firstOrCreate(
            [
                'student_id' => $student->student_id,
                'parent_id' => $parent->parent_id,
            ],
            ['is_primary_contact' => $primary]
        );
    }
}
