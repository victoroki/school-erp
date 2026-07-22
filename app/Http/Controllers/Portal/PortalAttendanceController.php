<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PortalAttendanceController extends Controller
{
    /**
     * Show attendance summary and monthly breakdown for the student.
     */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $student = $this->resolveStudent($user);

        if (!$student) {
            return view('portal.attendance', [
                'records'     => collect(),
                'summary'     => [],
                'message'     => 'No student profile found.',
                'currentMonth' => Carbon::now()->format('F Y'),
            ]);
        }

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth   = Carbon::parse($month)->endOfMonth();

        $records = StudentAttendance::with('classSection')
            ->where('student_id', $student->student_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get();

        $summary = [
            'present' => $records->where('status', 'present')->count(),
            'absent'  => $records->where('status', 'absent')->count(),
            'late'    => $records->where('status', 'late')->count(),
            'excused' => $records->where('status', 'excused')->count(),
            'total'   => $records->count(),
        ];

        return view('portal.attendance', [
            'records'      => $records,
            'summary'      => $summary,
            'currentMonth' => $startOfMonth->format('F Y'),
            'month'        => $month,
            'student'      => $student,
        ]);
    }

    /**
     * Resolve the student record for the authenticated user.
     */
    protected function resolveStudent($user): ?Student
    {
        if ($user->user_type === 'student' && $user->student) {
            return $user->student;
        }

        if ($user->user_type === 'parent' && $user->parent) {
            return $user->parent->students->first();
        }

        return null;
    }
}
