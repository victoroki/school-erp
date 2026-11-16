# Financial Customization — Implementation Summary

> Companion to [`docs/financial-customization-audit.md`](./financial-customization-audit.md).
> This document describes what was actually built, where it lives, and the
> invariants that every cash-moving code path must uphold.
>
> **This document describes the code as it stands.** Where the audit proposed
> something that was implemented differently, the code wins and the difference
> is called out.

---

## 0. What changed, at a glance

The accounting model was **extended, not rebuilt**. There is still no chart of
accounts, general ledger or journal; reports recompute from source tables.
Four capabilities were added on top of that model:

| # | Capability | Where |
|---|---|---|
| 1 | Supplier surface made usable (500 fixed, real PO/payment figures) | `Supplier`, `SupplierController`, `suppliers/show.blade.php` |
| 2 | Purchase-order **payment** step: immediate vs credit, partial payments, payables | `PurchaseOrder`, `PurchaseOrderPayment`, `PurchaseOrderController` |
| 3 | Sponsor / bursary **bulk receipts** allocated to students | `FeeBulkReceipt`, `BulkReceiptService`, `BulkReceiptController` |
| 4 | Financial reports extended (payables, inventory, fees in advance, P&L term filter) | `FinancialReportController` |
| 5 | Fee payments now post to the **bank** (new payments only, no backfill) | `FinanceService`, `LedgerService` |

---

## 1. Architecture

### 1.1 Two ledgers, one source of truth

| Ledger | Owner | Scope | Single source of truth |
|---|---|---|---|
| **Fee Management ledger** (student balances) | Fee Management module | Student fees, discounts, refunds, allocations | `App\Services\FeeBalanceService` |
| **Bank ledger** (bank/cash movement) | Financial Management module | Bank accounts, income, expenses, supplier payments, sponsor receipts | `App\Services\BankLedger` |

The only writer of `bank_accounts.current_balance` and `bank_transactions` is
`BankLedger`. Direct manipulation outside that service is a bug.

### 1.2 The cash-event rule (the single most important invariant)

> **Money enters or leaves the school exactly once, at the moment the money
> actually moves. Distributing money to students or settling a payable must
> never move cash again.**

Concretely:

| Event | Bank posting | Student/supplier effect |
|---|---|---|
| Sponsor bulk receipt created (with a bank account) | one `recordDeposit` | none yet |
| …then allocated to a student | **none** | ordinary `FeePayment` + `payment_allocations` |
| Purchase order received | **none** | stock incremented; a *payable* now exists |
| …then a supplier payment is recorded | one `recordWithdrawal` | `PurchaseOrderPayment` row |
| Fee payment collected (non-cash) | one `recordDeposit` | `FeePayment` + allocations |
| Fee payment reversed | deposit is voided | contra ledger entries, balances recomputed |

This is why `BulkReceiptService::allocateToStudent()` writes `FeePayment` rows
**directly** instead of going through `FinanceService::recordPayment()` — the
latter would post a second deposit. This is asserted by
`BulkReceiptAllocationTest::test_allocate_many_students_without_double_posting_cash()`.

### 1.3 Key tables

| Table | Purpose |
|---|---|
| `fee_bulk_receipts` | Parent record for money received from a sponsor. Holds the amount, the bank destination, and reversal columns. |
| `fee_payments.bulk_receipt_id` | Links a student-level payment to its parent receipt. Ordinary counter payments leave it NULL. |
| `payment_allocations` | **Reused unchanged.** Splits one `fee_payment` across several `student_fee_assignments`. |
| `purchase_orders.payment_arrangement` | `immediate` / `credit` / NULL (legacy row). |
| `purchase_orders.payment_due_date`, `credit_terms`, `paid_date` | Credit terms and settlement. |
| `purchase_order_payments` | One row per payment *or part-payment* towards an order. |
| `fee_payments.bank_account_id` | Where a banked fee payment was deposited. |

---

## 2. Migrations

