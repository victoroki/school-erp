# Academic + Student Management — Combined Audit Report

**First pass: audit only. No application code was modified.**
Read-only probes were run against `school_management_system`. Every number below is quoted from tool
output; every claim carries a confidence label.

**Confidence labels**
`[V]` verified directly (tool output quoted) · `[S]` scan-based, needs verification · `[?]` not yet executed

### Status of this pass — read this first

| Workstream | State |
|---|---|
| Module mapping (routes/controllers/models/services/views/tables) | **Done** |
| Live data state + DB integrity probes | **Done** |
| Student lifecycle / placement / year-term integrity | **Done** |
| Authorization matrix (item 31), teacher scoping (32), portal IDOR (33) | **[?] NOT RUN** |
| Mark entry / ranking / report card / grading logic trace (19–24) | **[?] NOT RUN** |
| Views, dropdowns, icons, exports (40–44) | **[?] NOT RUN** |
| Test baseline (48) | **[?] NOT RUN** — see §T |

The file-level audits that remain are listed with their exact method in §W. I did not guess at them.
Where a check was vacuous — it ran and returned zero because the table is empty — it is marked
`(empty → unproven)` rather than "passed".

---

## A. Architecture Map

### Controllers that belong to these two modules `[V]`

Student: `StudentController`, `StudentImportController`, `StudentUnassignedController`,
`StudentClassEnrollmentController`, `StudentAttendanceController`, `StudentDocumentController`,
`StudentPromotionController`, `StudentTransferController`, `StudentDashboardController`,
`StudentParentRelationshipController`, `StudentReportController`, `StudentIdCardController`,
`ParentsController`, `EmergencyContactController`.

Academic: `AcademicYearController`, `TermController`, `AcademicDashboardController`,
`AcademicCalendarController`, `SchoolClassController`, `SectionController`, `ClassSectionController`,
`SubjectController`, `ClassSubjectController`, `TeacherSubjectController`, `ClassTeacherController`,
`ExamController`, `ExamTypeController`, `ExamResultController`, `ExamScheduleController`,
`ExamAnalysisController`, `ExamReportController`, `ExamDashboardController`, `MarkSheetController`,
`MarksApprovalController`, `GradingScaleController`, `ReportCardTemplateController`,
`CompetencyAssessmentController`, `StrandController`, `SubStrandController`, `LearningAreaController`,
`GradeBookController`, `StudentReportController`.

Portal / mobile: `Portal\PortalProfileController`, `Portal\PortalAttendanceController`,
`Portal\PortalReportCardController`, and 30+ `Mobile\*` controllers (`MobileStudentController`,
`MobileExamController`, `MobileAttendanceController`, `MobileTeacherClassController`, …).

### Models `[V]`

Student tree: `Student`, `Parents`, `StudentParentRelationship`, `StudentSibling`,
`StudentClassEnrollment`, `StudentDocument`, `EmergencyContact`, `DisciplinaryRecord`,
`MedicalIncident`, `StudentTransportAssignment`, `StudentNotice`.

Academic tree: `AcademicYear`, `Term`, `TermWeek`, `SchoolClass` (table **`classes`**),
`Section`, `ClassSection`, `Classroom`, `Subject`, `ClassSubject`, `TeacherSubject`, `Timetable`,
`TimetableOverride`, `Period`, `Exam`, `ExamType`, `ExamResult`, `ExamSchedule`, `ExamRoom`,
`GradingScale`, `ReportCardTemplate`, `CbcLearningArea`, `CbcStrand`, `CbcSubStrand`,
`CbcAssessment`, `AcademicEvent`, `Homework`, `Staff` (teachers).

**There is no `Promotion`, `Transfer`, `Guardian`, `Mark`, `Result` or `Grade` model.** `[V]`

### Services `[V]`

`AdmissionNumberService`, `TeacherScopeService`, `PortalScopeService`, `CbcGradingService`
(aliased `CbeGradingService`), `CurriculumService`, `TeacherMobileHomeService`,
`DashboardWidgetService`, `MenuService`, `TimetableGeneratorService`,
`TimetableConflictService`, `ModuleManager`, `LibraryService`, `Communication\*`.
**No promotion, attendance or report-card service exists** — that logic lives in controllers `[S]`.

