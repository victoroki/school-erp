# Fee Management Module — Audit Report

**Project:** School ERP (Laravel 10.48, PHP 8.2, MySQL 8) — Kenyan school context
**Scope:** Fee Management module and everything directly connected to it
**Date:** 2026-09-23
**Status:** Inspection complete. Fixes **not yet applied** — see the phased plan at the end.

---

## 1. How this audit was carried out

Everything below was verified against the files on disk, the live database, and the existing
test suite. Findings are labelled by how they were confirmed:

- **[V]** = verified directly by me (read the file / ran the query / ran the test)
- **[S]** = found by an automated code scan, plausible and consistent with the code I read,
  but not yet individually re-confirmed

A note on method: my first read of two report views returned an older copy than what is on disk,
which initially produced a misleading result. I re-verified every claim with direct file greps and
hashes before writing this report. No finding below rests on a single stale read.

---

## 2. What the module is built from (map)

| Layer | Files |
|---|---|
| Routes | `routes/web.php:368-452` (`fees.*` group), `routes/web.php:445-452` (legacy `fee-management.*`), `routes/web.php:524-527` (portal), `routes/mobile.php:78-107` |
| Controllers | `FeeManagementController`, `FeeDashboardController`, `StudentFeeAssignmentController`, `FeeAdjustmentController`, `FeeArrearsController`, `FeeReportsController`, `RefundController`, `DiscountSchemeController`, `FeeCategoryController`, `FeeStructureController`, `TermController`, `FinanceDashboardController`, `Portal/PortalFeeController`, `Mobile/MobileFeeController`, `Mobile/MobileFinanceSummaryController` |
| Services | `LedgerService` (append-only ledger), `FeeBalanceService` (single source of truth for balances), `FeeAssignmentService`, `FinanceService`, `BankLedger` |
| Models | `FeePayment`, `StudentFeeAssignment`, `LedgerEntry`, `PaymentAllocation`, `Refund`, `FeeStructure`, `FeeCategory`, `FeeAdjustment`, `DiscountScheme`, `StudentDiscount`, `FeeInvoice` |
| Policies | `FeePolicy`, `FinancePolicy` |
| Views | 42 blades under `fee_management/` + `fee_structures/`, `fee_categories/`, `discount_schemes/`, `finance/` |
| Support | `App\Support\Money` (KES formatting), `App\Console\Commands\SendFeeReminders` |
| Tests | `FeeManagementTest`, `FeeCalculationTest`, `FeeCollectionTest`, `FeeReversalIntegrityTest`, `FeeReversalReportingTest`, `FeeAssignmentBulkRegressionTest`, `FeeTermAssignmentTest`, `MobileFeeIdempotencyTest`, `RefundBankPostingTest`, `MoneyFormattingTest`, `DiscountSchemeFormParityTest`, `RbacVisibilityTest` |

**Live data state** (production database `school_management_system`):

| Table | Rows |
|---|---|
| students | 40 |
| fee_structures | 127 |
| fee_categories | 10 |
| student_fee_assignments | 263 |
| fee_payments | 209 |
| payment_allocations | **0** |
| ledger_entries | **5** |
| fee_adjustments / refunds | 1 / 1 |
| discount_schemes / student_discounts | **0 / 0** |
| fee_invoices | **0** |
| bank_transactions | **0** |

Two of these numbers matter a great deal and are called out again below: **0 allocations against
209 payments**, and **5 ledger entries for 40 students**.

---

## 3. CRITICAL findings

### C1. Payment reversal is not protected by any permission **[V]**

- **Location:** `routes/web.php:426-427` → `FeeManagementController::reverseForm` / `reversePayment`; guard list at `app/Http/Controllers/FeeManagementController.php:19-21`
- **Problem:** The constructor guards only `index,show,print` (`fees.view`), `printReceipt` (`fees.print`) and `collect,collectPayment,storePayment` (`fees.collect`). The two reversal methods appear in **none** of those lists, so they carry no permission middleware at all.
- **Impact:** Any authenticated user — Teacher, Parent, Student — can reach
  `GET/POST /fees/payments/{payment}/reverse` and void a real payment. This is a direct
  financial-loss and data-integrity hole, exactly the "type the URL manually" case you asked about.
