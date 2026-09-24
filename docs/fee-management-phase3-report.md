# Fee Management — Phase 3 verification, fixes and UI audit

**Scope:** Fee Management module only (Laravel 10.48 / PHP 8.2 / MySQL 8, Kenyan school ERP)
**Date:** 2026-09-23
**Status:** Phases 0–3 complete and verified. Phase 4 findings presented, no risky migration applied.

---

## A. Tests

| Run | Result | Assertions | Duration |
|---|---|---|---|
| Before any change (clean baseline, caches cleared) | **416 passed, 1 failed** | 1996 | 480s |
| After the Phase 3 financial changes (pre-existing in the tree) | **419 passed, 0 failed** | 2063 | 261s |
| + `FeeReconciliationTest` (14 new tests) | **433 passed, 0 failed** | 2223 | 274s |
| `FeeScreensSmokeTest` (12 new tests, run alone) | **12 passed, 0 failed** | 33 | — |
| Definitive full run (Phases 0–3 + reconciliation + screen tests) | **445 passed, 0 failed** | 2256 | 365s |
| `FeeUiConsistencyTest` (5 UI guards, run alone) | **5 passed, 0 failed** | 13 | 0.9s |
| Final full run (everything above, plus the `::before` UI guards) | **450 passed, 0 failed** | 2269 | 326s |

The single baseline failure was a **defect in the test harness, not the application**:
`FeeAuthorizationTest` called `->status()` on a `Symfony\...\StreamedResponse`, which has no such
method. The two ledger export endpoints were working — that is precisely why they returned a stream.
Fixed with a `statusOf()` helper that reads `status()` or `getStatusCode()` as appropriate. The
intent of the assertion (403 vs not-403) is unchanged and still enforced.

**Fixed test that was asserting the wrong rule:** the same suite expected the *Accountant* to be able
to activate a term. `TermController` and the fee module's own Terms menu entry both require
`academics.settings.manage`, which `RbacSeeder` grants to Owner / Super Admin / Admin only. The
guard was right and the expectation was wrong, so the test now encodes the real permission
architecture and asserts both halves (Accountant denied; Owner/Super Admin/Admin allowed).

---

## B. Phase 3 verification — file by file

| File | Status | How it was verified |
|---|---|---|
| `app/Services/FeeBalanceService.php` (`paidTotalsSubquery()`) | **Verified** | `test_paid_totals_sql_matches_php_for_direct_and_reversed_payments` and `test_split_allocation_payment_is_attributed_identically_by_sql_and_php` compare the SQL derived table against `paidForAssignments()` per assignment, with direct payments, a reversed payment and a split allocation. |
| `app/Http/Controllers/FeeArrearsController.php` | **Verified** | `test_arrears_summary_totals_equal_its_own_table` (headline Expected/Collected/Outstanding agrees with the rows) + the arrears screen is asserted in every `assertPositionAgreesEverywhere` case. |
| `app/Services/AdminMobileHomeService.php` | **Verified** | Reuses the same shared SQL; covered indirectly by the parity test above. |
| `app/Http/Controllers/StudentFeeAssignmentController.php` | **Verified** | `studentSummary()` now calls `FeeBalanceService::summaryForStudent()`; the student-summary screen is asserted for every balance case. |
| `app/Http/Controllers/StudentReportController.php` | **Verified** | `test_student_fee_status_report_excludes_reversed_payments`: 10,000 paid (not 15,000) and 20,000 outstanding, where the report previously showed `total_paid = 0` for every learner. |
| `app/Http/Controllers/DashboardController.php` | **Verified** | The fee dashboard is asserted for every balance case (see §C). Five sites moved from `created_at` to `payment_date` and now apply `notReversed()`. |
| `app/Services/LedgerService.php` | **Verified — live data** | See §C/§B-ledger below. |
| `app/Http/Controllers/FeeManagementController.php` | **Verified** | `test_opening_the_statement_page_does_not_create_ledger_rows`. |

### Ledger / statement — verified against the live database

`getStudentStatement()` now derives its opening line:

