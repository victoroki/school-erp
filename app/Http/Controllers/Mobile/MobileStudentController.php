<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentParentRelationship;
use App\Services\TeacherScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStudentController extends Controller
{
    /**
     * GET /api/mobile/students
     *
     * Returns students scoped to the caller's role:
     *  - Admin / Super Admin / Owner: all active students.
     *  - Teacher: students in assigned class sections.
     *  - Parent: linked children only.
     *  - Student: self only.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        $query = Student::where('status', 'active');

        if ($user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
            // Full access — no additional filter.
        } elseif ($user->hasRole('Teacher')) {
            // Teachers see students in every class section they are assigned
            // to: subjects they teach (teacher_subjects) PLUS classes where
            // they are the class teacher.
            $classSectionIds = app(TeacherScopeService::class)->getClassSectionIds($user);
            $query->whereHas('studentClassEnrollments', function ($q) use ($classSectionIds) {
                $q->whereIn('class_section_id', $classSectionIds)
                  ->where('status', 'active');
            });
        } elseif ($user->hasRole('Parent')) {
            $parentRecord = \App\Models\Parents::where('user_id', $user->id)->first();
            if ($parentRecord) {
                $childIds = StudentParentRelationship::where('parent_id', $parentRecord->parent_id)
                    ->pluck('student_id');
                $query->whereIn('students.student_id', $childIds);
            } else {
                $query->whereRaw('1 = 0'); // No parent record → empty set.
            }
        } elseif ($user->hasRole('Student')) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        $students = $query->get([
            'student_id',
            'admission_no',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'phone',
            'status',
            'enrollment_status',
            'education_system',
            'is_hosteller',
            'uses_transport',
            'photo_url',
        ]);

        // Attach the class name from the active enrollment.
        $studentIds = $students->pluck('student_id');
        $enrollments = \App\Models\StudentClassEnrollment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->with('classSection.schoolClass', 'classSection.section')
            ->get()
            ->keyBy('student_id');

        $result = $students->map(function ($s) use ($enrollments) {
            $enrollment = $enrollments->get($s->student_id);
            $className = null;
            $classSectionId = null;
            if ($enrollment?->classSection) {
                $cs = $enrollment->classSection;
                $className = trim(($cs->schoolClass->name ?? '') . ' ' . ($cs->section->name ?? ''));
                $classSectionId = (int) $cs->class_section_id;
            }

            return [
                'student_id'        => $s->student_id,
                'admission_no'      => $s->admission_no,
                'first_name'        => $s->first_name,
                'middle_name'       => $s->middle_name,
                'last_name'         => $s->last_name,
                'gender'            => $s->gender,
                'phone'             => $s->phone,
                'className'         => $className,
                'class_section_id'  => $classSectionId,
                'status'            => $s->status,
                'enrollment_status' => $s->enrollment_status,
                'education_system'  => $s->education_system,
                'is_hosteller'      => $s->is_hosteller,
                'uses_transport'    => $s->uses_transport,
                'photo_url'         => $s->photo_url,
            ];
        });

        return response()->json($result);
    }
}
