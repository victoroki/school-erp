<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MedicalIncident;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            $query->where('student_id', $studentId);
        }
        // Admin/Teacher/Owner see all (no scoping)

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