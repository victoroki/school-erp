# Academic + Student Management — Verification + Implementation Report (Phase 3)

Continues `docs/academic-student-management-audit.md` (pass 1) and
`docs/academic-student-audit-phase2.md` (pass 2).
Labels: `[V]` verified directly from tool output · `[S]` scan-based · `[?]` not executed.

---

## A. Authorization matrix

**Structural finding first:** the routes for these modules carry **no permission middleware**.
Lines 198–275 of `routes/web.php` (students, attendance, promotion) sit inside
`Route::middleware(['auth', 'module'])` at line 36 only. Every academic/student permission is
enforced inside controller constructors via `$this->middleware('can:…')->only([…])`. `[V]`

That makes an omission invisible: a method named in **neither** `only([…])` list has no gate at all,
and only calling it reveals it. Two such omissions were found and one is now fixed.

| Area | Route | Controller method | Gate found | Verdict |
|---|---|---|---|---|
| Student list | `students.index` | `StudentController@index` | `students.view` | correct `[V]` |
| Student edit | `students.edit/update` | ditto | `students.manage` | correct `[V]` |
| Student delete | `students.destroy` | ditto | `students.manage` | correct `[V]` |
| Student import | `students.import*` | `StudentImportController` | `students.import` | correct `[V]` |
| Unassigned | — | `StudentUnassignedController` | `students.manage` | correct `[V]` |
| Documents / guardians / emergency / enrolment | — | respective controllers | `students.view` + `students.manage` | correct `[V]` |
| ID card | `students.id-card` | `StudentIdCardController` | `students.view` | correct `[V]` |
| Student reports | — | `StudentReportController` | `students.view` | correct `[V]` |
| Promotion | `student-promotion.*` | `StudentPromotionController` | `students.manage` | correct `[V]` |
| Transfer | — | `StudentTransferController` | `students.manage` | correct `[V]` |
| Attendance screen | `student-attendance.index` | `StudentAttendanceController@index` | `academics.view` | correct `[V]` |
| **Attendance report** | `student-attendance.report` | `@report` | **none → 200 to a permissionless user** | **FIXED** `[V]` |
| Attendance capture | `student-attendance.store` | `@store` | `academics.attendance.manage` | correct `[V]` |
| Marks entry | `exam-results.bulk`, `.store`, `.save-one` | `ExamResultController` | `exams.marks.enter-own` | correct `[V]` |
| Marks view | `exam-results.index/show` | ditto | `exams.results.view-own` | correct `[V]` |
| Marks approval | `marks-approval.*` | `MarksApprovalController` | `exams.approve` | correct `[V]` |
| Bulk report cards | `exam-reports.bulk` | `ExamReportController` | `exams.report-cards.export` | correct `[V]` |
| Mark sheets / grade book | `mark-sheets.index`, `grade-book.index` | `MarkSheetController`, `GradeBookController` | `exams.results.view-own` | gate correct, **scope leak — see B** `[V]` |
| Academic year | `academic-years.*` | `AcademicYearController` | `academics.view` read / `academics.settings.manage` write | correct `[V]` |
| Terms | `terms.*` | `TermController` | `academics.settings.manage` | correct `[V]` |
| Classes / sections / class-sections / subjects / class-subjects / teacher-subjects / class-teachers / periods / classrooms / learning areas / strands / sub-strands / grading scales | resource routes | respective controllers | `academics.view` read / `academics.settings.manage` write | correct `[V]` |
| Exams / exam schedules | `exams.*`, `exam-schedules.*` | `ExamController`, `ExamScheduleController` | `academics.settings.manage` (index/show **included**) | see F1 `[V]` |
| Report card templates | `report-card-templates.*` | `ReportCardTemplateController` | **controller is an empty stub — every route 500s** | **DEFECT** `[V]` |

### Report card templates are routed but not implemented `[V]`

```php
class ReportCardTemplateController extends Controller
{
    //
}
```
That is the entire class, while `routes/web.php:477` registers
`Route::resource('report-card-templates', ReportCardTemplateController::class)`. All seven routes
resolve to methods that do not exist, so they return **500 for every user** — it is not a permission
bug and gating it would protect nothing. Confirmed by the test run: the endpoint returned `500` to a
user with no permissions. Implement the controller or remove the route and its menu entry.

