# Financial Management Module — Audit Report

Module: Financial Management (bank accounts, bank transactions, bank reconciliation, income, expenses, budgets, financial years, financial reports, suppliers, payroll)

Scope of this pass: **read-only audit** — no code was modified. Findings are evidence-based with `file:line` references. Confidence labels: `[V]` = verified directly from code/live DB, `[S]` = supported by static analysis / partially verified. Severity: CRITICAL / HIGH / MEDIUM / LOW.

> Deferred action note: the audit brief item **#34 "Run the tests and record passed/failed"** was **NOT executed**. Running PHPUnit would create + seed the `school_erp_test` database (no `.env.testing` exists) — a state change that conflicts with the read-only constraint and risks interfering with the parallel Fee Management session sharing this repo. A concrete plan is in section N.

---

## A. Architecture map

### Route topology (all inside the `auth` + `module` middleware group, `routes/web.php:36`)
- `Route::resource('payrolls', PayrollController::class)` — L188
- `Route::resource('expenses', ExpensesController::class)` — L189; `expenses-pending` L190; `POST expenses/{id}/approve` L191; `POST expenses/{id}/pay` L192
- `Route::resource('expense-categories')` / `income-categories` — L194-195
- `prefix('finance')` → `GET /finance/dashboard` — L237-239
- `Route::resource('income')` — L241
- `Route::resource('bank-transactions')` — L242
- `Route::resource('bank-reconciliations')` — L243
- `Route::resource('budgets')` — L244; `GET budget-vs-actual` → `BudgetController@vsActual` L245
- `prefix('financial-reports')`: index / cashflow / p-and-l — L247-251
- `GET financial-years/{fy}/audit` — L253; `resource('financial-years')` L254; `audit-trail` L255-256
- `prefix('payroll-processing')`: index/create/calculate/review/finalize — L519-524

**There is NO route-level `can:`/`permission:` middleware.** Every permission check lives in a controller constructor (`$this->middleware('can:...')`), and several controller actions are **not covered by any guard** (see D/E).

### Controllers → guards
| Controller | Guards | Unguarded actions |
|---|---|---|
| `ExpensesController` | `finance.view` (index,show) · `finance.manage` (create,store,edit,update,destroy) · `finance.approve` (approve) | **pending, markAsPaid** `expenses/index.blade.php` days |
| `IncomeController` | `finance.view` (index,show) · `finance.manage` (create,store,destroy) | — (no edit/update methods) |
| `BankTransactionController` | `finance.view` (index,show) · `finance.manage` (create,store) | — |
| `BankReconciliationController` | **`finance.view` (index,show,update)** | — (update reconciled under view permission) |
| `BudgetController` | `finance.view` (index,show) · `finance.manage` (create,store,edit,update,destroy) | **vsActual** |
| `FinancialYearController` | `finance.view` (index,show) · `finance.manage` (create,store,edit,update,destroy) | **audit** |
| `FinancialReportController` | `finance.view` (all + cashflow-only) | — |
| `FinanceDashboardController` | `finance.view` (entire controller) | — |
| `SupplierController` | **`inventory.view` / `inventory.manage`** | — |
| `PayrollController` | `hr.view` (index,show) · `hr.manage` (create,store,edit,update,destroy) | — |
| `PayrollProcessingController` | `hr.view` (index,show) · `hr.manage` (create,calculate,store) | **review, finalize** |

### Domain services
- `app/Services/BankLedger.php` — sole writer of `bank_accounts.current_balance` + `bank_transactions` rows inside one DB transaction (withdrawal/deposit/transfer/reverse). Used by Expenses, Income, BankTransaction controllers. **Good design; see E-2 for the overdraft gap** `BankLedger.php:26-165`.
- `app/Services/FinanceService.php` — **FEE-OWNED** (fee assignment, `LedgerService::allocatePayment`, `FeeBalanceService`). It is NOT the Financial Management module's service. Shared-file dependency with the Fee session — see O.
- `app/Support/Money.php` — shared currency formatter (`KES`, thousands, 2dp).
- No chart of accounts / general ledger / journal for the finance module: **fully ABSENT.** Money moves are tracked per-bank-account (`bank_transactions`) with no double-entry posts. Ledger entries exist only in the Fee module (`ledger_entries`, Fee-owned).