- **Evidence:** `$this->middleware('can:fees.collect')->only(['collect', 'collectPayment', 'storePayment']);` — no entry for `reverseForm`/`reversePayment`.

### C2. Fee adjustment *reject* is ungated (approve is gated, reject is not) **[V]**

- **Location:** `routes/web.php:390`; `app/Http/Controllers/FeeAdjustmentController.php:19-21`
- **Problem:** `can:fees.approve` is bound to `only(['approve'])`. `reject` is missing.
- **Impact:** Any authenticated user can kill a legitimate adjustment request by POSTing to
  `/fees/adjustments/{id}/reject`. Asymmetric: the "yes" is protected, the "no" is not.
- **Evidence:** `$this->middleware('can:fees.approve')->only(['approve']);`

### C3. Other fee methods with no permission middleware **[V]**

Each of these routes exists and none of the listed methods appears in its controller's guard lists:

| Route | Controller method | Should require |
|---|---|---|
| `fees.payments.reverse-form` / `.reverse` | `FeeManagementController::reverseForm` / `reversePayment` | `fees.manage` or `fees.approve` |
| `fees.adjustments.reject` | `FeeAdjustmentController::reject` | `fees.approve` |
| `fees.adjustments.pending` | `FeeAdjustmentController::pendingApprovals` | `fees.view` (or `fees.approve`) |
| `fees.adjustments.student-adjustments` | `FeeAdjustmentController::studentAdjustments` | `fees.view` |
| `fees.adjustments.audit-log` | `FeeAdjustmentController::auditLog` | `fees.view` |
| `fees.adjustments.ajax.student-fees` | `FeeAdjustmentController::getFeeAssignmentsForStudent` | `fees.view` |
| `fees.assignments.student-summary` | `StudentFeeAssignmentController::studentSummary` | `fees.view` |
| `fees.assignments.unassigned` | `StudentFeeAssignmentController::unassigned` | `fees.view` |
| `fees.assignments.ajax.*` (4 routes) | `getFeesByClass`, `getFeesByClasses`, `getAutoAssignmentPreview`, `getAllFeeStructures` | `fees.view` |
| `fees.refunds.ajax.student-payments/{studentId}` | `RefundController::studentPayments` | `fees.view` |
| `fees.terms.activate` | `TermController::activate` | `academics.settings.manage` (see C5) |
| `fee-management.export-pdf` / `.export-excel` | `FeeManagementController::exportPdf` / `exportExcel` | `fees.view` + `fees.print` |
| `fee-categories.generate-code` | `FeeCategoryController::generateAutoCode` | `fees.manage` |

- **Impact:** Teachers, Parents and Students (who hold no `fees.*` permission) can read whole-school
  fee ledgers, student balances, pending approval queues, adjustment audit logs and per-student
  payment histories — and can download them.
- **Evidence:** e.g. `StudentFeeAssignmentController.php:25-26` binds `fees.view` to
  `only(['index', 'show'])` and `fees.manage` to `only(['create', 'store', 'edit', 'update', 'destroy'])`;
  `studentSummary`/`unassigned`/the four AJAX methods are absent from both.

### C4. `/fees/adjustments/pending` is unreachable — route shadowing **[V]**

- **Location:** `routes/web.php:388` (`GET /adjustments/{id}`) is registered **before** `routes/web.php:391` (`GET /adjustments/pending`)
- **Problem:** Laravel matches in registration order, so the literal `pending` is captured as `{id}` and routed into `show()`.
- **Impact:** The "Pending Approvals" screen cannot be opened — it either 404s (no adjustment with id "pending") or shows the wrong record, depending on the route-model binding. A real, dead screen.
- **Evidence:** `Route::get('/adjustments/{id}', ...)` at line 388 precedes `Route::get('/adjustments/pending', ...)` at line 391.

### C5. Terms are gated by an academic permission, and `activate` by nothing **[V]**

