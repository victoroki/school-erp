# Portal Report Cards + Route Integrity

Continues `docs/academic-student-scope-fix.md`. Teacher scoping is not revisited here.
Labels: `[V]` verified from tool output · `[?]` not executed.

---

## 1. Portal report-card 500 — root cause and fix `[V]`

The defect was narrower and more ordinary than the debug page made it look.

```php
// PortalReportCardController::index() — before
$student = $this->resolveStudent($user);

if (!$student) {
    return view('portal.report-cards', ['exams' => collect(), 'message' => 'No student profile found.']);
}
```

That branch passes `exams` and `message` — but **not `student`** — while the view's own header reads
`{{ $student->full_name }}` at `resources/views/portal/report-cards.blade.php:9`. So every account with
no linked learner got `Undefined variable $student`, and because the debug error page echoes request
context it read like a data leak. It never was one: the results query was always filtered by the
resolved learner.

**Fix — the learner now always comes from `PortalScopeService`, and an account with no learner is
denied instead of broken:**

```php
    protected function resolveStudent($user): ?Student
    {
        if ($student = PortalScopeService::selfStudent($user)) {
            return $student;              // a student account sees itself only
        }

        $ownChildren = app(PortalScopeService::class)->childrenFor($user);

        if ($ownChildren->isEmpty()) {
            return null;                  // parent with no linked child → denied
        }

        return Student::find($ownChildren->first()['student_id']);
    }
```

Both branches now `abort(403, 'No student is linked to this account.')` instead of rendering a view
they cannot populate, in `index()` and `show()`.

**Deliberately NOT `visibleStudentIds()`.** That method returns *every active learner* for
Owner/Super Admin/Admin/Accountant, so resolving the portal's learner through it would hand a staff
account a pupil's report card via the parent portal. `childrenFor()` resolves parent links only, which
is the correct ownership rule for this page.

`show(Exam $exam)` takes the exam from the URL but **never a learner id** — the learner comes from the
session and the results query is filtered by it, so swapping the exam id can only produce a 404. No
request-supplied student id is accepted anywhere on this page.

## 2. Portal isolation tests `[V]`

New `tests/Feature/PortalReportCardIsolationTest.php`:

```
OK (6 tests, 14 assertions)
```

| Test | Asserts |
|---|---|
| `test_the_portal_report_card_page_renders_for_a_student_account` | 200, shows the learner's own name — the 500 regression guard |
| `test_the_portal_report_card_page_renders_for_a_linked_parent` | 200 for a parent with a linked child |
| `test_an_account_with_no_linked_learner_is_denied_rather_than_broken` | **403** for a parent with no child, and for an unrelated authenticated user (was the 500) |
| `test_a_parent_cannot_open_another_learners_report_card` | requests an exam the parent's child did **not** sit, while another learner did → not 200, and the other learner's name/admission number never appear |
| `test_a_student_account_can_open_its_own_report_card` | 200, own name |
| `test_a_student_account_cannot_swap_in_an_unrelated_learner` | `?student_id=<other>` changes nothing; the other learner never appears in index or show |

One test of mine was wrong first: it asked for the **shared** exam, and 200 was the correct answer —
that is the parent's own child's report card. It now uses an exam only the other learner sat, which is
the question that actually tests ownership. The corrected version passes.

## 3. `AcademicAuthMatrixTest` — real measurement `[V]`

| Run | Result |
|---|---|
| Before this pass (phase 3) | `18 tests, 39 assertions, 4 failures` |
| After the teacher-scope fixes and the portal controller fix | `18 tests, 39 assertions, 2 failures` |
| After fixing my two test assertions (items 3 and 4 below) | failures reduced to the route-integrity test; **exact counts for this final run were not captured** — the failure output exceeded the lines I filtered for |

The two failures at `18/39/2` were both mine, not the application's:

1. **`test_bulk_report_card_export_needs_the_export_permission`** — I asserted 200; with no filters the
   page correctly redirects, so 302 is right. Now asserts **302** (the permission gate itself was
   already correct: a `view-own` user is refused). The fully-filtered happy path needs an
   exam/class/subject fixture and is **not covered yet**.
2. **`test_no_registered_route_points_at_a_missing_controller_method`** — see §4.

I am not reporting "1 failure" as a measurement: the last run's summary line was not captured.

## 4. Route integrity — scoped, and it found six more `[V]`