### Permissions (the only 3 finance perms that exist)
`finance.view`, `finance.manage`, `finance.approve` (`PermissionSeeder.php:54-56`, `2026_07_22_000002_insert_new_permissions_table.php:48-50`). A remap migration collapses legacy route-level names (e.g. `expenses.pay → finance.manage`, `bank-reconciliations.edit → finance.approve`) into these three (`2026_07_22_000003_remap_role_permissions_to_new_permissions_table.php:232-283`).

### Roles (actual) — `RbacSeeder.php:125-133`
Owner, Super Admin, Admin, Teacher, Accountant, Parent, Student. **Bursar does NOT exist.**

---

## B. Existing feature map

| Feature | Status | Entry |
|---|---|---|
| Finance dashboard | Implemented (`FinanceDashboardController`, `finance/dashboard`) | KPIs, charts, recent txns |
| Bank accounts | CRUD + show with balance health (`bank_accounts/`) | create/edit/delete |
| Bank transactions | Create-only index + record (`bank_transactions/`) | deposit/withdraw/transfer |
| Bank reconciliation | List + mark awaited txns reconciled (`bank_reconciliations/`) | index/show/update |
| Income | **Create, list, show, delete. NO edit/update.** | `income/` |
| Expenses | Full create→pending→approve→pay→show, edit, delete | `expenses/`, `expenses.pending` |
| Expense/income categories | CRUD | resource controllers |
| Budgets | CRUD + vs-actual report | `budgets/`, `budgets.vs-actual` |
| Financial years | CRUD + audit log view | `financial_years/` |
| Financial reports | Index/cashflow/P&L (fee payments merged in) | `financial-reports/*` |
| Suppliers | CRUD (gated by inventory, not finance) | `suppliers/`, `expenses` links supplier |
| Payroll | Two disconnected UIs (legacy CRUD `payrolls`; wizard `payroll-processing`). **Wizard finalize is a stub** | `payrolls/`, `payroll-processing/*` |
| Petty cash | Table `petty_cash_logs` + dashboard card only; **no management UI** | — |

---

## C. Live data state (read-only probe, live `school_management_system` DB)

| Table | Rows |
|---|---|
| bank_accounts | 3 (active; balances 500,000 / 1,193,453 / 750,000; minimums 10k/50k/20k) |
| bank_transactions | **0** |
| bank_reconciliations | 0 |
| budgets | 2 (1 expense @ 123,000 · 1 income @ 768,668, open FY 1) |
| financial_years | 1 (2026/2027, open, 2026-01-01→2026-12-31) |
| expenses | 3, all `paid` (87,687 / 56,756 / 6,547; 2 cash, 1 check w/ bank_account_id=2) |
| expense_categories / income_categories | 7 / 5 |
| income | **0** (despite an income budget of 768,668) |
| suppliers | 6 |
| staff_salary | 0 |
| petty_cash_logs | 0 |
| payrolls / payroll_details | exist (0 rows counted for payroll; details 0) |
| fee_payments / ledger_entries | 209 / 5 (fee module context) |

Key observations:
- **`bank_transactions` is empty while 3 expenses are marked paid** — the paid "check" expense (id 1, bank_account_id 2) has no statement row. These expenses were paid before `BankLedger` existed (the fallback branch in `ExpensesController::destroy`, L240-246, explicitly anticipates pre-BankLedger rows). The Fees Collection account balance (1,193,453) therefore cannot be audited from the ledger today; reconciliation screen shows nothing.
- **Income module has never been used** (0 rows) even though fee payments (209) flow into the dashboards/reports.

### Live schema vs migrations — CRITICAL mismatch (verified via `information_schema`)
| Column | Migration file declares | **Live DB column type** |
|---|---|---|
| `bank_accounts.opening_balance` / `current_balance` | `decimal(12)` = (12,0) | `decimal(12,2)` |
| `bank_transactions.amount` | `decimal(10)` = (10,0) | `decimal(10,2)` |
| `expenses.amount` | `decimal(10)` = (10,0) | `decimal(10,2)` |
| `income.amount` | `decimal(10)` = (10,0) | `decimal(10,2)` |
| `staff_salary.*` | `decimal(10)` = (10,0) | `decimal(10,2)` |
| `fee_payments.amount` | (fixed by `2026_09_21_000001`) | `decimal(10,2)` |