Tests added for this: `test_the_report_card_template_routes_point_at_an_empty_controller` (pins the
stub and fails the moment it is implemented) and
`test_no_registered_route_points_at_a_missing_controller_method` (scans the whole route table).
**The second test currently fails**, i.e. there is at least one **further** route pointing at a
missing controller method beyond this known stub. The exact list is pending one re-run.

## B. IDOR / scope — what is proven `[V]`

`TeacherScopeService` and `PortalScopeService` exist and are wired into `MarkSheetController`,
`GradeBookController`, `ExamReportController`, `ExamDashboardController`, `AcademicDashboardController`,
`StudentAttendanceController` and the mobile controllers `[V]`.

Two isolation tests were written and run against the endpoints that matter most:

- **Teacher scope — FAILING** `[V]`. A teacher holding `exams.results.view-own` with **no** class,
  subject or timetable assignment was given `200` when passing another class's `class_section_id` to
  one of `mark-sheets.index` / `grade-book.index` / `exam-results.index`. A teacher owning nothing must
  own nothing; returning a page for a class they do not teach is the direct-URL bypass item 2 asks
  about. Which of the three endpoints is at fault, and whether it renders real data or an empty shell,
  is pending the re-run.
- **Portal isolation — FAILING** `[V]`. An authenticated user with no role at all reached
  `portal.report-cards`, received a rendered page, and the response **contained this learner's
  admission number**. The portal group is behind `auth` only, so isolation is entirely the
  controller's job. If that number belongs to a child the viewer has no link to, this is a
  **CRITICAL cross-student disclosure** and is the highest-severity finding of this pass. It needs
  immediate confirmation from the re-run (the assertion failure was truncated) before I state the
  severity as final.

`MobilePortalDataIsolationTest` already exists and was part of the 283-test baseline below, so the
mobile side has coverage; the **web** portal path is the one now shown to fail.

## C. Promotion — fixed and verified `[V]`

Four defects were reproduced by test before being touched, then fixed in
`StudentPromotionController::store()`. The history-preserving core was left exactly as it was.

| Defect | Evidence before the fix | Fix |
|---|---|---|
| `from_class_section_id` validated then ignored | a POST naming an **unrelated** class still moved the learner: `Failed asserting that 2 is identical to 1` | the learner's current enrollment must belong to the selected class, else the row is skipped with a reason |
| target class-section not checked against the target year | an enrollment was written whose `academic_year_id` disagreed with its class-section's year: `Failed asserting that 1 is identical to 0` | reject before any write when `class_sections.academic_year_id !== academic_year_id` |
| not idempotent — re-running appended rows | same student twice in one submit produced **3** enrollments: `Failed asserting that 3 is identical to 2` | `array_unique` on the ids, and skip when the target placement already exists |
| audit entry carried only a count, `record_id = null` | `Failed asserting that null is not null` | one `AuditTrail::log('Student','PROMOTE', $studentId, …)` per learner, recording from/to enrollment ids, from/to class-section ids and the target year |

Validation also tightened from `required` to `exists:` on the three id fields, with
`student_ids.* => integer|exists:students,student_id`. The transaction and rollback are unchanged.
Skipped learners are reported through a warning flash rather than failing silently.

After the fix the promotion tests pass: old enrollment preserved (`is_current=false`,
`status='completed'`), new enrollment created, exactly one current enrollment per learner.

## D. Lifecycle status — unchanged, still open `[?]`

No write was made to `status`, `is_active` or `enrollment_status`. The counts from pass 2 stand:
`status × is_active` disagree on 4 learners, `enrollment_status` disagrees for all 40 and appears
unmaintained. This needs the decision on which field is authoritative before anything is migrated, and
because Fee Management reads `students`, changing it is a cross-module change.

## E. Marks — verified clean, approval still unused `[V]`

From the pass-2 probe, unchanged and re-stated: `duplicate (student,exam,subject) = 0`,
`NULL = 0`, `negative = 0`, `min/max = 5.00–96.00`, no orphans, no cross-year mismatch, no marks on
inactive or deleted learners. `grade_id` is set on all 1231. `is_approved` is `false` on all 1231 —
the approval gate itself is correctly permissioned (`exams.approve`, verified above), but the workflow
has never been exercised, and I have not yet traced which screens filter on it.

## F. Grading / ranking — `[?]` not run