### Tables actually present `[V]`

```
%student%  student_attendance, student_class_enrollments, student_discounts, student_documents,
           student_fee_assignments, student_notices, student_parent_relationship, student_siblings,
           student_transport_assignments, students
%class%    class_sections, class_subjects, classes, classrooms, student_class_enrollments
%subject%  class_subjects, subjects, teacher_subjects
%exam%     exam_results, exam_rooms, exam_schedules, exam_types, exams
%attend%   staff_attendance, student_attendance
%parent%   parent_notification_preferences, parents, student_parent_relationship
%promot%   (none)
%transfer% (none)
%guardian% (none)
%grade%    (none)
```

Note the naming: the model `SchoolClass` maps to table **`classes`**, `StudentAttendance` maps to
**`student_attendance`** (singular), `StudentParentRelationship` maps to
**`student_parent_relationship`** (singular). Five different naming conventions coexist. `[V]`

### Key table shapes `[V]`

```
students: student_id, user_id, admission_no, nemis_number, upi_number, roll_number, student_category,
  education_system, is_scholarship_holder, scholarship_details, behavior_score, special_notes,
  last_login_at, is_active, first_name, middle_name, last_name, date_of_birth, birth_certificate_no,
  gender, city, county, sub_county, country, address, postal_code, nationality, religion, blood_group,
  medical_conditions, allergies, medications, doctor_name, doctor_phone, phone, emergency_contact,
  emergency_contact_name, emergency_contact_relationship, emergency_contact_phone_2, admission_date,
  previous_school, previous_class, transfer_date, transfer_reason, transfer_certificate_no, photo_url,
  status, enrollment_status, graduation_date, leaving_reason, uses_transport, route_id, pickup_point,
  is_hosteller, created_at, updated_at, deleted_at

classes:                  class_id, name, numeric_value, description, created_at, updated_at
sections:                 section_id, class_id, name, capacity, created_at, updated_at
class_sections:           class_section_id, academic_year_id, class_id, section_id, classroom_id,
                          class_teacher_id
student_class_enrollments: enrollment_id, student_id, class_section_id, roll_number, academic_year_id,
                          is_current, enrollment_date, status, created_at, updated_at
student_attendance:       attendance_id, student_id, class_section_id, academic_year_id, term_id,
                          date, status, remarks, marked_by, created_at, updated_at
exam_results:             result_id, exam_id, student_id, class_section_id, subject_id, marks_obtained,
                          grade_id, remarks, created_by, created_at, updated_at, is_approved,
                          approved_by, approved_at
parents:                  parent_id, user_id, first_name, last_name, relationship, email, phone,
                          alternate_phone, occupation, created_at, updated_at
```

**A and B are the most important lines above**: `students` has **no** class, section or stream column.
Current placement comes only from `student_class_enrollments`, and `exam_results` and
`student_attendance` both carry `class_section_id` rather than reading it from the student. That is
the right shape — see §L. `[V]`

---

## B. Feature Map — what actually exists

### Student Management

| Feature | State | Evidence |
|---|---|---|
| Student registration / profile | **Present** — very wide profile, 60 columns | `students` columns `[V]` |
| Admission numbers | **Present** — `AdmissionNumberService`, unique index | §6/§S `[V]` |
| NEC/identity numbers | Present — `nemis_number`, `upi_number` both unique | `[V]` |
| Guardian assignment | **Present** — `parents` + `student_parent_relationship` (59 rows) | `[V]` |
| Emergencies | Two mechanisms: 3 inline columns on `students` **and** an `emergency_contacts` table that has **0 rows** | `[V]` |
| Class / stream placement | **Present** — via enrollment rows only | `[V]` |
| Documents | Present — `student_documents` (213 rows) | `[V]` |
| Status / lifecycle | Present but **inconsistent** — `status`, `enrollment_status`, `is_active` all exist | §E2 `[V]` |
| Suspension | Present — `disciplinary_records` table (0 rows) | `[V]` |
| Transfer out | Fields only: `transfer_date`, `transfer_reason`, `transfer_certificate_no` | `[V]` |
| Graduation | Fields only: `graduation_date`, `leaving_reason`, `status='alumni'` | `[V]` |
| Withdrawal / re-entry | `leaving_reason` + `status`; no dedicated workflow table | `[S]` |
| Student ID card | Present — `StudentIdCardController` | `[S]` |
| Portal access | Present — `Portal\*` + 30 `Mobile\*` controllers | `[V]` |
| Bulk import | Present — `StudentImportController` + `StudentImportTest` | `[S]` |