The precision fix exists **only on the live DB** (`2026_09_21_000001` fixed only `fee_payments`). **No migration in the repo alters the finance money columns to (10,2)/(12,2).** A fresh `php artisan migrate` reproduces `DECIMAL(10,0)`/`DECIMAL(12,0)` and silently truncates cents — see D-1.

---

## D. CRITICAL findings

### D-1. Money precision fix is not reproducible from migrations (portability)
`[V]` — `2025_06_17_100901_create_{bank_accounts,bank_transactions,expenses,income,staff_salary}_table.php` all declare `decimal(n)` (scale 0); the only alter-to-(10,2) is `2026_09_21_000001_add_reversal_state_to_fee_payments.php:44-46` (fee only). Live DB is correct at (10,2)/(12,2) only because of an undocumented manual alter.
**Impact:** fresh deployments lose cents; running system is fine. **Fix:** codify a guarded ALTER migration (e.g. `decimal(15,2)` — see budgets use `decimal(15,2)` at `2026_02_04_210000...:99`) replicating the live state, include `bank_accounts.minimum_balance` note, and add a test asserting column scale ≥ 2.

### D-2. Payroll "finalize" is a stub that silently reports success
`[V]` — `PayrollProcessingController::finalize` (`app/Http/Controllers/PayrollProcessingController.php:95-114`): the `DB::beginTransaction()` body contains only comments ("Create payroll master record", "Generate payslips", …), commits an emptor transaction, and flashes "Payroll processed successfully." `review()` (L89-93) returns an empty view. The route `POST /payroll-processing/finalize/{payroll}` is real (`routes/web.php:524`) and **unguarded**.
**Impact:** the school believes payroll is processed; nothing is written. Payroll is unusable for real operations.
**Fix:** implement finalize (write `payrolls` + `payroll_details`, post expense/bank ledger, payslips) or hide/disable the action until implemented.

### D-3. Income has no edit/update, but the UI ships an Edit button → guaranteed crash
`[V]` — `IncomeController` has `create/store/show/destroy` only (no `edit`/`update`), yet `resources/views/income/show.blade.php:28` renders `href="{{ route('income.edit', ...) }}"`. Clicking Edit → `BadMethodCallException`/route resolution 500. A wrong income amount today can only be fixed by delete + recreate (delete properly reverses the bank ledger `IncomeController::destroy:110-122`, so the data path is safe — the crash is pure UX/functional).
**Fix:** implement edit/update (re-post ledger delta) or remove the button.

### D-4. No segregation of duties for expenses — requester can approve and pay their own expense
`[S]` — Accountant, Admin, Super Admin and Owner each hold `finance.view + finance.manage + finance.approve` simultaneously (`RbacSeeder.php:103-105, 78, 57, 33`). `ExpensesController::store` sets `status=pending`, but every privileged role can then `approve()` (L149) and `markAsPaid()` (L169) the same row they created. There is no "requester cannot approve own expense" validation and no `approved_by != created_by` check. The audit brief explicitly calls for this control.
**Fix:** validation `approved_by !== created_by`; consider a separate approver role (or Bursar — see E-10) that holds `finance.approve` without `finance.manage`.

---

## E. HIGH findings

### E-1. Money-out actions available to any authenticated user (no guard)
`[V]` — `ExpensesController::markAsPaid` (`ExpensesController.php:169`) and `pending()` (L45) are not covered by any constructor middleware (guards are only on index/show/manage/approve). Routes `POST expenses/{id}/pay` and `GET expenses-pending` are in the `auth` group only. A Teacher/Parent/Student with no finance permission can:
- POST `/expenses/{id}/pay` → pay an expense (moving bank money) — `markAsPaid` L183-220 does validate the account exists and reuses `BankLedger`, so it *works*, not just crashes.
- View the pending-approvals list incl. amounts (`expenses/pending`).

