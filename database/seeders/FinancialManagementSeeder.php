<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\BankAccount;
use App\Models\FinancialYear;
use Carbon\Carbon;

class FinancialManagementSeeder extends Seeder
{
    public function run()
    {
        // 1. Financial Year
        $year = FinancialYear::create([
            'name' => '2025/2026 Academic Year',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'open'
        ]);

        // 2. Bank Accounts
        BankAccount::create([
            'account_name' => 'Main Operations Account',
            'account_number' => '1100223344',
            'bank_name' => 'Equity Bank',
            'branch_name' => 'Westlands',
            'account_type' => 'Business',
            'opening_balance' => 500000,
            'current_balance' => 500000,
            'minimum_balance' => 10000,
            'currency' => 'KES',
            'status' => 'active'
        ]);

        BankAccount::create([
            'account_name' => 'Fees Collection Account',
            'account_number' => '5566778899',
            'bank_name' => 'KCB Bank',
            'branch_name' => 'Kenyatta Avenue',
            'account_type' => 'Current',
            'opening_balance' => 1200000,
            'current_balance' => 1200000,
            'minimum_balance' => 50000,
            'currency' => 'KES',
            'status' => 'active'
        ]);

        // 3. Categories
        $incomeCats = [
            ['name' => 'School Fees', 'color_code' => '#10b981'],
            ['name' => 'Government Grants', 'color_code' => '#3b82f6'],
            ['name' => 'Donations', 'color_code' => '#f59e0b'],
            ['name' => 'Rental Income', 'color_code' => '#8b5cf6'],
        ];

        foreach ($incomeCats as $cat) {
            IncomeCategory::updateOrCreate(['name' => $cat['name']], [
                'description' => 'Standard income from ' . strtolower($cat['name']),
                'color_code' => $cat['color_code'],
                'status' => 'active'
            ]);
        }

        $expenseCats = [
            ['name' => 'Staff Salaries', 'color_code' => '#ef4444'],
            ['name' => 'Utility Bills', 'color_code' => '#f97316'],
            ['name' => 'Stationery', 'color_code' => '#6b7280'],
            ['name' => 'Infrastructure Maintenance', 'color_code' => '#d946ef'],
            ['name' => 'Laboratory Equipment', 'color_code' => '#06b6d4'],
        ];

        foreach ($expenseCats as $cat) {
            ExpenseCategory::updateOrCreate(['name' => $cat['name']], [
                'description' => 'Expenses related to ' . strtolower($cat['name']),
                'color_code' => $cat['color_code'],
                'status' => 'active'
            ]);
        }
    }
}
