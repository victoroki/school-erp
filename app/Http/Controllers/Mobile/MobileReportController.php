<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\StudentParentRelationship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileReportController extends Controller
{
    /**
     * GET /api/mobile/reports/report-cards
     *
     * Returns a flat list of report cards, one per student per exam, shaped
     * to match the mobile ReportCard contract:
     *   { examName, term, year, student_name, className, average, position, comment, subjects:[{subject,score,grade}] }
     */
    public function reportCards(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        $studentIds = $this->visibleStudentIds($user);

        $results = ExamResult::whereIn('student_id', $studentIds)
            ->with(['exam.academicYear.terms', 'subject', 'student', 'grade'])
            ->get();

        // Group by exam, then by student, computing raw cards with averages.
        $perExam = $results->groupBy('exam_id');
        $cards = [];

        foreach ($perExam as $examResults) {
            $exam = $examResults->first()->exam;

            $studentCards = $examResults->groupBy('student_id')->map(function ($sResults, $studentId) {
                $student = $sResults->first()->student;

                $subjects = $sResults->map(function ($r) {
                    return [
                        'subject' => $r->subject?->name ?? 'Unknown',
                        'score'   => (float) $r->marks_obtained,
                        'grade'   => $r->grade?->name ?? '-',
                    ];
                });

                $avg = $subjects->avg('score');

                // Class/section from the student's active enrollment.
                $className = '';
                $enrollment = \App\Models\StudentClassEnrollment::where('student_id', $studentId)
                    ->where('status', 'active')
                    ->with('classSection.class', 'classSection.section')
                    ->first();
                if ($enrollment && $enrollment->classSection) {
                    $className = trim(
                        ($enrollment->classSection->class?->name ?? '')
                        . ($enrollment->classSection->section?->name ? ' ' . $enrollment->classSection->section->name : '')
                    );
                }

                return [
                    'student_id'   => (int) $studentId,
                    'student_name' => trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')),
                    'className'    => $className,
                    'average'      => round($avg, 1),
                    'subjects'     => $subjects->values(),
                ];
            })->sortByDesc('average')->values();

            // Per-exam position = 1-indexed rank by average.
            $rank = 0;
            $termName = $exam?->academicYear?->terms?->first()?->name ?? '';
            foreach ($studentCards as $i => $studentCard) {
                $rank++;
                $blueprint = [
                    'examName'     => $exam?->name ?? 'Exam',
                    'term'         => $termName,
                    'year'         => $exam?->academicYear?->name ?? '',
                    'student_name' => $studentCard['student_name'],
                    'className'    => $studentCard['className'],
                    'average'      => $studentCard['average'],
                    'position'     => $rank,
                    'subjects'     => $studentCard['subjects'],
                    // No teacher comment source on the API contract yet.
                    'comment'      => null,
                ];
                $cards[] = $blueprint;
            }
        }

        return response()->json($cards);
    }

    /**
     * Resolve the student IDs visible to the given user.
     */
    private function visibleStudentIds($user): array
    {
        if ($user->hasAnyRole(['Owner', 'Super Admin', 'Admin', 'Teacher'])) {
            return Student::where('status', 'active')->pluck('student_id')->toArray();
        }

        if ($user->hasRole('Parent')) {
            $parentRecord = \App\Models\Parents::where('user_id', $user->id)->first();
            if ($parentRecord) {
                return StudentParentRelationship::where('parent_id', $parentRecord->parent_id)
                    ->pluck('student_id')
                    ->toArray();
            }
            return [];
        }

        if ($user->hasRole('Student')) {
            $student = Student::where('user_id', $user->id)->first();
            return $student ? [$student->student_id] : [];
        }

        return [];
    }
}
