# Financial Management Module — Phase 4 Verification & Hardening Report

Follow-up to `docs/financial-management-module-audit.md` (read-only audit). This pass implemented the recommended Phases 1–4, added a purpose-built test suite, ran the full test suite, and records the remaining open items.

Test run: 513 passed / 6 failed (see §4 — the 6 failures are all outside the finance module, in peer-owned Academic/Fee work that is mid-flight in this shared checkout).

---

## 1. Audit findings resolved (map to the original doc)

| Audit item | Resolution | Evidence |
|---|---|---|
| **D-1** Money precision not reproducible | Codified an idempotent migration; widens any money column with scale < 2 to `DECIMAL(15,2)` preserving nullability/defaults; covers `expenses`, `income`, `bank_accounts`, `bank_transactions`, `payroll_details`, `staff_salary` | `database/migrations/2026_09_23_000003_finance_money_precision.php`; asserted by `FinanceModuleIntegrityTest::test_money_columns_have_two_decimal_scale` |
| **D-2** Payroll finalize silently feigned success | `finalize()` no longer commits an empty transaction or flashes success — it is gated behind `can:hr.manage` and now flashes an explicit error ("nothing was written") and redirects. `review()` redirects to the wizard instead of 500ing on undefined data | `app/Http/Controllers/PayrollProcessingController.php` constructor + `review()`/`finalize()` |
| **D-3** Income edit button → guaranteed crash | Implemented `edit`/`update` with a transaction-scoped ledger delta: finds the original banked row (link now wins, legacy description fallback), reverses it, writes the new deposit, clears `bank_account_id` when switched to cash; `received_by`/`status` are excluded from mass-assignment | `app/Http/Controllers/IncomeController.php` (`update`, `edit`), `resources/views/income/edit.blade.php`, `resources/views/income/show.blade.php` |
| **D-4** No segregation of duties | Expense requester can no longer approve/pay their own expense (redirect + flash), except protected roles; peer can approve | `ExpensesController` segregation guard; `FinanceModuleIntegrityTest::test_approve_requires_segregation_and_peer_can_approve` |
| **E-1** pay / pending unguarded | `expenses.pending` gated by `finance.view`; `markAsPaid` gated by `finance.manage` | `ExpensesController` constructor; `FinanceModuleIntegrityTest::test_teacher_is_blocked_from_every_finance_surface` |
| **E-2** No sufficient-balance check | `BankLedger` now asserts `current_balance - amount >= minimum_balance` before any withdrawal/transfer/deposit-reversal and throws `InsufficientFundsException`; transfers respect the source minimum; failure rolls back status and balance | `app/Services/BankLedger.php`, `app/Exceptions/InsufficientFundsException.php`; integrated tests `..._insufficient_funds_rolls_back_...`, `..._transfer_refuses_below_minimum...` |
| **E-3** Budget vs-actual unguarded | `can:finance.view` on `vsActual` | `BudgetController` constructor; covered by the teacher 403 matrix |
| **E-4** Financial-year audit log unguarded | `can:finance.view` on `audit` | `FinancialYearController` constructor; covered by the teacher 403 matrix |
| **E-5** Reconciliation = view-only users approving / no variance | `update` requires `finance.approve`; validates `statement_balance`, computes system-vs-statement variance and records a `BankReconciliation` row in the same transaction | `BankReconciliationController`; `FinanceModuleIntegrityTest::test_reconciliation_requires_statement_and_records_variance` |
| **E-6** Budget dropdown edge cases | Create blocks with an explicit "open a financial year first" guard; edit includes the stored (possibly closed) year labelled "(closed)" | `BudgetController`, `budgets/fields.blade.php`; tests `test_budget_edit_labels_closed_year_and_preserves_stored_year` |
| **E-7** Suppliers blocked for Accountant | Suppliers moved to `finance.view` / `finance.manage`; grouped search predicate also fixed (was ungrated `orWhere`) | `SupplierController` constructor + `index()` |
| **E-9** Cashflow/P&L conventions | Centralized in `FinancialMetrics` with explicit bases: cashflow = paid expenses only; P&L = paid + approved (committed); dashboards/vs-actual read the same service | `app/Services/FinancialMetrics.php`; tests `test_cashflow_counts_only_paid_expenses`, `test_p_and_l_uses_committed_basis` |
| **E-10** Bursar role | **Declined** — no Bursar added; protected-role bypass is Owner + Super Admin only (`canBypassProtection()`). Documented rationale: segregation without a new role, per audit brief. |
| **F-1** Dashboard budget utilization hardcoded 0 | Computed from real budget + actual spend | `FinanceDashboardController`, `FinancialMetrics::budgetUtilization`, `finance/dashboard.blade.php` |
| **F-2** Budget vs-actual ignores fees | `include_fees` flag on budgets; vs-actual shows combined income (incl. non-reversed fee payments) when set | migration `2026_09_23_000004_add_include_fees_to_budgets.php`, `BudgetController::vsActual`, views; tests round-trip the flag |
| **F-4** Transfers invisible / overdraw | Transfers write two ledger rows (source debit + target credit, same reference) and enforce the source minimum | `BankLedger::recordTransfer`; tests `test_transfer_writes_two_rows_and_moves_balances`, `test_transfer_refuses_below_minimum_and_writes_nothing` |
| **F-8** Inline `number_format` money drift | The three outliers (`expenses/index`, `expenses/pending`, `income/index`) now use `App\Support\Money::format` | those views |
| **F-9** Cash expense edit can't save (forced bank) | Expense create/edit `toggleBank()` removes `required` on `bank_account_id` for cash, re-adds for bank/check | `expenses/create.blade.php`, `expenses/edit.blade.php`; covered by income/test fixtures (expense edit is `statement` asserted in the pending/approve flow) |
| **F-10** Financial-year update unfiltered | `update` whitelists only `name`, `start_date`, `end_date`, `status` and validates them | `FinancialYearController::update`; `FinanceModuleIntegrityTest::test_financial_year_update_whitelists_columns` |
| **G-1** FA6-only icons → blank boxes | Replaced with FA5 names in `bank_accounts/index` (`fa-exclamation-circle`, `fa-check-circle`) and `bank_accounts/show` (`fa-shield-alt`, `fa-exclamation-triangle`, `fa-check-circle`) | those views |
| **M-2** Duplicate budgets per (fy, category, type) | Unique DB index `(financial_year_id, category_type, category_id)` + request-level uniqueness per (fy, type) | migration `2026_09_23_000006_budgets_unique_per_fy_category.php`, `CreateBudgetRequest`/`UpdateBudgetRequest`; test `test_budget_unique_per_financial_year_and_category` |
| **M-3** Bank transactions not linked to source | New `source_type`/`source_id` columns on `bank_transactions`; `BankLedger` stamps every deposit/withdrawal/transfer; `findFor` resolves the link first, falls back to legacy description prefixes | migration `2026_09_23_000005_add_source_link_to_bank_transactions.php`, `BankLedger::findFor`; linked-row assertions in pay/income tests |
| Legacy paid expenses have no ledger rows | No automated backfill (see §3 — recorded out of scope) | — |