```
opening = FeeBalanceService::balanceForStudent() − net(ledger movements)
```

so the closing figure **is** the balance every other screen shows. Measured on the live database
(`school_management_system`, 40 students, read-only probe):

```
students with ledger rows             : 5
OLD behaviour mismatches (ledger only): 33 of 40
NEW behaviour mismatches (derived)    :  0 of 40
ledger rows before 5 / after 5, allocations before 0 / after 0 → NO WRITES OCCURRED
```

Worst case, student 5: the statement said **−200,000** while the student's real position was
**+3,300** — off by KES 203,300 — because a 200,000 refund had been credited against a student who
had paid only 28,900 in total and whose charges had never been posted to the ledger.

Also verified:

- **A GET no longer writes financial rows.** `seedOpeningBalance()` and its call from
  `FeeManagementController::show()` were removed. Opening balances are computed, not stored, so a
  student's figures no longer depend on whether anyone had previously opened their page.
- **Legacy `opening_balance`/`bootstrap` rows are folded into the derived opening, not listed**
  (`test_legacy_bootstrap_opening_rows_do_not_double_count`), so a student cannot carry the same
  position twice.
- No historical transaction was invented and nothing was backfilled.

---

## C. Reconciliation — the KES 30,000 / 10,000 / 20,000 scenario

Assign KES 30,000, record a payment of KES 10,000, and **KES 20,000.00** is now the same figure in:

| Path | Asserted |
|---|---|
| `FeeBalanceService::balanceForStudent()` | = 20,000.00 |
| `Student::balance_fee` | = 20,000.00 |
| `Student::fee_summary['balance']` | = 20,000.00 |
| Student statement (`LedgerService::getStudentStatement()['closing']`) | = 20,000.00 |
| Student fee page `/fee-management/{id}` | renders `KES 20,000.00` |
| Student fee summary `/fees/assignments/student/{id}` | renders `KES 20,000.00` |
| Arrears report `/fees/arrears` | renders `KES 20,000.00` |
| Fee dashboard `/fees/dashboard` | renders `KES 20,000.00` |
| Expected-revenue report | renders `KES 20,000.00` |

Covered additionally: full payment (0.00), partial, multiple payments, **reversed payment**
(excluded from paid everywhere), **split/allocation payment** (attributed once, SQL and PHP agree),
**overpayment/credit** (`KES -5,000.00` shown as a credit), **previous-year balance** (25,000 total
vs 20,000 for the current year on the year-scoped arrears report), and **discount** via
`final_amount` (25,000 net − 10,000 = 15,000).

---

## D. Phase 2 — status of every original issue

| Issue | Status | Evidence |
|---|---|---|
| **C4** `/fees/adjustments/pending` route shadowing | Already fixed; **now proven** | `test_pending_adjustments_route_is_not_shadowed_by_the_id_route` asserts the real route-collection order (not sorted `route:list` output) and that the screen renders. |
| **C6** Payment-Method drill-down (`$grandTotal` 500, `$byDay` never rendered) | Already fixed; **now covered** | `payment method drilldown renders the daily breakdown` — 200, `Daily Trend`, `Grand Total`. |
| **C7** Receipt Register (no print/export, missing view, no route) | Already fixed; **now covered** | Button `Print / Export PDF` + working route + `receipt register export returns a pdf` (asserts the `%PDF` magic number). |
| **H1** Refund completion had no confirmation | Already fixed; **now covered** | `refund completion asks for confirmation`. |
| **H2** Fee Category edit route name | Already fixed; **a further defect found and fixed** | See §E — the update form could never save unchanged fields. Both cases now covered. |
| **H4** Discount Summary | Renders 200; **now covered** (previously zero test coverage) | The suspected `->total()` on a Collection did not reproduce. |
| **M1** Collections tables lacked totals | Already fixed | `tfoot` totals present on the By Method / Latest Payments tables. |
| **M2** Adjustment approve/reject had no confirmation | Already fixed; **now covered** | `adjustment approval and rejection ask for confirmation`. |
| Payment reversal confirmation | Already fixed; **now covered** | `payment reversal asks for confirmation`. |
| Receipt register totals match the screen | Verified | Both the screen and the PDF are driven by the same controller branch and filter set. |

