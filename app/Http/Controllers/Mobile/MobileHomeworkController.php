<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\Parents;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentParentRelationship;
use App\Models\User;
use App\Services\TeacherScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileHomeworkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Homework::where('status', 'active');

        if ($user->hasRole('Teacher')) {
            $query->where('created_by', $user->id);
        } elseif ($user->hasRole('Admin') || $user->hasRole('Super Admin') || $user->hasRole('Owner')) {
            // Staff portal roles with homework.manage see everything.
            if (!$user->hasPermission('homework.manage')) {
                $query->where('created_by', $user->id);
            }
        } else {
            // Parent / Student / anyone else: only homework addressed to their
            // (children's) class(es) — never the whole school's active list.
            // Matching is done on the normalized labels because homework
            // stores class_name/subject as free strings.
            $labels = $this->visibleClassLabels($user);
            $homeworks = $query->orderByDesc('created_at')->limit(200)->get()
                ->filter(function ($h) use ($labels) {
                    // No class named = school-wide announcement.
                    if (!$h->class_name || trim($h->class_name) === '') {
                        return true;
                    }
                    return in_array($this->normalize($h->class_name), $labels, true);
                })
                ->values();

            return response()->json($this->mapHomeworks($homeworks));
        }

        return response()->json($this->mapHomeworks($query->orderByDesc('created_at')->limit(50)->get()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // The web route enforces homework.manage; the mobile API must not be a
        // bypass. Admin/Super Admin pass via their role permissions.
        if (!$user->hasPermission('homework.manage')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject'     => 'nullable|string|max:255',
            'class_name'  => 'nullable|string|max:255',
            'due_date'    => 'nullable|date',
        ]);

        // A teacher may only assign to a class they are actually assigned to
        // (same rule as the class dropdown on the device) — enforced here, not
        // just hidden client-side.
        if ($user->hasRole('Teacher') && !$user->hasAnyRole(['Owner', 'Super Admin', 'Admin']) && $request->filled('class_name')) {
            $assignedLabels = $this->teacherClassLabels($user);
            if (!in_array($this->normalize($request->class_name), $assignedLabels, true)) {
                return response()->json(['message' => 'You can only set homework for your assigned classes.'], 403);
            }
        }

        $homework = Homework::create([
            'created_by'  => $user->id,
            'title'       => $request->title,
            'description' => $request->description,
            'subject'     => $request->subject,
            'class_name'  => $request->class_name,
            'due_date'    => $request->due_date,
            'status'      => 'active',
        ]);

        return response()->json([
            'message' => 'Homework created successfully',
            'data' => [
                'id'          => $homework->id,
                'title'       => $homework->title,
                'description' => $homework->description,
                'subject'     => $homework->subject,
                'class_name'  => $homework->class_name,
                'due_date'    => $homework->due_date?->format('Y-m-d'),
                'created_at'  => $homework->created_at->toIso8601String(),
            ],
        ]);
    }

    private function mapHomeworks($homeworks): array
    {
        return $homeworks->map(fn ($h) => [
            'id'          => $h->id,
            'title'       => $h->title,
            'description' => $h->description,
            'subject'     => $h->subject,
            'class_name'  => $h->class_name,
            'due_date'    => $h->due_date?->format('Y-m-d'),
            'created_by'  => $h->creator?->name ?? 'System',
            'created_at'  => $h->created_at->toIso8601String(),
        ])->all();
    }

    /**
     * Normalized "Form 1 A" style labels for the sections a portal user may
     * see homework for: the student's own active enrollment(s), or every
     * active enrollment of a parent's linked children.
     */
    private function visibleClassLabels(User $user): array
    {
        $studentIds = collect();

        if ($user->hasRole('Student')) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $studentIds->push($student->student_id);
            }
        }

        if ($user->hasRole('Parent')) {
            $parent = Parents::where('user_id', $user->id)->first();
            if ($parent) {
                $studentIds = $studentIds->merge(
                    StudentParentRelationship::where('parent_id', $parent->parent_id)->pluck('student_id')
                );
            }
        }

        if ($studentIds->isEmpty()) {
            return [];
        }

        return StudentClassEnrollment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->with(['classSection.schoolClass', 'classSection.section'])
            ->get()
            ->map(fn ($e) => $this->normalize(
                trim(($e->classSection?->schoolClass?->name ?? '') . ' ' . ($e->classSection?->section?->name ?? ''))
            ))
            ->filter(fn ($l) => $l !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalized labels of the sections a teacher may assign homework to —
     * mirrors TeacherScopeService (subject assignments ∪ class-teacher).
     */
    private function teacherClassLabels($user): array
    {
        $ids = app(TeacherScopeService::class)->getClassSectionIds($user);
        if ($ids->isEmpty()) {
            return [];
        }

        return \App\Models\ClassSection::with(['schoolClass', 'section'])
            ->whereIn('class_section_id', $ids)
            ->get()
            ->map(fn ($cs) => $this->normalize(
                trim(($cs->schoolClass?->name ?? '') . ' ' . ($cs->section?->name ?? ''))
            ))
            ->filter(fn ($l) => $l !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalize(?string $label): string
    {
        // Collapse whitespace + case so "Form 1  A", "form 1 a" and the
        // mobile's trim/class_name-section_name join all compare equal.
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $label)));
    }
}
