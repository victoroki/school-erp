# Academic + Student Management — Audit Completion (Phase 2) + Implementation Spec

**Read-only. No application code was modified in this pass.**
Continues `docs/academic-student-management-audit.md`. Every number below is quoted from tool output.
Labels: `[V]` verified directly · `[S]` scan-based, needs verification · `[?]` not executed.

---

## 0. Two corrections to the first pass

### Correction 1 — the four "unassigned" students are not a defect `[V]`

The first pass flagged `students with NO enrollment row: 4` as CRITICAL (E4). The probe then identified
all four, and they are legitimately exited learners:

```
student 37 | ADM2025/101 | status=alumni      | graduation=2025-11-21 | prev_school=St. Omar Primary School
student 38 | ADM2025/102 | status=alumni      | graduation=2025-11-21 | prev_school=St. Odhiambo Primary School
student 39 | ADM2025/103 | status=transferred | transfer_date=2026-07-15 | prev_school=St. Wambui Primary School
student 40 | ADM2024/201 | status=inactive    | leaving_reason="Withdrew to a day school" | prev_school=St. Mutua Primary School
```

Two graduated, one transferred out, one withdrew. Having no current enrollment is the **correct**
state for all four, and every one of them retains `status`, `graduation_date`/`transfer_date` and
`leaving_reason`. **E4 is withdrawn** — this is working as intended, not a defect. The real finding is
the `is_active = 1` on all four (§2, E3, still open).

### Correction 2 — promotion already implements the history-preserving pattern `[V]`

The first pass raised E1 as CRITICAL on the grounds that there is no promotion history table and
`non-current (history) rows: 0`. Reading `StudentPromotionController::store()` shows the mechanism is
already the preferred one:

```php
DB::beginTransaction();
try {
    foreach ($studentIds as $studentId) {
        // Deactivate current enrollment
        StudentClassEnrollment::where('student_id', $studentId)
            ->where('is_current', true)
            ->update(['is_current' => false, 'status' => 'completed']);

        // Create new enrollment
        StudentClassEnrollment::create([
            'student_id' => $studentId,
            'class_section_id' => $toClassSectionId,
            'academic_year_id' => $academicYearId,
            'is_current' => true,
            ...
        ]);
    }
    DB::commit();
```

Old row → `is_current = false`, `status = 'completed'`. New row → new year, new class-section,
`is_current = true`. It is wrapped in a transaction with a rollback. **E1 is therefore downgraded from
"promotion may overwrite history" to "the mechanism is right and has simply never been run"** — the
live database has all 36 enrollments in one year (`enrollments by academic_year: {"2":36}`) and zero
history rows because no promotion has ever been executed, not because it destroys rows.

What is genuinely missing around it is narrower and now precisely identified — §A below.

---

## A. Promotion — exact gaps in the current implementation `[V]`

Reading the 87-line controller end to end, four real defects sit around an otherwise correct core.
These are the concrete implementation targets for items 12–13.

1. **No idempotency.** The deactivation is `where('student_id')->where('is_current', true)` and the
   insert is unconditional. Running promotion twice for the same year — or selecting the same student
   twice in one submit — leaves **two rows for the same `(student_id, academic_year_id)`**: the second
   pass deactivates the first row it just created and adds another. Nothing in the application or the
   schema prevents it. `[V]` (code) — the live table is clean: `duplicate (student_id,
   academic_year_id): 0` `[V]`, so a constraint can be added safely once the rule is confirmed.
2. **`from_class_section_id` is required but never used.** It is validated
   (`'from_class_section_id' => 'required'`) and then ignored: the deactivation keys on
   `student_id` alone. So a student's "from" class is never checked against the class the user actually
   selected, and a crafted request can promote a student out of any class.
3. **`to_class_section_id` is not validated against `academic_year_id`.** Nothing checks that the
   target class-section belongs to the target year, or that the target year differs from the current
   one. Today the live data happens to satisfy this (`enrollment academic_year != class_section
   academic_year: 0` `[V]`) but only because promotion has never run.
4. **No per-student history.** The only record is
   `AuditTrail::log('Student', 'PROMOTE', null, null, ['students_promoted' => count($studentIds)])` —
   `record_id` is `null` and the payload is a **count**, not the students. Which learner moved from
   which class to which, and when, is therefore not reconstructible from the audit trail either.

Positive: it uses `students.manage` as its gate, wraps the work in a transaction, rolls back on
exception, and reports how many students it moved. `[V]`

## B. Marks / exam integrity — completed `[V]`