---

## E. Fee Management UI audit — findings and fixes

### E1. The empty square/box glyphs — three causes, all fixed at the root

No `display:none` was added anywhere. The layout loads **Font Awesome 5.14.0** from cdnjs
(`vendor/infyomlabs/laravel-ui-adminlte/.../adminlte-layout.blade.php:11`), and the project ships
**no webfonts of its own** — the app bundle contains no Font Awesome at all. Three separate causes
were found: by rendering every fee screen, by validating each icon against Font Awesome's own
`metadata/icons.json` for 5.14.0, and by sweeping every stylesheet the layout loads for rules that
inject a `content` glyph.

**(a) Two icons request a style that has no glyph (the boxes on every page).**
FA 5.14.0 Free ships a small Regular subset. `far fa-sms` and `far fa-briefcase` request the
Regular style for icons FA 5.14.0 records as `styles: ["solid"]` only, so the Regular webfont has
no glyph for that code point and the browser paints an empty box. Both are in the shared
**`config/menu.php`**, which is exactly why the box appeared on *every* Fee Management page rather
than on one screen. Fixed at that shared root (`far` → `fas`, same icon, same slot, same colour).

**(b) Nine Font Awesome 6 icon names used against FA 5.14.0.**
These names do not exist in 5.14.0 at all, so they also render as boxes:

| Broken (FA6) | Correct (FA 5.14.0) | Where |
|---|---|---|
| `fa-scale-balanced` | `fa-balance-scale` | `assignments/index:466`, `reports/assignment_status:361`, `reports/expected_revenue:201` |
| `fa-magnifying-glass` | `fa-search` | `assignments/index:541`, `reports/assignment_status:435`, `reports/discount_summary:247` |
| `fa-pie-chart` | `fa-chart-pie` | `reports/collections:106` |
| `fa-chart-simple` | `fa-chart-bar` | `reports/expected_revenue:211` |
| `fa-calendar-xmark` | `fa-calendar-times` | `reports/payment_method:336` |

Worth knowing for whoever reads icon code next: the app has **two** Font Awesome generations.
Standalone pages load **6.4.0** (`auth/login.blade.php:15`, `setup.blade.php:16`) and correctly use
FA6 vocabulary (`fa-solid`, `fa-regular`); everything routed through the AdminLTE layout — including
every Fee Management screen — gets **5.14.0**. FA6 names copied from those pages into a fee view are
exactly what this defect was.

**(c) Two of the module's own class names are also Font Awesome icon names — the `::before` boxes.**

This is the defect reported from the screenshot: the stray box beside the table header and the one
inside the student search field. Font Awesome declares one rule per icon, shaped like

    .fa-table:before { content: "\f0ce"; }        /* specificity (0,1,1) */

and that selector fires on **any** element carrying the class — it never checks whether the element
is an icon. The Fee Management views prefix their own cosmetic classes with `fa-` (105 of them), and
two of those names happen to be real icon names:

| Module class | The element the module puts it on | Rule it collides with |
|---|---|---|
| `fa-table` | `<table class="table fa-table mb-0">` — 16 tables across 8 views | `.fa-table:before { content: "\f0ce" }` |
| `fa-search` | `<div class="fa-search">` — 4 views | `.fa-search:before { content: "\f002" }` |

Neither element is an icon and neither sets the Font Awesome font, so the injected glyph is drawn in
the inherited text font — an empty box. On a `<table>`, CSS table fix-up wraps the pseudo-element in
an anonymous row and cell, which is why the box lands at the top-left of the table rather than inline
with the text.

Fixed once, in the shared stylesheet, with no markup change:

    table.fa-table::before,
    div.fa-search::before {
        content: none;
    }

`content: none` stops the pseudo-element being generated at all. The element name in each selector is
doing two jobs at once: it keeps real icons out of scope — an icon in this codebase is always
`<i class="fas fa-…">`, so `fas fa-table` and `fas fa-search` keep their glyph everywhere else in the
ERP — and it lifts the selector to (0,1,2) against the library's (0,1,1), so the cancellation wins on
specificity rather than on which stylesheet happens to load first. No design, colour, size or spacing
value changed.