### E-2. No sufficient-balance check — expense can overdraw the bank account
`[V]` — `BankLedger::recordWithdrawal`/`recordTransfer` only `decrement('current_balance', $amount)` (`BankLedger.php:152, 69`); `ExpensesController::markAsPaid` performs no balance comparison (L199-220). `minimum_balance` is tracked and shown (dashboard, bank account health) but never *enforced*. Paying a KES 100,000 expense from a KES 60,000 account silently produces a negative balance.
**Fix:** guard in `BankLedger::record`/`recordTransfer` (reject when `current_balance - amount < minimum_balance` (or < 0)), matching the dashboard's own rule `whereColumn('current_balance','<','minimum_balance')`.

### E-3. Budget vs Actual is unguarded (financial exposure to all roles)
`[V]` — `BudgetController::vsActual` has no middleware (`BudgetController.php:127`); route `GET budget-vs-actual` (web.php:245). Any authenticated user sees budgeted + actual income/expenses. Menu labels it under `finance.view` (`config/menu.php:470`).
**Fix:** add `can:finance.view` (or `finance.reports`).

### E-4. Financial-year audit log is unguarded
`[V]` — `FinancialYearController::audit` (L63) not in the constructor's `only(...)`. Any authenticated user can hit `GET financial-years/{id}/audit` and page through `AuditTrail` rows. Notice the platform's own audit-trail pages are Owner-gated (see test N-2); this action bypasses that policy.
**Fix:** gate with `can:finance.view` or a dedicated audit permission.

### E-5. Mark-transactions-reconciled is gated by `finance.view`, not `finance.approve`
`[V]` — `BankReconciliationController` constructor: `can:finance.view` on everything (`BankReconciliationController.php:15`); `update()` (L36) marks transactions reconciled. The permission model intends reconciliation to be an **approve** action (`2026_07_22_000003...:264-265` maps `bank-reconciliations.edit → finance.approve`). As-is, a mere viewer can "approve" the bank statement.
**S** second issue: `update` marks rows reconciled without cross-checking the statement figure — a real reconciliation should compare `statement_balance` vs `system_balance` (`bank_reconciliations` table fields exist but the flow never computes them).
**Fix:** require `finance.approve`; compute statement/system variance and record it.

### E-6. Budgets: two form failures around financial years
`[V]` —
1. **Create with no open FY:** `BudgetController::create` feeds `FinancialYear::where('status','open')` (`BudgetController.php:46`) into `Form::select('financial_year_id', ...)` (`budgets/fields.blade.php:4`). If no year is open, the dropdown renders with zero options and `financial_year_id` is required (`budgets` FK `onDelete cascade`, `2026_02_04_210000...:96`) — the form is a dead end with no guidance (compare `vsActual` which at least flashes "Please open a financial year first", `BudgetController.php:131`).
2. **Edit of a budget from a now-closed year:** the same open-only list cannot render the stored year; `Form::model` falls back to the first option, so silently saving re-assigns the budget to the wrong financial year.
**Fix:** include closed/other years in the edit select (label "… (closed)"), and add an empty-state message + prevent submit on create.

### E-7. Suppliers gated by inventory permissions — Accountant cannot manage them
`[V]` — `SupplierController` constructor uses `can:inventory.view` / `can:inventory.manage` (`SupplierController.php:22-23`); menu item under Inventory (`config/menu.php:287-288`). Accountant (finance.* only) is blocked from the suppliers the expense form references (`CreateExpensesRequest.php:33` → `supplier_id exists`). Admin/SA/Owner fine.
**Fix:** either reuse `finance.manage` at the controller, or add finance permission to the menu item and document ownership.

### E-8. Payroll form values mismatch the DB enums → save may throw
`[S]` — `PayrollController::create` offers `payment_method` options `cheque` / `mobile_money` (`PayrollController.php:72-78`) that are not in the legacy enum `['cash','check','bank_transfer']` (`2025_06_17_100901_create_payroll_table.php:31`), and `status` options `processing`, `cancelled` vs enum `['pending','paid','canceled']` (L34). With MySQL strict mode an insert/update throws; with loose mode the DB coerces to a valid value. `payroll_details` inherits these columns via the rename (`2026_02_07_000000_revamp_hr_module.php:338`).
**Fix:** align the option lists with the actual column enums (or migrate the enums) — then add the missing `finance`-side bank movement for payroll.