```
exams PK = exam_id | subjects PK = subject_id | class_sections PK = class_section_id
duplicate (student,exam,subject) groups: 0
NULL marks_obtained: 0
negative marks_obtained: 0
marks_obtained min/max: {"mn":"5.00","mx":"96.00"}
results with a MISSING exam: 0        results with a MISSING subject: 0
results with a MISSING class_section: 0   results with a MISSING student: 0
results where exam academic_year != the enrolled year for that class_section: 0
results for a SOFT-DELETED student: 0     results for an INACTIVE student: 0
results NOT approved: 1231 | approved: 0
results with a grade_id set: 1231 | grade_id NULL: 0
marks by exam: {"3":249,"5":231,"7":14,"8":30,"9":14,"10":231,"11":231,"12":231}
```

**The marks data is clean.** No duplicates, no NULLs, no negatives, no orphans, no cross-year
mismatches, and no marks attached to inactive or deleted learners.

Two findings worth acting on:

- **The approval workflow has never been used** — `approved: 0`, `NOT approved: 1231`. Every mark in
  the system is unapproved, so `is_approved`/`approved_by`/`approved_at` and `MarksApprovalController`
  are unproven, and any screen that filters on `is_approved` is currently showing nothing. `[V]`
- **`grade_id` is populated on all 1231 results** — grades appear to be captured at save time rather
  than derived on render, which is the property that makes historical report cards stable. This is
  encouraging but **not yet proved**: I have not traced whether the render path recomputes the grade
  from the current `grading_scales` instead of reading `grade_id`. Item 6/8 of the brief. `[S]`

Also: `exams` has **no `term_id` column** `[V]`, and `exam_results` has neither `term_id` nor
`academic_year_id` `[V]`. Term/year attribution for a mark is therefore only reachable through the
exam. Any marks report that filters by term must join; one that forgets to will silently mix years.

## C. Class / stream / section structure — clean `[V]`

```
duplicate class names: 0                     duplicate (class_id, section name): 0
duplicate (class,section,academic_year) class_sections: 0
class_sections total vs distinct: 28 / 14 distinct classes
sections with no class_section row: 0
```

The Kenyan structure is modelled properly: `classes` runs PP1 → Grade 12 with `numeric_value` and CBC
descriptions ("Pre-Primary 1 (CBEC pre-primary, ages 4-5)", "Grade 6 … KPSEA assessment year",
"Grade 9 … JSS assessment year") `[V]`; `sections` are streams (A/B) bound to a class with
`capacity = 40`; `class_sections` binds them per year. **Zero duplicates anywhere**, so the missing
`(class_id, section_id, academic_year_id)` unique index (item 17) can be added safely.

## D. Enrollment uniqueness — candidate key proven clean `[V]`

```
duplicate (student_id, academic_year_id): 0      duplicate (student_id, class_section_id): 0
students with >1 current enrollment: 0
enrollments by academic_year: {"2":36}           enrollments by status: {"active":36}
```

Both candidate keys are currently duplicate-free, so either could be enforced. **Which one is correct
is not yet settled** and depends on the question in item 18: can a student change class or stream
mid-year? If yes, the same `(student_id, academic_year_id)` legitimately has two rows and the
constraint must be `(student_id, class_section_id)` plus a partial/`is_current` rule — which MySQL
cannot express as a plain unique index. That needs a decision before any migration (see §H).

## E. Guardians / parents — clean `[V]`

```
students with NO guardian link: 0               relationships to a missing student: 0
relationships to a missing guardian: 0          parent rows with no relationship: 0
duplicate (student, guardian) pairs: 0          guardians linked to >1 student: 0
```

No orphans, no dangling relationships, no duplicates on either side. Worth stating plainly because
this was flagged in the brief as a privacy-critical area and the *data* is sound — the question that
remains is the *access* one (item 3), not integrity.

## F. Academic year / term flags `[V]`

```
years flagged is_current: 1                       terms outside their academic year: 0
terms by year: {"1":3,"2":3}                      terms by status: {"completed":4,"upcoming":1,"active":1}
years: 2025 (is_current=0, 2025-01-06 → 2025-11-21), 2026 (is_current=1, 2026-01-05 → 2026-11-20)
```

**Exactly one current year and exactly one active term** — the "two years active at once" risk in item
12/20 does **not** materialise. Terms nest correctly inside their year, all six with `fee_due_date`
and `display_order`.

**New finding — the active term is stale.** The term flagged active is id 5, "Term 2" 2026, running
`2026-05-04 → 2026-08-07`. Today is 2026-09-23. The term flagged `upcoming` is id 6, "Term 3" 2026,
running `2026-08-24 → 2026-11-20` — which is the term actually in progress. So for roughly seven weeks
every date-sensitive rule keyed on "the active term" (attendance capture, marks period, fee due dates,
portal screens) has been operating on a term that had already finished. `[V]` from the term rows and
the current date. That is a HIGH finding because it is silent: no screen says "no term is current".

## G. Status fields — the third field is worse than the first pass reported `[V]`

```
status x is_active: [{"active",1,36},{"alumni",1,2},{"transferred",1,1},{"inactive",1,1}]
status x is_active mismatches: 4
enrollment_status x is_active mismatches: 40
```

