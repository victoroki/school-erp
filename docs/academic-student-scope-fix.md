# Teacher Scoping — Localized and Fixed

Continues `docs/academic-student-audit-phase3.md`, whose §B left the teacher-scope leak
"confirmed but not localised". This pass localised it and fixed it.

Labels: `[V]` verified from tool output · `[?]` not executed.

---

## 1. What the leak actually was — and one correction

Phase 3 reported **one** leaking endpoint inside an ambiguous single assertion. Split into three
independent tests, the result was worse and more precise: **all three** leaked.

```
1) ...test_a_teacher_with_no_class_cannot_open_mARK_SHEETSfor_another_class
   mark-sheets.index returned 200 ... for class_section 1
2) ...test_a_teacher_with_no_class_cannot_open_the_grade_book_for_another_class
   grade-book.index returned 200 ... for class_section 3
3) ...test_a_teacher_with_no_class_cannot_open_exam_results_for_another_class
   exam-results.index returned 200 ... for class_section 5
Tests: 4, Assertions: 4, Failures: 3.
```
`[V]`

**Correction to the severity language.** Phase 3 called this a scope "leak". Reading the three
controllers in full, it was **not** data exposure: in `MarkSheetController::index` and
`GradeBookController::index` the results query sits *inside* the same `filled([...])` block as the
scope check, so when that block was skipped the table came out empty, and the class/stream dropdown
was built from `getClassSectionIds($user)` — scoped to the teacher's own classes. What the teacher
received was a **200 page shell for a class they do not own**, not another class's marks.

I have not traced the internals of `exam-results.index` beyond the dropdown construction, so for that
one endpoint I am not claiming the rendered tables were empty. The guard is now in place either way.

The user's standard is met regardless: *"A teacher who owns nothing should not be able to open another
class's academic page at all"* — 200-with-an-empty-page is no longer accepted.

## 2. Root cause — identical in all three controllers `[V]`

The scope check was **conditional on the shape of the request**, not on the class being asked for:

```php
$viewAll     = $user->hasPermission('exams.results.view-all');
$hasSettings = $user->hasPermission('academics.settings.manage');

if ($viewAll || $hasSettings) { /* unrestricted lists */ }
else { /* scoped lists */ }

if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {   // ← outer gate
    if (! $viewAll && ! $hasSettings) {                                   // ← scope check INSIDE
        if (! $allowedIds->contains((int) $request->class_section_id)) {
            abort(403, ...);
        }
    }
    // ...data query...
}
return view(...);
```

A request carrying **only** `class_section_id` failed the outer `filled()` test, so the inner scope
check never executed and the method fell through to `return view(...)`. The guard's presence depended
on how many *other* filters the caller happened to supply — which is entirely caller-controlled.

This is the "filter is applied only to data and not to route access" case from the brief, with the
extra twist that it was also applied only to *some shapes of request*.

## 3. Fix `[V]`

A class-scoped guard placed **before** the branching, so it fires on any request that names a class —
independent of the other filters. Four methods now validate a supplied `class_section_id` against the
caller's own scope:

| File | Method | Guard added |
|---|---|---|
| `MarkSheetController.php` | `index` | `getClassSectionIds($user)` must contain a supplied `class_section_id`, else 403 |
| `GradeBookController.php` | `index` | same |
| `ExamResultController.php` | `bulk` | same |
| `ExamResultController.php` | `index` | `$scopeIds` (null for privileged users) must contain a supplied `class_section_id`, else 403 |

Each is a few lines, uses the existing `TeacherScopeService` (no new service, no policy change,
`[V]` preserved), and leaves the existing inner checks untouched so the subject-level check still
applies when all filters are present. Privileged users (`exams.results.view-all`,
`academics.settings.manage`) are unaffected — `$scopeIds === null` short-circuits.

`exam-results.index` needed a second pass: the first fix went into `bulk()`, but the failing route
`exam-results.index` maps to `index()`. Both are now guarded.

## 4. Verification `[V]`

```
$ php -l app\Http\Controllers\MarkSheetController.php
No syntax errors detected
$ php -l app\Http\Controllers\GradeBookController.php
No syntax errors detected
$ php -l app\Http\Controllers\ExamResultController.php
No syntax errors detected

$ php vendor/bin/phpunit -c phpunit.academic.xml --filter "teacher_with_no_class"
OK (4 tests, 4 assertions)
```

All three scope tests are green, on the isolated `school_erp_academic_test` schema.

## 5. State of `AcademicAuthMatrixTest` `[V]`

Last full-class run, before the final `exam-results.index` guard:

```
Tests: 18, Assertions: 39, Failures: 4.
```

Failures at that point were: the route-integrity scan, the bulk-export assertion, `exam-results.index`
scope, and the portal 500. The `exam-results.index` failure is now fixed and the four scope tests pass
in isolation. **The class has not been re-run in full since that last edit** — treating it as
"18/39/3" would be an assumption, not a measurement.

## 6. Still open — not done in this pass `[?]`

1. **Portal report-card 500.** `resources/views/portal/report-cards.blade.php:9` uses `$student`, which
   `PortalReportCardController` never passes. The controller/view contract fix, the
   `PortalScopeService` ownership check, and the six requested portal tests are **not done**.
2. **Route-integrity test scoping.** It still scans the whole ERP, so it fails on 14 routes belonging
   to Library / Inventory / Finance that this module must not touch. Needs scoping to
   Academic + Student controllers while still reporting the rest.
3. **`test_bulk_report_card_export_needs_the_export_permission`** — my assertion demanded 200; the
   correct behaviour without filters is a 302 redirect. The permission gate itself passes (a
   `view-own` user is refused). Test assertion to be relaxed to "not 403".
4. **`StudentTransportAssignmentController@show`** — registered route, missing method, 500s. Not fixed.
5. **`ReportCardTemplateController`** — still an empty stub behind 7 resource routes.
6. **Grading/ranking trace, historical grade stability, approval workflow, attendance end-to-end,
   UI/dropdown audit, `class_sections` unique index, lifecycle status map, stale-term warning,
   `APP_DEBUG` hardening** — all still `[?]`, as listed in phase 3 §F–§N.

## 7. Files changed in this pass

- `app/Http/Controllers/MarkSheetController.php`
- `app/Http/Controllers/GradeBookController.php`
- `app/Http/Controllers/ExamResultController.php` (two methods)
- `tests/Feature/AcademicAuthMatrixTest.php` (the ambiguous assertion split into three named tests)

**Shared files touched: none.** `TermController.php`, `Student.php`, `StudentReportController.php`,
`routes/web.php`, `config/menu.php` and both Fee services are untouched.

No live data was read beyond the counts already recorded in earlier passes, and none was modified.