### E-9. Cash-flow report counts *approved-but-unpaid* expenses as cash out; duplicate literal
`[V]` — `FinancialReportController::cashflow` filters expenses `whereIn('status', ['paid', 'approved', 'approved'])` (`FinancialReportController.php:46`) — three values, two identical, and it includes 'approved' which has not moved cash. P&L uses `['paid','approved']` (L64) — also overstates outgo by un-paid approved expenses. Dashboards use the same set (`FinanceDashboardController.php:54,59`). Approved-but-unpaid over-counting inflates expenses everywhere.
**Fix:** decide the accrual convention (recommend: cashflow = paid only; P&L = approved+paid or paid+accruals) and centralize (see I-3).

### E-10. Bursar role is missing (expected by the UI itself)
`[S]` — No Bursar role exists (`RbacSeeder` list). `BankTransactionController::create` comment literally says "so the bursar can see the position while picking an account" (`BankTransactionController.php:59`). Per the brief: flag as **missing role** — add `Bursar` with a defined permission set (recommend `finance.view + finance.manage` sans `finance.approve`, or explicit mapping to Accountant) once E-10/D-4 is sorted.

---

## F. MEDIUM findings

1. **Finance dashboard budget utilization is always 0** — `FinanceDashboardController.php:82` hardcodes `$budgetUtilization = 0` with "Calculated in view or service" comment; the view's budget status card shows meaningless 0. `[V]`
2. **Budget vs actual ignores fee income** — `BudgetController.php:144-152` sums `Income` only; fees intentionally commented out ("might add FeePayment sum"). Since fees are the school's main revenue (209 payments live), an income budget for "Fees" will nearly always read "short." `[V]`
3. **No cash/account groups beyond per-account** — no chart of accounts, GL, or journal for the finance module (absent; stated explicitly). Budget P&L and cashflow recompute from the same raw tables with slightly different criteria, so totals can disagree across screens (combined-income dashboard vs P&L totalIncome vs cashflow). Recommend a single financial service. `[S]`
4. **Transfers are invisible on the target account & can overdraw** — `BankLedger::recordTransfer` writes ONE row (`account_id` = source; `BankLedger.php:72-83`); the target account's statement never shows the credit; no balance check (E-2 applies). `[V]`
5. **Two overlapping payroll UIs** — legacy `Route::resource('payrolls')` CRUD (`PayrollController`, model `payroll_details`) and the wizard (`payroll-processing/`) both present; menu points to the wizard (`config/menu.php:413`). Risk of double-entry of the same month. `[S]`
6. **`config/menu.php` references non-existent permissions `finance.import` / `finance.export`** — parent finance section L435, financial-reports L474, cashflow L476. MenuService uses ANY-match intersect (`MenuService.php:166`) so visibility still works, but the export intent is unenforced (any `finance.view` holder sees the reports — and by E-3/E-5 anyone can reach the data anyway). `[V]`
7. **Supplier search uses `orWhere` across `name`/`code` ungrouped** — `SupplierController.php:33-36` `$query->where('name','like',...)->orWhere('code','like',...)` combined with `where('is_active',...)` can leak rows when status filter is applied. `[V]` (LOW-MEDIUM; worth fixing with a grouped where).
8. **Money displayed with inline `number_format` in several finance views** instead of shared `Money::format` — `expenses/index.blade.php:82`, `expenses/pending.blade.php:42`, `income/index.blade.php:87` (`KES {{ number_format($x, 2) }}`). The rest of the module already uses `Money::format` (finance/dashboard, bank_*, budgets/vs_actual, reports). Convention drift. `[V]`
9. **Expense edit form marks `bank_account_id` as `required` even for cash** — view attribute `required` on `expenses/edit.blade.php:88` while the rule only requires it when `payment_method !== 'cash'` (`UpdateExpensesRequest.php:37-39`) — a cash expense cannot be saved through the edit form without picking a bank. `[V]`
10. **Financial-year update is unfiltered** — `FinancialYearController::update` uses `$request->all()` (L55) with no validation (create has minimal; L31-35). `[S]`

---

## G. LOW severity / UI polish