Not traced. Calculation duplication across the seven controllers and their views is still unproven.

## G. Report cards — `[?]` not run, and blocked

`report_card_templates = 0` and the template controller is an empty stub (§A), so the workflow cannot
be exercised end to end yet. Historical grade stability is likewise untraced.

## H. Attendance — `[V]` gate verified, workflow still unproven

The capture gate is now verified from the outside: a user with only `academics.view` receives **403**
on `student-attendance.store`, and a user with `academics.attendance.manage` is accepted (302). The
read gate that was missing (`report`) is fixed. The workflow itself remains unproven —
`student_attendance = 0` live rows — and is scheduled for the isolated database.

## I. Terms — `[V]` verified, no change made

`years flagged is_current: 1`, `terms by status: {"completed":4,"upcoming":1,"active":1}`,
`terms outside their academic year: 0`. Only one year and one term are flagged, so the
"two years active at once" risk does not materialise. The stale-active-term finding from pass 2 stands
(active term ended `2026-08-07`; today `2026-09-23`) and is **untouched**, because `TermController` is
shared with Fee Management and the brief asks for coordination. No term change was made.

## J. Database constraints

**Added: none.** **Deferred: all of them**, deliberately:
- `exam_results (student_id, exam_id, subject_id)` — data clean (`0` duplicates) but the upsert
  behaviour must be implemented in the same change, or a duplicate submit becomes a 500 instead of a
  second row.
- `class_sections (class_id, section_id, academic_year_id)` — data clean, still not added.
- `student_class_enrollments` — correctly **not** added: the rule depends on the unresolved question of
  whether a learner may change class/stream mid-year.

## K. UI — `[?]` not run

No view, icon, dropdown or export auditing was performed in this pass.

## L. Tests

**Isolated baseline established** — this was the blocking item and it now works:

```
LIVE     database = school_management_system
ISOLATED database = school_erp_academic_test
live students row count (expect 40): 40
  schema school_erp_academic_test: 0 tables
  schema school_erp_precision_probe: 81 tables
  schema school_erp_test: 139 tables
=== BASELINE (isolated schema) ===
OK (283 tests, 1473 assertions)      Time: 04:37.064
```

The live database was verified intact (40 students) and the isolated schema started at 0 tables, so
`RefreshDatabase`'s `migrate:fresh` ran in the new schema, not against live data. `phpunit.academic.xml`
is a copy of `phpunit.xml` differing only in `DB_DATABASE`, so Academic/Student runs cannot collide with
the Fee or Finance sessions.

**New suite:** `tests/Feature/AcademicAuthMatrixTest.php`.

| Run | Result |
|---|---|
| Before fixes | `Tests: 15, Assertions: 20, Errors: 8, Failures: 6` — the 8 errors were my own harness (duplicate `users.username` and reused role names), fixed by making both unique |
| After fixes | `Tests: 16, Assertions: 37, Failures: 4` |

The 6 real defects from the first run are green after the fixes (attendance report gate, all four
promotion defects, plus the template-stub test). The 4 remaining failures are: the whole-route-table
stub scan (more than one broken route), the teacher-scope leak, the portal disclosure, and one
over-strict assertion of mine (a 302 instead of 403 is correct behaviour for the bulk export page when
filters are absent).

**Final counts and the exact broken-route list are pending the re-run in background
(processId `jetbqnvhivp`).** The last command exceeded the 300s foreground limit.

## M. Cross-module changes

**Files changed this pass:**
- `app/Http/Controllers/StudentPromotionController.php` — the four fixes in §C (module-owned, not shared)
- `app/Http/Controllers/StudentAttendanceController.php` — `report` added to the existing `academics.view` gate
- `phpunit.academic.xml` — new, test infrastructure
- `tests/Feature/AcademicAuthMatrixTest.php` — new
- `tmp_make_db.php`, `tmp_audit_as3.php` — read-only scratch probes, not to be committed

**Shared files NOT touched:** `Student.php`, `StudentReportController.php`, `TermController.php`,
`AcademicYearController.php`, `config/menu.php`, `routes/web.php`, `FeeBalanceService.php`,
`LedgerService.php`. The report-card-template defect could be fixed in `routes/web.php`, and I did not,
precisely because it is shared and being edited elsewhere.

## N. Remaining decisions and blockers