**Verification.** Sweeping all three stylesheets the layout loads — FA 5.14.0 from cdnjs, the compiled
AdminLTE/Bootstrap bundle (`public/build/assets/app-ZxWf6FO3.css`, 9,443 rules) and
`public/css/sidebar-fixed-final.css` — for rules that inject `content` into any of the module's 105 own
classes returns exactly three: Font Awesome's two, plus this cancellation. Nothing else decorates them.
Resolving the cascade for the two real elements:

    <table class="table fa-table mb-0">
      (0,1,1)  .fa-table:before         content: "\f0ce"
      (0,1,2)  table.fa-table::before   content: none      <- applies
    <div class="fa-search">
      (0,1,1)  .fa-search:before        content: "\f002"
      (0,1,2)  div.fa-search::before    content: none      <- applies

`FeeUiConsistencyTest` pins all three parts of this: the rule and its `content: none`, the element
scoping the fix depends on, and that the stylesheet carrying it reaches the fee screens.

**Verification (a and b).** Every fee screen was rendered through the real HTTP kernel and every icon
on it validated against the FA 5.14.0 metadata: **18 screens, 3,085 icons, 0 that can render as a
box.**
Project-wide, every `far`/`fab` usage in the fee module, `config/menu.php`, all views and all app
code now resolves to a glyph that FA 5.14.0 actually ships.

A note on the earlier false alarm: an initial check inferred style coverage from the stylesheet and
reported ten `far` icons as broken. That inference was wrong — `all.min.css` declares each icon once,
style-agnostically. Only `sms` and `briefcase` genuinely lack a Regular glyph; `far fa-eye`,
`far fa-edit`, `far fa-trash-alt` and the rest are valid and were left alone.

A blanket `display:none` on pseudo-elements was **not** used either — it would have masked (c) rather
than fixed it, and hidden any future one. The module's own pseudo-elements (`.fa-pill--*::before`) and
the sidebar rail (`.nav-sidebar .nav-link::before`) are untouched; the cancellation names its two
selectors explicitly, because those are the only two Font Awesome actually fires on.

Two further broken references exist **outside** Fee Management and were deliberately left alone:
`bank_accounts/show_fields.blade.php:35`, `exam_dashboard/index.blade.php:106`.

### E2. Search icon overlapping the placeholder — CSS specificity defect

A real defect, with two separate causes.

**(a) The icon's reserved space was being overridden.** Each affected view declared, in this order:

```css
.fa-filters select.form-control,
.fa-filters input.form-control { padding: 0 2rem 0 0.75rem !important; ... }   /* (0,2,1) */
...
.fa-search > i           { position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); }
.fa-search .form-control { padding-left: 2.15rem !important; }                 /* (0,2,0) */
```

`.fa-filters input.form-control` is two classes plus an element — specificity (0,2,1) — while
`.fa-search .form-control` is only (0,2,0). Both used `!important`, so **specificity decided and the
input kept `padding-left: 0.75rem`**, leaving the icon (at `left: 0.8rem`) sitting on top of the
placeholder text instead of in reserved space beside it. This is the root cause, not a positioning
problem: `position: relative` on the wrapper and `translateY(-50%)` on the icon were already correct.

Fixed at the root in the four affected views by qualifying the selector with the element name so it
ties on specificity (0,2,1) and wins on source order, plus beating the generic rule outright inside a
filter bar (0,3,1):

```css
.fa-search input.form-control,
.fa-filters .fa-search input.form-control { padding-left: 2.15rem !important; }
```

No `!important` was added or escalated, and **no design, colour, size or spacing value changed** —
the padding the author intended simply takes effect.

**(b) `fa-magnifying-glass`** — see E1(b). The heading next to the search box used an FA6 name, so a
box was drawn where the magnifier should be.

Rendered pages confirm the cascade now resolves correctly: `/fees/assignments`,
`/fees/reports/receipt-register`, `/fees/reports/discount-summary` and
`/fees/reports/assignment-status` all report `search-padding: icon padding wins`.