The test now scans the whole route table but only **Academic/Student-owned** controllers can fail it;
broken routes elsewhere are printed as context in the failure message, never as a reason for this
module to go red. Ownership is decided by controller basename markers (`Student`, `Academic`,
`Attendance`, `Exam`, `Mark`, `Grade`, `Cbc`, `Class`, `Section`, `Subject`, `Teacher`, `Promotion`,
`Transfer`, `Enrol`, `ReportCard`, `Term`, `Strand`, `LearningArea`, `Period`, `Classroom`,
`Guardian`, `Parent`, `Document`, `Emergency`, `Timetable`).

Scoping it immediately exposed **six Academic/Student routes that the previously-global failure had
been hiding** — every one a resource route whose `show()` was never written:

```
student-transport-assignments/{id}  -> StudentTransportAssignmentController@show
academic-calendar/{id}              -> AcademicCalendarController@show
exam-rooms/{id}                     -> ExamRoomController@show
learning-areas/{id}                 -> LearningAreaController@show
strands/{id}                        -> StrandController@show
sub-strands/{id}                    -> SubStrandController@show
```

Plus the seven `report-card-templates.*` routes on the empty `ReportCardTemplateController` stub
(allow-listed so the test fails only when someone implements it, and the separate
`test_the_report_card_template_routes_point_at_an_empty_controller` pins that state).

**So Academic/Student is NOT at zero broken routes: it is at 13 (6 + 7).** All are the same shape —
a `Route::resource` registered against a controller that implements only some methods. The fix is
per-route and small either way (`->except(['show'])` where the application genuinely has no detail
page, or a real `show()`), but it belongs in `routes/web.php`, which is shared with the Fee session,
so it needs to be done deliberately rather than in passing.

**Reported, not fixed — other modules' broken routes** (printed by the diagnostic, untouched):

```
book-issues/{id} (+edit, update)            inventory/requisitions/{id} (+edit, update, destroy)
inventory/purchase-orders/{id} (+edit, update, destroy)
bank-transactions/{id} (+show, edit, update, destroy)
bank-reconciliations (+create, store, edit, destroy)
financial-years/{id} (+show, destroy)       hr/staff-directory -> StaffController@directory
leave-applications/{id} (+edit, update)
```

These belong to Library, Inventory, Financial Management and HR. Left alone.

## 5. Not done in this pass `[?]`

Stated plainly, in the order the brief asked for them:

| Item | State |
|---|---|
| 5. `StudentTransportAssignmentController@show` | **Not fixed.** Now one of the six listed above. |
| 6. `ReportCardTemplateController` implement-vs-remove | **Not decided or implemented.** Still 7 dead routes. |
| 7. Grading / ranking trace | **Not run.** |
| 8. Historical grade stability | **Not run.** |
| 9. Approval workflow (`is_approved` on 1231 rows, 0 approved) | **Not traced.** |
| 10. `exam_results` unique constraint + upsert | **Not added.** |
| 11. `class_sections` unique index | **Not added** (live re-check for duplicates still pending). |
| 12. Enrollment index | Correctly still deferred. |
| 13. Lifecycle status map | **Not run.** |
| 14. Stale active-term warning | **Not implemented.** |
| 15. Attendance end-to-end | **Not run** (live rows still zero). |
| 16/17. UI + dependent dropdown audit | **Not run.** |
| 18. `APP_DEBUG` hardening | **Not inspected.** |
| 19. Full isolated suite | **Not re-run** after this pass's changes. |

## 6. Files changed in this pass

- `app/Http/Controllers/Portal/PortalReportCardController.php` — rewritten: scope-service resolution, 403 instead of the unpopulated view, `show()` documented as learner-from-session
- `tests/Feature/PortalReportCardIsolationTest.php` — new, 6 tests
- `tests/Feature/AcademicAuthMatrixTest.php` — route-integrity test scoped to this module + global diagnostic in the message; export assertion corrected to 302

**Shared files touched: none.** `routes/web.php` was deliberately not edited even though the six
`show()` routes live there.

Changed nothing in `Student.php`, `StudentReportController.php`, `TermController.php`,
`AcademicYearController.php`, `config/menu.php`, `FeeBalanceService` or `LedgerService`.

Test runs used `phpunit.academic.xml` → `school_erp_academic_test` only. No live record was read
beyond previously recorded counts, and none was modified.