| Migration | Creates / alters |
|---|---|
| `2026_09_27_000003_add_requisition_id_to_purchase_orders.php` | `purchase_orders.requisition_id` — links a generated PO back to its requisition. Nullable, because POs can be raised directly. |
| `2026_09_28_100001_create_purchase_order_payments.php` | `payment_arrangement`, `payment_due_date`, `credit_terms`, `paid_date` on `purchase_orders`, plus the `purchase_order_payments` table. |
| `2026_09_28_100002_create_fee_bulk_receipts.php` | `fee_bulk_receipts` table + `fee_payments.bulk_receipt_id`. Unique on `(sponsor_name, reference_number)`. |
| `2026_09_28_100003_add_bank_account_to_fee_payments.php` | `fee_payments.bank_account_id` — the fee-to-bank destination. |

**No historical data was backfilled.** Legacy `purchase_orders` keep
`payment_arrangement = NULL`, and legacy `fee_payments` get no bank row. NULL is
deliberately *not* treated as "unpaid": old fulfilled orders must not suddenly
appear as overdue payables, and old receipts must not invent bank movements.

---

## 3. Models

### 3.1 `Supplier` — `app/Models/Supplier.php`

The audit's S-1/U-1 defect: the profile page eager-loaded a relation that did
not exist, so **every** `GET /suppliers/{id}` returned 500.

Added:

- `purchaseOrders()` — the relation whose absence caused the crash.
- `expenses()` — the payee history the expense form has always captured.
- `outstandingPayable()` — money still owed across received orders, computed
  from the payment rows so it cannot drift from what was actually paid.

`outstandingPayable()` uses the shared `PurchaseOrder::scopeReceived()` rather
than a private status list, so the supplier page and the statement of financial
position can never disagree about which orders count.

### 3.2 `PurchaseOrder` — `app/models/PurchaseOrder.php`

Payment arrangement and state:

| Member | Meaning |
|---|---|
| `ARRANGEMENT_IMMEDIATE` / `ARRANGEMENT_CREDIT` | Pay on receipt, or pay later. |
| `payments()` | `hasMany(PurchaseOrderPayment)`. |
| `paidAmount()` | Sum of payment rows. |
| `outstandingBalance()` | `grand_total − paid`, clamped at 0. |
| `derivedPaymentStatus()` | `paid` / `partially_paid` / `overdue` / `unpaid` — **derived, never stored**, so it cannot drift from the payments. |
| `isReceived()` / `scopeReceived()` | Goods have arrived (fully or partially). One definition, shared by all payable reporting. |
| `recordPayment()` | The single transactional write path for paying a supplier. |

`recordPayment()` runs in one transaction with `lockForUpdate()` on the order,
then: validates against the outstanding balance, writes the payment row, and
posts the `BankLedger` withdrawal. The lock is what stops two concurrent
payments from both passing the balance test against the same figure.

Payment status is derived rather than stored — that is why the report, the PO
page and the supplier page always agree.

### 3.3 `PurchaseOrderPayment` — `app/Models/PurchaseOrderPayment.php`

One row per payment or part-payment. Belongs to a PO, optionally a bank
account, and the user who recorded it. Money is moved by
`PurchaseOrder::recordPayment()` in the same transaction that creates the row.

> **Note:** the audit sketch proposed a `status` column and a
> `reversePayment()` action here. Neither was built. Supplier payments are
> currently append-only and cannot be reversed; only the parent PO can be
> re-opened by adjusting. This is a known gap, not an oversight — see
> §10 Remaining issues.

### 3.4 `FeeBulkReceipt` — `app/Models/FeeBulkReceipt.php`

- `SPONSOR_TYPES` — cdf, county_government, ngo, church, donor,
  scholarship_provider, corporate, other.
- `allocations()` / `activeAllocations()` — child `FeePayment` rows.
- `allocatedAmount()` — money given to students via non-reversed payments.
- `remainingAmount()` — `amount − allocatedAmount()`. Reversed children return
  here automatically.
