<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PHASE 5 — real discipline records with server-side authorization.
 *
 * Reads: portal roles (Parent/Student) see ONLY their own/children's records
 * (unchanged Phase-0 behaviour, now also limited). Staff must hold
 * discipline.view — the same permission the web controller enforces — and see
 * records within their student scope (PortalScopeService), not everything by
 * role accident. Writes: discipline.manage only (Owner/Super Admin/Admin —
 * Teacher is NOT a holder in the seeded RBAC, and the mobile button is gated
 * by the same server-provided permission list, never by a mobile guess).
 */
class MobileDisciplineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DisciplinaryRecord::with(['student', 'reporter']);

        if ($user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
            // Console roles see everything, like the web listing.
        } elseif ($user->hasRole('Parent')) {
            $studentIds = $this->parentStudentIds($user);
            $query->whereIn('student_id', $studentIds);
        } elseif ($user->hasRole('Student')) {
            $studentId = $this->studentIdFromUser($user);
            $query->where('student_id', $studentId ?: 0);
        } else {
            // Every other role must hold the view permission — Accountant,
            // Teacher and any future role no longer inherit full visibility
            // as a fall-through.
            if (!$user->hasPermission('discipline.view')) {
                return response()->json(['message' => 'You are not authorised to view discipline records.'], 403);
            }
            $scoped = app(\App\Services\PortalScopeService::class)->visibleStudentIds($user);
            $query->whereIn('student_id', $scoped ?: [0]);
        }

        $records = $query->latest('incident_date')->limit(100)->get();

        return response()->json([
            'data' => $records->map(fn($r) => [
                'id'               => $r->disciplinary_record_id,
                'student_id'       => $r->student_id,
                'student_name'     => $r->student ? ($r->student->first_name . ' ' . $r->student->last_name) : 'Unknown',
                'admission_no'     => $r->student?->admission_no ?? 'N/A',
                'date'             => $r->incident_date?->format('Y-m-d'),
                'incident_type'    => $r->incident_type,
                'description'      => $r->description,
                'offense'          => $r->incident_type . ': ' . $r->description,
                'action_taken'     => $r->action_taken ?? 'None yet',
                'follow_up_status' => $r->status ?? 'open',
                'reported_by'      => $r->reporter?->name,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // SERVER-side enforcement (STEP 14): the mobile gate is cosmetic; this
        // check is the boundary. Portal roles can never file records.
        if (!$user->hasPermission('discipline.manage')) {
            return response()->json(['message' => 'You are not authorised to create discipline records.'], 403);
        }

        $request->validate([
            'student_id'    => 'required|exists:students,student_id',
            'date'          => 'nullable|date|before_or_equal:today',
            'incident_type' => 'nullable|string|max:100',
            'offense'       => 'required|string|max:1000',
            'action_taken'  => 'nullable|string|max:1000',
            'status'        => 'nullable|in:open,investigating,closed',
        ]);

        // Admin/console may target any student; narrower manage holders are
        // clamped to their visible scope (same rule as web data access).
        if (!$user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
            $scoped = app(\App\Services\PortalScopeService::class)->visibleStudentIds($user);
            if (!in_array((int) $request->student_id, array_map('intval', $scoped), true)) {
                return response()->json(['message' => 'This student is outside your scope.'], 403);
            }
        }

        $record = DisciplinaryRecord::create([
            'student_id'    => $request->student_id,
            'incident_date' => $request->date ?? date('Y-m-d'),
            'incident_type' => $request->incident_type ?: 'Misconduct',
            'description'   => $request->offense,
            'action_taken'  => $request->action_taken,
            // Web vocabulary is open|investigating|closed; a new mobile
            // incident starts OPEN, never the old stray 'pending'.
            'status'        => $request->status ?? 'open',
            'reported_by'   => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Discipline record stored successfully',
            'data'    => [
                'id'               => $record->disciplinary_record_id,
                'student_id'       => $record->student_id,
                'date'             => $record->incident_date?->format('Y-m-d'),
                'incident_type'    => $record->incident_type,
                'offense'          => $record->incident_type . ': ' . $record->description,
                'action_taken'     => $record->action_taken ?? 'None yet',
                'follow_up_status' => $record->status,
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
