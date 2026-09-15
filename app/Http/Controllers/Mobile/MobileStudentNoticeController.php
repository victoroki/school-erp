<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\StudentNotice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStudentNoticeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = StudentNotice::with('student', 'creator');

        if ($user->hasRole('Parent')) {
            $studentIds = $this->parentStudentIds($user);
            $query->whereIn('student_id', $studentIds);
        } elseif ($user->hasRole('Student')) {
            $studentId = $this->studentIdFromUser($user);
            $query->where('student_id', $studentId);
        }
        // Admin/Teacher/Owner see all

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

        $request->validate([
            'student_id'  => 'required|exists:students,student_id',
            'title'       => 'required|string|max:255',
            'body'        => 'nullable|string',
            'notice_type' => 'nullable|string|in:general,behavior,academic,medical,attendance,other',
        ]);

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
                'created_at'    => $notice->created_at->toIso8601String(),
            ],
        ]);
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