- `canBeReversed()` — only when nothing is allocated. Children must be reversed
  first, so the cash figure and the students' balances stay explainable.

### 3.5 `FeePayment`

Gained `bulk_receipt_id` and `bank_account_id`; both relations added. The
existing `reversed_at` / `scopeNotReversed()` semantics are unchanged.

### 3.6 `GeneratesDailySequenceNumber` — `app/Models/Concerns/`

Shared by `PurchaseOrder` and `Requisition`. Replaces the audit's S-2 defect
(`rand(100,999)`, no uniqueness check) with a sequential `PREFIX-YYYYMMDD-NNN`.

`createWithFreshNumber()` wraps the insert and **retries on a duplicate-key
violation**. A MAX+1 scan is still a read-then-write, so two clerks saving in
the same instant can compute the same candidate; the UNIQUE index is the real
guarantee, and the retry turns a lost save into a transparent second attempt.
Non-collision database errors are re-thrown immediately, never retried.

---

## 4. Services

### 4.1 `BulkReceiptService` — `app/Services/BulkReceiptService.php`

| Method | Behaviour |
|---|---|
| `createReceipt()` | Writes the receipt, and — if a bank account was chosen — posts **one** deposit for the full amount. |
| `allocate()` | Locks the receipt, rejects over-allocation, creates one `FeePayment` per student. **No bank movement.** |
| `reverseAllocation()` | Delegates to `LedgerService::reversePayment()`; the money returns to the receipt's remaining balance automatically. |
| `reverseReceipt()` | Only when nothing is allocated. Voids the original bank deposit. |

Allocation targets the student's outstanding charges **oldest-first** through
the existing `LedgerService::recommendAllocation()` / `allocatePayment()`, so
statements, receipts and balances need no new code.

Concurrency: `allocate()` takes `lockForUpdate()` on the receipt row, so two
requests cannot both spend the same remaining balance
(`test_concurrent_allocation_cannot_exceed_receipt`).

Idempotency: the controller accepts an optional `client_reference`; a repeat
submission is detected and refused rather than creating a second receipt.

### 4.2 `FinanceService` — `postFeePaymentToBank()`

The single place a fee payment becomes a bank deposit.

- `cash` payments never touch the bank ledger (the money is in hand).
- Every other method deposits into the chosen account, or the first active
  account when the collector did not pick one.
- **Exactly once:** if a non-voided `FeePayment` deposit already exists, the
  method returns immediately, so replays and double-submits are harmless.
- If no active account exists the fee payment is still recorded and the gap is
  reported by the reconciliation command.

### 4.3 `LedgerService::reversePayment()`

Unchanged reversal semantics (contra entries, `reversed_at`, balance recompute)
**plus** the new step: void the bank deposit this payment created. Without it,
reversing a fee receipt would leave the money in the bank.

### 4.4 `BankLedger::findFor()`

Gained an optional `$type` parameter. The legacy direction inference (only
`Income` sources were deposits, everything else withdrawals) misreads the newer
fee-payment and bulk-receipt deposits, so callers posting deposits now pass
`'deposit'` explicitly.

---

## 5. Controllers

### 5.1 `BulkReceiptController` — `app/Http/Controllers/BulkReceiptController.php`

| Action | Route | Permission |
|---|---|---|
| `index`, `show` | `GET fees/bulk-receipts`, `GET fees/bulk-receipts/{id}` | `fees.view` |
| `create`, `store` | `GET/POST fees/bulk-receipts` | `fees.manage` |
| `allocate` | `POST fees/bulk-receipts/{id}/allocate` | `fees.manage` |
| `reverseAllocation` | `POST fees/bulk-receipts/{id}/allocations/{paymentId}/reverse` | `fees.manage` |
| `reverseReceipt` | `POST fees/bulk-receipts/{id}/reverse` | `fees.manage` |

