<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Parents;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentParentRelationship;
use App\Models\User;
use App\Services\PortalScopeService;
use App\Services\TeacherScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MobileHomeworkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Homework::where('status', 'active');

        if ($user->hasRole('Teacher')) {
            $query->where('created_by', $user->id)->with('submissions');
        } elseif ($user->hasRole('Admin') || $user->hasRole('Super Admin') || $user->hasRole('Owner')) {
            // Staff portal roles with homework.manage see everything.
            if (!$user->hasPermission('homework.manage')) {
                $query->where('created_by', $user->id);
            }
            $query->with('submissions');
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

    /**
     * GET /api/mobile/homework/{homeworkId}
     *
     * Homework detail. For portal users the response includes my_submission —
     * the OWN submission of the authenticated student, or of the parent's
     * ?student_id= child (validated against the parent↔child link).
     */
    public function show(Request $request, int $homeworkId): JsonResponse
    {
        $user = $request->user();
        $homework = Homework::with('submissions')->find($homeworkId);

        if (!$homework) {
            return response()->json(['message' => 'Homework not found.'], 404);
        }

        $portalStudentId = null; // student this caller's my_submission refers to (or null)
        $isStaffView = false;

        if (in_array('Teacher', $user->roles->pluck('role_name')->all()) ||
            $user->hasAnyRole(['Admin', 'Super Admin', 'Owner'])) {
            if (!$this->canManageHomework($user, $homework)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            $isStaffView = true;
        } else {
            // Parent can pass ?student_id= to view a specific linked child.
            if ($user->hasRole('Parent')) {
                $parent = Parents::where('user_id', $user->id)->first();
                if (!$parent) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
                $requested = (int) $request->query('student_id', 0);
                if ($requested > 0) {
                    $linked = StudentParentRelationship::where('parent_id', $parent->parent_id)
                        ->where('student_id', $requested)
                        ->exists();
                    if (!$linked) {
                        return response()->json(['message' => 'Forbidden.'], 403);
                    }
                    $portalStudentId = $requested;
                }
            } elseif ($user->hasRole('Student')) {
                $student = PortalScopeService::selfStudent($user);
                if (!$student) {
                    return response()->json(['message' => 'No student record for this account.'], 404);
                }
                $portalStudentId = (int) $student->student_id;
            }

            // Portal users only see homework for their (child's) class.
            if (!$this->homeworkVisibleToPortal($homework, $portalStudentId === null ? $user : null, $portalStudentId)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        $payload = $this->mapHomeworkDetail($homework);
        if ($isStaffView) {
            $payload['submissions'] = $this->countsFor($homework);
        }
        $payload['can_submit'] = $user->hasRole('Student') && $portalStudentId !== null;

        if ($portalStudentId !== null) {
            $submission = HomeworkSubmission::where('homework_id', $homework->id)
                ->where('student_id', $portalStudentId)
                ->first();
            $payload['my_submission'] = $submission ? $this->mapSubmission($submission) : null;
        }

        return response()->json($payload);
    }

    /**
     * GET /api/mobile/homework/{homeworkId}/submissions
     *
     * Teacher review feed: server-authoritative roster counts + the submission
     * list for ONE homework the caller can manage (creator, or admin roles
     * holding homework.manage). Never school-wide by fall-through.
     */
    public function submissions(Request $request, int $homeworkId): JsonResponse
    {
        $user = $request->user();
        $homework = Homework::with('submissions.student')->find($homeworkId);

        if (!$homework) {
            return response()->json(['message' => 'Homework not found.'], 404);
        }

        if (!$this->canManageHomework($user, $homework)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'homework' => [
                'id'         => $homework->id,
                'title'      => $homework->title,
                'subject'    => $homework->subject,
                'class_name' => $homework->class_name,
                'due_date'   => $homework->due_date?->toDateString(),
            ],
            'counts' => $this->countsFor($homework),
            'submissions' => $homework->submissions
                ->sortByDesc(fn ($s) => $s->submitted_at)
                ->map(fn ($s) => $this->mapSubmission($s))
                ->values()
                ->all(),
        ]);
    }

    /**
     * POST /api/mobile/homework/{homeworkId}/submit
     *
     * Student only. The server resolves the student from the token — any
     * device-sent student_id is ignored. Class eligibility enforced against
     * the homework's normalized class_name. One row per (homework, student);
     * resubmission replaces it unless the teacher already reviewed it.
     * Attachment (multipart, optional) is stored on the private local disk.
     */
    public function submit(Request $request, int $homeworkId): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('Student')) {
            return response()->json(['message' => 'Only students can submit homework.'], 403);
        }

        $homework = Homework::find($homeworkId);
        if (!$homework || $homework->status !== 'active') {
            return response()->json(['message' => 'Homework not found.'], 404);
        }

        $student = PortalScopeService::selfStudent($user);
        if (!$student) {
            return response()->json(['message' => 'No student record for this account.'], 404);
        }

        if (!$this->homeworkVisibleToPortal($homework, null, (int) $student->student_id)) {
            return response()->json(['message' => 'This homework is not assigned to your class.'], 403);
        }

        $request->validate([
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        if (!$request->filled('content') && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Provide an answer (text or attachment) to submit.'], 422);
        }

        $existing = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->student_id)
            ->first();

        if ($existing && $existing->status === 'reviewed') {
            return response()->json(['message' => 'This submission has already been reviewed and can no longer be changed.'], 422);
        }

        $isLate = $homework->isLateSubmitted();

        $data = [
            'homework_id'  => $homework->id,
            'student_id'   => $student->student_id,
            'status'       => $isLate ? 'late' : 'submitted',
            'content'      => $request->input('content') ?? $existing?->content,
            'submitted_at' => now(),
            // Keep any prior "returned" feedback so the student can still see
            // why they were asked to resubmit; reviewed_* reset because the
            // teacher must look at the new work.
            'teacher_feedback' => $existing?->teacher_feedback,
            'reviewed_by'  => null,
            'reviewed_at'  => null,
        ];

        if ($request->hasFile('attachment')) {
            // Private local disk only — never the public/uploads symlink so
            // student work is never world-readable. Served via the
            // authorization-checked download route only.
            $path = $request->file('attachment')->store("homework_submissions/{$homework->id}", 'local');
            if ($existing?->attachment_path && $existing->attachment_path !== $path) {
                Storage::disk('local')->delete($existing->attachment_path);
            }
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $request->file('attachment')->getClientOriginalName();
            $data['attachment_mime'] = $request->file('attachment')->getMimeType();
        }

        $submission = $existing
            ? tap($existing)->update($data)
            : HomeworkSubmission::create($data);

        return response()->json([
            'message' => $isLate ? 'Homework submitted late.' : 'Homework submitted successfully.',
            'data' => $this->mapSubmission($submission),
        ]);
    }

    /**
     * PATCH /api/mobile/homework/submissions/{submissionId}/review
     *
     * Teacher feedback + status flip, restricted to the homework creator (or
     * an admin role with homework.manage). reviewed stays reviewed (feedback
     * can be edited); returned clears review stamps so the student may
     * resubmit.
     */
    public function review(Request $request, int $submissionId): JsonResponse
    {
        $user = $request->user();
        $submission = HomeworkSubmission::with('homework')->find($submissionId);

        if (!$submission) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        if (!$this->canManageHomework($user, $submission->homework)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'status'   => 'required|in:reviewed,returned',
            'feedback' => 'nullable|string',
        ]);

        if ($request->input('status') === 'reviewed') {
            $submission->update([
                'status'           => 'reviewed',
                'teacher_feedback' => $request->filled('feedback') ? $request->input('feedback') : $submission->teacher_feedback,
                'reviewed_by'      => $user->id,
                'reviewed_at'      => now(),
            ]);
        } else {
            $submission->update([
                'status'           => 'returned',
                'teacher_feedback' => $request->filled('feedback') ? $request->input('feedback') : $submission->teacher_feedback,
                'reviewed_by'      => null,
                'reviewed_at'      => null,
            ]);
        }

        return response()->json([
            'message' => 'Submission ' . $request->input('status') . '.',
            'data' => $this->mapSubmission($submission->fresh(['homework'])),
        ]);
    }

    /**
     * GET /api/mobile/homework/submissions/{submissionId}/attachment
     *
     * Private, authorization-checked download. Only the submitting student,
     * their parents, the homework creator, or an admin role with
     * homework.manage may download the file. Never world-readable.
     */
    public function attachment(Request $request, int $submissionId)
    {
        $user = $request->user();
        $submission = HomeworkSubmission::with('homework')->find($submissionId);

        if (!$submission || !$submission->attachment_path) {
            return response()->json(['message' => 'Attachment not found.'], 404);
        }

        if (!$this->canReadSubmission($user, $submission)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (!Storage::disk('local')->exists($submission->attachment_path)) {
            return response()->json(['message' => 'Attachment not found.'], 404);
        }

        return Storage::disk('local')->download(
            $submission->attachment_path,
            $submission->attachment_name ?: basename($submission->attachment_path)
        );
    }

    // ── Mapping / helpers ─────────────────────────────────────────────────

    private function mapHomeworks($homeworks): array
    {
        return $homeworks->map(fn ($h) => $this->mapHomeworkRow($h))->all();
    }

    private function mapHomeworkRow(Homework $h): array
    {
        $row = [
            'id'          => $h->id,
            'title'       => $h->title,
            'description' => $h->description,
            'subject'     => $h->subject,
            'class_name'  => $h->class_name,
            'due_date'    => $h->due_date?->format('Y-m-d'),
            'created_by'  => $h->creator?->name ?? 'System',
            'created_at'  => $h->created_at->toIso8601String(),
        ];

        // PHASE 6 — additive per-row aggregates for staff lists
        // (submitted/reviewed/missing are real, server-computed).
        if (isset($h->submissions) && $h->submissions !== null) {
            $row['submission_counts'] = $this->countsFor($h);
        }

        return $row;
    }

    private function mapHomeworkDetail(Homework $h): array
    {
        return [
            'id'          => $h->id,
            'title'       => $h->title,
            'description' => $h->description,
            'subject'     => $h->subject,
            'class_name'  => $h->class_name,
            'due_date'    => $h->due_date?->format('Y-m-d'),
            'created_by'  => $h->creator?->name ?? 'System',
            'created_at'  => $h->created_at->toIso8601String(),
        ];
    }

    private function mapSubmission(HomeworkSubmission $s): array
    {
        return [
            'id'                  => $s->id,
            'homework_id'         => $s->homework_id,
            'student_id'          => (int) $s->student_id,
            'student_name'        => $s->student ? trim($s->student->first_name . ' ' . $s->student->last_name) : 'Unknown',
            'admission_no'        => $s->student?->admission_no,
            'status'              => $s->status,
            'late'                => $s->status === 'late',
            'content'             => $s->content,
            'submitted_at'        => $s->submitted_at?->toIso8601String(),
            'teacher_feedback'    => $s->teacher_feedback,
            'reviewed_at'         => $s->reviewed_at?->toIso8601String(),
            'attachment'          => $s->attachment_path ? [
                'name' => $s->attachment_name,
                'mime' => $s->attachment_mime,
            ] : null,
        ];
    }

    /**
     * Server-authoritative submission counts. assigned/missing depend on a
     * roster (active enrollments in the current year whose section label
     * equals the homework's class_name); when the free-text class_name does
     * not match any section the counts render "roster unavailable" rather
     * than a fake 100%-missing.
     */
    private function countsFor(Homework $h): array
    {
        $rosterAvailable = false;
        $assigned = null;
        $homeworkLabel = $this->normalize($h->class_name);

        if ($h->class_name && trim($h->class_name) !== '') {
            $labels = $this->rosterLabels();
            if (in_array($homeworkLabel, $labels, true)) {
                $rosterAvailable = true;
                $assigned = count(array_filter($labels, fn ($l) => $l === $homeworkLabel));
            }
        }

        $submissions = $h->submissions ?? collect();
        if (!$submissions instanceof \Illuminate\Support\Collection) {
            $submissions = collect($submissions);
        }
        $byStatus = $submissions->countBy('status');
        $total = $submissions->count();
        $submitted = (int) ($byStatus['submitted'] ?? 0);
        $late = (int) ($byStatus['late'] ?? 0);
        $reviewed = (int) ($byStatus['reviewed'] ?? 0);
        $returned = (int) ($byStatus['returned'] ?? 0);

        return [
            'assigned'          => $rosterAvailable ? $assigned : null,
            'submitted'         => $submitted,
            'late'              => $late,
            'reviewed'          => $reviewed,
            'returned'          => $returned,
            'missing'           => $rosterAvailable ? max(0, $assigned - $total) : null,
            'roster_available'  => $rosterAvailable,
        ];
    }

    /** All active-enrollment labels in the current academic year (the roster). */
    private function rosterLabels(): array
    {
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return [];
        }

        return StudentClassEnrollment::where('status', 'active')
            ->where('academic_year_id', $year->academic_year_id)
            ->with(['classSection.schoolClass', 'classSection.section'])
            ->get()
            ->map(fn ($e) => $this->normalize(
                trim(($e->classSection?->schoolClass?->name ?? '') . ' ' . ($e->classSection?->section?->name ?? ''))
            ))
            ->filter(fn ($l) => $l !== '')
            ->values()
            ->all();
    }

    private function canManageHomework(User $user, Homework $homework): bool
    {
        if ((int) $homework->created_by === (int) $user->id) {
            return true;
        }

        // Admin roles with the explicit permission may manage any homework.
        return $user->hasPermission('homework.manage')
            && $user->hasAnyRole(['Owner', 'Super Admin', 'Admin']);
    }

    private function canReadSubmission(User $user, HomeworkSubmission $submission): bool
    {
        if ($this->canManageHomework($user, $submission->homework)) {
            return true;
        }

        // The submitting student themselves.
        if ($user->hasRole('Student')) {
            return (int) PortalScopeService::selfStudent($user)?->student_id === (int) $submission->student_id;
        }

        // A parent of the submitting student.
        if ($user->hasRole('Parent')) {
            $parent = Parents::where('user_id', $user->id)->first();
            if ($parent) {
                return StudentParentRelationship::where('parent_id', $parent->parent_id)
                    ->where('student_id', $submission->student_id)
                    ->exists();
            }
        }

        return false;
    }

    /**
     * Portal (Parent/Student) visibility of a homework item. When a specific
     * portal student is known ($portalStudentId) their own labels decide;
     * otherwise (e.g. parent without a ?student_id) the union of the caller's
     * class labels is used, mirroring index().
     */
    private function homeworkVisibleToPortal(Homework $homework, ?User $user, ?int $portalStudentId): bool
    {
        if (!$homework->class_name || trim($homework->class_name) === '') {
            return true; // school-wide
        }

        if ($portalStudentId !== null) {
            return in_array($this->normalize($homework->class_name), $this->studentClassLabels($portalStudentId), true);
        }

        if ($user && ($user->hasRole('Parent') || $user->hasRole('Student'))) {
            return in_array($this->normalize($homework->class_name), $this->visibleClassLabels($user), true);
        }

        return false;
    }

    private function studentClassLabels(int $studentId): array
    {
        return StudentClassEnrollment::where('student_id', $studentId)
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