The first pass found 4 students where `status` and `is_active` disagree. It did not check
`enrollment_status`, which disagrees for **all 40** — i.e. the column is unmaintained (empty or never
set to `active`). Three fields describe one lifecycle, one of them is dead, and the other two
contradict each other on exactly the four exited students. This is item 14, and the number to beat is
now precise: 4 status/is_active conflicts and 40 enrollment_status non-uses.

## H. Cross-module and shared-file notes

`academic_years`, `terms`, `classes`, `sections`, `class_sections`, `student_class_enrollments` and
`students` are all read by Fee Management, which is being worked in parallel. A new unique index on
`student_class_enrollments` or a new status rule changes what fee screens see. **Recommendation:** put
the enrollment-uniqueness and status-consolidation changes behind a written decision (item 18 and the
`status` question) rather than adding them opportunistically, and re-read the working tree
immediately before touching `Student.php`, `StudentReportController.php`, `TermController.php`,
`AcademicYearController.php`, `config/menu.php` or `routes/web.php`.

**Disclosure:** earlier in this conversation I modified `config/menu.php`, `routes/web.php`,
`StudentReportController.php`, `FeeDashboardController.php`, `FeeBalanceService.php`,
`LedgerService.php` and `RefundController.php` for the Fee work. This audit pass changed none of them.

## I. Still not executed — stated plainly `[?]`

I did not complete, and will not claim:

| Brief item | State | Why |
|---|---|---|
| Authorization matrix (1), teacher scoping (2), portal IDOR (3) | **Partial only** | `TeacherScopeService` and `PortalScopeService` exist and **are** applied in `MarkSheetController`, `GradeBookController`, `ExamReportController`, `ExamDashboardController`, `AcademicDashboardController`, `MobileTeacherClassController`, `MobileAttendanceController`, `MobileDisciplineController`, `MobileStudentNoticeController`, `MobileMedicalController`, and `PortalScopeService` itself delegates to `TeacherScopeService` for teachers `[V]`. The mobile/portal side carries explicit "never trust a device-supplied student_id" guards `[V]`. **But I have not enumerated the web routes' `can:` middleware, so the matrix is not built** and the web portal controllers are unverified. |
| Result calculation trace (5), grading (6), ranking (7) | **Not run** | Needs a code-path trace across 7 controllers plus views. `CbeGradingService` exists and `grade_id` is stored, but duplication is unproven either way. |
| Report cards (8) | **Not run** | `report_card_templates = 0`, so nothing can be verified without creating test data in an isolated DB. |
| UI / dropdowns / exports (9, 10) | **Not run** | Needs the rendered-view sweep; the fee-side tooling for this exists but was not run against these views. |
| Attendance (19) | **Not run** | `student_attendance = 0` — every check is vacuous. Requires a seeded test env. |
| Test baseline (11) | **Not run** | Deliberate: all suites share one database and two other sessions are active. Needs the dedicated schema. |

## J. Implementation order (not started)

Ordered by risk, each with its exact site. Nothing here has been implemented.

1. **Promotion hardening (items 12–13)** — `StudentPromotionController::store()`: require the student to
   be currently enrolled in `from_class_section_id`; validate `to_class_section_id` belongs to
   `academic_year_id`; skip (or reject) a student who already has an enrollment in the target year;
   record `record_id`/per-student detail in the audit entry. Add the unique index only after the
   mid-year-change question in item 18 is answered.
2. **Status single source (item 14)** — decide the authority, then make the other fields derive.
   Concrete work: 4 conflicts to reconcile, 40 `enrollment_status` values to define or drop, and a test
   that every lifecycle reader agrees.
3. **Marks protection (item 16)** — the data is clean, so add `(student_id, exam_id, subject_id)` and
   convert repeated submits to upsert. Then a test for double submit / retry / bulk resubmit.
4. **Class-section unique index (item 17)** — data is clean; safe to add.
5. **Enrollment unique index (item 18)** — blocked on the business rule, not on data.
6. **Stale active term (§F)** — surface "no current term" rather than silently using a finished one, and
   confirm activation is the only writer of the flag.
7. **Approval workflow (§B)** — either use it or remove the filter, but a screen that shows nothing
   because `is_approved` is false on all 1231 rows is a live defect.
8. Then the deferred audit sections (authorization matrix, IDOR, calculation trace, report cards,
   attendance, UI) and finally the isolated test baseline.

## K. Method notes / leftovers

Probe scripts in the repository root, all SELECT/SHOW only, none to be committed:
`tmp_audit_as.php` (superseded), `tmp_audit_as2.php`, `tmp_audit_as3.php`.

One line of `tmp_audit_as3.php` output fell between the paging boundary and is **not** reported above:
`results whose class_section is NOT one the student is/was enrolled in`. It should be re-read on the
next run rather than assumed.

No live student record was modified. No promotion, transfer, deletion, mark edit or enrollment write
was performed.
