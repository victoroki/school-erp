<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'users.index', 'users.create', 'users.edit', 'users.delete',
            'roles.index', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.index', 'permissions.create', 'permissions.edit', 'permissions.delete',
            'academic-years.index', 'academic-years.create', 'academic-years.edit', 'academic-years.delete',
            'school-classes.index', 'school-classes.create', 'school-classes.edit', 'school-classes.delete',
            'sections.index', 'sections.create', 'sections.edit', 'sections.delete',
            'class-sections.index', 'class-sections.create', 'class-sections.edit', 'class-sections.delete',
            'subjects.index', 'subjects.create', 'subjects.edit', 'subjects.delete',
            'class-subjects.index', 'class-subjects.create', 'class-subjects.edit', 'class-subjects.delete',
            'teacher-subjects.index', 'teacher-subjects.create', 'teacher-subjects.edit', 'teacher-subjects.delete',
            'periods.index', 'periods.create', 'periods.edit', 'periods.delete',
            'classrooms.index', 'classrooms.create', 'classrooms.edit', 'classrooms.delete',
            'timetables.index', 'timetables.create', 'timetables.edit', 'timetables.delete',
            'exam-types.index', 'exam-types.create', 'exam-types.edit', 'exam-types.delete',
            'grading-scales.index', 'grading-scales.create', 'grading-scales.edit', 'grading-scales.delete',
            'exams.index', 'exams.create', 'exams.edit', 'exams.delete',
            'exam-schedules.index', 'exam-schedules.create', 'exam-schedules.edit', 'exam-schedules.delete',
            'exam-results.index', 'exam-results.create', 'exam-results.edit', 'exam-results.delete',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'permission_name' => $name,
            ], [
                'description' => '',
            ]);
        }
    }
}