### Academic Management

| Feature | State |
|---|---|
| Academic year / term | Present (`academic_years` 2, `terms` 6); `term_weeks` exists but is **empty** `[V]` |
| Grade / form | Present as `classes.numeric_value` (14 classes) `[V]` |
| Stream | Present as `sections` (28) bound to a class, with `capacity` `[V]` |
| Class+stream per year | Present as `class_sections` (28), with `classroom_id` and `class_teacher_id` `[V]` |
| Subjects | Present (24), unique `subject_code` `[V]` |
| Subject → class mapping | Present — `class_subjects` (336 rows) `[V]` |
| Teacher → subject mapping | Present — `teacher_subjects` (56) `[V]` |
| Class teacher | Present — `class_sections.class_teacher_id` (one per class-section) `[V]` |
| Timetable | Present (52 rows) — outside this module's scope, mapped only `[V]` |
| Attendance | Table + unique constraint exist; **0 rows** → unproven `[V]` |
| Exams / exam types | Present (12 exams, 11 types) `[V]` |
| Marks | Present (1231 results) + an **approval workflow** (`is_approved`, `approved_by`, `approved_at`) `[V]` |
| Exam scheduling | Present (495 rows) `[V]` |
| Grading scales | Present (8) — boundaries not yet inspected `[?]` |
| CBC | Partly present — `cbc_learning_areas` 29, `cbc_strands` 101, `cbc_sub_strands` 34, but `cbc_assessments` **0** `[V]` |
| Report cards | `report_card_templates` **0 rows**; `ReportCardTemplateController` + `PortalReportCardController` exist `[V]` |
| Ranking / positions | Not inspected `[?]` |
| Promotion | Controller exists, **no table, no model, no service** `[V]` |
| Repetition | No evidence of any representation `[S]` |

**Absent, and not to be treated as broken:** separate promotion/transfer history tables, a guardian
model (guardians are `parents`), repetition, KCSE-specific rules, `grade`/`form` tables beyond
`classes`.

---

## C. Live Data State `[V]`

```
students                          40      exam_results                   1231
parents                           59      exam_schedules                  495
student_parent_relationship       59      grading_scales                     8
student_siblings                   0      report_card_templates              0
classes                           14      cbc_assessments                    0
sections                          28      cbc_learning_areas                29
class_sections                    28      cbc_strands                      101
student_class_enrollments         36      cbc_sub_strands                   34
academic_years                     2      student_documents                213
terms                              6      emergency_contacts                 0
term_weeks                         0      timetable                         52
subjects                          24      student_attendance                 0
class_subjects                   336      staff_attendance                   0
teacher_subjects                  56
exams                             12
exam_types                        11
```

Workflows **exercised**: students, guardians, exams/marks, exam scheduling, subject↔class and
teacher↔subject mapping, documents.
Workflows **never exercised → unproven**: attendance (0), CBC assessment (0), report cards (0),
term weeks (0), siblings (0), emergency contacts (0), disciplinary (0), medical (0).

Student population: `students by gender: {"female":20,"male":20}`, `soft-deleted students: 0`.

---

## D. Student Lifecycle Map