### E3. Money presentation

The module mixed `25,000`, `KES 25,000` and `KES 25,000.00` for the same figures. Fixed:

- **12 cells** rendered money with `number_format($x, 0)` — whole shillings — inside tables that
  showed cents in the very next column (`assignments/index`, `reports/assignment_status`). Their
  headers said `Billed` / `Collected` / `Balance` with **no currency at all** and their metric labels
  said `Billed (KES)` while the value dropped the cents. All now use `Money::format()` and the
  redundant `(KES)` was removed from the labels.
- **`student_summary.blade.php`** used bare `number_format($x)` — one argument means **zero decimals**
  — for nine money values, with the currency symbol nowhere adjacent. All now `Money::format()`.
- The "zero" branch of the balance column rendered a bare `0` next to `KES x,xxx.00` cells; now
  `Money::format(0)`.
- The delete-confirmation modal built its amount with `'KES ' + raw value`, printing e.g. `KES 5231.5`
  with no thousands grouping. It now groups and fixes to two decimals.
- A final sweep for the single-argument `number_format` form found only counts
  (`fully_paid_students`, `methods_used`, `today_payers`) — no money left unformatted.

### E4. Missing reconciliation total

The fee dashboard showed **Expected Revenue** and **Collected** but no **Outstanding** figure, so a
bursar had to subtract by hand. The Expected Revenue card footer — which merely repeated "Collected",
already shown on the adjacent Collection Rate card — now shows **Outstanding**, derived from those same
two figures so the dashboard cannot disagree with the arrears report.

### E5. Fee Category edit could never save unchanged fields

`UpdateFeeCategoryRequest` reused `FeeCategory::$rules` verbatim, including
`unique:fee_categories,name`, with no ignore-id. A unique rule with no ignore treats the row being
edited as a duplicate of itself, so submitting Edit without changing the name failed validation
("The name has already been taken") and a category's **type, status or description could never be
changed on its own**. The rule now ignores the record being updated. Both cases are covered:
renaming and editing-without-renaming.

### E6. Verified good (unchanged)

All fee report pages render 200 (payment method, its drill-down, receipt register, discount summary,
expected revenue, assignment status, arrears, collections); the receipt-register export produces a
real PDF; `/fees/adjustments/pending` resolves to the pending-approvals controller; every dangerous
action (reversal, refund completion, adjustment approve/reject) asks for confirmation **and** is
independently guarded on the backend.

---

## F. Authorization

- `FeeAuthorizationTest` static check passes: **every public method on every fee controller is covered
  by a permission guard**, so a newly added unguarded method fails the suite.
- Runtime: Teacher, Parent and Student receive **403** on all 20 previously-unprotected endpoints
  (payment reversal, adjustment reject/approve/audit-log, pending queue, student adjustments, the
  assignment AJAX endpoints, student summary, unassigned list, refund payment history, whole-school
  ledger exports, fee-category code generation) and on term activation.
- Accountant / Admin / Super Admin / Owner retain access to the fee endpoints they own.
- **Term activation requires `academics.settings.manage`** — deliberately not held by the Accountant,
  because activating a term changes what the whole application treats as the current term. The fee
  module's own Terms menu entry advertises the same permission, and
  `test_the_fee_terms_menu_entry_advertises_the_permission_the_route_enforces` locks that agreement.
- No view fix weakened any middleware or permission.

---

## G. Source of truth

`FeeBalanceService` is the single definition of a student's balance:

```
outstanding = active charges (final_amount) − valid payments (reversal-aware)
payments are attributed via payment_allocations when present, else via the direct
student_fee_assignment_id — the same rule in PHP and, via paidTotalsSubquery(), in SQL
```

Everything now derives from it:

- `FinanceService` → dashboard and finance metrics (`recomputeAssignment`, `collectionRate`)
- `Student::feeSummaryCache` → `balance_fee`, `paid_fee`, `fee_summary`
- Student fee summary screen, student fee status report, arrears report, mobile admin badge
- `LedgerService::getStudentStatement()` — the statement's **closing** figure is derived from
  `balanceForStudent()`, and its opening line is the honest balancing figure for charges that predate
  the ledger. The ledger is therefore a supporting journal, never a competing balance.