Views: `fee_management/bulk_receipts/{index,create,show}.blade.php`.
The show page renders **Received / Allocated / Remaining**, the allocation form
(scoped to students who actually have an outstanding balance, optionally
filtered by class), the payment history, and both reversal affordances.

### 5.2 `PurchaseOrderController` — `app/Http/Controllers/PurchaseOrderController.php`

| Action | Permission | Notes |
|---|---|---|
| `index`, `show` | `inventory.view` | |
| `create`, `store`, `edit`, `update`, `destroy` | `inventory.manage` | |
| `receive` | `inventory.approve` | **New gate.** Receiving commits stock. |
| `pay` | `finance.manage` | **New gate.** Payment commits money. |
| `arrange` | `finance.manage` | **New gate** — was missing entirely; see §7.1. |

`receive()` now refuses a `Cancelled` or already-`Fully_Received` order, closing
the audit's I-3 double-receive defect. `arrange()` is blocked once any payment
exists, so the arrangement cannot contradict the money already paid.

### 5.3 `FinancialReportController` — `app/Http/Controllers/FinancialReportController.php`

All actions remain `finance.view`.

- `balanceSheet()` — **extended, not rebuilt.** Added inventory at cost,
  a `TOTAL CURRENT ASSETS` subtotal, an explicit non-current-assets section,
  supplier payables from received orders, and fees received in advance.
- `resolveTermWindow()` — the term filter. A term's own start/end dates are
  authoritative; the term overrides any date inputs. Shared by `pAndL()` and
  `pAndLPdf()`, so the export cannot drift from the screen.
- `pAndL()` / `pAndLPdf()` — term filter plus the existing date range.

---

## 6. Console command

`app/Console/Commands/FeeBankReconciliation.php`

```
php artisan fee:bank-reconciliation            # summary
php artisan fee:bank-reconciliation --details  # list every unmatched payment
```

**Read-only.** It reports which valid fee payments have a matching bank
deposit. On the live database it currently reports 210 valid payments, 0
posted — i.e. the historical gap is *visible* and deliberately not closed
automatically. Deciding whether old cash was banked is an administrative
decision.

> The audit/earlier draft named this command `finance:reconcile-bank`. The
> shipped name is `fee:bank-reconciliation`.

---

## 7. Defects found and fixed during this work

### 7.1 Missing authorization on the payment-arrangement action (HIGH)

`PurchaseOrderController::arrange()` had **no `can:` middleware at all**. The
purchase-orders route group carries only `auth`, so any authenticated user —
including a Teacher or a Parent — could POST
`inventory/purchase-orders/{id}/arrange` and change whether an order is paid
immediately or on credit. Confirmed with a test before the fix (302, not 403).

Fixed by gating `arrange` behind `finance.manage`, matching `pay`. The PO show
view was also showing the form under `@can('inventory.manage')`, which would
have offered a button the server answers with 403; both now use `finance.manage`.

**Test:** `SupplierProfileTest::test_arrange_route_is_authorized_server_side`

### 7.2 The fee-to-bank destination was silently dropped (HIGH)

`FeeManagementController::storePayment()` did not validate or forward
`bank_account_id`, even though the collect-payment form renders a "Received
Into Account" selector for non-cash methods. The chosen account never reached
`FinanceService`, so **every** deposit fell to the first active account.

Fixed by adding the validation rule and forwarding it to both `recordPayment()`
and `recordTotalPayment()`.

**Test:** `FeeBankPostingTest::test_http_payment_honours_the_selected_bank_destination`
(creates two accounts and asserts the money lands in the *second* one).

### 7.3 "Received" was defined three different ways

`Supplier::outstandingPayable()` and the balance sheet each filtered on
`Fully_Received` only, while `PurchaseOrder::isReceived()` treats
`Partially_Received` as received too. Harmless today (there is no partial
receive action yet) but it would silently under-report payables the moment one
exists. Replaced with a single `PurchaseOrder::scopeReceived()`.

