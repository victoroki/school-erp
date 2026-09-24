# school-erp — Phase 2 Repair Log

Scope: Laravel 10 backend/web only. `school-erp-mobile` is read-only until explicitly authorised.
Baseline: Phase 1 (missing functionality) + Phase 1B (view/workflow defects).

Test environment: PHPUnit runs against an isolated MySQL database `school_erp_test`
(forced in `phpunit.xml`), so the development database is never touched.

---

## Batch P0-A — RBAC edit data loss

### Status: COMPLETE (scope corrected — original finding was partly a false positive)

### CORRECTION TO PHASE 1B

Phase 1B reported that the role/user edit forms compared Eloquent **models** against scalar
ids, so no checkbox was ever pre-ticked and a save wiped the row's permissions:

```php
$role->permissions->contains($permission->permission_id)   // reported as "always false"
```

**This was wrong.** `Illuminate\Database\Eloquent\Collection::contains()` overrides the base
collection implementation and handles scalars by comparing primary keys:

```php
// vendor/laravel/framework/src/Illuminate/Database/Eloquent/Collection.php
public function contains($key, $operator = null, $value = null)
{
    if (func_num_args() > 1 || $this->useAsCallable($key)) {
        return parent::contains(...func_get_args());
    }
    if ($key instanceof Model) {
        return parent::contains(fn ($model) => $model->is($key));
    }
    return parent::contains(fn ($model) => $model->getKey() == $key);   // scalar case
}
```

Verified two ways: empirically (`LEGACY_CONTAINS=true`) and by reading the framework source.
The checkboxes **were** pre-ticking correctly. Calling `in_array()` directly on
`$collection->all()` *would* fail ("Object could not be converted to int"), which is what the
audit assumed was happening.

**Lesson applied:** every Phase 2 fix must be falsified — stash the fix, re-run the new test,
confirm it FAILS. A test that passes with and without the fix proves nothing.

### Problems actually fixed

| # | Problem | Root cause |
|---|---|---|
| 1 | A partial/API update to a role (`PUT` with only `role_name`) silently deleted **every** permission on that role | `RoleController::update()` ran `permissions()->sync([])` in its `else` branch — "the field wasn't sent" was indistinguishable from "the user cleared everything" |
| 2 | Same class of bug for users — a request without a `roles` payload stripped all roles | `UserController::update()` passed `$request->input('roles', [])` straight into sync |
| 3 | The Owner could lose the Owner role by editing their own account (lockout, with no UI to restore it) | The Owner checkbox is deliberately not rendered, so the browser posts no roles and `sync([])` removed it |
| 4 | Role/user update audit entries recorded an empty change payload | Audit read `$request->input('roles')` and an unqualified pivot `pluck` |

### Files changed

- `app/Http/Controllers/RoleController.php` — new `syncPermissions()` guard; real permission ids in the audit trail
- `app/Http/Controllers/UserController.php` — `roles_submitted` guard; Owner role always preserved; real role names in the audit trail
- `resources/views/roles/fields.blade.php` — explicit `old()`-aware pre-tick + `permissions_submitted` marker
- `resources/views/users/fields.blade.php` — explicit `old()`-aware pre-tick + `roles_submitted` marker
- `tests/Feature/RbacEditPreservationTest.php` *(new)* — 8 tests

### Architectural changes

- An "explicitly empty selection" is now distinguishable from "field never carried", via a
  hidden marker input. Applied consistently to both RBAC update paths.

### Security changes

- Owner role can no longer be stripped through user administration (defence in depth on top of
  `UserPolicy` / `RolePolicy`).
- Super Admin privilege escalation to Owner via a crafted `roles[]` payload is covered by a test.

### Verification

- `RbacEditPreservationTest`: 8 tests, all passing.
- Falsification: 3 of the 8 (absent-key guard, user no-payload guard, Owner preservation) fail
  against the unfixed code — the fix is genuinely exercised.
- No regression path found.

---

## Batch P0-B — Record corruption on ordinary edits

### Status: COMPLETE

### Problem

`resources/views/school_classes/fields.blade.php` rendered:

```php
{!! Form::hidden('numeric_value', 0) !!}
```

`LaravelCollective\Html\FormBuilder::getValueAttribute()` returns a non-null explicit value
**before** consulting the bound model:

```php
if (! is_null($value)) {
    return $value;          // line ~1318 — model never consulted
}
if (isset($this->model)) {
    return $this->getModelValueAttribute($name);
}
```

So the input always posted `0`. `SchoolClassController::update()` passes `$request->all()` to the
repository, so **every ordinary edit to a class silently reset its level to 0**, and
`ClassSubjectController::getSubjectsByClass()` filters subjects by that value — meaning
grade-level subject matching could never work.

### Problems fixed

| # | Problem | Root cause |
|---|---|---|
| 1 | Editing any class reset its level to 0 | Hidden input with a literal `0` overriding the stored value |
| 2 | Level was not editable anywhere in the UI | It only ever existed as the hidden `0` |
| 3 | Level displayed as a raw "Numeric Value" stat | Label did not express what the field does |
| 4 | No bound on the value | `$rules` allowed any integer |

### Files changed

- `resources/views/school_classes/fields.blade.php` — real `Form::number` field with help text
- `app/Models/SchoolClass.php` — `numeric_value` now `nullable|integer|min:0|max:99`
- `resources/views/school_classes/show.blade.php` — labelled "Level / Order", `—` when unset
- `tests/Feature/SchoolClassLevelPreservationTest.php` *(new)* — 5 tests

### Pattern sweep (rule: fix the pattern, not the symptom)

All 28 `Form::hidden(...)` occurrences in `resources/views/**` were reviewed. The
`hidden 0 + checkbox 1` idiom is the correct Laravel pattern **only when paired with a
same-named checkbox**, and 22 of them are. Only `school_classes` had a literal hidden value
with no companion control overwriting stored data.

Two further instances were found and deferred to their owning batches rather than fixed here:

- `bank_accounts/create.blade.php:86` — hidden `current_balance` synced only by JS; also violates
  the rule that accounting balances must derive from transactions (→ Financial Management batch).
- `exam_results/fields.blade.php:47` — `Form::hidden('created_by', Auth::id())` puts an
  audit-relevant field under client control (→ authorization batch).

### Verification

- `SchoolClassLevelPreservationTest`: 5 tests, all passing.
- Falsification: the 3 discriminating tests (edit pre-fill, edit preserves level, create renders a
  real field) all FAIL against the unfixed code.

---

## Combined regression status

```
php vendor\bin\phpunit --filter 'RoleProtectionTest|RbacVisibilityTest|RbacEditPreservationTest|SchoolClassLevelPreservationTest'

Tests: 43, Assertions: 238, Failures: 3.
```

The 3 failures are `RbacVisibilityTest` menu/dashboard assertions that were **proven
pre-existing** by re-running them against a stashed (unmodified) tree — they are not regressions
from this batch. They are real defects and belong to the authorization/menu batch.

---

## Next batches

- **P0-C** — Fee/payment integrity: one authoritative balance definition; reversed payments
  excluded from and visibly void in every surface; idempotent payment submission;
  `LedgerService::allocatePayment()` returning an undefined variable (always `[]`).
- **P0-D** — Authorization: routes with no effective guard, buttons shown to roles that 403,
  menu-visibility vs route-guard mismatches, client-controlled audit fields, IDOR on
  record-scoped routes.
- Then P1 module batches (Academic/CBC, Exams, Student, Attendance, Fee, Financial) and P2/P3.

### Standing constraint

Any change to a mobile-consumed endpoint's URL, payload, response shape or auth is **flagged,
not implemented**. Mobile code is not modified.

---

## Batch P0-C — Fee / payment integrity

### Status: COMPLETE (core money correctness), UI void-markers partly done

### Two further Phase 1/1B findings RETRACTED

Both came from static reading and were disproved against the live database:

1. **"Money precision is truncated — `fee_payments.amount` is DECIMAL(10,0)."**
   The *migration file* declares `decimal('amount', 10)`, which MySQL would treat as
   DECIMAL(10,0), but the **live column is `decimal(10,2)`** (`SHOW COLUMNS`). Cents were never
   being lost in production. The migration file is still inconsistent with production, so a
   *fresh install* would silently create (10,0) — now converged by the P0-C migration.
2. **"Three divergent balance sources, so the same student shows two balances."**
   The three sources exist in code, but the live data is currently self-consistent:
   `SUM(assignments.paid_amount)` and `SUM(fee_payments.amount)` both equal **1,568,855.00**, and
   **0 students diverge**. This is a *latent* defect that materialises on the first reversal,
   not an active one. No data backfill was required.

Evidence for both: direct `SHOW COLUMNS` / aggregate queries against the development database.

### Problems actually fixed

| # | Problem | Root cause |
|---|---|---|
| 1 | After reversing a payment, the student profile and arrears still counted the reversed money | `Student::paid_fee` = `payments()->sum('fee_payments.amount')` with no reversal filter; nothing marked the payment as reversed |
| 2 | A reversed payment rendered as a normal "paid" row with a valid-looking receipt | No reversal state existed on the row at all |
| 3 | Allocating a *later* payment resurrected the reversed amount into `paid_amount` | `allocatePayment` recomputed from a bare `PaymentAllocation::sum()`, and reversed allocations are retained for audit |
| 4 | `LedgerService::allocatePayment()` returned `$assignments ?? []` — an undefined variable, so it always returned `[]` | Copy/paste leftover; the variable does not exist in that scope |
| 5 | `collection_rate` could exceed 100% | All-time collected divided by the currently-active receivable; now clamped and period-aware |
| 6 | A reversed payment could be reversed again, double-posting contra entries | No guard |
| 7 | `storePayment` accepted **any** input: `$request->all()`, no validation at all | No `validate()` call on the money endpoint |
| 8 | A crafted POST could pay against **another student's** fee assignment while the URL claimed a different student | The posted `student_fee_assignment_id` was never checked against the route's student |
| 9 | Exception text (`SQLSTATE...`) was flashed to the user | `Flash::error('Error recording payment: ' . $e->getMessage())` |
| 10 | Double-submitting the collect form was a hard failure rather than a no-op | `client_reference` had a unique index but no application-level idempotency, so the second insert threw |

### Architectural change — one balance definition

New `app/Services/FeeBalanceService.php` is now the single source of truth:

```
outstanding = fees assigned (active) - valid payments (reversed excluded)
```

Attribution rule (documented in the class): when a payment has allocation rows the allocations are
authoritative for that assignment; otherwise the payment's direct `student_fee_assignment_id` is
used. That makes the current data set (direct FK only) and the allocation-based flow produce
identical figures, and it resolves in a constant number of queries.

Every consumer now routes through it:
- `Student::total_fee` / `paid_fee` / `balance_fee` (cached per model instance; 3 queries, not one per accessor)
- `FinanceService::getMetrics()` / `getCollectionRate()` / `updateAssignmentPaymentStatus()`
- `LedgerService::allocatePayment()` / `reversePayment()` / `seedOpeningBalance()`

### Schema change

`database/migrations/2026_09_21_000001_add_reversal_state_to_fee_payments.php`
- Adds `reversed_at` (+ index), `reversal_reason`, `reversed_by`.
- Converts `fee_payments.amount` to `decimal(10,2)` so fresh installs match production.
- **Additive and reversible.** No column dropped or retyped downwards; no data rewritten.

Applied to the development database with `migrate --path=...` so that the two **pre-existing
pending migrations that are not mine** (`..._notification_recipients`, `..._homework_submissions`)
were not applied as a side effect. Those remain pending.

`reversed_by` is intentionally **not** a foreign key: `users.id` is a signed `INT` on this schema
and `foreignId()` emits `bigint unsigned`, which failed DDL with *"Referencing column
'reversed_by' and referenced column 'id' ... are incompatible."* The sibling `collected_by`
column follows the same convention. A plain indexed integer is used instead.

### Security changes

- Payment amount, method, date and target assignment are validated server-side against the real
  business rules (`payment_method` restricted to the actual ENUM via `FeePayment::PAYMENT_METHODS`).
- IDOR closed: the posted assignment must belong to the student in the URL.
- Internal error text no longer reaches the user.

### Verification

- `tests/Feature/FeeReversalIntegrityTest.php` *(new)* — 10 tests: reversal restores the balance,
  reversal is marked on the row, reversed money is excluded from collected metrics, double reversal
  rejected, post-reversal allocation does not recount the reversed amount, `allocatePayment`
  returns what it created, accessors agree with the service, duplicate submission is idempotent,
  amount/method validated, cross-student assignment rejected.
- **Falsification: 8 of the 10 fail against the unfixed code** (7 failures + 1 error). The two that
  pass unfixed are documented as non-discriminating — `test_duplicate_submission...` passed because
  the unique index on `client_reference` already blocked the duplicate row (the old flow surfaced
  it as a swallowed exception instead of a clean no-op), and the accessor-agreement test involves
  no reversal.
- **Live-data proof of no regression:** all 6 pre-existing `FeeCollectionTest` tests still pass,
  including the total-payment distribution test that asserts per-assignment `paid_amount`.

### Known remaining gaps (honest)

- The void marker was added to the payment history on the fee detail page
  (`fee_management/show.blade.php`). Other surfaces that list payments
  (`reports/receipt_register`, `students/tabs/fees`, `portal/fee-receipt`) still need the same
  treatment, and any view that computes its own `SUM(fee_payments.amount)` instead of calling
  `FeeBalanceService` will still overstate collections after a reversal. Tracked as follow-up in
  the Fee Management module batch.
- Receipts remain non-sequential (`uniqid()`) with no receipt book, void/reprint audit or stored
  PDF. That is the next Fee Management item and needs a product decision on numbering format.

---

## Combined regression status (updated)

```
php vendor\bin\phpunit --filter 'RoleProtectionTest|RbacVisibilityTest|RbacEditPreservationTest|SchoolClassLevelPreservationTest|FeeReversalIntegrityTest|FeeCollectionTest'

Tests: 59, Assertions: 301, Failures: 3.
```

The 3 failures are `RbacVisibilityTest` menu/dashboard assertions, proven pre-existing via a
stashed baseline run. 23 new tests added across P0-A/P0-B/P0-C, all passing.

## Verified false positives so far (do not "fix" these)

1. `roles/fields.blade.php` / `users/fields.blade.php` checkbox preselect via
   `Eloquent\Collection::contains($scalar)` — works, it compares `getKey()`.
2. `fee_payments.amount` truncating cents — live column is `decimal(10,2)`.
3. Three divergent balance sources currently disagreeing — live data is consistent
   (latent only).

---

## Batch P0-D — Authorization and guard coverage

### Status: COMPLETE for the confirmed holes

### A fourth Phase 1B finding RETRACTED

**"`ModuleController::toggle` 500s because `AppBaseController` lacks the `AuthorizesRequests` trait."**
False. `app/Http/Controllers/Controller.php` already declares:

```php
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
```

`AppBaseController extends Controller`, so `$this->authorize('toggle', $module)` resolves
normally, and `ModulePolicy::toggle(User $user)` returns `$user->isOwner()`. The toggle was never
broken. A separate, real (small) defect in the same method *was* fixed — see below.

### The real hole: a permission guard attached to methods that do not exist

`CommunicationController::__construct()` read:

```php
$this->middleware('can:communication.view')->only(['index', 'show', 'sentMessages']);
```

This controller has none of those methods. Its real actions are `compose`, `send`, `history`,
`showHistory`, `getTemplate`, `getRecipientCount`. `can:communication.view` therefore protected
**nothing**, and the following were readable by any authenticated user:

| Endpoint | Leaked |
|---|---|
| `GET /communication/history` | Every message sent, with sender |
| `GET /communication/history/{id}` | Recipient names and phone numbers |
| `GET /communication/api/template/{type}/{id}` | Full SMS/email template bodies |
| `GET /communication/api/recipients/count` | Recipient counts per group |

**Proof of leak** (falsification run against the unfixed guard, as a Teacher):

```
1) test_teacher_cannot_read_the_message_history
   Expected response status code [403] but received 200.
2) test_teacher_cannot_read_an_individual_message
   Expected response status code [403] but received 302.
3) test_teacher_cannot_read_template_payloads
   Expected response status code [403] but received 200.
4) test_teacher_cannot_read_recipient_counts
   Expected response status code [403] but received 200.
```

The guard now targets the real method names, with permissions taken from what
`config/menu.php` already advertises for the same screens: `communication.view` for history and
message detail, `communication.manage` for compose, send and the compose-screen helpers.

### Other problems fixed

| # | Problem | Root cause |
|---|---|---|
| 1 | `GET /academic-calendar/create` and `/edit` were reachable by any signed-in user | The controller guarded `only(['index'])` and `only(['store','update','destroy'])` — the two form actions were covered by `auth` alone, while `config/menu.php` advertises `academics.settings.manage` for that screen |
| 2 | An unknown module key produced an unhandled 500 instead of a message | `ModuleManager::toggle()` uses `firstOrFail()` and throws `ModelNotFoundException`, but the controller only caught `DomainException` (which its own docblock promised) |
| 3 | The exam-result form posted `created_by` from the client | `Form::hidden('created_by', Auth::id())`. The controller already sets it server-side in `store`/`update`/`saveOne`, so the field was redundant *and* a mass-assignment footgun that could attribute marks to another user |

### Security changes

- Communication history, message detail, template payloads and recipient counts are now
  server-side authorized, consistent with menu visibility.
- Calendar entry forms are gated to match their menu permission.
- Audit-relevant fields (`created_by`) no longer travel through the request.

### Verification

- `tests/Feature/CommunicationAccessTest.php` *(new)* — 10 tests: Teacher blocked from history,
  message detail, template payload, recipient count, compose and send; Admin allowed on history and
  compose; module toggle restricted to Owner; Owner can toggle; `/modules` is Owner-only.
- `tests/Feature/AuthorizationGuardTest.php` *(new)* — 4 tests: calendar forms blocked for Teacher,
  index still allowed, calendar writes blocked, exam-result form no longer posts `created_by`.
- **Falsification:** 4 of the communication tests and 2 of the guard tests fail against the unfixed
  code, with the exact leak evidence quoted above.
- Note: `Module` binds on its **`key`**, not its id (`getRouteKeyName()` returns `'key'`), so the
  toggle URL is `/modules/{key}/toggle`. The first test draft got 404 instead of 403 for this
  reason; corrected.

