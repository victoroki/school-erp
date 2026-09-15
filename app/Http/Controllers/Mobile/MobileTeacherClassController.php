<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\Staff;
use App\Models\TeacherSubject;
use App\Services\TeacherScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/mobile/teacher/classes
 *
 * Returns the authenticated teacher's assigned class sections together with
 * the subjects they are allowed to teach/homework/marks in that section.
 *
 * Available to teachers and admins. Admins/Super Admins get all classes with
 * all subjects (so they can create homework for any class). Teachers get
 * exactly their own assignments:
 *   - subjects from the teacher_subjects pivot for that section, plus
 *   - every subject offered in the section when they are the class teacher.
 */
class MobileTeacherClassController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles.permissions');
        $year = AcademicYear::where('is_current', true)->first();

        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return $this->allClasses($year);
        }

        if (! $user->hasRole('Teacher')) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $staff = Staff::where('user_id', $user->id)->first();
        if (! $staff) {
            return response()->json(['error' => 'Staff record not found for this user.'], 404);
        }

        $scope = app(TeacherScopeService::class);
        $sectionIds = $scope->getClassSectionIds($user);

        if ($sectionIds->isEmpty()) {
            return response()->json([]);
        }

        $sections = ClassSection::with(['schoolClass', 'section', 'teacherSubjects.subject', 'classTeacher'])
            ->whereIn('class_section_id', $sectionIds)
            ->get();

        $classTeacherIds = ClassSection::whereIn('class_section_id', $sectionIds)
            ->where('class_teacher_id', $staff->staff_id)
            ->pluck('class_section_id');

        // Subjects offered in the classes where this teacher is the class
        // teacher (they can homework/marks any subject there).
        $classTeacherClassIds = ClassSection::whereIn('class_section_id', $classTeacherIds)
            ->pluck('class_id')
            ->unique();

        $classTeacherSubjects = $classTeacherClassIds->isEmpty()
            ? collect()
            : ClassSubject::with('subject')
                ->whereIn('class_id', $classTeacherClassIds)
                ->get()
                ->map(fn ($cs) => ['subject_id' => $cs->subject_id, 'subject_name' => $cs->subject?->name])
                ->filter(fn ($s) => $s['subject_id'] !== null)
                ->values();

        $result = $sections->map(function ($section) use ($staff, $classTeacherIds, $classTeacherSubjects) {
            $isClassTeacher = $classTeacherIds->contains($section->class_section_id);

            // Subjects explicitly assigned via teacher_subjects.
            $assigned = $section->teacherSubjects
                ->filter(fn ($ts) => $ts->subject !== null)
                ->map(fn ($ts) => [
                    'subject_id'   => $ts->subject->subject_id,
                    'subject_name' => $ts->subject->name,
                ]);

            // Class teachers may homework any subject offered in the class.
            $subjects = $isClassTeacher
                ? $assigned->merge($classTeacherSubjects)
                : $assigned;

            return [
                'class_section_id' => $section->class_section_id,
                'class_id'         => $section->class_id,
                'class_name'       => $section->schoolClass?->name ?? 'Class',
                'section_name'     => $section->section?->name ?? '',
                'is_class_teacher' => $isClassTeacher,
                'subjects'         => $subjects->unique('subject_id')->values(),
            ];
        });

        return response()->json($result);
    }

    /**
     * Admin variant: every class section with every subject offered in it.
     */
    private function allClasses(?AcademicYear $year): JsonResponse
    {
        $sections = ClassSection::with(['schoolClass', 'section'])
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->academic_year_id))
            ->orderBy('class_id')
            ->get();

        $classIds = $sections->pluck('class_id')->unique();

        $subjectsByClass = ClassSubject::with('subject')
            ->whereIn('class_id', $classIds)
            ->get()
            ->groupBy('class_id')
            ->map(fn ($rows) => $rows
                ->filter(fn ($cs) => $cs->subject !== null)
                ->map(fn ($cs) => [
                    'subject_id'   => $cs->subject->subject_id,
                    'subject_name' => $cs->subject->name,
                ])
                ->unique('subject_id')
                ->values());

        $result = $sections->map(function ($section) use ($subjectsByClass) {
            return [
                'class_section_id' => $section->class_section_id,
                'class_id'         => $section->class_id,
                'class_name'       => $section->schoolClass?->name ?? 'Class',
                'section_name'     => $section->section?->name ?? '',
                'is_class_teacher' => false,
                'subjects'         => $subjectsByClass->get($section->class_id, collect()),
            ];
        });

        return response()->json($result);
    }
}