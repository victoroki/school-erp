<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Opt-in flag for fee revenue on income budgets.
     *
     * The school's main revenue is student fees, but fee payments carry no
     * income-category id, so a "Fees" income budget could only ever count
     * Income rows and would read permanently short (audit F-2). A budget row
     * may now declare that fee payments belong to its actuals.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (!Schema::hasColumn('budgets', 'include_fees')) {
                $table->boolean('include_fees')->default(false)->after('category_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (Schema::hasColumn('budgets', 'include_fees')) {
                $table->dropColumn('include_fees');
            }
        });
    }
};