`payment_allocations` is the attribution layer for split payments; the direct FK remains the fallback
for the 209 historical payments that have no allocation rows, so no backfill was needed or performed.
Reports that must aggregate in SQL use `paidTotalsSubquery()`, and a test pins it to the PHP
definition so the two cannot drift.

---

## H. Files changed

**Phase 3 (financial):**

| File | Change |
|---|---|
| `app/Services/FeeBalanceService.php` | Added `paidTotalsSubquery()` — the paid-per-assignment rule as a derived table for reports that aggregate in SQL. |
| `app/Http/Controllers/FeeArrearsController.php` | Added `paidTotals()`; all three payment subqueries use it; headline Expected/Collected/Outstanding computed from the same definition as the rows. |
| `app/Services/AdminMobileHomeService.php` | Uses the shared paid definition. |
| `app/Http/Controllers/StudentFeeAssignmentController.php` | `studentSummary()` uses `FeeBalanceService::summaryForStudent()`. |
| `app/Http/Controllers/StudentReportController.php` | Fixed a `pluck()` of a non-existent column (`student_fee_assignment_id` → `id`) that made every learner show `total_paid = 0`; paid now via `paidForAssignments()`. |
| `app/Http/Controllers/DashboardController.php` | Five fee-payment aggregates: `created_at` → `payment_date`, and `notReversed()` applied. |
| `app/Services/LedgerService.php` | `getStudentStatement()` derives its opening line from the balance service; `seedOpeningBalance()` removed; legacy bootstrap rows folded in. |
| `app/Http/Controllers/FeeManagementController.php` | No longer seeds ledger rows when rendering a statement. |
| `app/Http/Requests/UpdateFeeCategoryRequest.php` | Unique-name rule ignores the record being updated. |

**Views (UI):** `fee_management/assignments/index.blade.php`,
`fee_management/assignments/student_summary.blade.php`,
`fee_management/reports/{assignment_status,expected_revenue,discount_summary,collections,payment_method}.blade.php`,
`fee_management/dashboard.blade.php`.

**Shared navigation (the box glyphs):** `config/menu.php` — `far fa-briefcase` and `far fa-sms`
changed to `fas`, the style FA 5.14.0 actually ships a glyph for. One shared file, so the fix
covers every module's menu rather than patching fee pages individually.

**Search-field cascade fix (root, not per page):** the `.fa-search` icon-padding selector in
`fee_management/assignments/index.blade.php`, `fee_management/reports/assignment_status.blade.php`,
`fee_management/reports/discount_summary.blade.php` and
`fee_management/reports/receipt_register.blade.php`.

**Shared stylesheet (the `::before` boxes):** `public/css/sidebar-fixed-final.css` — one rule cancelling
Font Awesome's injected pseudo-element on `table.fa-table` and `div.fa-search`. No fee view markup
changed, and no design, colour, size or spacing value changed.

**Tests added:** `tests/Feature/FeeReconciliationTest.php` (14 tests),
`tests/Feature/FeeScreensSmokeTest.php` (12 tests), `tests/Feature/FeeUiConsistencyTest.php` (5 UI
guards: no icon may request a Font Awesome style that ships no glyph; every search field must reserve
space for its icon; the `::before` cancellation exists and cancels; the element scoping that
cancellation depends on still holds; and the stylesheet carrying it reaches the fee screens).
**Tests corrected:** `tests/Feature/FeeAuthorizationTest.php`.

---

## I. Phase 4 findings (no migration applied)