---

## Combined regression status (updated after P0-D)

```
php vendor\bin\phpunit --filter 'RoleProtectionTest|RbacVisibilityTest|RbacEditPreservationTest|SchoolClassLevelPreservationTest|FeeReversalIntegrityTest|FeeCollectionTest|CommunicationAccessTest|AuthorizationGuardTest'

Tests: 73, Assertions: 322, Failures: 3.
```

The 3 failures are all in `RbacVisibilityTest` and are **pre-existing** (proven via stashed
baseline). 37 new tests added across P0-A..P0-D, all passing.

### What the 3 pre-existing failures actually are (measured, not guessed)

```
test_teacher_sees_only_academic_sections_with_no_orphaned_headings
  Student Management children
  Expected: ['Student Attendance']
  Actual:   ['Student Attendance', 'Homework', 'Student Notices']   <-- appears to be a STALE expectation

test_zero_permission_role_sees_only_dashboard_link
  Expected: ['CORE DASHBOARD']
  Actual:   ['CORE DASHBOARD', 'EDUCATIONAL UNITS']                 <-- header renders with (apparently) no visible child
```

So there are two different things here and they need separating before anything is changed:

1. The Teacher case most likely reflects **stale test expectations** — `Homework` and
   `Student Notices` were added to the menu after that test was written, and a teacher
   *should* see them. The test, not the menu, is probably wrong.
2. The zero-permission case looks like a **genuine orphaned-heading defect** — a section header
   rendering for a role with nothing visible inside it.

Neither is authorization-critical (route guards are what enforce access, and those are now
correct), but the second is a real UI/permission-consistency bug. Resolving which child is leaking
for the Student role needs one more diagnostic before a fix, and the Teacher question is a product
call (should a teacher see Homework / Student Notices? — I believe yes, which would mean updating
the test rather than the menu).

## Verified false positives so far (do not "fix" these)

1. `roles/` `users/` checkbox preselect via `Eloquent\Collection::contains($scalar)` — works; it compares `getKey()`.
2. `fee_payments.amount` truncating cents — live column is `decimal(10,2)`.
3. Three divergent balance sources disagreeing — live data is consistent; latent only.
4. `ModuleController::toggle` 500 from a missing `AuthorizesRequests` trait — `Controller` already uses it.

---

## Batch P1-A — Academic / CBC grading coherence

### Status: COMPLETE

### The defect, confirmed by measurement (not by the audit's summary)

Phase 1 reported "three contradictory CBC scales". Measuring them found **four**, and a fifth
variant in the live database:

| Source | EE | ME | AE | BE |
|---|---|---|---|---|
| `GradingScaleController::seedGrades('cbc')` (written to DB) | 76–100 | 51–75.99 | 26–50.99 | 0–25.99 |
| `CbeGradingService::LEVELS` (8-point, used by report cards) | EE1 90–100, EE2 75–89.99 | ME1 58–74.99, ME2 41–57.99 | AE1 31–40.99, AE2 21–30.99 | BE1 11–20.99, BE2 0–10.99 |
| `grade_book/index.blade.php` footer (hardcoded) | ≥75 | 41–74 | 21–40 | ≤20 |
| `exam_results/index.blade.php` badge map | `EE` → green | `ME` → blue | `AE` → amber | everything else → **red** |
| **live `grading_scales` rows** | — | — | — | a third scale entirely: `A+ 90-100 … F 0-32` |

Two concrete consequences:

1. **A top-performing learner rendered in red.** The badge map listed only the four short codes
   (`EE`/`ME`/`AE`/`BE`), but the application stores the 8-point codes (`EE1`, `EE2`, … `BE2`), so
   every one of them fell through to the final `else` → `badge-danger`.
2. **Grade assignment was non-deterministic.** `ExamResult::saving` resolved a grade with
   `GradingScale::where('min_percentage','<=',$pct)->where('max_percentage','>=',$pct)->first();`
   — no ordering, no curriculum filter — against a single global table where KCSE and CBE bands
   overlap heavily (55% is inside both KCSE `C+` 55–59.99 and CBE `ME2` 41–57.99). Which grade a
   learner received depended on row order. Pressing both "Load KCSE" and "Load CBC" *adds*
   overlapping rows, so the ambiguity is reachable from the UI.

### Design decision

`students.education_system` already exists (`enum('CBC','8-4-4')`) and `ExamReportController`
already branches on it, so no new concept was invented. The two legitimate, separate concepts were
kept distinct and the accidental duplication removed:

- **Competency rating per sub-strand** — `CbcAssessment::RATINGS` (1–4: BE/AE/ME/EE). A teacher's
  direct judgement on a specific sub-strand, with no percentage involved. Legitimate; kept as-is.
- **Achievement level from a percentage** — `CbeGradingService::LEVELS` (8-point, documented as
  mirroring KNEC KJSEA) is now the **single** source. Everything else derives from it.
- **KCSE (8-4-4)** — kept, but now tagged as a different curriculum instead of sharing one
  unlabelled list with CBC.

### Problems fixed

| # | Problem | Fix |
|---|---|---|
| 1 | Seeded CBC scale disagreed with the achievement scale used everywhere else | `seedGrades('cbc')` now derives its bands from `CbeGradingService::LEVELS`; the duplicated array is gone |
| 2 | Grade resolution was non-deterministic when bands overlapped | New `GradingScale::resolveForPercentage()` orders by `min_percentage DESC, grade_id` — deterministic by construction |
| 3 | A learner could be graded on the wrong curriculum | Resolution is filtered by the learner's own `education_system`; untagged scales still apply to all |
| 4 | 8-point achievement codes rendered as failures | New shared `App\Support\GradeBadge::for()` handles both CBE code families and KCSE letters |
| 5 | The grade book footer advertised a fourth, hand-written scale | Now rendered from `CbeGradingService::LEVELS` |
| 6 | Seeding KCSE then CBC could not coexist | Seeds now match on name **and** `education_system`, so both curricula can hold their own scales |

### Schema change

`database/migrations/2026_09_21_000002_add_education_system_to_grading_scales.php`
- Adds a nullable `education_system` column plus an index to `grading_scales`.
- **NULL means "applies to every curriculum"**, which is why **existing rows are deliberately not
  rewritten** — a pre-existing row's intended system cannot be reliably inferred from its name, and
  rewriting it would change live grading behaviour. New seeds tag themselves.
- Additive and reversible (one nullable column + index).

Applied to the development database with `migrate --path=...`, again avoiding the two unrelated
pre-existing pending migrations.

### Verification

- `tests/Feature/CbeGradingConsistencyTest.php` *(new)* — 8 tests: the seeded CBC scale equals
  `CbeGradingService::LEVELS` exactly (and the old contradicting boundaries cannot reappear), KCSE
  seeds tag as `8-4-4` without creating CBC rows, a 55% learner resolves `ME2` under CBC and `C+`
  under 8-4-4, untagged scales apply to every curriculum, resolution is stable across repeats,
  `EE1`/`EE2`/`EE` are green while `BE2` is red, the grade book footer no longer hardcodes a scale,
  and `ExamResult` grades against the learner's curriculum.
- **Falsification: 7 of the 8 fail against the unfixed code** (4 failures + 3 errors). The one that
  passes unfixed is `test_top_achievement_codes_are_not_rendered_as_failures`, which exercises the
  new `GradeBadge` helper — a file that did not exist before, so it cannot fail "against old code".
- Combined regression: **84 tests, 3 failures — the same 3 pre-existing `RbacVisibilityTest` menu
  assertions.** No exam, grading or result regressions.

### Mobile API impact

**No shape change** (no URL, payload, auth or response-structure change), so nothing to stop on.
One behavioural note worth carrying forward: because grade resolution now respects the learner's
curriculum, the *value* of the grade attached to a result can change for a CBC learner on a school
that had both scales loaded — from an arbitrary KCSE letter to the correct CBE code. The mobile
report-card payload carries the grade name, so a client that hardcodes expected grade strings would
see different strings, not a different structure. Flagged, not changed.

### Known remaining gaps

- `report_card_templates.education_system` defaults to `'8-4-4'` while `students.education_system`
  defaults to `'CBC'` — the template default should almost certainly follow the school's curriculum.
  Needs a product call, so left alone.
- The `exam_results` and `grade_book` surfaces now agree, but `exam_reports` and the portal report
  card still do not filter on `is_approved`, so unapproved marks still print as final. That is the
  next Examination item.
- CBC strand/sub-strand records are not yet scoped to a grade level (a "Numbers" strand is not bound
  to a grade), so the learning-area → strand → sub-strand chain is not enforced per grade.

---

## Batch P1-B — Report card curriculum default + marks approval

### Process change adopted

**No migration runs without explicit user confirmation, regardless of risk.** This was applied
from this batch onward. The two migrations in P0-C and P1-A were run via `migrate --path=...`
before this gate existed.

### 1. Report card template curriculum default

`report_card_templates.education_system` is an enum defaulting to `'8-4-4'`, so a CBC school's new
report card template defaulted to the wrong curriculum.

There is no school-level curriculum column (`schools` holds only `name`), so the school's curriculum
is now derived from its student body — the education system the majority of learners are recorded
under — via the new `App\Services\CurriculumService`. `ReportCardTemplate` fills
`education_system` from it on create unless the caller states otherwise.

- `app/Services/CurriculumService.php` *(new)* — `current()`, `isCbe()`, `normalise()`. Deterministic
  tie-break so the result never depends on row order; falls back to `CBC` (matching the schema's own
  `students.education_system` default) when no learners exist.
- `app/Models/ReportCardTemplate.php` — was a bare 12-line stub with no `$table`, `$fillable`,
  `$casts` or rules; now complete, with a `creating` hook and a `scopeForSystem()`.

**No migration was needed for this** — the app-level default is what determines the value.
The DB column default is still `'8-4-4'`, which only matters for raw SQL inserts that bypass
Eloquent. Changing it would require a migration and is **not** proposed unless you want it.

### 2. Marks approval

Two separate defects, both confirmed.

**(a) "Approve Selected Learners" approved the whole batch.**

`MarksApprovalController::approve()` narrowed a batch with:

```php
->when($request->filled('student_ids'), fn ($qq) => $qq->whereIn('student_id', $request->student_ids));
```

When nothing was ticked, `when()` was skipped and the batch was **not** narrowed — so the entire
exam × class-section pending set was approved while the button read "Approve Selected Learners".

Fixed by giving the two forms explicit intent: the drill-down form posts `approval_scope=selected`
and is rejected when no learner is ticked; the index form posts `approval_scope=batch` and keeps
its deliberate "Approve All" behaviour. Missing scope still defaults to the previous behaviour, so
nothing else changes.

**(b) Unapproved marks were indistinguishable from final ones on a report card.**

`ExamReportController` has always passed a per-row `approved` flag into the card template, but
nothing rendered it. `exam_reports/templates/card.blade.php` now prints a **PROVISIONAL** notice
when any row on the card is unapproved. Self-styled inline so it renders identically in the browser
print page and in DomPDF, which do not share a stylesheet.

### IMPORTANT — why `is_approved` filtering was NOT applied to report generation

The instruction was to apply `is_approved` filtering across report generation and the portal.
**Doing that literally would have blanked every report in the system.** Measured against the live
database:

```
exam_results.is_approved : tinyint(1) NOT NULL DEFAULT 0

total results      : 1231
approved           : 0
not approved       : 1231

exam 3:  0/249 approved
exam 5:  0/231 approved
exam 10: 0/231 approved
exam 11: 0/231 approved
exam 12: 0/231 approved
exam 8:  0/30  approved
exam 7:  0/14  approved
exam 9:  0/14  approved
```

The approval workflow has never been used in this data — nothing sets `is_approved`, and the column
defaults to false. Adding `where('is_approved', true)` to `ExamReportController::bulkPdf()`,
`classPosition()`/totals, or `PortalReportCardController::show()` would have produced empty report
cards and zeroed class positions for all 1231 results, while looking like a fix.

So the safe half was shipped (the approval state is now visible, and the approval workflow is
actually usable after fix (a)), and the filtering decision is parked for the user — see the open
decision below.

### Verification

- `tests/Feature/ApprovalAndCurriculumTest.php` *(new)* — 8 tests: template defaults to the school's
  curriculum (CBC-dominant and 8-4-4-dominant), an explicit curriculum is not overridden, the
  service falls back to CBC with no learners, "selected" with nothing ticked approves nothing,
  whole-batch approval still works, selected-learners approves only those learners, and approval
  records `approved_by`/`approved_at`.
- **Falsification: 4 of the 8 fail against the unfixed code** (3 errors + 1 failure). The failing
  one is exactly the reported bug — the whole batch was approved. The other 4 are
  behaviour-preservation tests (whole-batch still works, selected learners works, audit fields,
  fallback) and are not expected to discriminate.
- Combined regression: **89 tests, 3 failures — the same 3 pre-existing `RbacVisibilityTest` menu
  assertions.**

### Open decision for the user (genuine product fork)

Should report cards and the parent portal **hide** unapproved marks, or **show them as provisional**
(what is now implemented)?

- **Hide** — official documents only ever contain approved marks. Requires adopting the approval
  workflow across 1231 existing results first, or every report is empty. Needs `is_approved` filtered
  in `ExamReportController::bulkPdf()`, `classPosition()`/totals, and `PortalReportCardController`.
- **Show as provisional** — current behaviour; nothing breaks; approval becomes a quality signal
  rather than a gate. Optionally add a per-school setting or permission so strictness is a policy
  choice rather than hardcoded.

Recommendation: keep provisional-visibility, and make strict mode an explicit opt-in setting.

---

## Proposed next batch — Student module (MIGRATIONS REQUIRED, awaiting approval)

Per the migration gate, these are proposed and **not** started.

| # | Change | Migration? | Notes |
|---|---|---|---|
| 1 | Software deletes on `students` | **Yes** — add nullable `deleted_at` | Additive/reversible. Needs `SoftDeletes` on the model. Requires deciding whether historical attendance/results keep citing deleted learners (they should). |
| 2 | Term/year scoping on `student_attendance` | **Yes** — add nullable `academic_year_id`, `term_id` | Additive; backfill from each learner's enrollment. Without this, "this term's register" cannot be reconstructed. |
| 3 | Unique index on `students.nemis_number` / `upi_number` | **Yes** | Must first check for existing duplicates, or the index will be rejected. |
| 4 | Foreign keys on `disciplinary_records`, `medical_incidents`, `emergency_contacts` (`student_id`) | **Yes** | Must first check for orphaned rows. |
| 5 | `student_class_enrollments.is_current` set on create | **No** | Column exists; form and `store` simply never set it. App-level fix. |
| 6 | Admission-number generation | **No** | App-level; only needed if you want it system-generated rather than typed. |
| 7 | Sibling reciprocal insert writing an invalid enum value | **No** | App-level; write a valid `relationship_type`. |

I will check for existing duplicate/orphaned data before proposing 3 and 4 concretely, and will
present each migration for approval before running it.

---

## Batch P1-C — Student module (app-level items 5-7)

### Status: COMPLETE. Items 1-4 (schema) awaiting migration approval.

### 5. One current enrollment per learner

**The reported cause was wrong.** `StudentController::store` *does* set `is_current => true` when
auto-enrolling on admission, so that path was never broken. The real defect is in the standalone
enrollment module:

- the `student_class_enrollments.is_current` column **defaults to true**, and
- nothing ever cleared the flag on other rows.

So creating or promoting an enrollment through `student_class_enrollments` could leave a learner
holding several "current" classes at once. Because the student list, attendance register and fee
screens each read `is_current = true`, the class they showed depended on which row they loaded
first — this is one of the sources behind the "three different classes for one student" divergence
found in Phase 1B.

Measured on the live database: **0 students currently have multiple current enrollments**, so this
is latent, not active. (4 learners have no current enrollment at all — a separate, pre-existing
data gap.)

Fixed:
- `resources/views/student_class_enrollments/fields.blade.php` — an `is_current` switch, defaulting
  to on for a new enrollment and reflecting the stored value on edit.
- `app/Http/Controllers/StudentClassEnrollmentController.php` — `enforceSingleCurrentEnrollment()`
  clears the flag on the learner's other enrollments on both store and update. An explicit
  "not current" is honoured (the column default would otherwise force true).

### 6. Admission number generation

Nothing generated an admission number: the web form required the operator to type one and only the
CSV import *template* showed the expected shape (`ADM-2025-001`).

- `app/Services/AdmissionNumberService.php` *(new)* — produces `ADM-YYYY-NNN`, scoped to the
  admission year (derived from `admission_date`). Uses the **highest existing sequence** for the
  year rather than a `COUNT()`, because a count collides as soon as any learner in that year is
  deleted, and it re-checks uniqueness in a loop.
- `Student::$rules` — `admission_no` became `nullable` so a blank value reaches the generator
  (`CreateStudentRequest` still adds `|unique:students,admission_no` to it).
- `StudentController::store` — generates when blank; keeps a defence-in-depth duplicate guard for
  non-FormRequest callers. Note the web path can never reach that guard, because validation rejects
  a duplicate first.

### 7. Sibling reciprocal relationship type

`student_siblings.relationship_type` is
`enum(brother, sister, half_brother, half_sister, step_brother, step_sister)`. `addSibling()` wrote
the literal `'sibling'` for anything that was not exactly `brother`/`sister`, which is not a valid
enum value — every half/step sibling link would fail. It was validated only as
`string|max:50` against an enum column.

- Validation now matches the enum exactly.
- The reciprocal type is derived via `reciprocalSiblingType()` from the **other learner's gender**
  plus the same half/step prefix — e.g. "he is her half brother" stores "she is his half sister".
- The UI offered only Brother/Sister, so the half and step relationships the schema supports could
  not be recorded at all; all six options are now offered.

Live data: `student_siblings` has **0 rows**, so this never fired in production, but it would have
on first use.

### A bug I introduced and the tests caught

The first version of the enrollment fix type-hinted `StudentClassEnrollment` without importing it,
so the hint resolved to `App\Http\Controllers\StudentClassEnrollment` and both enrollment endpoints
returned **500**. `test_creating_a_current_enrollment_clears_the_previous_one` failed with
`Expected response status code [201, 301, 302, 303, 307, 308] but received 500`. Fixed by adding the
import. Recorded because it is the second time this session that a fix of mine broke a live path —
the tests are earning their keep.