- **FA6-only icons render as empty boxes under FA 5.14 (the exact defect class from the fee screenshots)**
  - `bank_accounts/index.blade.php:113` — `fa-circle-exclamation`, `fa-circle-check` (FA6 names; FA5 = `fa-exclamation-circle`, `fa-check-circle`) `[V]`
  - `bank_accounts/show.blade.php:65` — `fa-shield-halved` (FA6; FA5 = `fa-shield-alt`) `[V]`
  - `bank_accounts/show.blade.php:81` — `fa-circle-exclamation` / `fa-triangle-exclamation` / `fa-circle-check` (FA6; FA5 = `fa-exclamation-triangle`) `[V]`
  Global FA 5.14.0 is loaded once (`vendor/infyomlabs/laravel-ui-adminlte/.../adminlte-layout.blade.php:11-13`); login/setup use FA 6.4.0 (out of scope). Fix by switching these four classes to FA5 names, verifying against `tmp_fa_meta.json`.
- **No second FA stylesheet anywhere in financial views** — confirms single-source rendering; the blank boxes are purely the FA6-only names above. `[V]`
- Currency label "Amount (KES)" used on expense forms — consistent with `Money::SYMBOL = 'KES'`. `[V]`

---

## H. Authorization matrix (roles × FM surfaces)

Rows = actual role; ✓ = holds permission (menu shows), guarded = controller enforces, ✗/— = blocked at menu or controller.

| Surface | Owner/SA/Admin | Accountant | Teacher | Parent/Student |
|---|---|---|---|---|
| finance.view (dashboards, lists, reports, bank/expense views) | ✓ | ✓ | ✗ | ✗ |
| finance.manage (create/edit/delete expense·income·bank·budget·FY) | ✓ | ✓ | ✗ | ✗ |
| finance.approve (expense approve, reconciliation) | ✓ | ✓ | ✗ | ✗ |
| **expenses.pay (markAsPaid)** | ✓ | ✓ | **✗ guard MISSING → reachable** | **✗ guard MISSING → reachable** |
| **expenses.pending** | ✓ | ✓ | **✗ guard MISSING → reachable** | **✗ guard MISSING → reachable** |
| **budget-vs-actual** | ✓ | ✓ | **✗ guard MISSING → reachable** | **✗ guard MISSING → reachable** |
| **financial-years/{id}/audit** | ✓ | ✓ | **✗ guard MISSING → reachable** | **✗ guard MISSING → reachable** |
| **reconciliation update** | ✓ (even view-only!) | ✓ | ✗ | ✗ |
| **payroll-processing review/finalize** | guard MISSING → reachable | reachable | reachable | reachable |
| Suppliers (inventory.*) | ✓ | **✗ — blocked** | ✗ | ✗ |
| Payroll (hr.*) | ✓ (SA/Admin/Owner only) | **✗** | ✗ (leave apply only) | ✗ |
| audit-trail.index | ✓ (Owner/SA/Admin per RbacSeeder + tests) | ✗ | ✗ | ✗ |

Bottom line: **the "viewers can't act" model breaks in four places (pay, pending, vs-actual, FY-audit), reconciliation needs view-only, and suppliers/payroll are off-limits to the Accountant.**

---

## I. Financial calculation map

| Figure | Where computed | Logic | Concern |
|---|---|---|---|
| Dashboard income this month | `FinanceDashboardController.php:29-40` | `Income(active)` + `FeePayment(notReversed)` | double-counts fee income only once — OK |
| Dashboard expenses | L52-60 | `Expenses(approved|paid)` | includes un-paid approved → overstated |
| Net cash flow / change % | L49, 62, 65-66 | derived | consistent with above |
| Bank totals | `BankTransactionController.php:50` | `sum(current_balance)` over active | balance vs ledger can drift (see C) |
| Low balance count | `FinanceDashboardController.php:70` | `current_balance < minimum_balance` | uses the rule E-2 says is unenforced |
| P&L income | `FinancialReportController.php:59-60` | `Income + FeePayment` | OK |
| P&L expenses | L62-67 | `(approved|paid)`, grouped by category | includes un-paid approved |
| Cash-flow out | L46 | `(paid|approved|approved)` | by definition cash out should be paid-only + dup literal |
| Budget vs actual | `BudgetController.php:137-163` | expense: `Expenses(paid|approved)` · income: `Income(active)` **only** | fees excluded; approved included |
| VAT/PAYE/NHIF/NSSF at source | `PayrollProcessingController.php:117-177` (PAYE 2024 bands, NHIF/NSSF tiers) | not persisted; finalize no-op | formulas should move to a service and be unit-tested |