### 7.4 Number collisions were possible under concurrency (MEDIUM)

The audit's fix replaced `rand()` with a MAX+1 scan, but the scan is a
read-then-write and the code never retried, so a collision surfaced to the user
as a failed save. Now wrapped in `createWithFreshNumber()`.

The retry is deliberately narrow. SQLSTATE `23000` covers *every* integrity
violation — foreign keys, NOT NULL, check constraints — so an implementation
that retried on the SQLSTATE alone would silently re-run a genuine data error
five times and then report a confusing failure. `isSequenceCollision()` now
requires both that the driver reported a *duplicate/unique* violation and that
the offending value is the number we just tried.

**Tests:**
`test_po_number_collision_is_retried_not_surfaced_as_an_error`,
`test_requisition_number_collision_is_retried_not_surfaced_as_an_error`,
`test_unrelated_integrity_error_is_not_retried_as_a_number_collision`,
`test_persistent_duplicate_is_capped_instead_of_looping_forever`


### 7.5 Bursary/sponsor receipts were unreachable from the UI

The feature was fully built (routes, controller, views, tests) but had no
sidebar entry, so no user could navigate to it. Added to the Fee Management →
Operations section of `config/menu.php`.

---

## 8. RBAC

No new permission keys were introduced. Everything reuses the existing RBAC
contract, so role assignments, policy classes and the super-role bypass are
untouched.

| Action | Permission |
|---|---|
| View bulk receipts | `fees.view` |
| Record / allocate / reverse bulk receipts | `fees.manage` |
| Receive purchase order | `inventory.approve` |
| Pay supplier / set payment arrangement | `finance.manage` |
| Financial reports (incl. PDF) | `finance.view` |

> The audit §10 proposed new permission keys (`bulk_receipt.create`,
> `bulk_receipt.reverse`, `supplier_payment.create`,
> `financial_report.view`). They were **not** created — the existing
> `fees.*` / `finance.*` / `inventory.*` permissions already express these
> decisions, and inventing parallel keys would fragment the RBAC model.

---

## 9. Tests

### 9.1 New financial suites

| File | Covers |
|---|---|
| `tests/Feature/SupplierProfileTest.php` | Profile 500 regression, PO links, supplier expenses/outstanding, numbering, **duplicate retry + retry cap + "don't retry unrelated integrity errors"**, double-receive, immediate payment, credit purchases, partial payment, overpayment, overdue, RBAC (pay and arrange). |
| `tests/Feature/BulkReceiptAllocationTest.php` | Received/allocated/remaining, exactly-one bank deposit, no double posting, over-allocation, concurrency, oldest-first targeting, allocation + receipt reversal, RBAC matrix. |
| `tests/Feature/FeeBankPostingTest.php` | Exactly-once posting, cash exclusion, replay safety, reversal, no-active-account case, **no backfill of history**, HTTP destination honoured. |
| `tests/Feature/FinancialPositionTest.php` | Balance sheet identity, supplier payables, inventory valuation, fees received in advance, P&L term filter, term filter in PDF. |

### 9.2 How to run

```bash
# The new financial work
php vendor/bin/phpunit tests/Feature/SupplierProfileTest.php \
  tests/Feature/BulkReceiptAllocationTest.php \
  tests/Feature/FeeBankPostingTest.php \
  tests/Feature/FinancialPositionTest.php

# Baseline regressions that must stay green
php vendor/bin/phpunit --testsuite Feature --filter \
  'FinanceModuleIntegrityTest|FinancePermissionTest|FeeReconciliationTest|FeeCollectionTest|\
FeeAuthorizationTest|FeeReversalIntegrityTest|FeeReversalReportingTest|RefundReconciliationTest|\
RefundBankPostingTest|RequisitionToPoTest|RbacVisibilityTest|FeeScreensSmokeTest|\
MobileFeeIdempotencyTest|FeeAssignmentBulkRegressionTest|FeeArrearsScopeTest|\
FeeTermAssignmentTest|FeeUiConsistencyTest|DiscountSchemeFeeCategoryTest|RbacEditPreservationTest'

php vendor/bin/phpunit --testsuite Unit

# Read-only fee-to-bank gap report
php artisan fee:bank-reconciliation
```