- **Location:** `TermController.php:17-18`; `routes/web.php:404`
- **Problem:** `can:academics.settings.manage` protects index/show/create/store/edit/update/destroy, but `activate` is in neither list.
- **Impact:** (a) The **Accountant** — who holds every `fees.*` permission and is the person who owns fee terms — cannot manage terms; (b) any authenticated user can POST `/fees/terms/{id}/activate` and silently change the school's active term, which changes what "current term fees" means everywhere.
- **Evidence:** `$this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);`

### C6. Payment-Methods report drill-down throws a 500 **[V]**

- **Location:** `resources/views/fee_management/reports/payment_method.blade.php:46,69,93` vs `app/Http/Controllers/FeeReportsController.php:589-591`
- **Problem:** The view renders `$grandTotal` (header, share calculation, footer total), but the detail branch of `paymentMethod()` returns
  `compact('stats', 'methodLabels', 'academicYears', 'yearId', 'detailMode', 'byDay', 'methodFilter') + ['byMethod' => collect()]` — no `$grandTotal`.
- **Impact:** Opening `/fees/reports/payment-method?detail=1&payment_method=cash` — the report's own
  drill-down link — produces an "Undefined variable $grandTotal" 500 instead of a per-day breakdown.
  The controller also computes and passes `$byDay`, but the view never renders it, so the promised
  per-day drill-down does not exist even once the crash is fixed.
- **Evidence:** controller line 589-591 (compact list above); view line 46 `KES {{ number_format($grandTotal, 2) }}`.

### C7. Receipt register cannot be printed, and its export endpoint is broken **[V]**

- **Location:** `resources/views/fee_management/reports/receipt_register.blade.php` (no export button anywhere); `FeeReportsController.php:749` → `Pdf::loadView('fee_management.reports.exports.receipt_register_pdf', ...)`
- **Problem:** (a) The view contains **no** export/print control at all — grep for `export|pdf|Print` returns zero matches, unlike its sibling reports; (b) `exportReceiptRegisterPdf()` loads a view that **does not exist** — `resources/views/fee_management/reports/exports/` contains only `expected_revenue_pdf`, `assignment_status_pdf`, `discount_summary_pdf`; (c) no route is registered for it in `routes/web.php:410-418`.
- **Impact:** A bursar or accountant cannot print or export the receipt register — the single most requested end-of-day document. The method is unreachable dead code that would 500 if it were wired up.
- **Evidence:** directory listing of `reports/exports/` (3 files); `routes/web.php:416-418` lists only three export PDF routes.

---

## 4. HIGH findings

### H1. "Complete Refund" — the action that moves money out — has no confirmation **[V]**

- **Location:** `resources/views/fee_management/refunds/show.blade.php:119`
- **Problem:** The adjacent Reject button at line 91 has `onclick="return confirm('Reject this refund?')"`; the Complete Refund submit at line 119 has none.
- **Impact:** One stray click on the last step of the refund workflow pays money out of a bank
  account (it decrements the account balance and writes a withdrawal row). Irreversible without a compensating entry.
- **Evidence:** line 91 vs line 119 as quoted.

### H2. Fee Category **Edit** form posts to a route name that does not exist **[S]**

- **Location:** `resources/views/fee_categories/edit.blade.php:22` vs `routes/web.php:53`
- **Problem:** The form uses `route('fee-categories.update', ...)`, but the resource is registered as
  `Route::resource('fee-categories', ...)->names('feeCategories')`, so the real name is `feeCategories.update`. (index/table/create use the correct `feeCategories.*`.)
- **Impact:** Opening Edit for a fee category throws `RouteNotFoundException` — fee categories can never be edited once created. Only the destructive delete path works.
- **Evidence:** `{!! Form::model($feeCategory, ['route' => ['fee-categories.update', $feeCategory->category_id], 'method' => 'patch']) !!}`

### H3. Expected-Revenue report: view/controller mismatch (currently in flux) **[V]**