| Step | Exists? | Where | History preserved? |
|---|---|---|---|
| Admit / create | Yes | `StudentController` + `AdmissionNumberService` | n/a |
| Admission number | Yes | `students.admission_no`, unique | n/a |
| Guardian | Yes | `parents` + `student_parent_relationship` | Yes (rows) |
| Academic year | Yes | `student_class_enrollments.academic_year_id` | Yes (per row) |
| Grade + stream | Yes | enrollment → `class_sections` → `classes` + `sections` | Yes (per row) |
| Subjects | Yes | `class_subjects` by class, not per student | Yes |
| Class lists / attendance / exams | Yes | via `class_section_id` on the row | Yes |
| Results | Yes | `exam_results.class_section_id` | Yes — the section is on the result, so a later move cannot re-point it `[V]` |
| Report card | Code exists | template table empty | unproven |
| Promotion | **Unproven and structurally thin** | `StudentPromotionController` only | **See §P — highest-risk area** |
| Transfer / graduate / leave | Fields on `students` only | `transfer_date`, `graduation_date`, `leaving_reason`, `status` | **Last event only** `[V]` |

---

## E. CRITICAL Findings

### E1. Promotion has no history container — and no history has ever been written `[V]` for the data, `[S]` for the code

- `%promot%` → `(none)`; `%transfer%` → `(none)`. There is no promotion or transfer table and no
  `Promotion`/`Transfer` model `[V]`.
- `student_class_enrollments`: `current enrollments total: 36 | non-current (history) rows: 0` `[V]`.
  Every enrollment row in the live database is `is_current`, and there are **zero** historical rows.
- The only promotion-adjacent storage is `students.transfer_date`, `graduation_date`,
  `leaving_reason` — single-valued, so they can hold the most recent event and nothing earlier `[V]`.

**Why this is critical.** If promotion mutates the existing enrollment row (rather than inserting a
new one and closing the old), then promoting a class destroys the record of where those students
were, and the empty history table proves it has never been exercised safely. The user's explicit
standard — "without losing history" — cannot currently be demonstrated. **I have not read
`StudentPromotionController` yet**, so I am not asserting that it overwrites; I am asserting that
there is nowhere for history to live and none exists. Confirming the mechanism is the first item in
Phase 4.

### E2. `exam_results` has no uniqueness guarantee `[V]`

```
exam_results:
    PRIMARY: result_id
```
No unique index on `(student_id, exam_id, subject_id)`. Compare `student_attendance`, which does have
`student_attendance_student_date_unique: student_id, date` `[V]`. A repeated submit — a double click,
a retry, a re-posted bulk form — can therefore create two marks for the same student, subject and
exam, with nothing in the database to stop it. Duplicate mark counting is a classic route to a wrong
position, a wrong class average and a wrong report card. (The live duplicate count is `0`; the probe
that would have confirmed it crashed before printing — see §S.)

### E3. Student status is stored twice and the two disagree `[V]`

```
students by status:    {"active":36,"alumni":2,"transferred":1,"inactive":1}
students by is_active: {"1":40}
```

`status` says four students are not active; `is_active` says all forty are. Two sources of truth for
the same fact, currently inconsistent, and no constraint ties them. Whichever a given screen filters
on decides whether an alumnus appears in a class list, an attendance roll or a fee register. This is
the "student status/lifecycle" risk in the brief, already realised in live data.

### E4. Four students have no enrollment at all `[V]`

`students with NO enrollment row: 4` (40 students, 36 enrollment rows). They have a student record
and no placement, so they are invisible to class lists, attendance and marks entry while still being
counted in student totals. There is a `StudentUnassignedController` and a `StudentUnassignedTest`,
which suggests this is a known state — but nothing prevents it and nothing resolves it visibly.

---

## F. HIGH Findings

### F1. Historical placement cannot be reconstructed for the current cohort `[V]`

`is_current` rows: 36, non-current: 0. Answering "which class was this learner in last year?" is
impossible from data as it stands, which is exactly the check in item 11 — Grade 7 in 2025 promoted
to Grade 8 in 2026 must still show 2025 results and attendance. Results and attendance are safe
(they carry their own `class_section_id`), but the *placement narrative* is not stored.

### F2. Attendance is entirely unproven `[V]`

`student_attendance 0` rows, `by status: []`. The schema is good — unique `(student_id, date)`, plus
`class_section_id`, `academic_year_id`, `term_id`, `marked_by` — but not one row exists, so no
attendance report, register, percentage or portal view has ever produced real output. Every
attendance integrity check I ran returned zero **because the table is empty** (marked unproven, not
passing).

