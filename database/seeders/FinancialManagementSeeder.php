<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\BankAccount;
use App\Models\FinancialYear;

class FinancialManagementSeeder extends Seeder
{
    public function run()
    {
        // 1. Financial Year (idempotent — one open financial year)
        FinancialYear::updateOrCreate(
            ['name' => '2026/2027 Financial Year'],
            [
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'status' => 'open',
            ]
        );

        // 2. Kenyan Bank Accounts
        $accounts = [
            [
                'account_name' => 'Shujaa Academy — Main Operations Account',
                'account_number' => '1100223344',
                'bank_name' => 'Equity Bank Kenya',
                'branch_name' => 'Westlands',
                'account_type' => 'Business',
                'opening_balance' => 500000.00,
                'current_balance' => 500000.00,
                'minimum_balance' => 10000.00,
                'currency' => 'KES',
                'status' => 'active',
            ],
            [
                'account_name' => 'Shujaa Academy — Fees Collection Account',
                'account_number' => '5566778899',
                'bank_name' => 'KCB Bank Kenya',
                'branch_name' => 'Kenyatta Avenue',
                'account_type' => 'Current',
                'opening_balance' => 1200000.00,
                'current_balance' => 1200000.00,
                'minimum_balance' => 50000.00,
                'currency' => 'KES',
                'status' => 'active',
            ],
            [
                'account_name' => 'Shujaa Academy — Cooperative Bank Deposit',
                'account_number' => '01133445566',
                'bank_name' => 'Cooperative Bank of Kenya',
                'branch_name' => 'Community',
                'account_type' => 'Deposit',
                'opening_balance' => 750000.00,
                'current_balance' => 750000.00,
                'minimum_balance' => 20000.00,
                'currency' => 'KES',
                'status' => 'active',
            ],
        ];

        foreach ($accounts as $acc) {
            BankAccount::firstOrCreate(['account_number' => $acc['account_number']], $acc);
        }

        // 3. Income Categories (Kenyan revenue streams)
        $incomeCats = [
            ['name' => 'School Fees', 'color_code' => '#10b981', 'description' => 'Tuition and boarding fee collections'],
            ['name' => 'Government Capitation Grant', 'color_code' => '#3b82f6', 'description' => 'Free Primary/Secondary education capitation from MoE'],
            ['name' => 'Donations & Bursaries', 'color_code' => '#f59e0b', 'description' => 'Donor funds and county bursary contributions'],
            ['name' => 'Rental Income', 'color_code' => '#8b5cf6', 'description' => 'Rental of school facilities and land'],
            ['name' => 'Levies & Activity Charges', 'color_code' => '#14b8a6', 'description' => 'Examination, co-curricular and activity levies'],
        ];

        foreach ($incomeCats as $cat) {
            IncomeCategory::updateOrCreate(['name' => $cat['name']], [
                'description' => $cat['description'],
                'color_code' => $cat['color_code'],
                'status' => 'active',
            ]);
        }

        // 4. Expense Categories
        $expenseCats = [
            ['name' => 'Staff Salaries', 'color_code' => '#ef4444', 'description' => 'TSC/board teacher and support staff salaries'],
            ['name' => 'Utility Bills', 'color_code' => '#f97316', 'description' => 'Electricity (KPLC), water and internet'],
            ['name' => 'Stationery & Teaching Materials', 'color_code' => '#6b7280', 'description' => 'Books, chalk, printing and classroom supplies'],
            ['name' => 'Infrastructure Maintenance', 'color_code' => '#d946ef', 'description' => 'Building and campus upkeep'],
            ['name' => 'Laboratory & Workshop Equipment', 'color_code' => '#06b6d4', 'description' => 'Science, ICT and workshop equipment'],
            ['name' => 'Transport & Fuel', 'color_code' => '#84cc16', 'description' => 'School buses, fuel and vehicle maintenance'],
            ['name' => 'Food & Catering', 'color_code' => '#f43f5e', 'description' => 'Boarding kitchen supplies and meals'],
        ];

        foreach ($expenseCats as $cat) {
            ExpenseCategory::updateOrCreate(['name' => $cat['name']], [
                'description' => $cat['description'],
                'color_code' => $cat['color_code'],
                'status' => 'active',
            ]);
        }
    }
}