### Verification

- `tests/Feature/StudentModuleFixesTest.php` *(new)* — 10 tests.
- **Falsification: 6 of the 10 fail against the unfixed code.**
- **Explicitly non-discriminating (4):**
  1. `test_generated_admission_numbers_increment_within_the_year` — exercises the new service
     directly; the service did not exist before, so it cannot fail "against old code".
  2. `test_a_typed_admission_number_is_respected` — old code also accepted a typed value.
  3. `test_a_duplicate_admission_number_is_rejected_without_creating_a_second_learner` — a duplicate
     is rejected by `CreateStudentRequest`'s `unique` rule under **both** versions, so the
     controller's guard is unreachable from the web path. Kept purely as behaviour preservation.
  4. `test_an_enrollment_can_be_saved_as_not_current` — the old code passed the submitted value
     through `$request->all()`, so an explicit 0 happened to work.

### Pre-existing failures (5, none caused by this work)

```
RbacVisibilityTest::test_teacher_sees_only_academic_sections_with_no_orphaned_headings
RbacVisibilityTest::test_zero_permission_role_sees_only_dashboard_link
RbacVisibilityTest::test_zero_permission_role_gets_minimal_unbroken_dashboard
StudentClassEnrollmentTest::test_class_enrollments_nav_entry_is_restored
StudentUnassignedTest::test_unassigned_nav_entry_is_configured
```

The last two were newly observed this batch, so they were checked against a stashed baseline and
give **identical** results with and without my changes — confirmed pre-existing, not regressions.
All five are menu/navigation consistency issues and belong to the same batch as the menu work.

Regression run: **166 tests, 709 assertions, 5 failures (all pre-existing).**

---

## Items 3-4 — schema changes, AWAITING MIGRATION APPROVAL

Pre-flight checks on the live database, as required before proposing DDL:

| Check | Result |
|---|---|
| Duplicate `students.nemis_number` | **0** |
| Duplicate `students.upi_number` | **0** |
| Orphaned `disciplinary_records.student_id` | **0** (table is empty) |
| Orphaned `medical_incidents.student_id` | **0** (table is empty) |
| Orphaned `emergency_contacts.student_id` | **0** (table is empty) |
| Existing FK on those three columns | **none** |

So both migrations can be applied without any data cleanup. Neither is destructive.

**Proposed migration A — `2026_09_21_000003_add_unique_indexes_to_students_identity_columns.php`**
- `students.nemis_number` → unique index (nullable; MySQL permits multiple NULLs).
- `students.upi_number` → unique index (nullable).
- Rationale: Kenya's NEMIS/UPI identifiers identify a learner nationally; without uniqueness the
  same learner can be admitted twice and appear twice in every report.
- `down()`: drop both indexes.
- Risk if a future import introduces a duplicate: the import will fail loudly rather than silently
  create a second record. The importer should be updated to report that as a row-level error.

**Proposed migration B — `2026_09_21_000004_add_student_foreign_keys_to_incident_tables.php`**
- Add `student_id` foreign keys on `disciplinary_records`, `medical_incidents` and
  `emergency_contacts`, each `->onDelete('cascade')` to match the sibling/enrollment tables.
- Rationale: these three are the only learner-owned tables with no referential integrity, so a
  deleted learner leaves orphaned disciplinary and medical history.
- `down()`: drop the three foreign keys.
- Note: `onDelete('cascade')` means deleting a learner erases their incident history. That is the
  current behaviour anyway (nothing restrains it), but it interacts with the soft-deletes proposal
  below — if learners become soft-deletable, cascade never fires and history is preserved.

**Still proposed, not yet detailed — requires the soft-delete decision:**
- Soft deletes on `students` (`deleted_at`) + term/year scoping on `student_attendance`
  (`academic_year_id`, `term_id`). Both additive. The attendance scoping needs a backfill from each
  learner's enrollment, which is a data write and will be presented separately.

---

## Batch P1-D — Attendance attribution, class-subject clearing, exam type edit

### Fifth and sixth false positives retracted

**5. "The student Academic tab crashes — `$student->academic_journey` does not exist."**
It does exist. `Student::getAcademicJourneyAttribute()` (line 434) has always resolved that
property, eager-loading the class section and academic year, and ordering **oldest-first** — which
is the correct order for a journey timeline anyway. The audit's literal grep for `academic_journey`
could not match a camelCase method name.

I initially "fixed" this by adding a second `academicJourney()` relation, which the tests then
revealed was resolving to the *accessor* (ascending results, contrary to the relation's own
`ORDER BY ... DESC` SQL). **That change has been reverted**: the accessor is the single definition,
the view calls `$student->academic_journey` again as it always did, and a guard comment plus a test
now prevent a duplicate accessor/relation pair being reintroduced.

**6. "Attendance `marked_by` writes `users.id` into a staff FK, breaking staff attendance."**
Only `student_attendance` is affected. Checked against `information_schema`:

| Column | FK target | Controller writes | Verdict |
|---|---|---|---|
| `student_attendance.marked_by` | `staff.staff_id` | `auth()->id()` | **BROKEN** |
| `staff_attendance.marked_by` | `users.id` | `Auth::id()` | correct |
| `medical_incidents.marked_by` | *(no FK)* | `Auth::id()` | correct |
| `MobileAttendanceController` | `staff.staff_id` | `$staff?->staff_id` | already correct |
| `MobileStaffAttendanceController` | `users.id` | `$user->id` | already correct |

So three of the four controller paths the audit implied were broken are in fact correct, and the
mobile app was right all along. I nearly "fixed" two working paths.

### The real attendance defect

`StudentAttendanceController::store` wrote `auth()->id()` into a column foreign-keyed to
`staff.staff_id`. Because `users.id` (1-43 live) and `staff.staff_id` (1-25 live) are **separate
sequences that overlap**, this did two different wrong things:

- for a user whose id has **no** matching staff row (id > 25) → FK violation, the register fails;
- for a user whose id **does** collide (id ≤ 25) → passes the FK and **silently attributes the
  register to whichever staff member owns that id**. Live measurement found **5 users whose id maps
  to a different staff member**, e.g. user 7 ("Student Demo") → `staff_id` 7.

Silent misattribution of an attendance register is worse than a crash, and `student_attendance` has
**0 rows** live — it has never worked.

Fixed: `marked_by` is now `auth()->user()?->staff?->staff_id` (null when the marker has no staff
record, which the nullable column permits), and `StudentAttendance::markedBy()` now points at
`Staff` (`staff_id`) instead of `User`.

### Class subject bulk clear deleted across academic years

`ClassSubjectController::bulkDestroy` ran `ClassSubject::where('class_id', $classId)->delete()` with
no year filter, so clearing the current year also destroyed every previous year's record of what was
taught in that class. `class_subjects.academic_year_id` exists, so the clear is now scoped to one
year: the posted `academic_year_id`, falling back to the current academic year, and it refuses to
act when neither is available. The flash message and audit entry now name the year, and the form
posts the group's own `academic_year_id`.

### Exam type edit targeted the wrong record

`resources/views/exam_types/edit.blade.php` built its route from `$examType->id`, but the primary key
is `exam_type_id`, so the update URL carried an empty id. Now uses `exam_type_id`.

### Verification

- `tests/Feature/AttendanceAndAcademicIntegrityTest.php` *(new)* — 8 tests.
- **Falsification: 6 of the 8 fail against the unfixed code.**
- **Explicitly non-discriminating (2):**
  1. `test_academic_journey_accessor_returns_the_enrollment_history` — the accessor always worked, so
     it cannot fail against old code. Kept as behaviour preservation and as documentation that the
     tab was never broken.
  2. `test_the_academic_tab_view_uses_the_existing_accessor` — asserts the *current* (correct) state;
     it also guards against my own duplicate-definition mistake returning.
- The attendance fixture deliberately forces `user_id != staff_id` and asserts they differ, so the
  test cannot pass by coincidence. It also needed a Super Admin marker: a Teacher is scoped to their
  own classes by the controller and would be redirected before any write.

### Regression

```
Tests: 116, Assertions: 466, Failures: 5.
```

All 5 are the known pre-existing menu/navigation failures (`RbacVisibilityTest` ×3,
`StudentClassEnrollmentTest`, `StudentUnassignedTest`), each confirmed pre-existing via a stashed
baseline. No new regressions.

### Standing note on test hygiene learned this batch

Twice now a test of mine has passed for the wrong reason (a duplicate admission number rejected by
validation rather than by the code under test; a journey assertion reading the accessor instead of
the relation). Both were caught only by writing the assertion so that it prints actual values, and
by asking "would this fail on the old code?" before counting it. Continuing to mark non-discriminating
tests explicitly rather than letting them inflate the count.

---

## Migrations A and B — RUN AND VERIFIED

Approved by the user and executed individually via `migrate --path=...` (the two unrelated
pre-existing pending migrations remain untouched).

**A — `2026_09_21_000003_add_unique_indexes_to_students_identity_columns.php`**

```
students_nemis_number_unique on nemis_number : unique=YES
students_upi_number_unique  on upi_number    : unique=YES
```

Pre-flight: 0 duplicate values and 0 empty strings in either column, so no cleaning was needed.
Both columns are nullable varchar(50) and MySQL permits multiple NULLs in a unique index, so
learners without a national identifier are unaffected.

**B — `2026_09_21_000004_add_student_foreign_keys_to_incident_tables.php`**

```
disciplinary_records_student_id_foreign -> students.student_id ON DELETE CASCADE
medical_incidents_student_id_foreign    -> students.student_id ON DELETE CASCADE
emergency_contacts_student_id_foreign   -> students.student_id ON DELETE CASCADE
```

Pre-flight: `students.student_id` is `int` and all three child columns are `int` (exact match —
required by MySQL, this is what failed on the earlier `reversed_by` attempt), no FK already
present, all four tables InnoDB. Behaviour check: an orphan insert was **rejected by the FK**.

Regression after both: **173 tests, 731 assertions, 5 failures** — the same 5 pre-existing
menu/navigation failures. No regressions.

---

## PROPOSAL — awaiting approval (migrations C and D)

### What the investigation changed about this plan

Two findings made this more urgent and slightly different from the original sketch.

**1. A hard delete of a learner would destroy financial records.** There are **18** constraints
referencing `students`:

| ON DELETE | Tables | Effect |
|---|---|---|
| **CASCADE (7)** | `ledger_entries`, `refunds`, `fee_adjustments`, `disciplinary_records`, `emergency_contacts`, `medical_incidents`, `student_siblings` ×2 | Learner deleted → **ledger entries, refunds and fee adjustments are silently erased** |
| **RESTRICT (10)** | `exam_results`, `student_attendance`, `student_class_enrollments`, `student_documents`, `student_parent_relationship`, `transport_registrations`, `assignment_submissions`, `hostel_allocations`, `hostel_fee` | Learner deleted → hard `QueryException` (uncaught → 500) |
| **NO ACTION (1)** | `student_notices` | same as RESTRICT |

So `StudentController::destroy` today either **500s** or **destroys the fee ledger**. Soft deletes
fix both: the row survives, so no cascade fires and no restrict blocks.

**2. `student_attendance` is empty (0 rows).** So the term backfill is a **no-op on this database**
— it is written to be correct for other installs, but it will not touch a single live row.

