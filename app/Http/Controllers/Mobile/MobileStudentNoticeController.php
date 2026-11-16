<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\StudentNotice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PHASE 5 — student notices with server-side authorization (STEP 11).
 *
 * The web controller was already gated (can:student-notices.view/.manage);
 * the mobile twin was not: any authenticated user could POST a notice about
 * any student, and the listing fell through to "see all" for every staff role
 * (Accountant included). Both are fixed here with the same permission slugs
 * the web uses, so the mobile client's gates are presentation only and the
 * server owns the boundary.
 */
class MobileStudentNoticeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = StudentNotice::with('student', 'creator');

        if ($user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
            // Console roles see everything, like the web listing.
        } elseif ($user->hasRole('Parent')) {
            $studentIds = $this->parentStudentIds($user);
            $query->whereIn('student_id', $studentIds);
        } elseif ($user->hasRole('Student')) {
            $studentId = $this->studentIdFromUser($user);
            $query->where('student_id', $studentId ?: 0);
        } elseif ($user->hasRole('Teacher') && $user->hasPermission('student-notices.view')) {
            // Teachers keep their portal scope (their section's students),
            // not the whole school.
            $scoped = app(\App\Services\PortalScopeService::class)->visibleStudentIds($user);
            $query->whereIn('student_id', $scoped ?: [0]);
        } else {
            // Any other role needs the view permission explicitly — the old
            // fall-through handed Accountant (and future roles) full visibility.
            if (!$user->hasPermission('student-notices.view')) {
                return response()->json(['message' => 'You are not authorised to view student notices.'], 403);
            }
        }

        $notices = $query->orderByDesc('created_at')->limit(50)->get();

        return response()->json($notices->map(fn($n) => [
            'id'            => $n->id,
            'student_id'    => $n->student_id,
            'student_name'  => $n->student ? ($n->student->first_name . ' ' . $n->student->last_name) : 'Unknown',
            'admission_no'  => $n->student?->admission_no ?? 'N/A',
            'title'         => $n->title,
            'body'          => $n->body,
            'notice_type'   => $n->notice_type,
            'created_by'    => $n->creator?->name ?? 'System',
            'created_at'    => $n->created_at->toIso8601String(),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // SERVER-side enforcement: Students and Parents must never create
        // notices (STEP 11), regardless of what the client shows.
        if (!$user->hasPermission('student-notices.manage')) {
            return response()->json(['message' => 'You are not authorised to create student notices.'], 403);
        }

        $request->validate([
            'student_id'  => 'required|exists:students,student_id',
            'title'       => 'required|string|max:255',
            'body'        => 'nullable|string|max:5000',
            'notice_type' => 'nullable|string|in:general,behavior,academic,medical,attendance,other',
        ]);

        // Admin/console may target any student; Teachers are clamped to their
        // teaching scope so a crafted request cannot notice an unrelated child.
        if (!$user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
            $scoped = app(\App\Services\PortalScopeService::class)->visibleStudentIds($user);
            if (!in_array((int) $request->student_id, array_map('intval', $scoped), true)) {
                return response()->json(['message' => 'This student is outside your scope.'], 403);
            }
        }

        $notice = StudentNotice::create([
            'student_id'  => $request->student_id,
            'created_by'  => $user->id,
            'title'       => $request->title,
            'body'        => $request->body,
            'notice_type' => $request->notice_type ?? 'general',
        ]);

        return response()->json([
            'message' => 'Student notice created successfully',
            'data'    => [
                'id'            => $notice->id,
                'student_id'    => $notice->student_id,
                'title'         => $notice->title,
                'body'          => $notice->body,
                'notice_type'   => $notice->notice_type,
                'created_by'    => $notice->creator?->name ?? $user->name,
                'created_at'    => $notice->created_at->toIso8601String(),
            ],
        ], 201);
    }

    private function parentStudentIds(User $user): array
    {
        $parent = $user->parent ?? null;
        if (!$parent) return [];
        return $parent->students()->pluck('students.student_id')->toArray();
    }

    private function studentIdFromUser(User $user): int
    {
        return Student::where('user_id', $user->id)->value('student_id') ?? 0;
    }
}