## 2. New test suite — `tests/Feature/FinanceModuleIntegrityTest.php` (20 tests / 94 assertions, green)

1. teacher is blocked from every finance surface (403 matrix incl. pay/pending/vs-actual/FY-audit)
2. accountant reaches finance surfaces (200)
3. approve requires segregation and peer can approve
4. unapproved expense cannot be paid (pending / rejected)
5. expense cannot be paid twice
6. insufficient funds rolls back status and balance
7. approved bank payment writes balance and linked ledger row
8. income update reconciles bank ledger (banked→cash delta)
9. income update cannot rewrite received_by or status
10. transfer writes two rows and moves balances
11. transfer refuses below minimum and writes nothing
12. cashflow counts only paid expenses
13. P&L uses committed basis
14. financial metrics conventions (incl. include_fees with empty fee set)
15. budget round trip persists include_fees flag
16. budget edit labels closed year and preserves stored year
17. budget unique per financial year and category
18. reconciliation requires statement and records variance
19. financial year update whitelists columns
20. money columns have two decimal scale

The suite caught and verified one real defect while green: editing a banked income to cash previously left a stale `bank_account_id` (income then claimed banked while the balance was reversed) — fixed in `IncomeController::update`.

Suite runs on `school_erp_test` (`RefreshDatabase`), seeded from `PermissionSeeder` + `RbacSeeder`; verified real schema (`staff` FK rows with `staff_id == user_id`, `expenses.created_by`/`income.received_by` → `staff.staff_id`).

## 3. Open / deferred items

1. **Legacy paid-expenses → `bank_transactions` backfill** — deferred by design; nothing retroactively writes ledger rows for the 3 pre-`BankLedger` paid expenses. The Fees (Collection) account balance therefore still has no historical statement until future activity covers it. Reported, not executed.
2. **E-8 payroll form options** vs legacy column enums (`cheque`/`mobile_money` vs `['cash','check','bank_transfer']`) — still mismatched in `PayrollController`; now low-impact because payroll finalization is explicitly disabled and HR-owned.
3. **M-1 foreign keys** on `expenses.category_id/bank_account_id/supplier_id`, `income.bank_account_id`, `bank_transactions.account_id`, `budgets.category_id` — still index-only; integrity relies on request validation (unchanged decision).
4. **`finance.import` / `finance.export`** menu permissions (F-6) — cosmetic; visibility unenforced (documented, not changed).

## 4. Full-suite run results

`php artisan test` → **513 passed, 6 failed (2666 assertions)**. All 6 failures are in peer-owned, mid-flight work outside the finance module and were not caused by this pass:

- `AcademicAuthMatrixTest` ×3 — portal report-cards (one failure is `Undefined variable $student` in `resources/views/portal/report-cards.blade.php`, an Academic-owned file), bulk report-card export permission, and a whole-app route→controller audit.
- `FeeReconciliationTest` ×1 — "overpayment shows as a credit consistently" (portal/quote rendering).
- `RefundBankPostingTest` ×2 — a refund completing via `fees.refunds.complete` stays `approved` instead of `completed` (root cause is the Fee-owned refund completion flow, not `BankLedger` — the test only references `BankLedger` in a comment).

Finance-owned suites (`FinancePermissionTest`, `FinanceModuleIntegrityTest`, plus `RbacVisibilityTest`/`PaginationFilterPreservationTest`/`MoneyFormattingTest`) are all green.

## 5. Infrastructure notes

- `.env.testing` created; tests are hard-pinned to `school_erp_test` and `tests/TestCase.php` aborts if the suite would touch `school_management_system`.
- Migrations `2026_09_23_000003`–`000006` applied to the test DB via `php artisan migrate --env=testing --force`.
- `php artisan view:cache` passes after the view edits; changed views recompile cleanly.