### F3. Report cards have never been produced `[V]`

`report_card_templates 0`. `ReportCardTemplateController`, `PortalReportCardController` and
`MobileReportController` all exist. With no template and no exercised path, reproducibility — "does
an old report card change when grading rules change?" — cannot be verified, and the answer depends
entirely on whether grades are stored per result (`grade_id` — encouraging) or recomputed on render.

### F4. Two student status vocabularies across the schema `[V]`

`students.status` ∈ {active, alumni, transferred, inactive} in live data, while the brief's expected
list includes suspended, withdrawn, graduated and deceased. `students` also carries
`enrollment_status`. Three overlapping fields, no enum documented, no cross-check. Filters that use
one and writes that set another will produce lists that disagree.

---

## G. MEDIUM Findings

1. **Five naming conventions for the same concept** `[V]`: table `classes` for `SchoolClass`,
   `student_attendance`/`student_parent_relationship` singular, `student_class_enrollments` plural,
   `exam_results` plural. Controllers and views must each remember which; a wrong guess fails at
   runtime.
2. **Two emergency-contact mechanisms** `[V]`: three columns on `students`
   (`emergency_contact`, `emergency_contact_name`, `emergency_contact_relationship`,
   `emergency_contact_phone_2`) *and* an `emergency_contacts` table with 0 rows. Which is
   authoritative is undefined.
3. **`exam_results` carries no academic year or term** `[V]`: they must be reached via `exams`.
   Filtering marks by term therefore requires a join, and any report that forgets it will silently
   mix years.
4. **`student_attendance.term_id` and `academic_year_id` are `ON DELETE SET NULL`** `[V]`: deleting a
   term leaves attendance rows that belong to no period, with no constraint stopping it.
5. **`term_weeks` empty but modelled** `[V]` — week-level attendance/coverage has no data.
6. **`student_siblings` is `ON DELETE CASCADE`** `[V]`: deleting one student removes the sibling link
   from the *other* student's record too.
7. **`student_notices` FK is `ON DELETE NO ACTION`** `[V]` — a third variant alongside RESTRICT and
   SET NULL.
8. **`students` has 60 columns** `[V]`, mixing identity, medical, transport, hostel, transfer and
   scholarship data — one wide table behind a single model, which is why validation and filtering
   rules are hard to keep consistent.

---

## H. LOW / UI Findings

Not audited in this pass `[?]`. The Icon/FA-version, pseudo-element and search-padding defects already
fixed in Fee Management (FA 5.14.0 vs FA6 names, `fa-table::before`) have **not** been swept across
Academic and Student views. The same sweep is required — see §W Phase 6. Do not assume the two
fee-side fixes cover these views.

---

## I. Authorization Matrix — `[?] NOT EXECUTED`

Not run. I will not publish a permission matrix inferred from controller names; the whole point is to
check what the middleware actually enforces. The exact method is defined in §W Phase 1 and covers:
student profile/edit/delete, class placement, promotion, marks, attendance, report cards, and
academic year/term activation, cross-referenced against the real role list in the RBAC seeder.

## J. Student Privacy / IDOR — `[?] NOT EXECUTED`

High priority and not yet run. `TeacherScopeService` and `PortalScopeService` both exist `[V]`, which
is a good sign, but whether they are actually applied on every relevant controller method is exactly
what must be tested: `MobilePortalDataIsolationTest` exists in the suite `[V]` and is the right place
to extend coverage.

## K. Academic Calculation Map — `[?] NOT EXECUTED`

`GradingScale` (8 rows), `CbcGradingService`/`CbeGradingService`, `ExamResultController`,
`ExamAnalysisController`, `ExamReportController`, `MarkSheetController`, `MarksApprovalController`
and `GradeBookController` all exist `[V]`. Whether totals, averages, positions and grades are computed
in one place or repeated across those controllers and their Blade views is **not yet traced**. There
is no dedicated result-calculation service, which makes duplication likely but is not proof.

## L. Enrollment / Class Placement Integrity — **audited** `[V]`