Formatting: finance module predominantly `Money::format` → `KES 25,000.00`; a few inline `KES number_format(…,2)` outliers (F-8).

---

## J. Broken / risky workflows

1. **Income edit** → guaranteed 500 via broken Edit button (D-3).
2. **Payroll run** → "finalize" writes nothing but says success (D-2).
3. **Pay an expense** → unauthorised roles can; no overdraft guard (E-1, E-2).
4. **Reconcile** → view-only users can approve statement; no variance computed (E-5).
5. **Budget create with zero open YEARS** / **edit a closed-year budget** → dropdown dead-end / silent year reassignment (E-6).
6. **Supplier CRUD for Accountant** blocked mid-workflow (E-7).
7. **Reconciling history**: legacy paid expenses have no ledger rows; `bank_transactions` empty (C).
8. **Payroll status/method enum collisions** can throw on save (E-8).

---

## K. Reports

- `financial-reports/` index, cashflow, P&L — implemented, all roll into `finance.view`. Cashflow/P&L misuse non-paid approved expenses (E-9); no CSV/PDF export despite menu advertising `finance.export` (F-6).
- `budgets.vs-actual` — visually strong (KAST bars, thresholds); income under-counts fees (F-2); unguarded (E-3).
- `financial-years/{id}/audit` — works, unguarded (E-4).
- Global `audit-trail` — Owner/Admin gated; cross-checked by tests.
- **No management dashboards for income/expense trends beyond the finance dashboard.**

---

## L. UI audit (visual/consistency — subset relevant to the reported icon issue)

- Bank Accounts index/show: **empty icon boxes** from FA6-only classes (G-1). Everything else renders under shared FA 5.14.0.
- About what renders "but cannot change": the **budget financial-year dropdown** (E-6) can render a value that cannot be changed to a valid one; **expense pay selector** requires an account on the row (works); **category dropdown** in budget form is JS-driven and functional (verified `budgets/fields.blade.php:34-77` — selects correctly on edit via `Form::model`).
- `income/show.blade.php:28` Edit → crash (D-3).

---

## M. Database integrity

- No foreign keys on `expenses.category_id`, `expenses.bank_account_id`, `expenses.supplier_id`, `income.bank_account_id`, `bank_transactions.account_id`, `budgets.category_id` (original migrations create indexes only). Integrity relies on request validation. `[V]`
- No unique constraint preventing duplicate budget rows per (financial_year, category, type). `[S]`
- `bank_transactions` doesn't link to its source (`expenses`/`income` only via description parsing in `BankLedger::findFor`, `BankLedger.php:126-137`) — reversal depends on string prefixes; fragile if descriptions collide or are edited.
- Live money columns at (10,2)/(12,2) vs migrations (10,0)/(12,0) — portability gap (D-1).
- Audit trails: all finance mutations log via `AuditTrail::log` (expense create/update/approve/pay/delete, income record/delete, budget CRUD, FY CRUD, bank transaction, reconciliation, supplier/payroll CRUD). Strong. `[V]`

---

## N. Tests

Inventory (from full test scan; single Feature suite):
- Representative finance-relevant tests: `RbacVisibilityTest` (finance report visibility 403 for Student; audit-trail Owner-only; fee reports 200-Accountant / 403-Teacher), `FeeAuthorizationTest` (every fee endpoint permission-guarded), `FeeScreensSmokeTest` (fee report screens render 200 as Accountant; destructive actions carry `confirm()`), `FeeReconciliationTest`, `RefundReconciliationTest`, `RefundBankPostingTest`, `MobileFeeIdempotencyTest`, plus fee dashboard/reports/UI suites. (Fee-owned — read-only.)