Supporting facts measured: `students` 40 rows; `student_class_enrollments` 36;
`academic_years` 2 (#2 2026 is current); `terms` 6 rows keyed by `academic_year_id` with
`code`/`start_date`/`end_date`, current active term is #5 (`T2`, 2026-05-04..2026-08-07);
`Staff.php` already uses `SoftDeletes` so there is a house pattern to follow.

Column types that matter: `terms.id` is **bigint unsigned**; `academic_years.academic_year_id` is
**int**. The new columns must match those exactly.

### Migration C — soft deletes on students

`2026_09_21_000005_add_soft_deletes_to_students.php`

- Add `deleted_at` (nullable timestamp) to `students`, plus an index.
- Additive and reversible.

Application changes that go with it (no migration):
- `Student` model: `use SoftDeletes`.
- `StudentController::destroy` becomes a soft delete; add a `restore` action and a "trashed"
  filter on the list so a removed learner can be found and restored.
- Every `Student::` query automatically excludes trashed learners, which is what makes the roster,
  attendance register and fee screens consistent.

**Consequences you should decide on knowingly** (the unique indexes on `students` are
`admission_no`, `user_id`, `nemis_number`, `upi_number`):

- A soft-deleted learner **keeps** their admission number, NEMIS and UPI. Re-admitting the same
  learner therefore means **restoring** the existing record, not creating a new one — the unique
  index will reject a duplicate. I believe that is correct (a re-admitted learner is the same
  person, and restoring preserves their fee ledger and exam history), but it is a workflow change
  worth confirming.
- Historical rows in `exam_results`, `student_attendance`, `ledger_entries` etc. are queried
  directly, not through the student relation, so they **remain** in reports. That preserves
  history deliberately. If you would rather soft-deleted learners vanish from exam and fee
  reports too, that is a separate change and I would want your call on it.

### Migration D — attendance term scoping

`2026_09_21_000006_add_term_scoping_to_student_attendance.php`

- Add `academic_year_id` (**int**, nullable, FK → `academic_years.academic_year_id`).
- Add `term_id` (**unsignedBigInteger**, nullable, FK → `terms.id`).
- Indexes on both, plus a composite index on `(student_id, date)`.
- Add a **unique** index on `(student_id, date)`.

Why the unique index here: web attendance keys an entry on
`(student_id, class_section_id, date)` while the mobile app keys on `(student_id, date)`. The same
learner marked once on each surface produces **two rows for one day**, and a mobile-written row
carries a null `class_section_id` so it also disappears from class-filtered views. A learner can
only have one attendance status per day, so the database should enforce that. Safe to add now
because the table is **empty**; on a populated database this would need duplicate reconciliation
first.

This index must ship **together** with an app change, or it turns today's silent duplicate into a
500: the web path (`StudentAttendanceController`) must key on `(student_id, date)` to match mobile.

**Backfill** (no-op on this database, 0 rows):

```
for each student_attendance row:
    academic_year_id := the enrollment for that student whose academic year contains `date`
                        (else the student's current enrollment's year, else null)
    term_id          := the term of that academic year where
                        start_date <= date <= end_date      (else null)
```

Both columns stay nullable, so no row can fail the backfill and nothing is guessed destructively:
a row the backfill cannot resolve keeps `NULL` rather than being assigned a wrong term. The
backfill runs inside the migration in chunks, so it is safe on a large table.

Not included, deliberately: locking or back-dating rules, and Migration E. Terms already drive fee
due dates (`terms.fee_due_date`), so once `term_id` exists on attendance the same scoping can be
applied to `fee_payments` later — but that is Fee Management's batch, not this one.

### Files this proposal would touch

```
database/migrations/2026_09_21_000005_add_soft_deletes_to_students.php   (new)
database/migrations/2026_09_21_000006_add_term_scoping_to_student_attendance.php   (new)
app/Models/Student.php                       use SoftDeletes
app/Http/Controllers/StudentController.php   soft delete + restore + trashed filter
app/Models/StudentAttendance.php             fillable/casts for the new columns
app/Http/Controllers/StudentAttendanceController.php   key on (student_id, date)
tests/Feature/StudentSoftDeleteTest.php      (new)
tests/Feature/AttendanceTermScopingTest.php  (new)
```

**Awaiting approval before anything is created or run.**

---

## Batch P1-E — Examination statistics honesty (fake data removal)

### Two more false positives to retract

**7. "The entry screen computes the percentage with `MAX(max_marks)` while the model stores the
grade with `MIN(max_marks)`."** Both use the same method. `ExamResult::saving` grades via
`$examResult->getPercentageAttribute()`, and the views read the same accessor, so the displayed and
stored figures cannot diverge that way.

The real divergence is different and was confirmed: `exam_results` has **no `max_marks` column**, so
`getMaxMarksAttribute()` resolves the maximum from `exam_schedules` (keyed by **class** + subject,
`min(max_marks)`), defaulting to 100 when no schedule row exists. Any view computing its own
percentage (or treating a raw mark as a percentage) therefore disagrees with the model. That is the
actual defect, and it is fixed in the places that did it.

**8. "`exam_dashboard` shows an Overall Pass Rate hardcoded to 76.5."** The literal `76.5` is in
`exam_analysis/subject.blade.php`, not `exam_dashboard`. The underlying problem — fabricated
statistics — is real, but the location in the report was wrong.

### Problems fixed

| # | Problem | Root cause |
|---|---|---|
| 1 | `exams/show` displayed **"Highest Score 98.0%"** and **"Lowest Score 12.0%"** for every exam in the system | Two hardcoded `<b>` tags; the controller never computed them |
| 2 | "Mean Score" averaged **raw marks** and printed a `%` sign | `examResults()->avg('marks_obtained')`, ignoring each paper's maximum — meaningless when a paper is out of 50 |
| 3 | Pass rate used `marks_obtained >= 40`, a 40% pass **only** when the paper happens to be out of 100 | Hardcoded threshold; `exam_schedules.passing_marks` already existed and was ignored |
| 4 | Grade badges were **hardcoded per view**: `mark_sheets/index` rendered every grade `badge-danger`, and `exam_results/table` listed a few KCSE letters with everything else red | Three separate hand-written maps; the 8-point CBE codes the app actually stores all fell through to red |
| 5 | `exam_results` percentage accessor fires **one query per row** (plus a class-section lookup) because `max_marks` is not a column | N+1 on the largest live exam (249 results) |

### Implementation notes

- `ExamController::show` now builds `max_marks` and `passing_marks` maps **once** from the exam's
  schedules (which are already eager-loaded), converts the paper's raw pass mark to a percentage, and
  computes the mean, highest, lowest and pass rate on a percentage basis. No per-row queries.
- `passing_marks` is used as the threshold, falling back to 40% only when a paper does not define
  one — so the pass rate finally means the same thing on every paper.
- `exams/show` gained an explicit Pass Rate row and renders `—` instead of inventing a figure when an
  exam has no results.
- All three grade-badge maps now call the shared `App\Support\GradeBadge::for()`.

### Files changed

```
app/Http/Controllers/ExamController.php                  real statistics, no per-row queries
resources/views/exams/show.blade.php                     hardcoded KPIs removed, pass rate added
resources/views/mark_sheets/index.blade.php              shared grade badge
resources/views/exam_results/table.blade.php             shared grade badge
tests/Feature/ExamStatisticsTest.php                     (new)
```

### Verification

- `tests/Feature/ExamStatisticsTest.php` — 4 tests.
- **Falsification: 4 of 4 fail against the unfixed code.** Every test in this batch discriminates;
  nothing needed flagging as non-discriminating.
- The tests assert on the *specific* fabricated strings (`98.0%`, `12.0%`) being absent, so they
  cannot pass merely because the page rendered.
- Full regression: **120 tests, 484 assertions, 5 failures** — the same 5 pre-existing
  menu/navigation failures.

### Still open in this module (next item)

`exam_analysis/performance.blade.php` and `exam_analysis/subject.blade.php` are **entirely mock
data** — 450 students, 78.5% pass rate, 65.2 average, fixed chart arrays — and
`ExamAnalysisController::performance()`/`subject()` pass only `$exams`. They render plausible but
completely fabricated analysis. Per the Phase 2 instruction to remove fake functionality, these need
either real aggregates or removal; that is the next Examination item and is a larger change than the
hardcoded scalars above.

---

## Status

| Batch | State |
|---|---|
| P0-A RBAC edit data loss | complete |
| P0-B record corruption on edit | complete |
| P0-C fee/payment integrity | complete |
| P0-D authorization holes | complete |
| P1-A CBC grading coherence | complete |
| P1-B report card default + marks approval | complete |
| P1-C Student (items 5-7) | complete |
| P1-D attendance attribution, class-subject clearing, exam type edit | complete |
| P1-E examination statistics | complete |
| Migration A (unique NEMIS/UPI) | **run and verified** |
| Migration B (student FKs) | **run and verified** |
| Migration C (soft deletes) / D (attendance term scoping) | **proposed, awaiting approval** |

Retracted false positives to date: **8.** Every one was plausible from static reading and wrong when
checked against the framework source, the live database, or the generated SQL.

---

## Migrations C and D — RUN AND VERIFIED

Approved by the user with all three open decisions confirmed: re-admission **restores** the existing
record; soft-deleted learners **remain** in historical reports but leave the active roster /
attendance register / fee collection; and the unique index ships **with** the app-side key change.
Both were run as separate migrations, individually via `migrate --path=...`.

### Pre-flight checks

| Check | Result |
|---|---|
| `students.deleted_at` already present | **no** |
| live `students` rows | 40 |
| `student_attendance` rows | **0** |
| duplicate `(student_id, date)` pairs the unique index would reject | **0** |
| `academic_years.academic_year_id` type | `int` (PRI) |
| `terms.id` type | `bigint unsigned` (PRI) |
| existing FKs on `student_attendance` | 3 (`student_id`, `class_section_id`, `marked_by`) |
| web attendance key | `(student_id, class_section_id, date)` — confirmed in source |
| mobile attendance key | `(student_id, date)` — confirmed in source |
| terms available for backfill | 6 (T1–T3 for 2025 and 2026, each with date ranges) |

### C — `2026_09_21_000005_add_soft_deletes_to_students.php`

```
deleted_at : timestamp : null=YES
index students_deleted_at_index on deleted_at
live students still present: 40
```

### D — `2026_09_21_000006_add_term_scoping_to_student_attendance.php`

```
academic_year_id : int             : null=YES
term_id          : bigint unsigned : null=YES

student_attendance_student_date_unique on student_id : unique=YES
student_attendance_student_date_unique on date       : unique=YES
student_attendance_academic_year_index on academic_year_id
student_attendance_term_index          on term_id

academic_year_id -> academic_years.academic_year_id
term_id          -> terms.id
```

Backfill ran as three set-based `UPDATE ... JOIN` statements (academic year from the enrollment
covering the date → fall back to the current enrollment → term from the date range). A **no-op on
this database** (0 rows), written for correctness elsewhere. Unresolvable rows keep `NULL` rather
than being assigned a guessed term.

### App changes (landed with D, as required)

| File | Change |
|---|---|
| `app/Models/Student.php` | `use SoftDeletes` |
| `app/Models/StudentAttendance.php` | `academic_year_id`/`term_id` fillable, `academicYear()`/`term()` relations, and `resolvePeriodFor()` — resolves year+term for a date, NULL-safe |
| `app/Http/Controllers/StudentAttendanceController.php` | keys an entry on `(student_id, date)` (was `+ class_section_id`) and populates year/term |
| `app/Http/Controllers/StudentController.php` | `destroy` no longer unlinks the photo (removal is reversible) and is now a soft delete; new `restore()`; `index` gains a `trashed` filter |
| `routes/web.php` | `students/{id}/restore`, registered **before** the resource route |

### Verification

- `tests/Feature/StudentSoftDeleteTest.php` (8 tests) + `tests/Feature/AttendanceTermScopingTest.php`
  (6 tests).
- **Falsification: 12 of the 14 fail against the unfixed code** (7 errors + 5 failures).
- **Explicitly non-discriminating (2):**
  1. `test_the_active_roster_excludes_removed_learners` — passes on the old code for the *wrong*
     reason: the hard delete removed the row entirely, so it was absent from the roster too. It
     still documents the intended behaviour but proves nothing on its own.
  2. `test_the_database_rejects_a_duplicate_learner_and_date` — the unique index comes from the
     untracked migration, which survives a stash, so it passes either way.
- Live data untouched: 40 students still present, all 40 without `deleted_at`.
- **Full regression: 134 tests, 523 assertions, 5 failures** — the same 5 pre-existing
  menu/navigation failures. No regressions.

### Two things worth carrying forward

- The 5 pre-existing failures are all menu/navigational. They are now the largest remaining cluster
  of known-broken behaviour and belong in the Settings/navigation batch.
- `mobile attendance` still writes `academic_year_id`/`term_id` as NULL, since mobile code is not
  being touched. Both columns are nullable, so nothing breaks, but app-recorded attendance will not
  carry term scoping until the app is changed. **No API shape change** — the columns are new and not
  part of any response. Flagged, not implemented, per the standing instruction.

---

## Batch P1-F — exam_analysis mock data removed

### Status: COMPLETE

**On the record:** the user asked whether this was done, in progress, or skipped. It was **not
started**. At the end of the previous turn I described it as "next" in chat but never logged it as an
open item, so the log did not show it. That is exactly the kind of defect riding along as a footnote
and it should not have happened. It is fixed now, and this entry records it as done rather than
pending.

### The defect

`ExamAnalysisController::performance()` and `subject()` passed **only `$exams`**. Every figure on
both screens was hardcoded:

| Screen | Fabricated content |
|---|---|
| performance — KPIs | 450 Total Students, 78.5% Pass Rate, 65.2 Average Score, 12 Subjects Tested |
| performance — subject table | 3 fixed rows: Mathematics 68.5/98/32/75%, English 72.3/95/45/82%, Kiswahili 65.8/92/38/70% |
| performance — charts | trend series `[62, 65, 68, 65.2]` labelled "Term 1..Current"; grade series `[45, 120, 180, 85, 20]` |
| subject — top / weakest lists | English 72.3%, Chemistry 70.5%, Mathematics 68.5% / Physics 58.2%, History 60.1%, Geography 62.3% |
| subject — statistics | 12 subjects, 76.5% average pass rate, 65.2 overall mean |
| subject — table | 3 fixed rows with invented Median/Mode/Std Dev per subject |
| subject — chart | 12 hardcoded subjects with 12 hardcoded averages |

None of it came from the database. `rankings()` had a related defect: it aggregated
`SUM(marks_obtained)` and `AVG(marks_obtained)` and presented them as scores, the same raw-marks
error fixed in `exams/show` in P1-E.

### What replaced it

`ExamAnalysisController::buildAnalysis()` computes real aggregates from `exam_results`, resolving
each paper's maximum from `exam_schedules` (because `exam_results` has no `max_marks` column) and
each pass threshold from that paper's own `passing_marks`:

- **Per subject:** learners assessed, mean / median / mode / standard deviation of the percentage
  scored, highest, lowest, pass rate, and a grade resolved through `GradingScale::resolveForPercentage()`
  with the school's curriculum — so the grade shown here cannot disagree with the grades actually
  stored.
- **Overall:** learners assessed, subjects tested, mean percentage, highest, lowest, pass rate.
- **Grade distribution:** real counts grouped by the stored grade.
- **Trend:** average score for each of the six most recent exams, oldest first. The previous series
  was labelled "Term 1..Current", but exam results carry no term of their own — rather than invent
  term data the chart is now honestly labelled "Average Score by Exam".

Both views now render an explicit empty state ("No marks have been recorded for this exam yet…")
instead of inventing figures when an exam has no results, and the subject view derives its top and
weakest lists from real means.

**No metric was dropped for lack of a data source.** Every column the old tables displayed —
including median, mode and standard deviation — is computed for real. Nothing needed flagging as
uncomputable.

### A bug I introduced and caught before finishing

My first version of the grade-distribution chart put an inline array literal inside a Blade
`@json()` directive, which **does not compile** — it produced a `ParseError` and a 500 on both
screens. The tests caught it immediately (3 of 4 failing with `500 is identical to 200`). The
palette now lives in the controller.

### Files changed

```
app/Http/Controllers/ExamAnalysisController.php        real aggregates for performance + subject
resources/views/exam_analysis/performance.blade.php    was 207 lines of fiction
resources/views/exam_analysis/subject.blade.php        was 206 lines of fiction
tests/Feature/ExamAnalysisTest.php                     (new)
```

### Verification

- `tests/Feature/ExamAnalysisTest.php` — 4 tests.
- **Falsification: 4 of 4 fail against the unfixed code.** Every test discriminates; nothing to flag.
- The tests assert the specific fabricated values (`450`, `78.5`, `65.2`, `76.5`, `12.3`, and the
  hardcoded chart series) are **absent**, so they cannot pass merely because the page rendered.
- One assertion needed scoping: a bare `assertDontSee('History')` matched the application's own
  navigation, so the check now targets the rendered table cell.
- **Full regression: 138 tests, 560 assertions, 5 failures.**

### Remaining in Examination

`rankings()` still aggregates raw `SUM(marks_obtained)` as "total marks" rather than percentage, and
`rankings.blade.php` prints "Passed" on every row. Carried into the next Examination pass rather than
folded in here.

---

## Immediate next batch — the menu/navigation cluster

The 5 remaining failures are the largest outstanding cluster and have now been referenced across
several batches without being fixed. They get their own batch next, **not** another footnote:

```
RbacVisibilityTest::test_teacher_sees_only_academic_sections_with_no_orphaned_headings
RbacVisibilityTest::test_zero_permission_role_sees_only_dashboard_link
RbacVisibilityTest::test_zero_permission_role_gets_minimal_unbroken_dashboard
StudentClassEnrollmentTest::test_class_enrollments_nav_entry_is_restored
StudentUnassignedTest::test_unassigned_nav_entry_is_configured
```

Measured evidence already in hand from earlier batches:

- **Teacher menu**: `Student Management` renders children `['Student Attendance', 'Homework',
  'Student Notices']` but the test expects only `['Student Attendance']`. This looks like a **stale
  test**, not a menu bug — `Homework` and `Student Notices` were added to the menu after the
  assertion was written, and a teacher should see both. Fixing the menu to satisfy the test would
  remove working functionality, so the test is the likely correction.
- **Zero-permission role**: sees the `EDUCATIONAL UNITS` heading with (apparently) no visible child
  beneath it — the orphaned-heading pattern the test name describes. This looks like a **genuine
  bug** in the section-header rendering.
- **Navigation entries**: `student-class-enrollments` and `student-unassigned` are expected to be
  configured in the menu and are not.

I will diagnose all five against `MenuService`/`config/menu.php` and the dashboard view before
changing anything, then split them into "menu is wrong" and "test is stale" with evidence, in the
same style as the eight retracted false positives.

---

## Batch P1-G — the menu/navigation cluster (5 long-standing failures)

### Status: COMPLETE. Suite is green: 141 tests, 607 assertions, 0 failures.

This cluster had been carried across five batches as a footnote. It was diagnosed properly here, and
the five failures split cleanly into **three stale tests** and **two real config omissions**.

### Investigation first — the menu logic is sound

I reproduced the filter directly with a probe rather than reading the tests' intent:

```
== top-level items with an empty/missing permission gate ==
  [1]  key='dashboard'      label='Dashboard'      children=none  owner_only=NULL
  [17] key='administration' label='Administration' children=3     owner_only=true

== a genuinely permission-less role ==
  topHeaders: ["CORE DASHBOARD"]
  topLabels : ["Dashboard"]
```

So `MenuService` already derives heading visibility correctly from children — a role holding nothing
sees only the dashboard, with no orphaned heading. The zero-permission invariant was never broken.

### Finding: the seeder's docblock is stale, not the code

| Role | Docblock claims | Actually seeded |
|---|---|---|
| Parent | `0 (portal, ownership-scoped via Policy)` | **`homework.view`, `student-notices.view`** |
| Student | `0 (portal, ownership-scoped via Policy)` | **`homework.view`, `student-notices.view`** |

`RbacSeeder::ROLE_PERMISSIONS` deliberately grants both (lines 112-119 for Parent, 116-119 for
Student). `homework` and `student-notices` are children of the **Student Management** section, which
sits under the `EDUCATIONAL UNITS` heading — so that heading rendering for a Student is **correct
behaviour**, not a leak. The docblock above it was simply never updated when the grants were added.
**Docblock corrected.**

### The three stale tests

| Test | Why it was stale |
|---|---|
| `test_teacher_sees_only_academic_sections_with_no_orphaned_headings` | Expected `students` children to be only `['Student Attendance']`. Teacher holds `homework.view` and `student-notices.view`, so `Homework` and `Student Notices` legitimately appear. Expectation predated both menu entries. |
| `test_zero_permission_role_sees_only_dashboard_link` | Named a role "zero permission" that has two permissions. Renamed to `test_portal_role_sees_only_what_its_permissions_unlock`, now asserting the real set **and** that every money/staff/admin section stays hidden. |
| `test_zero_permission_role_gets_minimal_unbroken_dashboard` | Asserted `EDUCATIONAL UNITS` was absent from the Student dashboard. It belongs there. All sensitive-widget assertions kept; the heading assertion inverted. |

**No production behaviour was changed for these three.** The defect was in the expectations, so
correcting the tests was the fix — and importantly, changing the *menu* to satisfy them would have
**removed working functionality** (a teacher's access to Homework and Student Notices).

To keep the original intent covered rather than dropped, the zero-permission invariant now has its own
test against a role that genuinely holds nothing:

```php
test_permission_less_role_sees_only_the_dashboard()
```

### The two real defects — config omissions

Both routes work and both patterns were still listed in the section's own `active` array (line 147),
so the entries were intended to exist and had been deleted:

```php
// was:  // Unassigned Students removed — not in use
['key' => 'student-enrollments', 'label' => 'Class Enrollments',    'route' => 'student-class-enrollments.index', 'permission' => ['students.view', 'students.manage']],
['key' => 'student-unassigned',  'label' => 'Unassigned Students',  'route' => 'student-unassigned.index',       'permission' => ['students.manage']],
```

Two working features — class enrollments and the unassigned-students worklist — were unreachable
from the navigation. The `active` patterns remaining in the same array are the giveaway that this was
an accidental deletion rather than a decision; a comment claiming "not in use" contradicted a route
that still resolves and a test that still asserts the entry should be configured.

**Reversible by deleting those two array entries** if the school genuinely does not want them.

### Files changed

```
config/menu.php                       two child entries restored
database/seeders/RbacSeeder.php       stale docblock corrected (Parent/Student permission counts)
tests/Feature/RbacVisibilityTest.php  three stale expectations corrected, 1 new invariant test
```

Note: `php artisan config:clear` was needed for the menu change to take effect — a cached config would
mask it. Worth remembering on deploy.

### Verification

- `RbacVisibilityTest` + `StudentClassEnrollmentTest` + `StudentUnassignedTest`: **28 tests, 188
  assertions, all passing**.
- The 2 config tests genuinely discriminate: they were failing before the change and pass after.
- The 3 visibility tests are **not discriminating in the usual sense** — production code did not
  change, so "fails on unfixed code" does not apply. Their evidence is the seeded permissions and the
  permission-less probe output quoted above, not a red/green transition.
- **Full regression: 141 tests, 607 assertions, 0 failures.** The suite is green for the first time
  this session.

---

## Remaining open items, tracked explicitly (not footnotes)

| Module | Item |
|---|---|
| Examination | `rankings()` still aggregates raw `SUM(marks_obtained)` as "total marks" and `rankings.blade.php` prints "Passed" on every row |
| Fee | Hardcoded "Term 1/2/3" select on assignments create (free strings → `term_id` resolved by code, can silently store NULL); void markers still missing on receipt register / student fees tab / portal receipt; discount-scheme create/edit field drift; method filter offers values the enum cannot match; pagination drops filters on ~11 views |
| Academic | `assessment_types` views reference routes that are not registered; learning-area level vocabulary split; `class_subjects` create/edit are different UIs; period `type='break'` not filtered by the timetable generator; destructive timetable regeneration |
| Student | Transport/hostel switches on the admission form are inert (no route/stop/room fields submitted); parents' relationship dropdown stores array indexes; family tab null-unsafe sibling chain; disciplinary status enum mismatch between form and profile |
| Attendance | Three different attendance-percentage rules; hardcoded report year range; null-unsafe class labels; portal attendance issues |
| Financial | Dead report links (`href="#"`); `expense_categories/edit` uses a plural variable the controller never passes; `bank_accounts` route-name mismatch; mocked reconciliation; missing `financial_years` edit view; `income.show` route absent |
| Communication | Template tokens sent literally; bulk path writes no delivery log; provider secret overwritten with its own mask |
| Security | Buttons rendered to roles that 403 across Communication/Settings/Fee/HR |
| Mobile | Mobile attendance writes `academic_year_id`/`term_id` as NULL until the app changes (no API shape change) |

---

## Batch P1-H — Fee assignment term scoping (real data-integrity bug)

### Status: COMPLETE

### The defect

`fee_management/assignments/create.blade.php` hardcoded:

```html
<option value="Term 1">Term 1</option>
<option value="Term 2">Term 2</option>
<option value="Term 3">Term 3</option>
```

`StudentFeeAssignmentController::assignFeesToStudents()` resolves the term with:

```php
$termId = Term::where('academic_year_id', $academicYearId)->where('code', $term)->value('id');
```

and `terms.code` holds **`T1` / `T2` / `T3`** while `terms.name` holds `Term 1` / `Term 2` / `Term 3`.
The lookup therefore **never matched**. Confirmed against the live database:

```
Term::where('code', 'Term 1') => NULL
Term::where('code', 'T1')     => 1
```

Consequences for every assignment created through this form:

- `term_id` was written **NULL**, which silently removes the row from every term-filtered arrears
  view, statement and report.
- `term` was written as the literal `'Term 1'` while every other row in the same column holds a code
  (`'T2'`), so term-based grouping split into two inconsistent populations.

### Why live data looked clean — and why that mattered

```
student_fee_assignments grouped by (term, term_id):
  term='T2'  term_id=5  count=263
  rows with NULL term_id: 0 of 263
```

All 263 rows carry a properly resolved term. The reason is `StudentClassEnrollmentObserver`, which
calls `autoAssignFeesToStudent()` whenever an enrollment is created — a path that resolves the term
**correctly** from codes. So the auto-assigner produced every existing row, and the broken form had
simply never been used in production. A naive reading of the data would have concluded "no problem
here"; the defect was only visible by comparing the form's values against `terms.code`.

### Problems fixed

| # | Problem | Fix |
|---|---|---|
| 1 | Term dropdown hardcoded three label strings | Populated from `$terms`; the option **value** is `code`, the label is `name`. The controller already loaded and passed `$terms` — the view simply never used it |
| 2 | `term` validated only as `required` (any string accepted) | Validated with `in:` against the codes that actually exist for the submitted academic year |
| 3 | A term that resolves to nothing wrote `term_id = NULL` silently | `assignFeesToStudents()` now throws rather than writing an unscoped fee record (defence in depth behind the validation) |
| 4 | No terms at all for a year produced the same silent NULL | Explicit error and redirect instead of proceeding |
| 5 | The active term was not pre-selected | `old('term')` then the term with `status = 'active'`, then the first available |

### Files changed

```
app/Http/Controllers/StudentFeeAssignmentController.php   term validated against real codes; resolver guard
resources/views/fee_management/assignments/create.blade.php   dropdown populated from $terms
tests/Feature/FeeTermAssignmentTest.php                   (new)
```

### Verification

- `tests/Feature/FeeTermAssignmentTest.php` — 5 tests.
- **Falsification: 3 of 5 fail against the unfixed code.**
- **Explicitly non-discriminating (2):**
  1. `test_assigning_a_fee_stores_a_resolved_term_id` — posts a **valid** code (`T2`). The old resolver
     also matched valid codes; only the *form's* values were wrong, so this passes either way. Kept
     because it still proves a submitted code round-trips to the right `term_id`.
  2. `test_auto_assignment_also_resolves_a_term` — the observer path was always correct. Included
     because it documents *why* the live data is clean, which is the evidence that made this bug hard
     to see.
- The two rejection tests assert the **delta** in row count, not the absolute count: the enrollment
  observer auto-assigns a row during `setUp()`, so an absolute assertion would have passed for the
  wrong reason.
- **Full regression: 144 tests, 612 assertions, 0 failures.** Suite still green.

### Still open in Fee Management

- Void markers still missing on `reports/receipt_register`, `students/tabs/fees` and
  `portal/fee-receipt` (only the fee detail page has one).
- Discount-scheme create/edit field drift (5 fields set on create cannot be viewed or edited).
- `reports/collections` method filter offers `mpesa`/`cheque`/`other`, none of which exist in the
  payment-method enum, and the "By Method (Filtered)" table ignores the filter.
- Pagination drops filters/search on roughly eleven list views across nine modules.
- Currency inconsistency (`KES` vs `KSh`) and `number_format(..., 0)` on money in six fee views.

---

## Batch P1-I — reversal exclusion across money aggregates, and a payment-method data-loss finding

### Status: COMPLETE (focused suite green; full-suite figure recorded in the batch that follows)

### Finding 1 — `notReversed()` was documented as mandatory and called by nobody

`FeePayment::scopeNotReversed()` carries this docblock:

> Every balance, collection total and report figure must apply this scope.

It had **zero callers** in application code. Consequence: every money aggregate in the web app
counted voided payments as collected money. A reversed payment is one that was recorded and then
undone — so any total that includes it overstates income, and any arrears figure derived from it
understates what a family owes.

Applied at every web money-aggregate site:

```
app/Http/Controllers/FeeArrearsController.php
app/Http/Controllers/FinanceDashboardController.php      (3 sites)
app/Http/Controllers/FinancialReportController.php       (2 sites)
app/Http/Controllers/FeeReportsController.php            (4 sites)
app/Http/Controllers/DashboardController.php
app/Services/DashboardWidgetService.php
```

The scope was also made to **qualify its column**. Unqualified it is ambiguous against
`student_fee_assignments`, which several report queries join — so applying the scope naively would
have thrown `SQLSTATE[23000]: Column 'reversed_at' in where clause is ambiguous` on exactly the
reports that needed it most. Covered by
`test_the_scope_is_safe_to_apply_to_joined_queries`.

### Finding 2 — "By Method (Filtered)" ignored the method filter

`FeeReportsController::collections()` built the by-method breakdown from a query carrying only the
**date** filters while the heading asserted "(Filtered)". Filtering to `online` still showed the cash
row, so the table contradicted the filter above it. The breakdown now shares the same filtered query
as the rest of the report.

### Finding 3 — Collections totals included voided money, silently

`$totalCollected` and the daily series summed every payment, reversed or not. Now excluded — **with a
disclosure banner** showing the excluded count and amount. Excluding money quietly would be its own
defect: a bursar reconciling against a bank statement needs to see that N payments were taken out,
not just a smaller number.

### Finding 4 — void markers missing on payment surfaces

Only the fee detail page marked a reversed payment. Added to the receipt register, the student fees
tab and the portal receipt. Portal decision recorded under "Product forks" below.

### Finding 5 — the method filter offered values the ENUM cannot hold

`reports/collections` listed `mpesa`, `cheque` and `other`. The column is:

```
enum('cash','check','card','bank_transfer','online')
```

None of the three were members, so each matched **zero rows** — while `check` and `online`, which do
exist, could not be filtered at all. The dropdown is now driven by
`FeePayment::PAYMENT_METHODS = ['cash', 'check', 'card', 'bank_transfer', 'online']`, which matches
the ENUM exactly. A "Unspecified" group is offered for the empty value (Finding 6).

### Finding 6 — DATA LOSS: the original payment methods of 209 rows are unrecoverable

All 209 live `fee_payments` rows have `payment_method = ''`:

```
'' => 209        (no other distinct value exists)
created_at = 2026-09-06, payment_date = 2026-08-30, all 209 rows
```

**Mechanism, verified directly.** Inserting an invalid ENUM member with strict mode disabled stores
the empty string error value:

```
insert 'M-Pesa' with sql_mode=''  =>  stored as ''
```

with `sql_mode` otherwise `STRICT_TRANS_TABLES,...`. So the 2026-09-06 import ran with strict mode
off and supplied method values that were not valid ENUM members (a natural spelling like `M-Pesa` or
`MPESA` would do it). MySQL **silently** substituted `''`; no error was raised, and the import
appeared to succeed.

**The original values cannot be recovered from this database.** Checked and ruled out:

| Possible source | Result |
|---|---|
| `receipt_number` | Pattern is `RCP-<assignment>-<amount>` — no method component |
| `transaction_id` / `client_reference` | Empty on these rows |
| Any other table with a method column | Only `fee_payments` has one |
| A staging/import table | None exists |

**Therefore no method values were fabricated.** The rows render as "Unspecified" and are selectable
as a filter group. Recovering the real split requires the **source file the import came from** — the
operator needs to re-supply it. Flagged as an open item rather than papered over.

Every `fee_payments` method surface now labels the empty group instead of rendering a blank cell or a
misleading card icon: `reports/collections`, `reports/payment_method`, `payments/show`,
`payments/reverse_payment`, `payments/fee-detail` (used by `portal/fee-detail`). Note
`$payment->payment_method ?? 'N/A'` does **not** catch this case — `''` is not null, so the
null-coalesce renders blank.

### Product fork — recorded rather than decided silently

**Should a voided payment appear on the parent portal?** The data could not settle it, so the choice
is stated explicitly: **visible, marked VOID**. A parent whose statement silently loses a line cannot
reconcile it against their own records, and hiding the row would make the portal disagree with the
receipt they were given. This matches the existing pattern on the fee detail page. One-line change to
reverse if the school prefers concealment.

### Flagged, not fixed — mobile

`AdminMobileHomeService` (mobile dashboard) has the same missing exclusion. **Mobile code untouched**
per standing rule. This is a *data-correctness* difference, **not** an API-shape change: field names
and types are identical, only the mobile totals would stop including voided payments. The app writes
`academic_year_id`/`term_id` as NULL for attendance until it is updated, which is the pre-existing
mobile gap already logged.

### Files changed

```
app/Models/FeePayment.php                                  notReversed() qualifies its column
app/Http/Controllers/FeeArrearsController.php              scope applied
app/Http/Controllers/FinanceDashboardController.php        scope applied (3 sites)
app/Http/Controllers/FinancialReportController.php         scope applied (2 sites)
app/Http/Controllers/FeeReportsController.php              scope applied (4 sites); breakdown honours method filter; voided excluded with disclosure
app/Http/Controllers/DashboardController.php               scope applied
app/Services/DashboardWidgetService.php                    scope applied
resources/views/fee_management/reports/collections.blade.php      real ENUM options; Unspecified group; void markers
resources/views/fee_management/reports/payment_method.blade.php   Unspecified label
resources/views/fee_management/reports/receipt_register.blade.php void marker (already had label fallback)
resources/views/fee_management/show.blade.php              neutral icon + label for empty method
resources/views/fee_management/reverse_payment.blade.php   label fallback
resources/views/portal/fee-detail.blade.php                label fallback
resources/views/students/tabs/fees.blade.php               void marker (already had label fallback)
resources/views/portal/fee-receipt.blade.php               void marker (already had label fallback)
tests/Feature/FeeReversalReportingTest.php                 (new)
```

### Verification

- `tests/Feature/FeeReversalReportingTest.php` — **11 tests, 38 assertions, all passing.**
- Falsification: this batch's assertions are red against the unfixed code by construction (the scope
  had no callers and the breakdown ignored the filter), and each was confirmed failing before the
  corresponding fix was accepted.
- One assertion I wrote and then corrected: `assertDontSee('>Cash<')` also matched the filter
  dropdown's own `<option>Cash</option>`, so it now targets the breakdown table cell
  (`font-semibold">Cash</td>`). Recorded because it would otherwise have been a false pass.
- The legacy-insert test cannot insert `''` under the default strict mode, so it relaxes
  `sql_mode` for that one insert and restores it in a `finally`, then asserts the stored value really
  is `''` — i.e. it reproduces the import mechanism rather than asserting a fiction.

### Open items from this batch

1. **Requires operator input:** the source file for the 2026-09-06 payment import, to restore the 209
   lost payment methods.
2. Mobile dashboard voided-payment exclusion — needs explicit authorisation to touch mobile.

### Still open in Fee Management

Discount-scheme create/edit field drift; pagination dropping filters on roughly eleven list views;
`KES` vs `KSh` inconsistency and `number_format(..., 0)` on money in six fee views.

---

## Batch P1-J — mobile void exclusion, a fee-cache bug I had introduced, and discount-scheme form parity

### Status: COMPLETE (full-suite confirmation recorded below)

### Authorised change — mobile voided-payment exclusion

Per explicit authorisation, `AdminMobileHomeService` now applies the reversal exclusion that the web
aggregates got in P1-I. Four sites:

```php
$collectedToday = FeePayment::whereDate('payment_date', $date)->notReversed()->sum('amount');
$paymentsToday  = FeePayment::whereDate('payment_date', $date)->notReversed()->count();
$byMethod       = FeePayment::whereDate('payment_date', $date)->notReversed()...
```

plus the raw arrears subquery, which bypasses Eloquent scopes and therefore needed the clause written
out:

```sql
FROM fee_payments fp
WHERE fp.reversed_at IS NULL
GROUP BY fp.student_fee_assignment_id
```

Including voided payments there inflated `paid_total`, which **under-counted** `students_in_arrears`.

**No API shape change** — field names and types are identical, only the values change. Verified by
`test_finance_today_excludes_reversed_payments`, which also asserts the JSON structure is unchanged.

Line 207 (`StudentFeeAssignment::sum(paid_amount)`) was deliberately left alone: `paid_amount` is
maintained by `FeeBalanceService`, and I confirmed by reading the code that both
`paidForAssignments()` and `totalCollected()` exclude reversed payments, so the denormalised column is
already correct. Changing it would have been a redundant second fix.

### A bug I had introduced — `Student::refresh()` returned stale money

Running the **whole** suite (363 tests) rather than my usual subset surfaced
`FeeCalculationTest::test_fee_calculations` failing with `0.0 matches expected 400`. That test is not
in my regression subset, so it had been passing unnoticed as a result of my own P1 change.

Cause: `Student::$feeSummaryCache` is an ordinary property, and Eloquent's `refresh()` re-reads the
row while leaving ordinary properties untouched. So this returned the **pre-payment** figure:

```php
$student->refresh();
$student->paid_fee;   // still the old value
```

Every controller that records a payment, refreshes the model and re-renders a fee figure was reading
stale money. `forgetFeeSummary()` existed but had to be remembered by every caller.

Fixed by overriding `refresh()` to drop the cache. Verified in isolation: neutering that single method
reproduces the exact failure (`0.0 matches expected 400`), and restoring it clears it. Note that
stashing all of `Student.php` does **not** isolate this — that reverts the whole accessor architecture
and the test passes for an unrelated reason, so the isolation had to be done method-by-method.

### My term-validation change broke two existing tests — resolved in favour of the code

`FeeAssignmentBulkRegressionTest` posted `'term' => 'Term 1'` and asserted the stored row had
`'term' => 'Term 1'`. That is the **broken contract** from P1-H: the label string that could never
resolve a `term_id`. Its fixture also created **no `terms` rows at all**, which the old code tolerated
only by silently writing `term_id = NULL`.

So the tests were codifying the bug rather than catching it. Updated:
`Term` with `code = 'T1'` added to the fixture, both requests post `'T1'`, and the assertion now
expects `'term' => 'T1'`. Recorded explicitly because "the fix broke a passing test" deserves the
reasoning, not just the edit.

### Discount-scheme create/edit drift — the five fields

`create.blade.php` rendered five fields `edit.blade.php` did not:

```
academic_year_id, valid_from, valid_to, requires_approval, auto_apply
```

The **show** page displays all five, and the controller's `edit()` already loaded and passed
`$academicYears` — the view simply never used it. So a scheme could be created with a validity window
and an approval requirement that could be *seen* on the detail page and never *changed*.

Fix: the five fields moved into `discount_schemes/fields.blade.php`, which both forms include, and
were removed from `create.blade.php`. Parity is now structural rather than coincidental.

### Second, sharper defect: a flag could be turned on and never off

An unticked checkbox is absent from the request, and both `store()` and `update()` used
`$request->all()`. So an unticked box left the previous value in place — `requires_approval` and
`auto_apply` could be enabled and never disabled through the UI.

Fixed with a `formInput()` helper on the controller applying `$request->boolean(...)`. The form posts
every field, so an absent flag means false. Done controller-side rather than with a hidden-input pair
because the hidden/checkbox combination interacts with Laravel Collective's model binding, and the
controller is the single place the semantics belong.

### Files changed

```
app/Services/AdminMobileHomeService.php                    notReversed() x3 + raw subquery clause
app/Models/Student.php                                     refresh() drops the fee-summary cache
app/Http/Controllers/DiscountSchemeController.php          formInput() normalises checkbox flags
resources/views/discount_schemes/fields.blade.php          five shared fields added
resources/views/discount_schemes/create.blade.php          five duplicate fields removed
tests/Feature/FeeAssignmentBulkRegressionTest.php          fixture gains a Term; posts/asserts 'T1'
tests/Feature/MobileAdminDashboardTest.php                 new voided-exclusion test
tests/Feature/DiscountSchemeFormParityTest.php             (new)
```

### Verification

- **Falsification, mobile:** with `AdminMobileHomeService` stashed, the new test fails with
  `Failed asserting that 4000 is identical to 0` — the voided payment counted as collected money. It
  discriminates.
- **Falsification, refresh cache:** method-level isolation as described above.
- **Falsification, discount parity:** the parity assertion is red before the partial is shared, since
  `edit` rendered none of the five; the flag tests are red before `formInput()`, since an absent
  checkbox left the stored `true` in place.
- `FeeCalculationTest | FeeAssignmentBulkRegressionTest | MobileAdminDashboardTest |
  FeeTermAssignmentTest | FeeReversalReportingTest`: **29 tests, 152 assertions, all passing.**

### Pre-existing failures, established against a stashed baseline (not mine)

Running the whole suite revealed 6 problems. I stashed all 95 modified files and re-ran those classes
against pristine code. **Three fail on untouched code:**

```
Tests\Feature\TimetableAjaxSmokeTest::test_get_class_sections_by_year_returns_seeded_sections   (error)
Tests\Unit\TimetableGeneratorServiceTest::test_reports_missing_teacher_assignment               (failure)
Tests\Feature\TeacherManagementTest::test_disabling_module_hides_sidebar_child_and_blocks_routes (failure)
```

The other three were mine and are fixed above. Worth noting the lesson: **my regression subset was
smaller than the suite**, which is exactly how the `refresh()` bug survived several batches. Full-suite
runs are now the standard for batch verification, not the subset.

### Still open in Fee Management

Pagination dropping filters/search on roughly eleven list views across nine modules; `KES` vs `KSh`
inconsistency and `number_format(..., 0)` on money in six fee views.

---

## Batch P1-K — the three pre-existing failures, and a retraction

### Status: COMPLETE — full suite green

**`php vendor/bin/phpunit` → OK (369 tests, 1732 assertions, 0 failures, 0 errors).**

The suite was never green while these three pre-existing failures were carried. Every failure is now
resolved: the three that were mine, and the three that predated this work.

Running the whole suite left exactly the three problems previously established as pre-existing against a
stashed baseline. All three are now fixed, so the suite is clean. This batch also produced a **retraction
of a finding I got wrong in P1-I**, which is the most important entry here.

---

### RETRACTION — the 209 lost payment methods ARE recoverable, and there is no import file to chase

In P1-I I reported these as unrecoverable data loss and said recovering them needed "the source file the
import came from". **That was wrong.** There was no external import. The source is this repository's own
`FeeSeeder`, and the original value is `mpesa`.

The chain, all verified against live data:

| Evidence | Result |
|---|---|
| `FeeSeeder::recordPayment()` line 196 | `'payment_method' => 'mpesa'` — **not** a member of `enum('cash','check','card','bank_transfer','online')` |
| `DatabaseSeeder::run()` line 21 | `SET SESSION sql_mode=""` — so MySQL stored the ENUM error value instead of raising |
| `FeeSeeder` line 195 | `'payment_date' => now()->subDays(7)`; seeded 2026-09-06 → **2026-08-30**, matching all 209 rows |
| `FeeSeeder` line 198 | `'RCP-' . strtoupper(dechex($id)) . '-' . str_pad($amount*100, 8, '0')` — matches every observed receipt (`RCP-1-015000`, `RCP-A-001800`) |
| Live `transaction_id` | All 209 begin `MP` — FeeSeeder's literal prefix |
| Live `remarks` | 152 of 209 contain "M-PESA"; the rest are FeeSeeder's "Partial fee payment." |
| `payment_allocations` | **0** of the 209 have an allocation row → they were inserted directly, not through the payment service |
| `receipt_number` like `RCP-%` | 209 of 209 |

**Why my P1-I conclusion was wrong.** I checked for a *staging table*, a *transaction reference* and
another *table with a method column*, and concluded the value was gone. I did not consider that the
application's own seeder was the producer — even though I had just read the `sql_mode=""` line that
makes the silent coercion possible. I looked for an external import because the data looked imported,
rather than tracing the fingerprints that were already in front of me. The lesson is recorded here
deliberately: **"no other table has this column" is not the same as "this value cannot be recovered" —
check the code that writes the column, not only the data.**

The seeder is now fixed to write `'online'` (M-PESA is mobile money; the collections report already
documents that mapping), so future seeds are correct.

**The 209 existing rows are deliberately NOT touched.** Repairing them is a bulk UPDATE on the payments
ledger, which is a gated category, and the standing instruction was to leave them. The values are now
known — all 209 were seeded as M-PESA and should become `online` — so the repair is available and
evidence-backed whenever it is authorised. Until then they continue to render as "Unspecified".

---

### Pre-existing failure 1 — `StaffSeeder` cannot run on a fresh database

`TimetableAjaxSmokeTest` was erroring with `Field 'current_address' doesn't have a default value`. The
probe showed three required columns the seeder never supplied:

```
== staff columns that are NOT NULL with no default ==
  first_name, last_name, date_of_birth, gender, date_of_joining,
  work_email, phone_primary, current_address, city, country, staff_type

== required but NOT supplied by StaffSeeder ==
  current_address, city, country
```

`2026_04_30_141148_make_staff_fields_nullable` relaxed only `employee_number` and `basic_salary`, so
these three are NOT NULL by design. **`php artisan db:seed` aborted on a fresh install**, which is why
this failed: it is an application defect, not a test artefact.

Fixed at the loop rather than across 24 rows, with `country => 'Kenya'` set rather than blank because
every record in the file is Kenyan (counties, KRA/NHIF/NSSF numbers, `.ac.ke` addresses).

Note the full `DatabaseSeeder` **masked** this: it sets `sql_mode=""`, so the missing NOT NULL columns
took implicit empty defaults instead of failing. Only a seeder run under strict mode — as the smoke test
does — exposes it.

### Pre-existing failure 2 — the seeders were not idempotent, contrary to their own documentation

`DatabaseSeeder`'s docblock promises "a reseed is always safe and idempotent". A second seed crashed:

```
SQLSTATE[23000]: Duplicate entry '31-monday-5-4' for key 'timetable.uniq_teacher_slot'
```

`TimetableSeeder` guarded slots with in-memory arrays that start **empty** on every run, and never
consulted existing rows. So a reseed re-inserted occupied slots against the table's three composite
uniques. Fixed by seeding the guard arrays from the rows already stored for the year and adding the
missing `class_section` guard — so a reseed now **converges** (fills gaps) rather than colliding.

### Pre-existing failure 3 — the generator scheduled teachers to sections they were not assigned to

`test_reports_missing_teacher_assignment` expected English for section 2 to be reported unplaced after
its teacher was removed; it was silently placed instead. Cause, at `TimetableGeneratorService`:

```php
// First try exact class_section_id match. If none found, fall back
// to any section of the same class_id (subjects are class-level,
// not section-level).
```

The fallback's stated premise is false for this schema. `teacher_subjects.class_section_id` exists, and
the assignment form labels it **"Target Class & Section"** and marks it **required**. So the fallback
matched a teacher assigned to a *different* section of the same class and scheduled them anyway — and
in doing so it silently papered over assignment gaps instead of reporting them.

**Behaviour change, recorded explicitly because it affects timetable generation:** the fallback is
removed, so only teachers assigned to that specific section are eligible. Schools with incomplete
assignment data will now see those lessons reported as unplaced rather than filled by an unassigned
teacher. That is the honest direction — a blank is inspectable, a wrongly-assigned teacher is not — and
it is a six-line revert restoring the fallback if the school prefers silent substitution.

The `teacher_subjects` seeder assigns per class section, so the seeded dataset is consistent with this.

### Two stale expectations corrected (no production change)

| Test | Was | Why |
|---|---|---|
| `TimetableAjaxSmokeTest::test_get_class_sections_by_year_returns_seeded_sections` | `assertJsonCount(6)` | The seeder defines 14 classes (PP1, PP2, Grades 1-12) × 2 sections (A, B) = **28**, all current-year. The endpoint filters by year correctly, so 28 is right. Now derived from the database with an added label assertion, so it cannot go stale the same way again |
| `TeacherManagementTest::..._hidden_and_blocks_routes` | required a `Teacher Onboarding` sidebar child | `config/menu.php` records the removal with a reason ("covered by Teacher Management Add button"), and the Teacher Management index links to `teacher-onboarding.create`. The feature is reachable; only the nav item is gone. Assertion inverted, and a new assertion requires that link to be present so reachability is still verified |

The second is a genuine *stale test*, not a menu bug — unlike the `student-class-enrollments` /
`student-unassigned` case in P1-G, which I restored, because there the removal had no rationale and no
alternative path to the feature existed.

---

### Files changed

```
database/seeders/StaffSeeder.php                    supply current_address, city, country
database/seeders/FeeSeeder.php                      'mpesa' -> 'online' (valid ENUM member)
database/seeders/TimetableSeeder.php                converge on reseed; guard all three uniques
app/Services/TimetableGeneratorService.php          remove cross-section teacher fallback
tests/Feature/DatabaseSeedIntegrityTest.php         (new)
tests/Feature/TimetableAjaxSmokeTest.php            derived count + label assertion
tests/Feature/TeacherManagementTest.php             stale nav expectation corrected
```

### Verification

- **Falsification, seeders:** with `StaffSeeder`, `FeeSeeder` and `TimetableSeeder` stashed, both new
  tests fail — the idempotency test with `Duplicate entry '31-monday-5-4' for key
  'timetable.uniq_teacher_slot'`, and the integrity test on the invalid-method array. Both discriminate.
- The staff defect is proven by `TimetableAjaxSmokeTest` (strict mode) rather than the full seed, which
  masks it via `sql_mode=""` — worth remembering when diagnosing seed failures.
- **Falsification, generator:** `TimetableGeneratorServiceTest` was failing before the fallback removal
  and passes after; it is the pre-existing red test that named the defect.
- `DatabaseSeedIntegrityTest`: 2 tests, 6 assertions, passing.
- `TimetableGeneratorServiceTest`: 7 tests, 72 assertions, passing.
- `TimetableAjaxSmokeTest`: 1 test, 7 assertions, passing.
- `TeacherManagementTest`: 5 tests, 37 assertions, passing.

### New guards added

`DatabaseSeedIntegrityTest` runs the **real** `DatabaseSeeder` and then asserts (a) the seed produced
rows, (b) no staff row has a NULL `current_address`/`city`/`country`, (c) every `payment_method` is a
member of `FeePayment::PAYMENT_METHODS`, and (d) a second seed does not duplicate rows. Neither seeder
defect was detectable by reading the seeders — both needed the code to be executed and the result
inspected.

---

### Open decision for the operator

- **209 seeded payments** have `payment_method = ''`. Proven origin and target: all are M-PESA from
  `FeeSeeder`, so the repair is `'' -> 'online'` on exactly those 209 rows. Gated (payments ledger) and
  left untouched. One statement once authorised.

---

## Batch P1-L — verification addendum for the discount-scheme parity work

### Status: COMPLETE

`DiscountSchemeFormParityTest` was written in P1-J but not executed until the suite was free, because
running tests concurrently against the shared `school_erp_test` database corrupts results. On its first
run **two of its own four tests failed**, both from faults in the test rather than the application.
Recorded because they are exactly the kind of assertion that produces false signal:

| Fault in my test | Fix |
|---|---|
| The "parity in both directions" check flagged `_method` as a field the edit form renders but create does not. `Form::model([... 'method' => 'patch'])` emits `_method`; create uses `Form::open` and does not. That is a framework artefact of using PATCH, not field drift | `_method` and `_token` excluded from the compared field set |
| The checkbox assertions used `/name="requires_approval"[^>]*checked/`, which assumes `checked` is emitted **after** `name`. Laravel Collective's attribute order does not guarantee that, so the test failed even though the box was correctly ticked | New `inputTagFor()` helper extracts the whole `<input …>` tag by name and asserts `checked` appears anywhere in it — order-independent |

### Verification

- **Falsification: 3 of the 4 tests fail against the unfixed form.** With
  `fields.blade.php`, `create.blade.php` and `DiscountSchemeController` stashed:
  `test_edit_form_renders_the_same_fields_as_create`, `test_edit_form_shows_the_stored_values_for_those_fields`
  and `test_a_flag_can_be_turned_off` are all red.
- **Explicitly non-discriminating (1):** `test_a_flag_can_be_turned_on`. A *present* checkbox was always
  handled correctly by `$request->all()`, so this passed before the fix too. It only guards against a
  future regression in the opposite direction, and is flagged here rather than counted as evidence for
  the fix.
- Final: `DiscountSchemeFormParityTest` — 4 tests, 31 assertions, passing.

---

## Batch P1-M — filters dropped by pagination

### Status: COMPLETE — full suite green

**`php vendor/bin/phpunit` → OK (371 tests, 1745 assertions, 0 failures, 0 errors).**

### The defect

List screens built the paginator from a filtered query but never told it to carry the query string, so
page 2 was generated from the route alone and **every filter was silently discarded** — a user who
filtered to one class and clicked "next" saw the whole school. The failure is invisible on page 1,
which is why it survived.

### Counting it correctly took three attempts, and two of my own scans were wrong

This is the important part of this entry. My first two numbers were both wrong, in opposite directions.

**Attempt 1 — 92 of 101.** Every `->paginate()` in `app/` without a `withQueryString()` in the same
statement. Useless: most lists have no filters, so there is nothing to lose, and several use
`->appends($request->query())` which is the equivalent call under a different name.

**Attempt 2 — 32, then 27.** Added "reads filter input" and accepted `appends()`.
`ClassTeacherController` was flagged and is **not** a defect — it calls
`->paginate(15)->appends($request->query())`. Retracted.

**Attempt 3 — 14, not 26.** I then applied the change to 26 controllers and wrote a probe to confirm the
split between real fixes and ones the view already handled. That probe returned "26 genuine, 0
redundant" — and I knew it was wrong, because I had already read
`books/index.blade.php:129` calling `$books->appends(request()->query())->links()`. The bug was mine:

```php
$rel = str_replace(__DIR__ . '/resources/views/', '', $f->getPathname());
$rel = str_replace('\\', '/', $rel);
```

On Windows `getPathname()` uses backslashes, so stripping a forward-slash prefix matched nothing. Every
view-cache key became an absolute path, every lookup missed, and **everything** was classified as a
genuine fix. Normalising before stripping produced the real answer. Lesson recorded: a probe that
agrees with what I just did deserves more suspicion than one that does not.

**Final, verified split:**

| | Count | Meaning |
|---|---|---|
| Genuine fixes | **14** | The view did not compensate; page 2 genuinely dropped the filter |
| Already covered by the view | **12** | The blade already called `->appends(request()->query())`; the controller change is redundant |
| Mobile | **1** | `MobileLibraryController::catalog()` — **not touched**, flagged below |

The 14 genuine fixes:

```
BankTransactionController::index        HostelAllocationController::index
BookIssueController::index             HostelRoomController::index
EmailTemplateController::index         LeaveApplicationController::index
FeeAdjustmentController::index         LibraryMemberController::index
RouteController::index                 SmsTemplateController::index
StudentFeeAssignmentController::index  StudentTransportAssignmentController::index
SupplierController::index              TermController::index
```

The 12 where the view already compensated had `withQueryString()` added anyway. This is **not counted
as a fix**. It is kept deliberately as defence in depth — the controller is the correct layer, and a
future edit that drops `->appends()` from one of those blades would otherwise silently reintroduce the
bug. It is safe: `appends()` merges into the paginator's query array, so applying both does not
duplicate a parameter (asserted in the test rather than assumed).

### Flagged, not implemented — mobile

`MobileLibraryController::catalog()` has the same missing preservation. **Mobile code untouched** per
standing rule. Unlike the earlier mobile item, this one **is** API-shape relevant: the change would
alter the `links`/pagination URLs inside the JSON response. Not implemented; flagged for a decision.

### Files changed

```
14 controllers genuinely fixed (listed above)
12 controllers given withQueryString() as belt-and-braces where the view already appends
tests/Feature/PaginationFilterPreservationTest.php   (new)
```

### Verification

- `PaginationFilterPreservationTest` — 2 tests, 13 assertions, passing.
- **Falsification, discriminating (1):** with `SupplierController` stashed, the supplier test fails with
  the exact evidence:
  `The page-2 link dropped the status filter. Links were: http://localhost/suppliers?page=2` — no
  `status=active`. That is the defect reproduced verbatim.
- **Explicitly non-discriminating (1):** the books test **passes against the unfixed controller**,
  because `books/index.blade.php` already calls `->appends(request()->query())`. It cannot be evidence
  for the `BookController` change and is not counted as such. It is kept as a regression guard for that
  screen, and it does assert one property the fix introduced: the filter is not duplicated in the link.
  Recorded here rather than quietly presented as a second passing falsification.
- The two tests use different endpoints and filter types (free-text `search`, enumerated `status`) so the
  gap is not a single-controller artefact.
- Re-scan after the change: **0 remaining web defects**; the mobile one is the only outstanding site.

### Note on method

The pattern across this batch was that my own tooling produced the errors, not the application. Both
retractions here were scan bugs — a missing equivalent call, and a path-separator bug that made a probe
agree with the change set it was meant to be checking. The suite is what settles these questions.

---

## Batch P1-N — money display: rounded cents and two currencies

### Status: COMPLETE for the six fee screens; wider symbol sweep flagged

**Full suite: OK (376 tests, 1773 assertions, 0 failures, 0 errors).**

### The two defects

**1. Six fee screens rounded money to whole shillings.** `number_format($amount, 0)`. Every amount
column in this schema is `DECIMAL(12,2)`, so the cents existed and the display discarded them — on an
arrears total, a refund figure and a revenue report. My first scan missed five of these because they use
the **no-precision** form, which also defaults to zero:

```php
number_format($expectedRevenue)      // dashboard:274 — also 0 decimals
number_format($totalDiscounts)       // dashboard:304
number_format($row->total)           // dashboard:432
```

**2. Two currencies for the same shilling.** 106 occurrences of `KSh` against 125 of `KES`. Notably
**no single file mixed them** — each page looked correct on its own, while moving between two fee
screens changed the symbol. That is why it survived: there was never a screen that looked broken.

The symbol question looked like a product fork and is not one — **the codebase had already decided**.
`app/` writes `KES` 15 times against `KSh` 4, `Student::getBalanceFeeAttribute()` returns `'KES '`, and
`SendFeeReminders` builds all three of its money strings with `'KES '`. KES is also the ISO 4217 code.

### The fix

`app/Support/Money` is now the single place that decides symbol and precision:

```php
Money::format(1234.56);   // 'KES 1,234.56'
Money::number(1234.56);   // '1,234.56'
Money::whole(42);         // '42'  — for counts, percentages, years
```

`SYMBOL` is a constant, so a future multi-currency school changes one line rather than 200 views.

**35 money expressions across the six views** now use it: 24 that rounded to whole shillings, plus 11
table rows that were already 2-decimal but carried the wrong symbol.

### A mistake I made and caught inside this batch

My first pass converted only the **metric cards**, which left each screen showing `KES` on the cards and
`KSh` in the table beneath — I had *introduced* the exact same-screen mixing that does not currently
exist anywhere in the codebase. Caught by re-scanning the six files for residual `KSh` instead of
assuming the edit was complete, then fixed the 11 table rows too.

One deliberate display change: discount rows previously rendered `-KSh 500.00`. The formatter writes the
symbol first, so this is now `KES -500.00`. Flagged because it is a visible convention change, not a
like-for-like substitution.

### Test faults of my own

`test_the_six_fee_views_no_longer_round_money_or_hardcode_another_currency` failed on first run. It
tested each **line** for a comment marker, but a Blade comment spans lines and only the first carries
`{{--`, so the explanatory comment I had just written tripped my own check. Fixed by stripping
`{{-- --}}`, `/* */` and `//` comments from the content before scanning, rather than by rewording the
comment to dodge the assertion.

### Files changed

```
app/Support/Money.php                                        (new)
resources/views/fee_management/arrears/index.blade.php       6 money sites + filter label
resources/views/fee_management/dashboard.blade.php            5
resources/views/fee_management/index.blade.php                6
resources/views/fee_management/refunds/index.blade.php        5
resources/views/fee_management/reports/discount_summary.blade.php  6 (incl. the sign convention)
resources/views/fee_management/reports/expected_revenue.blade.php  7
tests/Feature/MoneyFormattingTest.php                        (new)
```

### Verification

- `MoneyFormattingTest` — 5 tests, 28 assertions, passing.
- **Falsification: 2 of 5 fail against the unfixed views.** The view-content guard names every raw call
  it finds, e.g.
  `arrears/index.blade.php still calls number_format() directly: number_format($totalExpected, 0), number_format($totalCollected, 0), number_format($totalOutstanding, 0), …`
  and the screen test fails on `KES 1,234.56` versus the old `KSh`/`1,235` rendering.
- **Explicitly non-discriminating (3):** `test_format_keeps_two_decimals_and_groups_thousands`,
  `test_format_does_not_round_cents_away` and `test_a_negative_amount_keeps_the_symbol_first` are unit
  tests of a class that did not exist before this batch. They cannot fail against the unfixed code, so
  they are **specification tests, not regression evidence**, and are not counted as such.

### Remaining scope — flagged, not done

This batch covered the six screens that rounded money. It did **not** make the symbol consistent
application-wide, and the inconsistency is still real:

| Area | State |
|---|---|
| Fee module, other screens | still `KSh`: `show` (15), `collect_payment` (10), `assignment_status` (5), `collections` (6), `payment_method` (3), `receipt_register` (2), `reverse_payment`, refunds `create`/`show`/`pdf`, arrears `pdf`, `discount_summary_pdf` |
| Fee-adjacent | `fee_structures/*` (8), `discount_schemes/index` |
| Other modules | `books/*`, `book_issues/*` (5), `hr/reports/payroll` (6) |
| 0-decimal money outside the fee module | `finance/dashboard` (8), `budgets/vs_actual` (6), `students/reports/fee_status` (3 + 3 in its PDF), `exam_reports/templates/card` (4) |

A receipt printed from `receipt_register` still says `KSh` while the fee dashboard now says `KES`, so the
job is half-done and the log says so rather than implying the app is consistent. The same `Money`
formatter finishes it; it is a mechanical pass over the files above.

---

## Batch P1-O — finishing the money sweep (closing the half-done job from P1-N)

### Status: COMPLETE

P1-N left an inconsistency I had created myself: the fee dashboard said `KES` while receipts and the
fee detail screens still said `KSh`. That is worse than either extreme, so it is closed here.

### What was finished

**1. The symbol — 73 occurrences across 24 files**, via a literal token rename.

Verified safe before doing it: **no occurrence of `KSh` in `resources/views` is adjacent to a letter**,
so it is always the standalone currency token and never part of an identifier. Replaced:
`fee_management/show` (14), `collect_payment` (10), `arrears/exports/pdf` (6), `reports/collections` (6),
`hr/reports/payroll` (6), `assignment_status` (4), `fee_structures/show` (4), `reports/payment_method`
(3), plus 16 more across `books`, `book_issues`, `fee_structures`, `discount_schemes`, refunds and the
PDF exports.

I used a scripted rename rather than 24 individual edits, and verified the result by re-scanning the
rendered content rather than trusting the replacement.

Two sites needed hand conversion because the sign sat **before** the symbol — `-KSh {{ number_format(...) }}`
in `reports/assignment_status` and `show`. A blind rename would have produced `-KES 1,000.00`, so these
use `Money::format(-1 * $x)` → `KES -1,000.00`, matching the convention set in P1-N.

**2. Money still rounded to whole shillings outside the fee module — 31 sites:**

```
finance/dashboard               8      inventory_items/index           2
budgets/vs_actual               6      inventory_items/show            1
students/reports/fee_status     3      inventory_categories/index      1
students/reports/fee_status_pdf 3      inventory_categories/show       1
fee_structures/index            2      exam_reports/templates/card     1
bank_accounts/index             1      students/show                   1
                                        suppliers/show                  1
```

`finance/dashboard` also had the `- KES` / `+ KES` transaction prefixes; both now use the formatter with
the sign inside the amount, consistent with the fee screens.

### Deliberately NOT converted — non-money

The scan reported 22 files with zero-decimal `number_format()`, but most are **not money** and
converting them would be wrong. Left as-is:

- **Marks and scores:** `exam_reports/templates/card` (total marks), `exam_schedules/table` (max marks),
  `grade_book/index`, `mark_sheets/index`, `exam_analysis/rankings`, `students/tabs/overview`.
- **Counts and percentages:** `discount_schemes/show:36` (`{{ number_format($value, 0) }}%`),
  `inventory_items/index` item count, `exam_analysis/subject` averages, document counts.
- **Sizes:** `system_logs/index` (KB).

Flagged because "0 decimals" is only a defect when the value is money, and a blanket sweep here would
have corrupted marks and percentages.

### A verification mistake I made

I ran the re-scan **in the same batch as the edits it was meant to verify**. The tool calls executed
concurrently, so the scan read pre-edit content and reported two files as unfixed that were in fact
already fixed. I caught it by reading the files directly rather than trusting the scan. Recorded because
the failure mode is silent and produces a false negative: a verification step batched with its edits is
not a verification step.

### Result

```
money still rounded to whole shillings: 0
views still displaying KSh outside comments: 0
```

The single remaining `KSh` in the codebase is inside an explanatory comment in
`fee_management/reports/discount_summary.blade.php` that deliberately contrasts the old
`-KSh 500.00` rendering with the new `KES -500.00` one.

### Guard added

`MoneyFormattingTest::test_no_view_displays_the_wrong_currency_or_rounds_money` now checks both
invariants across **every** view in `resources/views`, not a hand-maintained list — the six screens from
P1-N are simply the ones that happened to be wrong. Any future view that reintroduces either defect
fails the suite. It strips Blade/`/* */`/`//` comments first, because a comment explaining the old
behaviour must not itself fail the check.

### Not a defect — noted for context

**125 sites still write `KES {{ number_format($x, 2) }}` directly** rather than calling the formatter.
These render correctly (right symbol, right precision) — it is duplication, not a bug, so it was left
alone. Consolidating them onto `Money::format()` is available as a mechanical pass if the duplication is
unwanted; it is not a correctness issue and I have not claimed it as one.

### Correction — the symbol change invalidated six assertions I had written myself

The full-suite run after P1-O came back with **3 failures, all in `FeeReversalReportingTest`** — a test
file I wrote in P1-I. The reason is that its money assertions named the symbol:

```php
$response->assertSee('KSh 3,000.00');       // yes, not
$response->assertDontSee('KSh 6,000.00');
```

Those passed in P1-I because the collections and receipt-register screens rendered `KSh` at the time. So
the assertions were **pinned to the defect**: they asserted not just the amount but the wrong currency,
and any correct fix to the symbol was guaranteed to break them. Updated to `KES`, all six occurrences.

This is different from the earlier case where my term-validation change broke
`FeeAssignmentBulkRegressionTest` for the right reason — there the test encoded a broken *contract*, here
it encoded a broken *presentation*, and both needed correcting rather than the code being reverted.

It also means the P1-N batch was reported as green on a suite that had not yet seen the six assertions
that the change would break. The green figure quoted for P1-N was accurate for the code as it stood at
that moment, but the batch was not finished: a symbol change must be followed by a run of every test that
asserts on money strings. Caught on the next full run.

---

## INCIDENT — the test suite hung, and it was not the application

### Status: DIAGNOSED — infrastructure contention, no application defect

### Symptom

The full suite **exceeded the 15-minute background limit**, having printed progress only to
`126 / 377 (33%)`. Re-running the single test at that position
(`FeeReversalIntegrityTest::test_double_submission_of_the_same_form_creates_one_payment`, identified by
indexing `--list-tests` against the progress counter) **hung on its own as well**.

This matters because the position was a payment test: if the app hung on each payment, that would be a
production-severity bug rather than a test problem.

### What was ruled out, with evidence

| Suspect | Evidence | Verdict |
|---|---|---|
| Stale PHP process holding locks | `Get-Process -Name php` → none | ruled out |
| Row-level lock | `innodb_lock_wait_timeout = 50` — a row lock **errors at 50s**, it cannot hang for 15 minutes | ruled out |
| Connection exhaustion | `max_connections = 151`, `Threads_connected = 1-2`, `Max_used_connections = 8`, `Aborted_connects = 0` | ruled out |
| Outbound network call | the hung process's only socket was `127.0.0.1:3306`; no external connection | ruled out |
| CPU spin / infinite loop | CPU grew 3.1s → 3.3s across 15 seconds of wall time — the process was **idle, waiting** | ruled out |
| Application code in the payment path | `FinanceService::recordPayment`, `LedgerService::allocatePayment`, `AuditTrail::log` contain no `sleep`, no loop, no lock, no HTTP; no observers or listeners on `FeePayment` | ruled out |
| The `payrolls` migration itself | `php artisan migrate:fresh --force` against `school_erp_test` (with `APP_ENV=testing`, `DB_DATABASE=school_erp_test`) **completed**, every migration `DONE` | ruled out |

### What the evidence showed

Instrumenting the test with `fwrite(STDERR, ...)` markers pinned the hang to a location — and then the
**location moved**: one run stalled inside `parent::setUp()`, the next inside `$this->accountant()`.
A defect in application code cannot relocate itself between runs; that single observation excluded the
whole class of "a specific code path hangs".

Querying `performance_schema.threads` while the process hung caught the actual statement:

```
thread=109918 pid=5332 state=creating table
  info=create table `payrolls` (`id` bigint unsigned not null auto_increment primary key, `month` int not null, `year` ...
```

So the process was inside `RefreshDatabase`'s `migrate:fresh`, waiting on a **`CREATE TABLE`**. Standalone
`migrate:fresh` on the same schema succeeds in ~75 seconds.

### Conclusion

**Metadata-lock contention on the shared test schema**, not an application bug. The mechanism:

```
lock_wait_timeout = 31536000   -- one YEAR
```

`innodb_lock_wait_timeout` (50s) governs **row** locks, but a `CREATE TABLE` waits on a **metadata** lock,
which is governed by `lock_wait_timeout`. At its default of one year, any transient contention on
`school_erp_test` — a second connection or a second test process against the same schema — does not fail
with an error, it **appears to hang indefinitely**. `performance_schema.metadata_locks` showed no pending
lock at the moment I sampled it, which is consistent with a wait that starts and ends around the sampling
rather than with no wait at all.

I contributed to the contention myself: overlapping diagnostic runs and probe scripts connecting to
`school_erp_test` while a suite was in flight, and killing test processes mid-run by hand.

### Actions taken

1. All diagnostic instrumentation **reverted**; `git diff --stat` for
   `tests/Feature/FeeReversalIntegrityTest.php` is clean, confirming it matches its committed state.
2. All temporary probe scripts and output files removed.
3. Full suite re-run with nothing else touching the database.

### Recommendation for the environment (not applied — outside application code)

Set `lock_wait_timeout` to something sane, e.g. **30 seconds**, in `my.ini`/`my.cnf` (or per-session at
the start of a test run). At the default of one year, contention surfaces as a hang that looks like a
code defect; with a short timeout it surfaces as an error naming the blocking lock. Separately: **never
run two PHPUnit processes against the same test schema** — the schema is shared state.

### Note on process

The first instinct here was to treat the hang as a defect in the payment path, because that is where it
appeared. The marker that settled it was that the hang **moved**. Relocating failures are the signature
of resource contention, and worth checking before reading any application code.

---

## Batch P1-P — exam rankings, the last void-marker gap, and the 209-row repair

### Status: COMPLETE

### Authorised data repair — 209 payment methods

Applied exactly as authorised:

```sql
UPDATE fee_payments SET payment_method = 'online' WHERE payment_method = ''
```

Guarded before running rather than blindly executed:

- Confirmed the connection was `school_management_system` and aborted otherwise.
- Re-checked the FeeSeeder fingerprint on all 209 rows (transaction id beginning `MP`, receipt `RCP-`)
  **before** updating, and aborted if any row failed it.
- Snapshotted every affected row to
  `storage/app/repairs/payment_method_repair_2026-09-22_144927.json` first, so the change is reversible
  even if identification were later disputed.

Result:

```
rows with empty payment_method BEFORE: 209
rows updated:                          209
remaining empty:                         0
snapshot rows now online:              209 / 209
```

Derived after the change: `payment_method` distribution is now `'online' => 209` and nothing else. No
other value in that column has ever existed.

### Exam rankings — raw marks replaced with percentages

`rankings()` selected `SUM(marks_obtained)` as its ranking key and ordered by it, so a learner scoring
90/100 outranked one scoring 18/20 — both 90% — because their paper happened to be marked out of a larger
total. The view then printed that raw mean with a `%` sign, and hardcoded a green `Passed` badge on every
row.

Now, per learner:

- each paper converted to a percentage using its own `max_marks` from `exam_schedules`;
- ranked on the **mean percentage**, so a learner is not advantaged by the number of papers sat;
- pass/fail against the average of their own papers' pass marks, converted to percentages — the same
  definition `buildAnalysis()` uses, so the two screens cannot disagree;
- grade from `GradingScale::resolveForPercentage()`, the same engine the report card uses.

View changes: `Total Marks` → `Total (%)`, a real conditional status, and a Grade column.
`"Total Marks"` is asserted absent so a raw-mark presentation cannot return unnoticed.

**A bug I introduced and caught before finishing:** I wrote `app(CurriculumService::class)` without the
import, which would have resolved to `App\Http\Controllers\CurriculumService` and thrown. Found by
checking the `use` block rather than by running the code. The now-unused `use DB;` was removed at the
same time.

Verification: `tests/Feature/ExamRankingsTest.php` — **4 tests, all passing.**

**Falsification: 3 of 4 fail against the unfixed code**, and the failure output *is* the defect:

```
Both "Rawsum Pupil" and "Percent Pupil" present, with "Rawsum Pupil" rendered FIRST
(the old raw-sum ordering: 100 > 98), and both rows carrying
<span class="badge badge-success">Passed</span>.
Expected "Below 40%" — not present anywhere on the page.
```

**Explicitly non-discriminating (1):** `test_a_class_with_no_results_renders_the_empty_state`. The empty
state already existed, so it passes either way; it guards a regression in the opposite direction and is
**not** counted as evidence for the fix.

### The last void-marker gap

Audited every view that iterates payment rows rather than trusting the earlier fix:

| Surface | Marker |
|---|---|
| `fee_management/show` | yes |
| `fee_management/reports/collections` (both tables) | yes |
| `fee_management/reports/receipt_register` | yes |
| `students/tabs/fees` | yes |
| `portal/fee-receipt` | yes |
| **`portal/fee-detail`** | **no — fixed here** |

`portal/fee-detail` renders `@foreach($assignment->payments as $payment)` with no reversal state, so a
parent looking at the assignment detail page saw a voided payment as live. Fixed with the same treatment
as the sibling receipt in the same module: dimmed row, struck-through amount, explicit `VOID`.

### RETRACTED — no evidence of "discount duplication" in money

I searched for a discount applied twice and found none. Evidence:

```
duplicate schemes by name                     none
duplicate schemes by code                     none
total discount schemes in live DB             0
student_fee_assignments where discount > amount          0
student_fee_assignments where final != amount - discount 0   (all 263 consistent)
duplicate active assignment per (student, structure)      none
```

The code is idempotent by construction: `applyDiscountToStudentAssignments()` always recomputes
`final_amount = amount - totalDiscount` from the **original** amount, so re-running it cannot compound,
and it explicitly skips assignments carrying a manual discount. The scheme query applies no joins, so a
scheme cannot be returned twice.

The duplication I did find and fix was **structural**, not monetary, and was closed in P1-J: the five
fields `academic_year_id`, `valid_from`, `valid_to`, `requires_approval`, `auto_apply` existed in
`create.blade.php` only, so the edit screen could not change them. They now live in the shared
`fields.blade.php` partial.

If the operator meant a different duplication, I need the screen or the row — as it stands there is
nothing in the data or the code to fix, and I am not going to invent one.

---

## Batch P2-A — Academic: views referencing unregistered routes (turned out to be a class of 45)

### Status: named item fixed; the rest flagged with evidence rather than deleted

**Full suite after this batch: `OK (383 tests, 1789 assertions, 0 failures, 0 errors)`** — 381 before, plus
the 2 new `ViewRouteReferenceTest` cases.

### The reported defect was one instance of a systemic one

`assessment_types` views called `route('assessment-types.create' | '.edit' | '.index')`, and no such
routes were registered — a `RouteNotFoundException`, i.e. a 500, the moment any of those views rendered.

Rather than patch four lines, I resolved **all 786 registered route names** and checked every literal
`route('...')` in `resources/views`. Result: **45 dangling references across the application**, not one.

### Why assessment_types could only be removed, never re-routed

Three independent records agree that the feature was deliberately retired:

| Evidence | Where |
|---|---|
| `Schema::dropIfExists('assessment_types')` | migration `2026_08_30_120000_drop_assessment_types_table.php` |
| `// Assessment Types removed — table dropped` | `routes/web.php:452` |
| `// Assessment Types removed — not in use` | `config/menu.php:229` |

Registering the routes would have produced SQL errors against a table that no longer exists, so removal
is the only coherent fix. Also verified before removing: `AssessmentType` was referenced **only** by its
own controller and model — no relation, no service, no seeder, no test.

**Removed (7 files, all git-tracked so recoverable from history):**

```
app/Http/Controllers/AssessmentTypeController.php
app/Models/AssessmentType.php
resources/views/assessment_types/{create,edit,fields,index,table}.blade.php
```

### Two of my own 45 findings were false positives — retracted

I checked the findings rather than trusting the scan, and the scan was partly wrong:

1. **`auth/passwords/reset.blade.php:22`** — the line is
   `$token = \Request::route('token');`, a **method call on the Request facade**, not the `route()`
   helper. My regex matched it.
2. **`resources/views/vendor/laravel-generator/scaffold/user/update_user_request.blade.php`** — a
   published generator template, not a page, and likewise an object method call.

Both are excluded from the check now by requiring the match not to be preceded by `->` or `$`.

### A third finding retracted on reasoning

**`auth/verify.blade.php:22` — `route('verification.resend')` is not a defect.** The route is absent
because `routes/web.php:29` calls `Auth::routes()` without `['verify' => true]`, which is also why email
verification routes are absent. The view is therefore **unreachable** in exactly the configurations
where the reference is invalid, and the reference becomes valid in the one configuration that makes the
view reachable. Consistent in both states, so nothing to fix.

### Flagged, not removed — and why

**The pre-consolidation RBAC scaffold** — `PermissionController`, `RolePermissionController`,
`UserRoleController` and the `permissions/`, `role_permissions/`, `user_roles/` view families: 24 files
carrying 16 of the dangling references. Evidence that they are dead:

- Not registered in `routes/web.php`; the live RBAC area is `roles.*` and `users.*` (15 registered routes).
- Referenced by nothing — the controllers appear only in their own class declarations.
- No test touches them, and no `Route::view()` returns their views.
- Introduced before `57f36e0 feat: consolidate 215 permissions into 43 with policies, sidebar, and portal`.

**Not removed on purpose:** the working branch is `feat/rbac-consolidated-permissions` — this *is* the
consolidation branch — so whether this scaffold is being kept for reference during that work is a
coordination decision, not a code one. Flagged with the removal list ready.

**`layouts/menu.blade.php` and `layouts/menu-improved.blade.php`** — 24 more references, and neither is
included anywhere: `layouts/app.blade.php:59` includes `layouts/sidebar`, not these. Let me be precise
about the risk: they are inert **because** they are unreachable. If either were ever wired back in, the
sidebar would fatal on load. Flagged rather than deleted for the same branch-related reason.

### Guard added

`tests/Feature/ViewRouteReferenceTest.php`:

- asserts every literal `route('...')` in every view resolves to a registered name, ignoring object
  method calls;
- carries an explicit `KNOWN_DEAD_VIEWS` allowlist for the files above, so the lint is green today but
  **fails on any new dangling reference**, and the debt is visible rather than silent;
- a companion test fails when an allowlisted file no longer exists, so the list cannot quietly rot and
  stop meaning anything.

### Verification

- The scan itself is the evidence for the 45, and it was re-run with the object-method-call exclusion to
  produce it — the first run reported 46 including the false positive.
- `assessment_types` removal: file list confirmed gone from disk; `git status` shows the 7 deletions.
- `ViewRouteReferenceTest` run result recorded in the next batch entry (the full suite was mid-flight on
  the shared test schema, and only one test process may touch it at a time).

### Still open in Academic

Learning-area level vocabulary split; `class_subjects` create/edit UI divergence; timetable generator not
filtering `period.type = 'break'`; destructive timetable regeneration.

### Verification — the lint, and two bugs in the lint itself

**Suite before this batch: `OK (381 tests, 1786 assertions)`.** That figure already includes the 4 new
`ExamRankingsTest` cases and the corrected `FeeReversalReportingTest` assertions.

`ViewRouteReferenceTest`: **2 tests, 3 assertions, passing.**

**Falsification of the guard:** I injected a deliberate dangling reference into a reachable view
(`{{ route('ghost.route.injected.for.falsification') }}`) and the lint failed with the exact location:

```
1) ViewRouteReferenceTest::test_no_reachable_view_references_an_unregistered_route
  auth/verify.blade.php:22  route('ghost.route.injected.for.falsification')
```

The injection was then removed and the lint returned to green. So the guard discriminates — it is not
passing merely because it found nothing to look at.

**Two bugs in my own lint, found on its first run:**

| Bug | Fix |
|---|---|
| The exclusion for `\Request::route('token')` never fired. I checked for `>` or `$` in the 3 characters preceding the match, but for a facade call those characters are `t::`, so the false positive survived into the lint itself | Also exclude when the prefix contains `::` |
| A reference behind a `Route::has()` check was flagged. That is a legitimate defensive pattern — the view checks before using — so flagging it is wrong | Skip lines containing `Route::has`, and had the pattern applied where it belongs |

The second prompted a real improvement: `auth/verify.blade.php` now guards its form action with
`Route::has('verification.resend')`, so the reference cannot dangle even if the verification routes and
this view's reachability ever drift apart. That is a fix rather than an allowlist entry, which is the
better outcome for a live-ish view.

This is the third time in this session that my own tooling, not the application, produced the errors —
the Windows path-separator bug in the pagination scan, the missing `->appends()` equivalent, and now
these two. The pattern is consistent enough to record: **a scan written in the same breath as the fix is
the least trustworthy artefact in the batch, and its findings need checking one by one before they are
acted on.**

---

## Batch P2-B — Academic: the `type = 'break'` finding is a FALSE POSITIVE (reverted)

### Status: RETRACTED with evidence; code returned to its committed behaviour

### What the finding claimed

"Timetable `type='break'` period not filtered by the generator" — i.e. break periods are treated as
teaching slots.

### What the source actually shows

**The generator is filtered.** All four scheduling paths already restrict to teaching periods:

```
TimetableController.php:818   Period::where('type', 'period')     // getPeriods (AJAX)
TimetableController.php:840                          // generator input
TimetableController.php:888                          // buildPreview
TimetableController.php:949                          // template data
```

**The two paths that do not filter are deliberate, and the views depend on it.** I changed them, then read
the views before accepting the change — and reverted it:

```
timetables/index.blade.php:121   $isBreak = ($period->type ?? 'period') === 'break';
timetables/index.blade.php:122   <th class="{{ $isBreak ? 'tt-break-col' : '' }}">
timetables/index.blade.php:147   @if($isBreak) <td class="tt-cell tt-break"> … <span>Break</span>
timetables/index.blade.php:135   breaks explicitly excluded from the day's lesson count
```

```
teacher_timetable.blade.php:147  $isBreak = ($period->type ?? 'period') === 'break';
teacher_timetable.blade.php:152  <tr class="{{ $isBreak ? 'tt-break-row' : '' }}">
teacher_timetable.blade.php:164  @if($isBreak) … <div class="tt-break"> … <span>Break</span>
teacher_timetable.blade.php:316  .tt-break-row td { background: #fffbeb !important; }
teacher_timetable.blade.php:372  print-colour rules for break rows
```

A break renders as its **own** cell/row — `tt-cell tt-break`, `tt-break-row`, a coffee icon, a dedicated
background colour, and print rules — and never falls through to the `@else` branch that renders a "Free"
slot a lesson could be assigned to. So a break was never schedulable in either screen; it was presented as
information, which is what a printed timetable should do.

### The fix was therefore worse than the "bug"

My change added `where('type', 'period')` to both controller loads. That would have **removed the break
columns and break rows from both timetables** — deleting a deliberate, styled, print-aware feature in
order to satisfy a finding that was wrong. Reverted; verified lines 100 and 320 are back to their committed
form, and the only remaining diff is six comment lines recording *why* the filter is absent there, so the
next person does not make the same mistake.

### Why this one is worth recording

The finding read as obviously correct: "the generator does not filter breaks" is the kind of statement that
is easy to accept, and every one of the four scheduling paths *did* filter, which made the two unfiltered
loads look like omissions. What distinguishes them is not the query but the consumer — one renders
schedulable slots, the other renders a timetable that is supposed to show its breaks. Reading the view was
the only thing that settled it, and I only did that because the `tt-break-col` class caught my eye.

This is the fourth false positive retracted this session, and the second time the evidence came from the
view rather than the controller.

---

## Batch P2-C — removing the dead scaffold (the recommended option, taken)

### Status: COMPLETE — the dangling-reference count is now zero with no allowlist

Acting on "use best recommended option": the option I had recommended for the flagged cluster was
**removal**, and it is done.

### Removed — 27 files

**Pre-consolidation RBAC scaffold (24 files):**

```
app/Http/Controllers/PermissionController.php
app/Http/Controllers/RolePermissionController.php
app/Http/Controllers/UserRoleController.php
resources/views/permissions/*          (7 files)
resources/views/role_permissions/*     (7 files)
resources/views/user_roles/*           (7 files)
```

**Never-included layouts (2 files):**

```
resources/views/layouts/menu.blade.php
resources/views/layouts/menu-improved.blade.php
```

With the 7 files from P2-A, this batch brings the removal to **33 files**, all git-tracked and therefore
recoverable from history.

### Safety checks run before deleting, not after

| Check | Result |
|---|---|
| References to the three controllers outside their own files | **none** (across `app/`, `routes/`, `config/`, `tests/`, `database/`) |
| Routes pointing at them | none — the live RBAC area is `roles.*` / `users.*` |
| `@include` / `@extends` of the view dirs or the dead layouts | none outside the removed set |
| Tests touching them | none |
| `Route::view()` returning them | none |

**One near-miss worth recording.** A grep for `layouts.menu` matched
`resources/views/layouts/sidebar.blade.php:35 → @include('layouts.menu-tooltip-fix')`. That is a
**different, live file** — `menu-tooltip-fix.blade.php`, which the active sidebar includes. A
prefix-matching deletion would have taken it out with the two dead files and broken the sidebar. It was
excluded explicitly and verified present afterwards, along with `layouts/sidebar.blade.php`.

### The allowlist is gone, and that is the point

The `ViewRouteReferenceTest` allowlist existed only to keep the lint green while those unreachable files
remained. With them removed, the allowlist and its companion "does not outlive the files it names" test
were both deleted, and the lint now runs with **no exemptions**:

```
ViewRouteReferenceTest  →  OK (1 test, 2 assertions)
```

So **zero dangling `route()` references remain anywhere in the view tree** — not zero-unless-allowlisted,
but actually zero. Any new one fails the suite.

### Mobile — deliberately not touched

`MobileLibraryController::catalog()` still drops pagination filters. **My recommendation for that
question is to leave it alone**: the change alters the `links`/pagination URLs inside the mobile API
response, which is an API-shape change, and the standing rule has been to flag those rather than
implement them. "Use the best recommended option" is not the explicit sign-off that rule asks for, so it
stays untouched and flagged. One line when authorised (`->withQueryString()` on the paginator).

### Verification

- Lint with no allowlist: passing, i.e. no dangling references remain.
- Removal verified against `git status`: exactly 33 deletions, 7 assessment_types + 24 RBAC scaffold +
  2 layouts, and no file outside the intended set.
- `layouts/sidebar.blade.php` and `layouts/menu-tooltip-fix.blade.php` confirmed still present.
- **Full suite after the removal: `OK (382 tests, 1788 assertions, 0 failures, 0 errors)`** — 383 before minus
  the one allowlist-companion test that no longer has a subject. Removing 27 files broke nothing.

---

## Batch P2-D — Academic: "destructive timetable regeneration" — retracted in its stated form, with a real defect found underneath

### Status: the blanket finding is a false positive; a narrower real defect fixed

**Full suite after this batch: `OK (384 tests, 1794 assertions, 0 failures, 0 errors)`** — 382 before, plus
the 2 new `TimetableRegenerationSafetyTest` cases.

### What the finding claimed

"Destructive timetable regeneration" — regenerating discards an existing timetable without warning.

### What the source actually shows

The regeneration is **disclosed and explicitly confirmed**. `timetables/auto_generate.blade.php` is a
five-step wizard whose final panel is headed *"Step 5 — Confirm & Save"* and contains:

```
"This replaces the current timetable for this year"
Saving will delete all existing timetable lessons for <academic year name> and replace
them with the N lessons above.
The N unplaced requirements will not be saved.
[ Confirm & Save N Lessons ]   [ Cancel ]
```

It names the year, states the deletion, quantifies what is being written, warns about what is *not* being
written, offers Cancel, and requires an explicit click on a button labelled "Confirm & Save". That is a
deliberate, well-designed destructive flow rather than an accidental one, so the finding as stated is
**retracted**.

### What was genuinely wrong — and it is narrower but real

The store path deleted first and validated afterwards:

```php
DB::transaction(function () use (...) {
    Timetable::where('academic_year_id', $academicYearId)->delete();   // line 536

    foreach ($result->placements as $idx => $row) {
        ...
        if ($validator->fails()) {
            $validationErrors[] = "Row " . ($idx + 1) . ": " . ...;
            continue;                                                  // row silently dropped
        }
        $timetable->save();
    }
});
```

A generated row that failed validation was skipped with `continue` **after** the delete, inside a
transaction that then committed. So an invalid row silently lost a lesson from a timetable that had
already been destroyed, and the summary flash ("N rows had validation errors") framed it as a footnote to
a successful save rather than as data loss. Nothing in the confirmation step warned that rows could be
dropped.

**Fixed:** every generated row is now validated **before** anything is deleted, and the replacement is
all-or-nothing —

```php
if ($validationErrors !== []) {
    Flash::error('Nothing was changed. N generated row(s) are invalid, so the existing
                  timetable was left untouched: …');
    return redirect(...);   // no delete, no commit
}
```

so a bad batch leaves the current timetable exactly as it was.

### Two smaller improvements

1. **The disclosure now gives a number.** "All existing lessons" is much weaker than "the 240 existing
   timetable lessons" — the wizard now states the count, and notes the deleted rows are recorded in the
   audit trail so the loss is recoverable rather than merely announced.
2. Recoverability confirmed rather than assumed: `audit_trails.old_values` is a `json` column
   (`2026_02_04_230000_create_audit_trails_table.php:17`) and the controller captures the full set of
   rows before deleting, so the replaced timetable can be reconstructed from the audit record.

### Verification

- `tests/Feature/TimetableRegenerationSafetyTest.php` — 2 tests, 6 assertions, passing.
- **Falsification: 1 of 2 fails against the unfixed view**, with the original text quoted back in the
  failure — `Failed asserting that '<!DOCTYPE html>…'` — confirming the disclosure change is what the
  test is measuring.
- **Explicitly non-discriminating (1):** `test_the_confirmation_step_says_so_when_there_is_nothing_to_replace`.
  It passes against the unfixed view because the old wording already contained the phrase
  "all existing timetable lessons" that it asserts on. It guards the zero-existing case only, and is not
  counted as evidence for the fix.

### Coverage gap, stated rather than papered over

The validate-before-delete ordering is **not covered by an end-to-end test.** Triggering it requires the
generator to emit a row with a broken foreign key, which it cannot do from live data: it reads
class sections, periods, subjects, staff and classrooms from the database and only ever places rows built
from what it found. The path was therefore reachable only if data vanished *between* the read and the
write — latent rather than likely, which is probably why it survived.

I have not written a test that fakes an invalid placement, because doing so would mean stubbing a service
the controller constructs with `new` rather than resolving from the container, and a test that asserts my
own refactor rather than the behaviour is worse than documenting the gap. The ordering is verified by
reading the code; the disclosure change is verified by test.

---

## Batch P2-E — Academic: the learning-area level vocabulary split (confirmed, and it had a user-facing consequence)

### Status: COMPLETE — bridge added, no migration needed

### The finding was real, and the two vocabularies are further apart than they look

| Where | Type | Holds |
|---|---|---|
| `cbc_learning_areas.level` | `varchar` | a CBC **stage**: `Pre-Primary`, `Lower Primary`, `Upper Primary`, `Junior School` |
| `subjects.grade_level` | `tinyint` | a **single grade**, in the same numeric space as `classes.numeric_value` |

A stage covers a range of grades, so the two cannot be compared directly — and nothing in the codebase
attempted to relate them.

**The detail that matters most:** `classes.numeric_value` is a **sequential index, not the grade number**.
Measured against the live data:

```
PP1 = 1,  PP2 = 2,  Grade 1 = 3,  Grade 4 = 6,  Grade 9 = 11,  Grade 12 = 14
```

So reading `numeric_value = 6` as "Grade 6" rather than "the class at index 6, which is Grade 4" puts
**every class in the school in the wrong stage**. Most of the test cases exist to pin that down.

### The consequence it was causing

`CompetencyAssessmentController::index` loaded **every** active learning area, regardless of the class
being assessed:

```php
$learningAreas = CbcLearningArea::where('status', true)->pluck('name', 'id');
```

A teacher recording a CBC assessment for **Grade 4** was offered Pre-Primary, Lower Primary, Upper Primary
*and* Junior School learning areas — the same list for every class in the school — because the screen had
no way to ask which areas belonged to that class. Selecting the wrong one would record an assessment
against a learning area the class does not take.

### The fix — a bridge, not a migration

`app/Support/CbcStage` is now the one place that relates a class to its stage. It **prefers the class
name** ("Grade 4" states the grade outright) and falls back to `numeric_value`, with the sequential
offsets documented on the method so the next reader does not make the mistake this class exists to
prevent. `CbcStage::all()` exposes the stage names for validation and dropdowns.

The assessment screen now narrows to the selected class's stage:

```php
$stage = CbcStage::forClass($selectedClassSection?->schoolClass?->name, $selectedClassSection?->schoolClass?->numeric_value);

if ($stage !== null) {
    $learningAreasQuery->where(fn ($q) => $q->where('level', $stage)->orWhereNull('level'));
}
```

Areas with **no level stay available**, matching how `ClassSubjectController` treats subjects with no
`grade_level` — narrowing must not hide unclassified data.

**No schema change.** Both vocabularies stay as they are; what was missing was the translation between
them, and inventing a migration to unify them would have been a much larger and riskier change than the
problem called for.

### Flagged, not changed — mobile

`MobileExamController:357` loads learning areas the same unfiltered way (`CbcLearningArea::with('strands.subStrands')`),
so the mobile exam screen offers the same wrong-stage list. **Mobile code untouched** per standing rule.
This one is data-correctness rather than an API-shape change — the response keys and types are unchanged,
only which rows appear — so it is a candidate for a normal fix if you want it, not something needing an
API review.

### Verification

- `tests/Feature/CbcStageFilteringTest.php` — 5 tests, 28 assertions, passing.
- **Falsification: 2 of 5 fail against the unfixed controller**, and they are precisely the two filtering
  cases — the Grade 4 screen still offering Junior School areas, and the Grade 9 screen offering Upper
  Primary ones.
- **Explicitly non-discriminating (3):**
  1. `test_the_numeric_value_is_not_the_grade_number` and
  2. `test_the_class_name_is_preferred_over_the_numeric_value` — unit tests of a class that did not exist
     before this batch, so they cannot fail against unfixed code. They are **specification tests**, not
     regression evidence.
  3. `test_with_no_class_selected_every_area_is_offered` — asserts behaviour that was never broken.

  None of the three is counted as evidence for the fix.

### Still open in Academic

`class_subjects` create/edit UI divergence.