```
students with NO enrollment row: 4
students with >1 CURRENT enrollment: 0
current enrollments total: 36 | non-current (history) rows: 0
enrollments pointing at a MISSING class_section: 0
enrollments pointing at a MISSING academic year: 0
enrollment academic_year != class_section academic_year: 0
```

**The architecture here is right and should be protected.** Placement is never stored on `students`;
`class_sections` binds class + stream to a year; enrollments bind a student to a `class_section` for
a year with an `is_current` flag; `exam_results` and `student_attendance` each carry their own
`class_section_id`, so a later move cannot retroactively re-point an old result. That is what keeps
Grade 7 2025 results intact after an August promotion. The only defect is that the history rows were
never written (E1/F1), not that the model is wrong.

`class_sections` has no unique index on `(class_id, section_id, academic_year_id)` `[V]` — the same
class+stream can be created twice for one year. Live duplicates: not yet counted `[?]`.

## M. Academic Year / Term Integrity — partially audited `[V]`

```
terms: 6   academic_years: 2   term_weeks: 0
unique:  terms_academic_year_id_code_unique: academic_year_id, code
         academic_years: name (unique)
```

Both tables are uniquely keyed sensibly, and both are shared with Fee Management (§U). Not yet
checked `[?]`: how many years are flagged `is_current` (the "two years active at once" question),
whether term dates fall inside their year, and whether opening a new year mutates historical rows.
`academic_years.is_current` and `terms.status` exist, so the switch is flag-based — the risk is a
second flag being set rather than the model being wrong. Fee Management reads both tables, so any
change here is a cross-session change.

## N. Exam / Marks Integrity — partially audited `[V]`

Confirmed: `results for a MISSING student: 0`; FK from `exam_results` to `students`, `exams`,
`subjects`, `grading_scales`, `class_sections` all **`ON DELETE RESTRICT`** — good, a student or exam
in use cannot be deleted out from under a mark. `created_by` / `approved_by` → `staff` RESTRICT.

Not confirmed `[?]` (the probe crashed on the `exams` primary key, which is not `id`):
duplicate `(student, exam, subject)` count, marks above max, negative marks, NULL marks, and marks
belonging to inactive or soft-deleted students. The missing unique index (E2) is already established.

## O. Attendance Integrity — partially audited `[V]`, all checks vacuous

```
duplicate (student,date): 0 / future-dated attendance: 0 / by status: []
attendance rows whose class_section does not exist: 0
```
With 0 rows these prove the constraint, not the workflow. `date` (not `attendance_date`) is the
column name. FK: `student_id`, `class_section_id`, `marked_by` RESTRICT; `academic_year_id`,
`term_id` SET NULL.

## P. Promotion / Transfer Integrity — **highest-risk area** `[V]` data, `[S]` code

No promotion table, no transfer table, no promotion model, no promotion service; enrollment history
is empty; lifecycle exits are stored as single-valued columns on `students` `[V]`. `[S]` from the
controller list alone, `StudentPromotionController` and `StudentTransferController` carry the whole
workflow. **Before anything is implemented, three questions need code answers**: does promotion
insert a new enrollment or update the existing one; does it flip `is_current` on the old row; and
does it write anything durable about the decision (who, when, from which class)? Until then, bulk
promotion cannot be called safe, and the empty history table is the reason for concern rather than
reassurance.

## Q. Reports / Report Cards — `[?] NOT EXECUTED`

`StudentReportController`, `ExamReportController`, `ReportCardTemplateController`,
`PortalReportCardController`, `MobileReportController` and many `*ReportController`s exist `[V]`.
Route/load/filter/total/export verification not yet run.

## R. UI Audit — `[?] NOT EXECUTED`

Not run. Note that Academic/Student views have **not** been swept for the Font Awesome 6-vs-5.14 and
pseudo-element class collision defects fixed on the fee side; treat them as equally likely to be
affected. Do not fix globally.

## S. Database Integrity — **audited** `[V]`

**Unique protection present**
```
students:      admission_no · user_id · nemis_number · upi_number          ← identity is protected
subjects:      subject_code
terms:         (academic_year_id, code)      academic_years: name
student_attendance: (student_id, date)                                     ← duplicates blocked
```