---

## 10. Remaining issues / known gaps

These are deliberate, and none of them are silent:

1. **Supplier payments cannot be reversed.** The audit sketched
   `purchase_order_payments.status` + a `reversePayment()` action. Neither was
   built. A mistaken supplier payment can currently only be corrected by
   creating a further payment, which is impossible once the balance is settled.
   This is the most significant gap in the PO work.
2. **No fixed-asset register.** The balance sheet reports the non-current asset
   section explicitly as "Not recorded" rather than inventing figures. Real
   fixed-asset accounting needs a new table (the audit §11 spec) and was out of
   scope.
3. **Balance-sheet date filter is still a snapshot.** Bank balances are current
   balances, not as-at `endDate` (audit I-6). Unchanged by this work.
4. **No partial goods receipt.** `receive()` always moves the order straight to
   `Fully_Received`. `scopeReceived()` already accounts for
   `Partially_Received` so a future partial-receive action will not need a
   reporting change.
5. **Historical fee payments remain unposted to the bank** (210 rows,
   KES 1,569,155 on the live database). Intentionally not backfilled — see
   §6.
6. **Bursary income is not classified as income.** Bulk receipts are cash and
   fee income, but they are not written to the `income` table, so they do not
   appear as a separate "Donations & Bursaries" line in the P&L. Recording them
   there as well would double-count against fee income; surfacing them as a
   memorandum line is the sensible next step.
7. **`ExpensesController::update` still has no status gate** (audit S-3) — an
   approved or paid expense can still be edited. Untouched: it is a pre-existing
   defect in a different module, not introduced here.
8. **`Route::resource('purchase-orders')` registers `edit`/`update`/`destroy`
   that the controller does not implement.** Pre-existing; visiting those URLs
   errors. The resource should use `->except([...])`.

---

## 11. Verification record

Run on branch `feat/rbac-consolidated-permissions` (all changes uncommitted).
Runtime PHP 8.2.12, Laravel 10.48.29, PHPUnit 10.5.64.

| Suite | Result |
|---|---|
| Unit | **OK — 68 tests, 287 assertions** (identical to baseline) |
| Targeted finance/fee/procurement Feature | **OK — 197 tests, 1263 assertions** (identical to baseline) |
| `SupplierProfileTest` | **OK — 24 tests, 79 assertions** |
| Bulk receipt + fee-to-bank + financial position + requisition→PO | **OK — 40 tests, 122 assertions** |
| Full Feature suite (94 files, run in 4 chunks) | **OK — 839 tests, 4345 assertions** |

**Total: 1,168 tests, 6,096 assertions, no failures.**

Ordering note: the full Feature suite was run in chunks after all functional
changes. The last two edits — narrowing `isSequenceCollision()` and adding the
sidebar entry — were then re-validated by re-running Unit, the 197-test targeted
baseline, all four new financial suites, and the RBAC/visibility suites
(`RbacVisibilityTest`, `ViewRouteReferenceTest`, `SuperRoleAccessTest` —
45 tests, 204 assertions). `php vendor/bin/phpunit` as a single invocation
exceeds the 2-hour tool timeout in this environment, which is why the Feature
suite is chunked.

No new failures. No pre-existing test was modified to make it pass, and no
baseline count changed.

### Not verified / out of scope

- `pint` is not clean on this repository *as a baseline* — untouched files such
  as `app/Models/Student.php` and `app/Models/FeePayment.php` also report style
  violations. Reformatting was therefore deliberately not run, to avoid burying
  the functional diff in unrelated churn.
- `git diff --check` reports one pre-existing issue in an unrelated library
  view: `resources/views/book_issues/fields.blade.php` (new blank line at EOF).

