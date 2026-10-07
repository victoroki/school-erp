# Financial Customization Audit

Phase 1 — read-only audit for the finance customizations requested by management.
**No application code, migrations, or production data were modified.** Read-only DB probes
ran against the live `school_management_system` database via tinker (SELECT/COUNT only).
All tests ran against the isolated `school_erp_test` database per `phpunit.xml`.

Branch audited: `feat/rbac-consolidated-permissions` (224 dirty paths from prior in-flight work — untouched).
Audit date: 2026-09-28.

---

## 1. Executive Summary

The ERP already has substantial, tested Finance and Fee Management modules with a clear
module boundary (Fee Management owns the student fee ledger; Financial Management owns
bank/cash/income/expense/budget/supplier surfaces). Several defects from earlier audits
(`docs/fee-management-audit.md`, `docs/financial-management-module-audit.md`,
`docs/financial-management-phase4-report.md`) have already been fixed and are now covered
by a green regression suite (197 targeted tests passing at baseline).

Against the six management requirements:

| Requirement | Current state in one line |
|---|---|
| **A. Statement of Financial Position** | **[P]** Balance Sheet exists (`financial-reports/balance-sheet`) and derives from real data, but covers only bank/petty-cash/receivables vs unpaid expenses+refunds. No fixed assets, no inventory valuation, no prepayments, no supplier payables (because credit purchases don't exist yet). |
| **B. Statement of Financial Performance** | **[P]** P&L exists with correct conventions (cash vs committed basis via `FinancialMetrics`), fee income included. No term filter; no bursary/donation income granularity (income categories exist but `income` table has never been used — 0 rows). |
| **C. Bulk bursary/sponsor allocation** | **[X]** No sponsor/bursary receipt concept anywhere. However, the allocation machinery it needs already exists (`payment_allocations` multi-charge allocation, oldest-first strategy, reversal-safe balance service) — the gap is a *parent receipt + remaining balance*, not an allocation engine. |
| **D. Requisition immediate vs credit** | **[X]** No payment step for POs at all: receiving a PO writes inventory but never creates a payable, an expense, or any money movement. There is no way to pay a supplier through the PO flow, so "immediate vs credit" is undefined rather than wrong. Expenses have a pending→approve→paid flow that already implements "immediate payment" correctly. |
| **E. Supplier financial position** | **[P/X]** Supplier CRUD works (finance-gated), but there are no invoices, no payables, no payments, and the supplier profile page **crashes (500)** due to a broken eager-load. Total Order Value exists; everything else is missing. |
| **F/G/H. Integrity / security / accounting model** | **[V]** Largely healthy. Expense segregation-of-duties, overdraft guard, append-only student ledger, reversal-safe balances, audit trail everywhere. Two verified defects: the supplier-show 500, and a dead PO link on the supplier profile. Fee payments bypass `bank_accounts` (by design, but it weakens the balance sheet's cash section). |

The accounting model is **transaction-summary driven (not ledger-driven)** outside the Fee
module: reports recompute from source tables (`expenses`, `income`, `fee_payments`) via
`FinancialMetrics`. There is **no chart of accounts / general ledger / journal** in the
Financial Management module; the only double-entry-ish ledger is the Fee module's
append-only `ledger_entries` (per-student, single-sided). The safest path is to **extend
the transaction-summary model** — add payables to purchase orders and read them in the
reports — not to introduce a GL.

---

## 2. Existing Architecture

### 2.1 Fee Management (domain owner: student fee ledger)

- **Routes** (`routes/web.php`): `fees/*` prefix (dashboard, collect, assignments, adjustments,
  discounts, arrears, refunds, reports, integrity) + legacy `fee-management/*` collection routes.
- **Controllers**: `FeeManagementController`, `FeeDashboardController`, `StudentFeeAssignmentController`,
  `FeeAdjustmentController` (approval workflow + audit log), `FeeArrearsController`, `FeeReportsController`,
  `RefundController`, `DiscountSchemeController`, `FeeIntegrityController`, `Portal/PortalFeeController`,
  `Mobile/MobileFeeController`, `Mobile/MobileFinanceSummaryController`.
- **Services** (the important ones):
  - `LedgerService` — append-only per-student ledger (`ledger_entries`): charges, payments,
    adjustments, refunds, reversals (contra entries; nothing is ever deleted). Running balances
    recomputed per entry.
  - `FeeBalanceService` — **single source of truth for balances**:
    `outstanding = assigned − (valid payments − completed refunds)`. Handles split allocations,
    reversed payments, unattributed refunds. Exposes SQL subquery form (`paidTotalsSubquery()`)
    so reports agree with screens by construction.
  - `FinanceService` — fee assignment + `recordPayment` / `recordTotalPayment` (oldest-first
    allocation across assignments, idempotency via `client_reference`), `getMetrics()`.
  - `FeeAssignmentService` — individual/class/all bulk fee assignment + discount matching.
- **Models**: `StudentFeeAssignment` (charge; `final_amount`, `paid_amount` denormalised effective paid),
  `FeePayment` (receipt; `reversed_at/reason/by` reversal state, `client_reference` idempotency),
  `PaymentAllocation` (one payment → many assignments), `LedgerEntry`, `Refund`, `FeeInvoice` (exists,
  **0 rows live, write path partially wired**), `FeeStructure`, `FeeCategory`, `DiscountScheme`,
  `StudentDiscount`, `FeeAdjustment` (+ `FeeAdjustmentAuditLog`).
- **Permissions**: `fees.view / fees.collect / fees.manage / fees.print / fees.approve` (config `rbac`,
  seeded; `FeeAuthorizationTest` covers every endpoint).

### 2.2 Financial Management (domain owner: school money)

- **Routes** (`routes/web.php`): `finance/dashboard`; resources `income`, `expenses`
  (+ `expenses-pending`, `expenses/{id}/approve`, `expenses/{id}/pay`), `bank-transactions`,
  `bank-reconciliations`, `budgets` (+ `budget-vs-actual`), `financial-years` (+ audit),
  `financial-reports/*` (index, cashflow, p-and-l, balance-sheet, fee-collection-trends),
  petty-cash, suppliers, audit-trail.
- **Services**:
  - `FinancialMetrics` — single source for money figures with explicit bases:
    `BASIS_CASH` (paid only — cashflow, dashboard) vs `BASIS_COMMITTED` (paid+approved — P&L, budgets).
    Income = active `Income` rows + non-reversed `FeePayment`s.
  - `BankLedger` — sole writer of `bank_accounts.current_balance` + `bank_transactions`
    (deposit/withdrawal/transfer, two rows per transfer, `source_type/source_id` link,
    `assertSufficientBalance` overdraft guard, `reverse()` marks rows `voided`).
- **Models**: `BankAccount` (`opening/current/minimum_balance`), `BankTransaction`, `BankReconciliation`,
  `Income`(+Category), `Expenses`(+Category, status pending→approved→paid/rejected, `supplier_id` nullable),
  `Budget` (financial_year, category_type, `include_fees` flag), `FinancialYear`, `PettyCashLog`,
  `Supplier`, `Payroll/PayrollDetail` (finalize explicitly disabled), `AuditTrail`.
- **Permissions**: `finance.view / finance.manage / finance.approve` (only three that exist;
  `finance.export/import` are documented dead references).

### 2.3 Procurement / Requisition / Inventory

- **Routes**: `inventory/*` group: dashboard, add/issue/adjust stock, stock history,
  `requisitions` resource + `POST requisitions/{id}/approve`, `purchase-orders` resource +
  `POST purchase-orders/{id}/receive`.
- **Flow**: Requisition (Pending) → approve with supplier choice (`RequisitionApprovalService::
  approveAndCreateOrder`) → generates PO (`Pending_Approval`, items copied, VAT 16%) →
  (separately) PO approve (status change only) → `receive()` sets `Fully_Received`,
  increments `inventory_items.quantity`, writes `inventory_transactions`.
- **Permissions**: `inventory.view / inventory.manage / inventory.approve`.
- **No money at any step**: receiving does not create an expense, payable, or bank movement;
  there is no payment step, no due date, no supplier balance.

### 2.4 Suppliers

- `suppliers` resource, **gated `finance.view/manage`** (moved from inventory in the Phase-4 pass).
- Fields: name, code (auto), contact, payment_terms (`Cash, Net 15/30/60/90`), bank_details,
  supply_categories (JSON), rating, is_active.
- `Expenses` may reference a supplier (`expenses.supplier_id`, nullable) — the only existing
  supplier↔money link.

### 2.5 Accounting/ledger architecture (summary)

| Layer | Mechanism | Double-entry? |
|---|---|---|
| Student fees | `ledger_entries` append-only, per-student, running balance | No (single-sided, student-as-entity) |
| Bank/cash | `bank_transactions` + `current_balance` via `BankLedger` | No (single-sided per account) |
| Reports | `FinancialMetrics` recomputation from source tables | n/a |
| GL / COA / journals | **Absent** | — |

Conventions already decided and tested (do not re-litigate): cashflow = cash basis;
P&L = committed (accrual-ish) basis; fee income = non-reversed `fee_payments`.

---

## 3. Financial Position (Balance Sheet) — Current State

Existing implementation: `FinancialReportController::balanceSheet` →
`resources/views/financial_reports/balance_sheet.blade.php`, route `financial-reports/balance-sheet`,
permission `finance.view`. Derives everything from real data (no hard-coded balances).

| Requirement | Status | Evidence |
|---|---|---|
| Financial reports area | [V] | `routes/web.php:293-301`, `FinancialReportController` |
| Cash (incl. petty cash) | [V] | `BankAccount.current_balance` (snapshot), `PettyCashLog` credit−debit |
| Bank | [V] | same |
| Mobile Money | [P] | No distinct type; could be a `bank_accounts` row, but `account_type` is free-text (`Business/Current/Deposit` live) — no canonical "mobile money" category |
| Fee receivables | [V] | `StudentFeeAssignment` `SUM(final_amount) − SUM(paid_amount)` clamped ≥ 0 |
| Inventory | [X] | Not on the balance sheet; `inventory_items` has `quantity × cost_per_unit` available to derive stock value |
| Advances | [X] | No concept |
| Prepayments | [X] | No concept |
| Land / Buildings / Vehicles / Furniture / Equipment / computers | [X] | **No fixed-asset register.** `vehicles` table exists (transport module — operational, not accounting: no cost/depreciation fields used for assets) |
| Accumulated depreciation | [X] | No concept anywhere |
| Supplier / accounts payable | [X] | No payable is ever created; only proxy is unpaid `expenses` (pending+approved) |
| Accrued expenses | [P] | Approximated by "pending & approved (unpaid) expenses" — actual accrual treatment absent |
| Salaries payable | [X] | Payroll finalize is explicitly disabled (stub) |
| Statutory liabilities | [X] | PAYE/NHIF/NSSF computed in payroll calculator but never persisted |
| Fees received in advance | [X] | Student credit balances exist (negative outstanding) but are not surfaced as a liability |
| Long-term loans | [X] | No concept |
| Equity / Accumulated fund | [P] | Computed as `assets − liabilities` residual only ("Net Assets (Equity)") |
| Filters | [P] | Date range inputs render, but **balance-sheet figures are point-in-time snapshots** (bank balances are current, not as-at `endDate`) — the filter is partially misleading |

Also note: **cash section completeness** — fee payments (`fee_payments`, 210 live rows) never
touch `bank_accounts`/`bank_transactions`, so the balance sheet's cash is only what was
manually banked via Income. The receivables line compensates arithmetically only if fees were
never spent from unbanked cash.

---

## 4. Financial Performance (P&L) — Current State

Existing implementation: `FinancialReportController::pAndL` (+ `pAndLPdf`),
`financial-reports/p-and-l`, permission `finance.view`. Uses `FinancialMetrics`:
income = active `Income` + non-reversed `FeePayment`; expenses = `expenseBreakdownByCategory`
on `BASIS_COMMITTED`; surplus/deficit computed. PDF export exists.

| Requirement | Status | Evidence |
|---|---|---|
| Income statement / surplus-deficit | [V] | `pAndL()`; tested by `FinanceModuleIntegrityTest::test_p_and_l_uses_committed_basis` |
| Fee income | [V] | `FinancialMetrics::feeIncomeBetween` (`FeePayment::notReversed()`) |
| Bursaries/grants as income | [P] | `income_categories` seeded incl. "Donations & Bursaries" (`FinancialManagementSeeder.php:73`) — but the `income` table has **0 live rows**; no workflow uses it. Model supports it today. |
| Donations | [P] | Same as above |
| Salaries expense | [X] | No payroll posting (finalize disabled) |
| Food/electricity/water/repairs/teaching materials/admin/transport | [P] | These are `expense_categories` (user-creatable, 7 live). P&L groups by category correctly. No fixed list, but that is by design. |
| Financial-year filter | [V] | Defaults to open FY dates; explicit `start_date`/`end_date` params |
| Date-range filter | [V] | yes |
| Term filter | [X] | No term dimension on income/expenses (terms exist only in the fee domain) |
| Branch/campus filter | [X] | Single-school schema (multi-school scaffolding exists in `schools` table but unused) |

---

## 5. Bulk Bursary/Sponsor Allocation — Current State

**Nothing named sponsor/bursary receipt exists.** Searches for bursary/sponsor/CDF/bulk-payment
find only: `is_scholarship_holder` flag on students, `DiscountScheme` (`financial_aid` eligibility),
and an income category label. There is no entity that holds "money received from an organization
for distribution to students".

| Requirement | Status | Evidence |
|---|---|---|
| Bulk receipts / sponsor payments | [X] | No model/table/route |
| Bursaries/scholarships | [P] | Only as **discounts** (`DiscountScheme` + `StudentDiscount`, `FeeAssignmentService` line 451 matches `merit` on `is_scholarship_holder`) — a waiver of charges, not money received |
| Fee credits | [P] | A payment exceeding a student's charges shows as negative outstanding (credit) — `FeeBalanceService::outstandingForAssignments` is deliberately unclamped |
| Payment allocation (one payment → many charges) | [V] | `payment_allocations` table + `LedgerService::allocatePayment` + `recommendAllocation` (oldest_first) + `recordTotalPayment` flow |
| Unallocated/remaining balance tracking | [X] | Payments are created **only** in `FinanceService::recordPayment/recordTotalPayment`, which always allocate the full amount immediately. A payment cannot exist with a remaining balance. |
| CSV/Excel import | [V infra] | PhpSpreadsheet already used for student/book imports (`StudentImportController`, `BookImportService`) — pattern to copy, not new dependency |
| Payment reversal | [V] | `LedgerService::reversePayment` (contra entries, `reversed_at`, balance recompute) |
| Adjustment approval | [V] | `FeeAdjustmentController` workflow with `FeeAdjustmentAuditLog` |
| Audit logs | [V] | `AuditTrail::log` on every fee write |
| Student statements/receipts | [V] | `LedgerService::getStudentStatement` (derived opening), printable receipt with amount-in-words |
| Allocation to specific term / fee item | [P] | Allocation targets assignments which carry `term_id`/fee structure — so it works via assignments, but there is no UI to choose "allocate to Term 2 only" beyond picking assignments |
| Equal distribution | [X] | No such strategy (trivial to add to `recommendAllocation` if wanted) |

**Key architectural fact for Phase 2:** the natural extension is a *parent* "bulk receipt"
record (sponsor, amount, remaining) with each student allocation creating a normal
`FeePayment` (method `bank_transfer`/`online` etc.) allocated to that student's assignments —
reusing the entire existing chain (allocations, ledger, receipts, reversal, balances). The
unallocated remainder stays on the parent record and **must not** become a student credit.

---

## 6. Requisition: Immediate vs Credit — Current State

Existing flow (tested): `RequisitionToPoTest`, `RequisitionApprovalService`.

| Requirement | Status | Evidence |
|---|---|---|
| Requisition → approval → PO | [V] | `RequisitionApprovalService::approveAndCreateOrder` (idempotent, transactional, audit-logged, VAT 16%) |
| Direct PO creation (no requisition) | [V] | `PurchaseOrderController::store` |
| Goods receipt → inventory | [V] | `PurchaseOrderController::receive` (increment quantity + `inventory_transactions`) |
| **Any payment step for a PO** | [X] | None. `receive()` writes stock only; no expense, no payable, no bank movement, no `amount_paid`/`due_date`/credit fields anywhere on `purchase_orders` |
| Immediate payment (req → approve → purchase → pay) | [P] | The **Expenses** module implements this shape exactly: request (`pending`) → `finance.approve` → `markAsPaid` (`finance.manage`) → `BankLedger` withdrawal. PO→Expense link does not exist. |
| Credit purchase (Dr Expense/Inventory, Cr Supplier Payable) | [X] | No payable concept. Receiving goods is invisible to finance. |
| Payment status (unpaid/partial/paid/overdue) | [X] on POs; [V] on Expenses | Expenses: pending/approved/paid/rejected + `payment_date` |
| Due date / credit terms | [X] on POs; [P] on Suppliers | `suppliers.payment_terms` (Cash/Net 15/30/60/90) exists but nothing consumes it |
| Partial payment | [X] | No `amount_paid` column on `purchase_orders` |
| Supplier statement/ledger | [X] | Nothing to state against |

Consequence today: a school that receives KES 600,000 of goods on credit shows **no expense,
no liability, and no supplier balance** until someone manually raises an `Expenses` row — and
`Expenses` is payment-oriented, not goods-receipt-oriented.

---

## 7. Supplier Payables — Current State

| Requirement | Status | Evidence |
|---|---|---|
| Supplier CRUD + search | [V] | `SupplierController` (finance-gated), grouped search |
| Total purchases / invoices | [X] | No invoices; PO totals only |
| Amount paid / outstanding | [X] | No payment records per supplier. (Paid `Expenses` with `supplier_id` are the only trace — 3 live expenses, no supplier linked) |
| Due dates / overdue balance | [X] | No columns |
| Partial payments | [X] | — |
| Supplier statement / ledger | [X] | — |
| **Supplier profile page** | **[B]** | **Verified crash**: `SupplierController::show` eager-loads `->purchaseOrders` (`SupplierController.php:69`) and the view renders it, but `App\Models\Supplier` has **no `purchaseOrders()` relation** — `Supplier::with(['inventoryItems','purchaseOrders'])->first()` throws `RelationNotFoundException` (read-only repro via tinker). Every `GET /suppliers/{id}` 500s. |
| Dead link on profile | [B] | `suppliers/show.blade.php:178` renders `<a href="#">{{ $order->po_number }}</a>` — no route |
| Total Order Value stat | [P] | Sums `purchaseOrders.grand_total` (no received-vs-ordered or paid distinction) |

---

## 8. Security Findings

Only actual defects are graded. The RBAC baseline is strong: controller-level `can:` middleware,
segregation of duties on expense approval (with Owner/Super-Admin `canBypassProtection()` bypass —
intentional, do not report as a bug), fee endpoints covered by `FeeAuthorizationTest`,
finance surfaces by `FinanceModuleIntegrityTest`, module visibility middleware (`module`).

| # | Severity | Finding | Evidence |
|---|---|---|---|
| S-1 | **HIGH** | `GET /suppliers/{id}` 500s for every role including Owner — supplier module is unusable beyond the list page. Not a privilege escalation, but a broken authorized surface. | `SupplierController::show` + missing model relation (verified at runtime) |
| S-2 | **MEDIUM** | Requisition numbers use `rand(100,999)` with no uniqueness check or retry (`RequisitionController::store`: `'REQ-'.date('Ymd').'-'.rand(100,999)`); `Requisition::$rules` declares `unique` but the create path never validates it → silent duplicate requisition numbers possible (also PO store uses the same pattern, though `RequisitionApprovalService` does retry properly). | `RequisitionController.php:54`, `PurchaseOrderController.php:59` |
| S-3 | **LOW** | `ExpensesController::update` allows editing an expense with no status gate: a `pending` expense can be edited freely (fine) but an **`approved`** one can also be edited post-approval, silently changing the amount an approver authorised (audit trail records it, but there is no re-approval). Paid expenses can also be edited (amount changes don't touch the bank ledger). | `ExpensesController::update` has no status check; compare `approve`/`markAsPaid` which both gate on status |
| S-4 | **LOW** | `finance.export` / `finance.import` remain referenced-but-undefined permissions (menu). Documented in earlier audits; harmless (visibility falls back to `finance.view`) but misleading. | `config/menu.php`, `FinancePolicy` docblock |
| S-5 | **LOW** | Payroll enum mismatch (`cheque`/`mobile_money` vs column enum) still present in `PayrollController` — low impact while finalize is disabled. | Phase-4 report §3.2 |

Deliberate behaviours confirmed and **not** defects: Owner-only Admin module; Owner hidden from
role/permission management; `finance.export` refusal in `FinancePolicy`; reconciliation requiring
`finance.approve`; `BankLedger` overdraft guard.

---

## 9. Data Integrity / Accounting Findings

| # | Severity | Finding | Evidence |
|---|---|---|---|
| I-1 | **HIGH (accounting completeness)** | Fee payments never touch the bank layer: `FinanceService::recordPayment/recordTotalPayment` write `fee_payments` + ledger + allocations but never `BankLedger::recordDeposit`. Cash section of the balance sheet and bank reconciliation cannot see 210 live fee payments. (Refunds *do* post to `BankLedger` — `RefundController:343` — so money-out is recorded while money-in is not.) | `FinanceService` vs `RefundController.php:343` |
| I-2 | **HIGH** | PO goods receipt has zero financial effect (no expense, no payable) — the root cause of requirements D and E. | `PurchaseOrderController::receive` |
| I-3 | **MEDIUM** | `receive()` always jumps to `Fully_Received` and double-receive risk: no guard prevents a second `POST receive` (idempotence only by accident: increments stock again and overwrites `received_by/date`). | `PurchaseOrderController::receive` — no status precondition |
| I-4 | **MEDIUM** | Legacy `opening_balance`/`bootstrap` ledger rows and pre-ledger fee history are handled correctly (derived statement opening) — **no action needed**; noted to prevent regressions. | `LedgerService::getStudentStatement` |
| I-5 | **MEDIUM** | `FeeInvoice` is written by `FeeAssignmentService` but has **no read/update surface** (0 live rows, no invoice UI beyond model scopes). Risk of half-implemented invoicing drifting. | `FeeInvoice` model + grep |
| I-6 | **LOW** | Balance-sheet date filter implies point-in-time but bank balances are current snapshots. | `balanceSheet()` |
| I-7 | **LOW** | `BankTransaction` reversal marks rows `voided`; `findFor` legacy fallback matches by description prefix — documented fragility. | `BankLedger::findFor` |
| I-8 | **INFO** | Money precision is now reproducible: `2026_09_23_000003_finance_money_precision.php` widens scale<2 columns; asserted by test. Do not re-add `decimal(10)` columns. | migration + `FinanceModuleIntegrityTest::test_money_columns_have_two_decimal_scale` |
| I-9 | **INFO** | Refund FK cascade risk (hard force-delete of a student destroys refund/ledger history) — students use soft deletes; documented in `docs/fee-management-phase3-report.md`. Keep avoiding force-delete. | phase-3 report finding 4 |

---

## 10. UI/UX Findings

| # | Severity | Finding | Evidence |
|---|---|---|---|
| U-1 | **HIGH** | Supplier profile 500 (same root cause as S-1) — list page works, profile crashes. | verified at runtime |
| U-2 | **MEDIUM** | Supplier profile PO links dead (`href="#"`). | `suppliers/show.blade.php:178` |
| U-3 | **MEDIUM** | Expenses form captures `supplier_id` but `expenses/show.blade.php` never displays the supplier (field saved but never shown). | `expenses/create.blade.php:50` vs show view (no "supplier" occurrences) |
| U-4 | **LOW** | Requisition show page "Generated Purchase Order" link works, but the approver must pick a supplier from *all* suppliers (no filtering by `supply_categories` or `payment_terms` display at choice time). | `requisitions/show.blade.php:107-121` |
| U-5 | **LOW** | "Expense Breakdown by Staff" tile on the financial reports index is a disabled `#` placeholder. | `financial_reports/index.blade.php:41-44` |
| U-6 | **LOW** | Balance-sheet filter inputs (see I-6) look like they restate history but don't. | `balance_sheet.blade.php` |
| U-7 | **INFO** | Payroll wizard review/finalize now flash explicit "not implemented" instead of faking success — correct behaviour, keep. | Phase-4 report |

---

## 11. Database Changes Required

**No migrations were created.** Specification only. All changes should follow the established
conventions (`decimal(15,2)` money, `AuditTrail` on writes, append-only where financial).

### C. Bulk bursary/sponsor allocation (Fee domain)
- **New table** `fee_bulk_receipts`: id, `sponsor_name`, `sponsor_type` (enum-ish string: bursary/cdf/county/ngo/church/donor/other), `reference_number`, `amount` decimal(15,2), `payment_method` (reuse fee enum), `transaction_id`, `receipt_number` unique, `received_date`, `academic_year_id` (nullable), `term_id` (nullable), `bank_account_id` (nullable — for finance-domain posting), `remarks`, `created_by`, reversal columns (`reversed_at/reason/by`), timestamps.
- **New column** `fee_payments.bulk_receipt_id` (nullable FK) — links a student-level payment to its parent bulk receipt; **no schema change** to `payment_allocations` (reused as-is).
- **New relation**: `fee_bulk_receipts → fee_payments` hasMany; remaining = `amount − SUM(fee_payments.amount WHERE not reversed)`.
- Index: `bulk_receipt_id`; unique `receipt_number`.

### D. Credit purchases / PO payables (Procurement + Finance domains)
- **New columns on `purchase_orders`**: `payment_type` enum('immediate','credit') (nullable for legacy rows), `amount_paid` decimal(15,2) default 0, `payment_due_date` date nullable, `credit_terms` varchar nullable (default from `suppliers.payment_terms` on save), `payment_status` enum('unpaid','partial','paid') nullable, `paid_date` nullable, `paid_bank_account_id` nullable FK, `paid_by` nullable FK.
- **New table** `purchase_order_payments`: id, `po_id` FK, `amount` decimal(15,2), `payment_date`, `payment_method`, `bank_account_id` FK nullable, `reference_number`, `created_by`, timestamps + audit. (Partial payments need a child table; simple immediate-pay can write one row.)
- Status change: none required to the existing PO status enum — payment status is orthogonal.
- Index: `(supplier_id, payment_status)` for payable reporting.

### E. Supplier financial position
- **No schema change** — derive: purchases = sum PO grand_total (received ones), paid = sum `purchase_order_payments`, outstanding = grand_total − amount_paid, overdue = outstanding ∧ past due. Consider a view/service, not columns.

### A/B. Financial position & performance
- **No schema change required for the core upgrade** (balance sheet + P&L can read the new payables and existing tables). 
- **Optional new table** `fixed_assets` (asset_number, name, category enum-able string, purchase_date, cost decimal(15,2), depreciation_method, useful_life_months, salvage_value, accumulated_depreciation, status) **only if** management wants real fixed-asset accounting — otherwise leave the balance sheet without non-current assets and label the section explicitly.
- Optional: `bank_accounts.account_type` normalisation (add 'mobile_money') — cosmetic.

### Data migrations
- Backfill `purchase_orders.payment_status='unpaid'` for legacy received POs (or leave NULL = legacy).
- **No** backfill of fee payments into bank transactions (decision required in Phase 2 — see risks).

---

## 12. Proposed Implementation Plan (smallest safe set)

Ordered; each step is independently shippable and test-covered.

1. **Fix the broken supplier surface (quick win, pre-req for everything supplier-related)**
   Add `purchaseOrders()` to `App\Models\Supplier`; link PO numbers to
   `inventory.purchase-orders.show`; display supplier on `expenses/show`.
   Files: `app/Models/Supplier.php`, `resources/views/suppliers/show.blade.php`, `resources/views/expenses/show.blade.php`.

2. **PO payment step (Requirement D)** — extend, don't replace:
   a. Migration: payment columns on `purchase_orders` + `purchase_order_payments` table (§11).
   b. `PurchaseOrderController`: add `pay()` (immediate: requires approved+received, writes payment row + `BankLedger::recordWithdrawal` inside one transaction with overdraft guard) and `recordCredit()` (credit: sets `payment_due_date`/terms, **no money movement**); partial payments allowed; `payment_status` derived on write.
   c. Approval-form addition on the requisition approve screen: "Immediate payment" vs "Credit" choice (defaults from supplier `payment_terms`).
   d. Supplier balance display + simple outstanding list (`PurchaseOrder::scopeOutstanding`).
   e. Tests: immediate pay moves bank and forbids overdraft; credit receipt creates payable and zero bank effect; partial payments accumulate; overdue classification; double-receive and double-pay guards; RBAC (`inventory.approve` or `finance.manage` — decide with management, see §15).

3. **Bulk bursary/sponsor receipts (Requirement C)** — Fee-domain feature:
   a. Migration: `fee_bulk_receipts` + `fee_payments.bulk_receipt_id` (§11).
   b. `BulkReceiptService` (Fee domain): create receipt → allocate to students (per-student amounts; each allocation creates a standard `FeePayment` via `LedgerService::allocatePayment`; class-select and CSV upload reusing the PhpSpreadsheet pattern); enforce `sum(allocations) ≤ amount`; remaining stays on the receipt.
   c. Controller + routes under `fees/bulk-receipts` with `fees.manage` (allocate) / `fees.collect` (record) gates; show page with allocated vs unallocated; reversal of the parent reverses unallocated student payments only with explicit consent flow (reuse payment reversal).
   d. Tests: no over-allocation; remainder preserved and traceable; reversal integrity; student balances correct; RBAC matrix.

4. **Balance sheet upgrade (Requirement A)** — rework `FinancialReportController::balanceSheet` into a structured report (current assets incl. inventory valuation `SUM(quantity × cost_per_unit)`, fees received in advance = negative student balances; current liabilities incl. **supplier payables from step 2** + unpaid expenses + approved refunds; equity section with explicit "Accumulated Fund" label; drop/label the misleading date filter as "as at today"). Optional `fixed_assets` section only if the table is approved.

5. **P&L term/granularity (Requirement B)** — add optional term filter (fee income by term via assignments' `term_id`; non-fee income/expenses have no term — label them accordingly). Surface bursary income by encouraging `Income` usage or, if sponsors should be income-classified, map bulk receipts to `IncomeCategory` "Donations & Bursaries" in step 3.

6. **Fee payments → bank (decision required)** — post a `BankLedger::recordDeposit` when fees are received (method ≠ cash) so bank reconciliation covers fee income. This changes reconciliation behaviour; needs management sign-off (§15 risk).

---

## 13. Files Expected to Change

**Step 1:** `app/Models/Supplier.php` · `resources/views/suppliers/show.blade.php` · `resources/views/expenses/show.blade.php`

**Step 2:** `database/migrations/*_create_purchase_order_payments_and_payment_columns.php` (new) ·
`app/Models/PurchaseOrder.php` · `app/Models/PurchaseOrderPayment.php` (new) · `app/Models/Supplier.php` ·
`app/Http/Controllers/PurchaseOrderController.php` · `app/Http/Controllers/RequisitionController.php` ·
`app/Services/RequisitionApprovalService.php` · `routes/web.php` ·
`resources/views/inventory/purchase_orders/{index,show,create}.blade.php` ·
`resources/views/inventory/requisitions/show.blade.php` · `resources/views/suppliers/{index,show}.blade.php` ·
`tests/Feature/PurchaseOrderPaymentTest.php` (new)

**Step 3:** `database/migrations/*_create_fee_bulk_receipts.php` (new) ·
`app/Models/FeeBulkReceipt.php` (new) · `app/Models/FeePayment.php` (relation) ·
`app/Services/BulkReceiptService.php` (new) · `app/Http/Controllers/BulkReceiptController.php` (new) ·
`routes/web.php` · `config/menu.php` (fees section) ·
`resources/views/fee_management/bulk_receipts/{index,create,show}.blade.php` (new) ·
`tests/Feature/BulkReceiptAllocationTest.php` (new)

**Step 4:** `app/Http/Controllers/FinancialReportController.php` ·
`resources/views/financial_reports/balance_sheet.blade.php` ·
`app/Services/FinancialMetrics.php` (receivables/inventory/payables helpers) ·
`tests/Feature/FinanceModuleIntegrityTest.php` (new cases)

**Step 5:** `app/Http/Controllers/FinancialReportController.php` ·
`resources/views/financial_reports/p_and_l.blade.php` · `app/Services/FinancialMetrics.php`

**Step 6:** `app/Services/FinanceService.php` (or `LedgerService`) · `tests/Feature/FeeReconciliationTest.php`

Explicitly **out of scope / not to be created**: chart of accounts, general ledger, journals,
GL-based balance sheet. The transaction-summary architecture stands; do not introduce a second one.

---

## 14. Tests Required (before Phase 2 is "complete")

Existing green baseline to protect (must stay green):
`FinanceModuleIntegrityTest` (20), `FinancePermissionTest`, `FeeReconciliationTest`,
`FeeCollectionTest`, `FeeAuthorizationTest`, `FeeReversalIntegrityTest`, `FeeReversalReportingTest`,
`RefundReconciliationTest`, `RefundBankPostingTest`, `RequisitionToPoTest`, `RbacVisibilityTest`,
`FeeScreensSmokeTest`, `MobileFeeIdempotencyTest`, `FeeAssignmentBulkRegressionTest`,
`FeeArrearsScopeTest`, `FeeTermAssignmentTest`, `FeeUiConsistencyTest`,
`DiscountSchemeFeeCategoryTest`, `RbacEditPreservationTest` — **197 tests / 1263 assertions**.

New tests to write:

1. **Supplier profile** renders 200 for finance.view holder and 403 for Teacher (regression for S-1/U-1); PO links navigate.
2. **PO immediate payment**: approved+received PO paid → bank balance decremented, `bank_transactions` withdrawal row with `source_type`, `payment_status='paid'`; double-pay refused; unapproved/unreceived PO cannot be paid; insufficient funds rolls everything back (mirror `FinanceModuleIntegrityTest` patterns).
3. **PO credit purchase**: credit receipt → no bank movement; payable appears in supplier outstanding; partial payments accumulate to `amount_paid`; overpayment refused; overdue computed from `payment_due_date`.
4. **PO receive idempotence**: second receive rejected (protects I-3).
5. **Bulk receipt**: create 5,000,000 → allocate 4,700,000 across students → each student's balance drops by exactly their allocation; receipt shows unallocated 300,000; over-allocation rejected; allocations visible in each student's statement; receipt reversal reverses only its own child payments and restores balances.
6. **Bulk receipt RBAC**: view/allocate/reverse matrix across Owner/Admin/Accountant/Teacher/Parent.
7. **Balance sheet**: includes supplier payables from credit POs; includes inventory value; fees-received-in-advance line equals negative student balances; assets − liabilities = equity (identity test).
8. **P&L term filter**: fee income respects term; surplus/deficit arithmetic exact.
9. (If step 6 approved) fee payment creates a linked bank deposit; reconciliation sees it; reversal removes it.

---

## 15. Risks / Backward Compatibility

1. **Historical POs**: legacy rows have NULL payment fields. Decide: treat NULL as "legacy, unpaid-irrelevant" (display "—" instead of "Unpaid") so old fulfilled POs don't suddenly appear as overdue payables. Recommend NULL ≠ unpaid.
2. **Receiving-then-paying sequence**: management's credit flow implies goods can be received before any payment exists. Ensure reports don't treat a received-but-unpaid PO as an expense *and* a payable simultaneously (choose payable-only until paid; the expense recognition point for P&L must be one convention — recommend: expense when received (committed basis already matches), payable until settled).
3. **Permission boundary for PO payment**: paying a supplier is a finance action on an inventory document. Options: `finance.manage` on the pay action (mirrors expenses) or `inventory.approve`. Existing precedent (expenses: pay = `finance.manage`, approve = `finance.approve`) suggests finance owns payment. This changes what Accountants can do in the inventory module — get explicit sign-off.
4. **Bulk receipts create many real payments** — receipts, statements and collection reports will each include them. That is desired (money genuinely arrived), but the "collections" report grouping should be able to separate sponsor bulk receipts from counter collections (via `bulk_receipt_id` presence) or bursary analysis will be noisy.
5. **Reversal semantics**: reversing a bulk receipt must not silently strand partially-allocated money; each child payment reversal restores the receipt's remaining balance (sum of child reversals), never deleting the parent.
6. **Fee payments → bank posting (step 6)** changes reconciliation history: previously "unreconciled" bank positions excluded fee income entirely. Enabling it mid-year creates a one-time jump; recommend a cutoff date or an explicit opening adjustment rather than silent backfill.
7. **Balance sheet restatement**: adding inventory/payables/credit-balance lines changes published figures vs the current balance sheet. Communicate the change of basis (the current report *under-states* assets and liabilities).
8. **Do not break**: student fee receipts/balances (untouched — bulk receipts ride on existing payment flow), existing `Expenses` flow (kept as the immediate-payment path), `payment_allocations` semantics, the `BASIS_CASH`/`BASIS_COMMITTED` conventions, and the RBAC contract (`FinancePolicy`, `FeePolicy`, config `rbac` super-roles).
9. **Exports/prints**: PDF templates for cashflow/P&L exist; balance sheet has print CSS but no PDF route — adding one is low risk; keep `Money::format` conventions.

---

## Appendix — Baseline test evidence

```
$ php vendor/bin/phpunit --testsuite Unit
OK (68 tests, 287 assertions)                     Time: 01:19

$ php vendor/bin/phpunit --testsuite Feature --filter 'FinanceModuleIntegrityTest|
  FinancePermissionTest|FeeReconciliationTest|FeeCollectionTest|FeeAuthorizationTest|
  FeeReversalIntegrityTest|FeeReversalReportingTest|RefundReconciliationTest|
  RefundBankPostingTest|RequisitionToPoTest|RbacVisibilityTest|FeeScreensSmokeTest|
  MobileFeeIdempotencyTest|FeeAssignmentBulkRegressionTest|FeeArrearsScopeTest|
  FeeTermAssignmentTest|FeeUiConsistencyTest|DiscountSchemeFeeCategoryTest|
  RbacEditPreservationTest'
OK (197 tests, 1263 assertions)                   Time: 05:05
```

Live data snapshot (read-only counts, `school_management_system`):
`fee_payments` 210 · `student_fee_assignments` 263 · `ledger_entries` 7 · `payment_allocations` 1 ·
`refunds` 1 · `expenses` 3 (all paid) · `income` 0 · `bank_accounts` 3 · `bank_transactions` 0 ·
`budgets` 2 · `financial_years` 1 · `suppliers` 6 · `purchase_orders` 1 (Pending_Approval) ·
`requisitions` 2 · `inventory_items` 23 · `fee_invoices` 0 · `discount_schemes` 0.
All 210 fee payments carry method `online` (seeded), so no mobile-money/cash distribution exists yet.
