<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileDisciplineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DisciplinaryRecord::with('student');

        if ($user->hasRole('Parent')) {
            $studentIds = $this->parentStudentIds($user);
            $query->whereIn('student_id', $studentIds);
        } elseif ($user->hasRole('Student')) {
            $studentId = $this->studentIdFromUser($user);
            $query->where('student_id', $studentId);
        }

        $records = $query->latest('incident_date')->get();

        return response()->json([
            'data' => $records->map(fn($r) => [
                'id'              => $r->disciplinary_record_id,
                'student_name'    => $r->student ? ($r->student->first_name . ' ' . $r->student->last_name) : 'Unknown',
                'admission_no'    => $r->student?->admission_no ?? 'N/A',
                'date'            => $r->incident_date?->format('Y-m-d'),
                'offense'         => $r->incident_type . ': ' . $r->description,
                'action_taken'    => $r->action_taken ?? 'None yet',
                'follow_up_status'=> $r->status ?? 'pending',
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id'  => 'required|exists:students,student_id',
            'offense'     => 'required|string',
            'action_taken'=> 'nullable|string',
        ]);

        $record = DisciplinaryRecord::create([
            'student_id'    => $request->student_id,
            'incident_date' => $request->date ?? date('Y-m-d'),
            'incident_type' => 'Misconduct',
            'description'   => $request->offense,
            'action_taken'  => $request->action_taken,
            'status'        => 'pending',
            'reported_by'   => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Discipline record stored successfully',
            'data'    => $record,
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