- **Location:** `resources/views/fee_management/reports/expected_revenue.blade.php` vs `FeeReportsController::expectedRevenue()` (`:33-136`), failing test `RbacVisibilityTest::test_expected_revenue_report_requires_only_fees_view`
- **What I observed:** The failing test reported HTTP 500 with `Undefined variable $totalOriginal`
  against the **older** copy of this view (277 lines, using `$totalOriginal` / `$revenueByClass` /
  `$paymentStatusBreakdown`). The copy now on disk (497 lines, hash `EFC84601…`) has been rewritten
  to use `$stats` / `$rollups` / `$categories`, matching the controller.
- **Impact (current, unconfirmed):** The page may already be fixed — **I could not confirm this**,
  because the verification test run was cancelled before it finished (see §7). Either way this view
  and its controller have a history of drifting apart, which is a maintenance hazard worth pinning
  down with a test.
- **Evidence:** runtime log `[2026-09-23 09:45:42] testing.ERROR: Undefined variable $totalOriginal`
  with `"view":"…\fee_management\reports\expected_revenue.blade.php"`.

### H4. Discount Summary report has the same mismatch shape **[S]**

- **Location:** `resources/views/fee_management/reports/discount_summary.blade.php` (382 lines) vs `FeeReportsController.php:317-343`
- **Problem:** Reported to reference `$totalDiscounts`, `$totalOriginalForDiscounted`, `$discountSchemes` and `$discounts->total()` / `$discounts->hasPages()`, while the controller passes `$stats`, `$schemes`, `$discounts = collect()`. Calling `->total()` on a `Collection` is a fatal `BadMethodCallException`.
- **Impact:** If confirmed, the Discount Summary screen is a hard 500 and no discount/scholarship reporting is possible.
- **Status:** Do not trust this without a run — **the same "view drifted from controller" pattern is real (H3), and this is the next most likely instance.** Verify with the route before fixing.

### H5. Live data shows the allocation layer is entirely unused **[V]**

