<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MedicalIncident;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PHASE 5/6 — medical incident reads, server-authorized (STEP 16).
 *
 * PHASE 6: the RBAC proxy is gone — the seed now defines explicit
 * medical.view/medical.manage (granted to Owner/Super Admin/Admin only;
 * Teacher is NOT granted medical access — a Nurse is a staff designation,
 * not an invented role). Portal users see only their own records
 * (Parent→children, Student→self); staff roles must hold medical.view.
 * Mobile WRITE is not offered: no safe existing workflow, so the screen is
 * read-only and the gap is reported, not papered over.
 */
class MobileMedicalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = MedicalIncident::with('student');

        if ($user->hasRole('Parent')) {
            // Parent: scoped to linked children
            $studentIds = $this->parentStudentIds($user);
            $query->whereIn('student_id', $studentIds);
        } elseif ($user->hasRole('Student')) {
            // Student: scoped to self
            $studentId = $this->studentIdFromUser($user);
            $query->where('student_id', $studentId ?: 0);
        } else {
            // Staff: explicit medical permission (PHASE 6 — no more
            // students.* proxy; Teacher is not granted medical.view).
            if (!$user->hasPermission('medical.view')) {
                return response()->json(['message' => 'You are not authorised to view medical records.'], 403);
            }
            // …plus the student-scope clamp so a staff role with medical.view
            // sees their own students, not the whole school infirmary log.
            if (!$user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
                $scoped = app(\App\Services\PortalScopeService::class)->visibleStudentIds($user);
                $query->whereIn('student_id', $scoped ?: [0]);
            }
        }

        $incidents = $query->orderByDesc('incident_date')->limit(50)->get();

        return response()->json($incidents->map(fn($i) => [
            'id'               => $i->medical_incident_id,
            'student_id'       => $i->student_id,
            'student_name'     => $i->student ? ($i->student->first_name . ' ' . $i->student->last_name) : 'Unknown',
            'admission_no'     => $i->student?->admission_no ?? 'N/A',
            'incident_date'    => $i->incident_date?->format('Y-m-d'),
            'symptoms'         => $i->symptoms,
            'details'          => $i->details,
            'treatment_given'  => $i->treatment_given,
            'notified_parents' => $i->notified_parents,
            'marked_by'        => $i->marker?->name ?? 'System',
        ]));
    }

    private function parentStudentIds(User $user): array
    {
        // Parent model linked via student_parents pivot
        $parent = $user->parent ?? null;
        if (!$parent) return [];
        return $parent->students()->pluck('students.student_id')->toArray();
    }

    private function studentIdFromUser(User $user): int
    {
        // Student user linked via students.user_id
        return \App\Models\Student::where('user_id', $user->id)->value('student_id') ?? 0;
    }
}