1. **The portal disclosure (§B) needs confirming and then fixing first.** If it is a real leak it
   outranks everything else here.
2. **Which teacher-owned endpoint returned 200 (§B)** — the fix is endpoint-specific and needs the
   re-run's output.
3. **Report-card templates: implement or remove?** The controller is an empty stub behind a full
   resource route. Removing the route touches `routes/web.php`, which is shared — your call.
4. **Can a learner change class/stream mid-year?** Still blocks the enrollment uniqueness rule.
5. **Which lifecycle field is authoritative** (`status` vs `is_active` vs `enrollment_status`)? Blocks
   the status consolidation, and touches a table Fee Management reads.
6. **Is the stale active term** (ended 2026-08-07) **a data-entry task or should the system warn?**
   Any change to `TermController` is shared with Fee Management.

No live student, mark, term or enrollment record was modified. No promotion, transfer or deletion was
run against live data.

---

# Addendum — results of the background re-run

## Correction: the portal finding is a broken page, NOT a disclosure

Section B reported a possible CRITICAL cross-student disclosure because the portal response contained
a learner's admission number. That was wrong, and the correction matters more than the original claim:
the response was a **Spatie Ignition error page**, not portal output.

```
Spatie\LaravelIgnition\Exceptions\ViewException: Undefined variable $student
in file C:\...\resources\views\portal\report-cards.blade.php on line 9
```

`PortalReportCardController` never passes `$student` to the view, so
`resources/views/portal/report-cards.blade.php:9` dereferences an undefined variable and the page
**500s for every user**. The admission number appeared in the debug page's request/session context,
not in portal data. **No leak is proven. I withdraw the CRITICAL disclosure claim.**

What is left is a serious functional defect instead: the **parent/student portal report-card page has
never worked** — it throws on render. This is consistent with `report_card_templates = 0` and the empty
`ReportCardTemplateController` (§A): the report-card feature is unfinished end to end, and this view
is the third part of it that is not implemented.

Also worth noting from the same output: because `APP_DEBUG` is on in the test environment, an internal
error is returned as an HTML debug page containing stack traces and request context rather than a
clean error. That is what made this look like a data leak. A production deployment on localhost-bound
developer defaults would expose the same thing.

## The broken-route list is larger than the stub

`test_no_registered_route_points_at_a_missing_controller_method` found **14 further routes** whose
controller method does not exist, beyond the 7 on the `ReportCardTemplateController` stub. They return
500 for every user:

```
book-issues/{book_issue}                        -> BookIssueController@show
book-issues/{book_issue}/edit                   -> BookIssueController@edit
book-issues/{book_issue}                        -> BookIssueController@update
inventory/requisitions/{requisition}/edit       -> RequisitionController@edit
inventory/requisitions/{requisition}            -> RequisitionController@update
inventory/requisitions/{requisition}            -> RequisitionController@destroy
inventory/purchase-orders/{purchase_order}/edit -> PurchaseOrderController@edit
inventory/purchase-orders/{purchase_order}      -> PurchaseOrderController@update
inventory/purchase-orders/{purchase_order}      -> PurchaseOrderController@destroy
student-transport-assignments/{...}             -> StudentTransportAssignmentController@show
bank-transactions/{bank_transaction}            -> BankTransactionController@show
bank-transactions/{bank_transaction}/edit       -> BankTransactionController@edit
bank-transactions/{bank_transaction}            -> BankTransactionController@update
bank-transactions/{bank_transaction}            -> BankTransactionController@destroy
```

One belongs to **Student Management** (`StudentTransportAssignmentController@show`). The rest are
Library, Inventory and Finance — **not modified, and reported only**, because those modules are owned
by other sessions and the brief requires me not to interfere.

## Still unresolved after the re-run

- **Teacher scope (B)** remains confirmed-but-unlocalised: a teacher with no class, subject or timetable
  assignment received `200` from one of `mark-sheets.index` / `grade-book.index` /
  `exam-results.index` when given another class's `class_section_id`. The re-run printed the failure but
  not which endpoint, so the fix is not yet targeted. The test names all three in its message; the next
  run should assert them individually so the failing one is unambiguous.
- **Final test counts:** `Tests: 16, Assertions: 37, Failures: 4` — that is the state of
  `AcademicAuthMatrixTest`. The full isolated baseline remains `OK (283 tests, 1473 assertions)`.