- **Location:** `payment_allocations` — **0 rows** against **209 payments**; `ledger_entries` — **5 rows** for 40 students
- **Problem:** `FeeBalanceService` deliberately supports two attribution models: allocation rows when
  present, otherwise the payment's direct `student_fee_assignment_id`. With zero allocations, every
  live figure comes from the direct-FK path, which means the allocation code path (including
  `LedgerService::allocatePayment`, `recommendAllocation`, and the whole "pay total balance across
  several fees" flow) has **never been exercised against real data**.
- **Impact:** The very first time a bursar uses "Pay total balance", the system writes allocations and
  switches behaviour for that student. Any latent defect there lands on live money. The existing
  `FeeReversalIntegrityTest` covers reversal, but the allocation split itself needs a real test.
- **Evidence:** row counts as listed; `FeeBalanceService::paidForAssignments()` (`:46-70`) shows the two paths.

### H6. The student ledger is not maintained for most students **[V]**

- **Location:** `LedgerService::seedOpeningBalance()` (`:344-371`) called from exactly one place: `FeeManagementController::show()` (`:115`)
- **Problem:** Opening balances are bootstrapped lazily, only when somebody happens to open that one
  student's legacy detail page. Every other screen — dashboard, arrears, reports, the portal — computes
  from `FeeBalanceService` instead.
- **Impact:** Two parallel truths. A student's **statement page** (built from `ledger_entries`) and the
  **arrears report** (built from `fee_payments` + `student_fee_assignments`) can legitimately disagree
  until someone visits the statement page. With 5 ledger rows for 40 students, that disagreement is
  the normal state right now, not an edge case. This is the "numbers must stay consistent throughout
  the system" requirement failing on real data.

### H7. `Student::fee_summary` counts reversed payments **[V]**

- **Location:** `app/Models/Student.php:558-568`
- **Problem:** `getFeeSummaryAttribute()` computes `$totalPaid = $this->payments()->sum('fee_payments.amount')` — a raw sum that includes reversed payments.
- **Impact:** Directly contradicts the invariant documented on `FeePayment::scopeNotReversed()`
  ("every balance, collection total and report figure must apply this scope") and the intent of
  `FeeBalanceService`. `$student->paid_fee` and `$student->fee_summary['total_paid']` return different
  numbers for any student with a voided receipt. Any caller using the array form inherits the error.
- **Evidence:** line 561 as quoted; compare `getPaidFeeAttribute()` (`:250-253`) which routes through `FeeBalanceService`.

### H8. `finance.export` and `finance.import` permissions do not exist **[V]**

- **Location:** `FinancialReportController.php:17` (`can:finance.export`), `app/Policies/FinancePolicy.php:26,31`, `config/menu.php:435,474,476`
- **Problem:** The seeded set is exactly `fees.{view,manage,approve,collect,print}` and
  `finance.{view,manage,approve}`. `finance.export` and `finance.import` are referenced but never created.
- **Impact:** The cash-flow report (`financial-reports/cashflow`) is a permanent 403 for **everyone**,
  including Owner, Super Admin and Accountant — the same class of bug the `fees.export` regression
  documented in `RbacVisibilityTest` was. Menu entries referencing the missing permissions likewise never appear.
- **Evidence:** permission list from `database/seeders/*`; grep hits as listed.

### H9. Duplicate assignment-protection logic with no database constraint **[V]**

- **Location:** `FeeAssignmentService::bulkAssign()` (`:264-319`), `autoAssignFeesToAllStudents()` (`:194-253`), `FinanceService::batchAssignFee()` (`:47-88`)
- **Problem:** Duplicate protection is a `SELECT ... exists()` check followed by a separate `INSERT`,
  and there is **no unique index** on `student_fee_assignments` for
  `(student_id, fee_structure_id, academic_year_id, term)` — the migration (`2026_02_06_125058`) creates only
  non-unique indexes `['student_id','academic_year_id','term']` and `['fee_structure_id']`.
- **Impact:** Two staff running "assign fees to class" at the same time can create duplicate charge
  rows for the same student and fee, doubling what that student owes. Nothing at the database layer
  prevents it. This is your edge case 14 applied to billing rather than payment.
- **Evidence:** migration index list; `exists()`-then-`create()` pattern at `FeeAssignmentService.php:291-309`.

### H10. Two services implement student fee assignment with different semantics **[V]**

- **Location:** `FinanceService::assignFeeToStudent()` (`:18-42`) and `FeeAssignmentService::assignFeeToStudent()` (`:17-38`)
- **Problem:** Two public methods with the same name do the same job, but `FinanceService` defaults the
  academic year and term from the fee structure and resolves `term_id` by looking up `Term.code`,
  while `FeeAssignmentService` takes them as required arguments and resolves `term_id` the same way
  but from `fee_structure_id` vs `fee_structure_id` inconsistently in its callers.
- **Impact:** Which figure a screen shows depends on which service the caller reached for — the classic
  root cause of "the same student's balance differs between screens" that the module has clearly fought before.

---

## 5. MEDIUM findings

| # | Location | Problem | Impact |
|---|---|---|---|
| M1 | `fee_management/reports/collections.blade.php:109-153` | "By Method (Filtered)" and "Latest Payments" tables have no totals row (sibling `payment_method.blade.php:88-96` does) **[V]** | Bursar adds columns by hand to reconcile a day's cash |
| M2 | `fee_management/adjustments/show.blade.php:150,179` | Neither Approve nor Reject has a `confirm()` **[S]** | Approving permanently rewrites a student's fee with one click |
| M3 | `FeePayment` enum is `cash,check,card,bank_transfer,online`; `fee_management/refunds/show.blade.php:104` offers `mpesa` **[V]** | M-Pesa is not a fee-payment method; the collections view documents "M-Pesa is recorded as Online" (`collections.blade.php:33-37`) | A bursar looking for "M-Pesa" in the collection report must know to look under "Online"; the refund method list uses a different vocabulary than the payment list |
| M4 | `bank_transactions.amount` is `decimal(10)` = `decimal(10,0)` **[V]** | Same whole-shilling precision defect that `2026_09_21_000001` had to repair on `fee_payments` | A refund with cents to a bank account silently rounds at the bank ledger while the student ledger keeps cents — the two ledgers drift |
| M5 | `FeeAdjustmentController` / `StudentFeeAssignmentController` etc. use `fees.view` on `index`,`show` only | AJAX endpoints used **by** those pages are unguarded (part of C3) | After fixing C3 the UI must still work — needs a functional test, not just a middleware added |
| M6 | `refunds.student_id` FK has `cascadeOnDelete()` **[V]** | Deleting a student deletes their refund history | An audit record disappears with the student — contradicts "financial records should not simply disappear" |
| M7 | `Student::getPaymentStatusAttribute()` returns `'Paid'/'Partial'/'Unpaid'/'No Fee'`; `StudentFeeAssignment::getPaymentStatusAttribute()` returns lowercase `'paid'/'partial'/'unpaid'` **[V]** | Two case conventions for the same concept | Any view or report that filters/compares on one but is fed the other silently mismatches |
| M8 | `FeePolicy::viewAny/view` hardcode `['Super Admin','Admin','Accountant']` **[V]** | Role check, not permission check | A school-defined custom role granted `fees.view` would be denied if the policy were ever wired up. Today no fee controller calls `authorize()`, so the policy is decorative — meaning the *only* real gate is the middleware, which is exactly why C1–C3 matter |
| M9 | `FinancialReportController`/`FinancePolicy` reference non-existent permissions (H8) | also `config/menu.php:435` | Menu entries can never render |
| M10 | Currency formatting is inconsistent across screens **[S]** | I saw at least four styles: `Money::format()` (‑8 files), literal `'KES '.number_format($x,2)` (~20 files), bare `number_format($x,2)` with the symbol only in a header (~12), bare `number_format($x,0)` (3), plus JS `'KES ' + toLocaleString()` (3) | `App\Support\Money` exists precisely to stop this ("KES is also the ISO 4217 code") but is applied to only part of the module. Money appears rounded to whole shillings on some reports and with cents on others, on the same data |

---

## 6. What is already done well (so we don't break it)

This module has clearly been hardened before, and the existing design is sound. Worth protecting:

- **`FeeBalanceService` is a genuine single source of truth** with a documented model
  (`outstanding = assigned − valid payments`) and a two-query batch API that avoids N+1.
- **Reversal is modelled properly**: `FeePayment::scopeNotReversed()` is qualified (safe in joins),
  a reversed payment keeps its row and receipt, `reversed_at` is indexed, and the migration notes
  explain exactly why the earlier approach was wrong.
- **Receipt numbers and idempotency keys have real database constraints**: `receipt_number` is
  `unique` and `client_reference` is `unique`, so duplicates are impossible at the storage layer.
- **Money precision was already addressed** for `fee_payments` (`DECIMAL(10,2)`) with a comment
  explaining the `decimal(10)` trap — the pattern simply hasn't been applied to `bank_transactions` yet (M4).
- **The refund workflow is properly staged** (requested → approved/rejected → completed), checks the
  source account balance before paying out, writes the ledger entry and the bank withdrawal in one
  transaction, and logs to `AuditTrail`.
- **`BankLedger` centralises balance + ledger-row writes** in one commit, which is the right shape.
- **`Money`** and `CbeStage`/`GradeBadge`/`TeacherScoped` support classes exist; the naming is settled (KES).
- **The regression suite is substantial** (~80 fee-related assertions pass) and several tests document
  the *reason* for a fix in their comments — that is unusually good and should be extended, not replaced.

---

## 7. Test baseline — and one honest gap

I ran the fee suite before making any changes. Two runs produced (slightly varying) results:

- **Run 1:** `2 failed, 79 passed` · **Run 2:** `3 failed, 78 passed`
- Consistent failures: `FeeReversalReportingTest` → `reversed payment still appears in the register marked void`,
  `receipt register total excludes the voided receipt`; plus `RbacVisibilityTest` →
  `expected revenue report requires only fees_view` (the H3 500) in the second run.

**Gap I must flag:** I started a clean verification run (`view:clear` then the suite) so that the
baseline reflected the *current* files rather than cached compiled views — **that run was stopped
before it finished, so I have no trustworthy post-change baseline yet.** The H3 and H4 statuses are
therefore provisional. The two `FeeReversalReportingTest` failures are more interesting than they look
and are the first thing to re-run: they may be genuine, or they may be stale compiled views like H3.

I have also verified that the earlier test runs executed against a stale compiled view cache, so any
conclusion drawn from them needs one clean re-run once we resume.

---

## 8. Not present in the system (scope honesty)

- **No M-Pesa / payment-gateway integration of any kind.** There is no STK Push, no C2B endpoint, no
  callback handler, no Daraja/Safaricom/Paystack/Flutterwave code anywhere in `app/`, `routes/`,
  `config/` or `resources/`. M-Pesa is recorded **manually** as the `online` payment method, and the
  collections view says so in a comment. So the whole of your §6 — duplicate callbacks, idempotency,
  unmatched transactions, STK push, reconciliation — describes a capability that **does not exist yet**
  rather than one that is broken. Building it is a project, not a fix. What does exist and works is
  manual M-Pesa recording with a reference number, and a unique `client_reference` for
  offline/mobile idempotency.
- **No fee-category "mandatory / optional / one-time / recurring / class-specific / student-specific"
  flags are enforced at billing time.** `fee_categories` has a `type` enum of
  `mandatory|optional`, and `fee_structures` has `payment_frequency` of
  `one-time|termly|monthly|custom`, but `FeeAssignmentService` assigns every matching structure to
  every matching student regardless of either field. Optional fees are therefore billed as if mandatory.
- **No fee statement PDF / print view for parents** in the portal (portal shows on-screen only).
- **M-Pesa-style "unmatched transaction" reconciliation queue** does not exist; `bank_reconciliations`
  exists but is empty (0 rows).

---

## 9. Proposed plan (phased — please confirm before I start changing money code)

I have **not modified any file yet**. Given that this is financial code, I suggest fixing in this order,
verifying after each phase. Each phase is independently shippable.

**Phase 0 — Re-establish a trustworthy baseline (small, do first)**
Clear compiled views, run the full fee suite, and record the true current pass/fail set.
Re-confirm H3 and H4 against the live files. *Deliverable: a dated baseline table.*

**Phase 1 — Close the authorization holes (C1, C2, C3, C5)**
Add the missing middlewares so every financial method is gated, fix the route-shadowing bug (C4) by
moving the literal routes above `{id}`, and align the fee-terms permission with the fee module.
Add a test per previously-ungated route asserting 403 for a Teacher/Parent and 200 for Accountant —
this is the piece that makes the fix real rather than cosmetic. *Deliverable: code + `FeeAuthorizationTest`.*

**Phase 2 — Fix the broken screens (C6, C7, H1, H2, H4, M1, M2)**
Payment-method drill-down (`$grandTotal` + render `$byDay`), receipt-register print/export
(add the missing view + route + button), refund completion confirmation, fee-category edit route,
discount summary, missing totals, adjustment confirmations. *Deliverable: code + a view-render smoke test per report route.*

**Phase 3 — Make the numbers agree everywhere (H5, H6, H7, H10)**
Route `Student::fee_summary` through `FeeBalanceService`; give the ledger a defined relationship with
legacy payments (or state plainly which screen is authoritative and show the same figure on both);
consolidate the duplicated `assignFeeToStudent`; cover the allocation split with a real test.
*Deliverable: code + consistency test asserting the statement, arrears and dashboard agree for one student.*

**Phase 4 — Database integrity (H9, M4, M6)**
Unique index on `student_fee_assignments(student_id, fee_structure_id, academic_year_id, term)` after
checking the live table for existing duplicates, `bank_transactions.amount` → `DECIMAL(10,2)`,
and reconsider the cascade delete on refunds. *Deliverable: migrations + a data-integrity report.*

**Phase 5 — Consistency and polish (M3, M7, M10, and the areas in §8)**
Unify money rendering on `Money::format()` across the module, unify payment-status vocabulary,
decide how M-Pesa is named in the UI, and scope the optional/one-time fee semantics.

Phases 1–3 are the ones that affect money safety and everyday usability, so I would start there.
Phase 4 needs a duplicate check against the live table before any migration touches it, and I will not
make destructive schema changes without showing you the findings first.