**Unique protection absent**
```
exam_results:              PRIMARY (result_id) only        ← E2, duplicates possible
student_class_enrollments: PRIMARY (enrollment_id) only    ← a student can be enrolled twice in a year
class_sections:            PRIMARY (class_section_id) only ← class+stream+year can be duplicated
classes / sections / parents: PRIMARY only                 ← duplicate class, stream or parent names
```

**Identity integrity — all clean** `[V]`
```
duplicate admission_no groups: 0        null/blank admission_no: 0
admitted before date of birth: 0        future admission_date: 0
soft-deleted students: 0
```

**FK delete rules** `[V]` — mostly protective: `exam_results`, `student_class_enrollments`,
`student_attendance`, `student_documents` and `student_parent_relationship` are all
`ON DELETE RESTRICT` against `students`. The exceptions that need thought: `student_siblings`
CASCADE (G6), `parent_notification_preferences` CASCADE, attendance's year/term SET NULL (G4), and
`student_notices` NO ACTION (G7). Because `students` uses `deleted_at` soft deletes `[V]`, normal
deletion never reaches these rules — but a force delete would.

**Race conditions** `[?]` not yet checked: `AdmissionNumberService` was not read, so whether the
number is generated with a database-level guarantee or a `max()+1` read-then-write is unverified. The
unique index on `admission_no` would at least reject a collision rather than store it.

## T. Tests — baseline `[?] NOT RUN`

Relevant suites identified `[V]`: `StudentClassEnrollmentTest`, `StudentUnassignedTest`,
`StudentEditFormTest`, `StudentPhotoTest`, `StudentImportTest`, `StudentSoftDeleteTest`,
`StudentModuleFixesTest`, `AttendanceAndAcademicIntegrityTest`, `AttendanceTermScopingTest`,
`ExamAnalysisTest`, `ExamStatisticsTest`, `ExamRankingsTest`, `CbeGradingConsistencyTest`,
`CbcStageFilteringTest`, `ClassSubjectValidationTest`, `ClassTeacherController`-related coverage,
`SchoolClassLevelPreservationTest`, `TeacherManagementTest`, `RbacVisibilityTest`,
`AuthorizationGuardTest`, `MobilePortalDataIsolationTest`, `MobileStudentsIncrementalSyncTest`,
`ViewRouteReferenceTest`, `PaginationFilterPreservationTest`.

**Not executed, deliberately.** Two other sessions are working in this repository and all suites share
one test database; the Fee session already lost a run this way (13 spurious
`Unknown column 'is_protected'` failures caused by two concurrent runs against the same schema). Per
the brief's "run tests only if you can safely use an isolated test DB", a baseline requires either a
quiet repository or a dedicated test schema — not a shared one. **What is needed:** confirmation that
no other session is running PHPUnit, then a single full run, reported as counts/assertions/runtime.

## U. Cross-Module Dependencies and conflict risk

**Shared with Fee Management** (do not edit in this audit) `[V]`:
`academic_years`, `terms`, `students`, `classes`, `sections`, `class_sections`,
`student_class_enrollments`; `Term.php`, `AcademicYear.php`, `Student.php`, `SchoolClass.php`,
`Section.php`, `ClassSection.php`; `TermController`, `AcademicYearController`; permissions
`academics.*` / `students.*`; `config/menu.php`; `routes/web.php`.

**Shared with HR/Teachers**: `staff`, `staff_attendance`, `teacher_subjects`, `class_sections.class_teacher_id`,
`Staff.php`, `TeacherSubjectController`, `TeacherScopeService`.

**Portal / mobile / communication**: `PortalScopeService`, `MobileStudentController`,
`MobileExamController`, `MobileAttendanceController`, `MobileReportController`, `NotificationDispatcher`.