| # | Finding | Detail |
|---|---|---|
| 1 | **Duplicate fee assignments** | **None.** Zero duplicates under all four candidate keys — `(student, fee_structure)`, `+academic_year_id`, `+term_id`, `+term` — and no NULL `academic_year_id`, `term_id` or `fee_structure_id` in 263 rows. A unique index is therefore data-safe. |
| 2 | **Proposed unique key** | `(student_id, fee_structure_id, academic_year_id, term)`. Prefer the `term` **string** over `term_id`: `term_id` is `SET NULL` on term deletion and MySQL treats NULLs as distinct, which would let duplicates back in. The existing index on `(student_id, academic_year_id, term)` is non-unique. |
| 3 | **`bank_transactions.amount` precision** | Live column is `decimal(10,2)`, **but the creating migration declares `decimal('amount', 10)`** → `DECIMAL(10,0)`. A fresh install would silently round bank amounts to whole shillings — exactly the trap that produced migration `2026_09_21_000001` for `fee_payments`. Needs a matching `ALTER` migration. `refunds.amount` is `decimal(15,2)`, inconsistent with the `decimal(10,2)` used elsewhere. |
| 4 | **Refund cascade delete** | `refunds.student_id`, `ledger_entries.student_id` and `fee_adjustments.student_id` are all `ON DELETE CASCADE`. `students` uses soft deletes (`deleted_at` present), so an ordinary delete is safe; only a **hard/force delete** destroys financial history. Recommend restricting the FK and archiving instead, before any force-delete path is used. |
| 5 | **`fee_payments.student_fee_assignment_id` is `SET NULL`** | Deleting an assignment keeps the payment row but drops its link. With no allocation rows, that payment would stop counting toward the balance. Worth reviewing together with #1. |
| 6 | **Live refund anomaly** | Refund #1 = **200,000** for student 5, whose total recorded payments are **28,900**, with `bank_account_id = NULL` and a 200,000 credit in the ledger. `Refund::$rules` requires only `student_id`, `amount ≥ 0.01` and `reason` — there is **no cap** on refunds. Recorded as a data/control finding; nothing was rewritten. |
| 7 | **Allocation layer** | Still 0 allocation rows against 209 payments. The path is now covered by tests (split/TOTAL payment), so its first real use is no longer untested — but no historical allocation was created. |

---

## J. Remaining issues

1. **Refund controls need a product decision.** (a) A completed refund currently does not reduce the
   student's outstanding balance (the ledger records it, the balance service does not) — defensible as
   an exit/overpayment event, but it must be stated. (b) The ledger records a refund as a *credit*,
   i.e. "reduces what is owed", whereas money leaving the school means the account owes more again.
   (c) Nothing caps a refund at the amount actually paid. Changing any of these moves live figures
   (student 5 would go from 3,300 to 203,300 under interpretation (a)+(c)), so it is reported rather
   than changed.
2. **Scoping difference between screens.** The arrears report is scoped to one academic year (default:
   current) while the student profile and statement are all-time. Documented in
   `test_previous_year_balance_is_included_in_the_student_position`; needs a product decision on
   whether arrears should offer an all-years view.
3. **`finance.export` / `finance.import` are referenced but never seeded** (`FinancePolicy`,
   `config/menu.php`). The effective permanent-403 was already fixed by pointing
   `FinancialReportController` at `finance.view`, but the dead references remain: if anyone wires the
   policy up, exports are denied to everyone.
4. **Optional / one-time fee semantics are not enforced at billing time** (`fee_categories.type`,
   `fee_structures.payment_frequency`) — pre-existing, outside Phases 0–3.
5. **Two broken Font Awesome 6 icons outside the fee module** (`bank_accounts/show_fields.blade.php:35`,
   `exam_dashboard/index.blade.php:106`) — not changed, as they are outside module scope.
6. **PDF/print templates and several views still use `KES {{ number_format($x, 2) }}`** rather than
   `Money::format()`. Output is identical, so there is no visible defect, but the helper is not the
   single path it should be.
7. **Empty-state coverage is not complete** across every fee view (tables, filters, modals were checked
   for layout and totals; a per-view empty-state pass was not finished).
8. **Test isolation is weak.** Several suites (e.g. `FeeAuthorizationTest`) mutate roles/permissions
   without `RefreshDatabase`, so running a *filtered subset* can produce false failures. Two suites
   must not share `school_erp_test` concurrently. Worth documenting or isolating in `TestCase`.