**Planned commands (NOT run — see header):**
```
# 1. Create the scratch test DB (one-time, admin creds)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS school_erp_test"
# 2. Point the test suite at it (creates/seed staging only)
php artisan test --env=testing    # after creating .env.testing with DB_DATABASE=school_erp_test
# 3. Targeted finance run
php artisan test --filter='RbacVisibilityTest|FeeAuthorizationTest|FeeScreensSmokeTest'
```
Expected (static analysis): RbacVisibilityTest passes (guards in controllers line up), screens smoke-tests pass. Gaps not yet covered by tests: E-1 (markAsPaid/pending/vsActual/FY-audit unguarded), E-2 (overdraft), D-2 (finalize stub), D-3 (income edit crash), E-6 (budget dropdown). These should be encoded as new tests in a later phase.

---

## O. Shared dependencies with the parallel Fee Management session

**Do NOT touch without coordination — the Fee session is actively editing these:**
- `app/Services/FinanceService.php`, `LedgerService.php`, `FeeBalanceService.php` (fee-domain; finance module does not use them).
- `app/Support/Money.php` (shared formatter; both modules read it — read-only here).
- Tables `fee_payments`, `ledger_entries`, `fee_structures`, `student_fee_assignments`, `refunds`, `payment_allocations` — the Finance module **reads** fee payments (dashboards/reports/budget could read them) but must not write these.
- `config/menu.php` (finance section L429-482 already coexists with fee edits — any edit risk is low but coordinate).
- `routes/web.php` (finance group edits coordinated).
- `tests/Feature/Fee*Test.php` — Fee-owned.
- `docs/fee-management-audit.md`, `tmp_*.php` / `tmp_fa_meta.json` — Fee-owned scratch files; do not clean.

New FM-surface writes go through `BankLedger` + the finance tables only (`bank_accounts`, `bank_transactions`, `bank_reconciliations`, `expenses`, `income`, `budgets`, `financial_years`, `expense_categories`, `income_categories`, `suppliers`, `payrolls`, `payroll_details`) — these have **no overlap** with Fee-owned files/tables except read-only fee reads. Safe to edit sidebar finance entries and this module's controllers/views without merge conflicts.

---

## P. Already done well

- `BankLedger` centralizes balance + statement writes in one transaction; reversals mark rows `voided` (history preserved), double-submit guard on `markAsPaid`, DB::transaction for pay+ledger atomicity. `[V]`
- Permission introspection: controller constructors are consistent for guarded paths; the `finance.*` trio is simple and uniform; the legacy route-permission remap went cleanly through one migration.
- Financial reports fixed the historical `finance.export` permanent-403 (documented in `FinancialReportController.php:17-22`). `[V]`
- Money formatting converges on one formatter (finance module 90%+, and labels use KES consistently).
- Bank account health/minimum-balance tracking and the Budget-vs-actual page (utilization bars, thresholds, variance, print) are well-crafted. `[V]`
- AuditTrail logged on every finance write; `ExpensesController::destroy` correctly reverses ledger before delete; `IncomeController::destroy` same. `[V]`

---

## Q. Recommended phases (proposed — NOT implemented; await approval)

1. **Phase 1 — Control & integrity (CRITICAL):**
   - Codify money precision as an idempotent migration replicating the live (10,2)/(12,2) schema (D-1) + a test asserting column scale.
   - Implement `PayrollProcessingController::finalize` or gate/disable it (D-2).
   - Guard `markAsPaid`, `pending`, `vsActual`, `financial-years.audit`; raise reconciliation `update` to `finance.approve`; guard payroll review/finalize (E-1/3/4/5).
   - Enforce overdraft rule inside `BankLedger` (E-2).
2. **Phase 2 — Segmentation & roles:**
   - Requester-cannot-approve/pay rule; introduce **Bursar** role with a defined permission split; align supplier menu/permissions for Accountant (D-4, E-7, E-10).
3. **Phase 3 — Fix broken workflow/UI:**
   - Income edit/update (with ledger delta) or remove button (D-3); fix FA6 icon names in bank_accounts views (G-1); fix budget year dropdown edge cases (E-6); fix payroll enum options (E-8); centralize cashflow/P&L conventions, include fees in budget actuals (E-9, F-2, F-3).
4. **Phase 4 — Verification & hardening:**
   - Create `school_erp_test` scratch DB; run the full suite + new tests covering E-1/D-2/D-3/E-6/E-2; add `menu:validate` to CI; extend a finance test for the shared Money formatter.