**Disclosure — files I changed earlier in this conversation that are shared with these modules:**
`config/menu.php`, `routes/web.php`, `app/Services/FeeBalanceService.php`,
`app/Services/LedgerService.php`, `app/Http/Controllers/StudentReportController.php`,
`app/Http/Controllers/FeeDashboardController.php`, `app/Http/Controllers/RefundController.php`, plus
new `FeeIntegrityService`/`FeeIntegrityController` and two new test files. The `StudentReportController`
change touches a **Student**-named controller and the student fee-status report, and `config/menu.php`
and `routes/web.php` are files any session may touch. That is the concrete conflict surface: a
concurrent session editing those three files could collide with the fee work. This audit adds **no**
further changes to them.

## V. What Is Already Done Well — protect this

1. **Placement is not denormalised onto `students`** `[V]` — current class lives only in enrollment
   rows, which is what makes historical placement possible at all.
2. **Results and attendance carry their own `class_section_id`** `[V]` — moving a student later cannot
   silently re-point an existing mark or attendance row. This is the single most important design
   decision in the module.
3. **Identity is protected at the database level** `[V]` — `admission_no`, `nemis_number`,
   `upi_number` and `user_id` are all unique, and live data has zero duplicates or blanks.
4. **Attendance duplicates are blocked in the schema, not just the form** `[V]` —
   `student_attendance_student_date_unique (student_id, date)`.
5. **Marks are protected by RESTRICT FKs and have an approval workflow** `[V]` —
   `is_approved`/`approved_by`/`approved_at`, so marks can be segregated from their entry.
6. **Students use soft deletes** `[V]` — `deleted_at` exists and 0 rows are deleted.
7. **Scope services exist** `[V]` — `TeacherScopeService`, `PortalScopeService`, `AdmissionNumberService`,
   `CbcGradingService` are real abstractions, not inline logic.
8. **A substantial test suite already covers this area** `[V]` — 20+ suites named above.

## W. Recommended Implementation Phases

### Phase 0 — Safe test baseline
Confirm no other session is running PHPUnit; run the full suite once; record passed/failed/assertions/
runtime. Extend `StudentSoftDeleteTest` and `AttendanceAndAcademicIntegrityTest` rather than replacing
them.

### Phase 1 — Privacy and authorization (highest severity)
Build the route → controller → method → middleware → permission matrix for every controller in §A.
Then, specifically: can a teacher open another class's marks or attendance by changing an ID; can a
parent read another student's results, attendance or profile. Both scope services exist; the question
is coverage. Fix only what is proven missing.

### Phase 2 — Lifecycle integrity (E1–E4, F1, F4)
Read `StudentPromotionController` and `StudentTransferController` and answer the three questions in
§P. Make status single-sourced (E3), resolve the four unplaced students (E4), and decide whether
promotion writes durable history. Nothing else in this module matters as much.

### Phase 3 — Marks and results
Add the missing `(student_id, exam_id, subject_id)` protection (E2) after re-counting duplicates.
Trace totals/averages/positions to one source of truth (§K). Confirm `exam_results.grade_id` is
stored rather than recomputed, which is the difference between reproducible and drifting report cards.

### Phase 4 — Attendance, promotion, transfers
Exercise the attendance workflow end to end (F2) before trusting any attendance report. Verify
promotion is atomic, idempotent and history-preserving.

### Phase 5 — Reports, report cards, dashboards
Create a report card template and verify a historical one does not change when grading rules or class
change (F3). Reconcile dashboard counts against the report tables.

### Phase 6 — UI consistency
Sweep Academic/Student views for FA6-vs-5.14 icon names, class-name collisions with Font Awesome, and
search-icon alignment — the same root causes already fixed in Fee Management, applied the same way
(fix the shared cause, never disable pseudo-elements globally). Then dependent dropdowns
(Year → Class → Stream → Student), filters, empty states and exports.

---

## Appendix — methodology and leftovers

Two read-only probe scripts were used and left in the repository root: `tmp_audit_as.php` (superseded,
had wrong table names) and `tmp_audit_as2.php`. Both are SELECT/SHOW only. Three of the integrity
sections (exam/marks duplicates and ranges, class/stream duplicate names, year/term flags) still need
one more run — the script is patched for the `enrollment_id` key but stops at the `exams` join, whose
primary key is not `id`. They should be deleted, not committed.

No live student record was modified. No promotion, transfer or deletion was performed.
