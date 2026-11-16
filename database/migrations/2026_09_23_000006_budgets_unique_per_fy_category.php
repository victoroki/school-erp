<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce one budget line per financial year + category.
     *
     * The create/update requests already carry a Rule::unique() check, but a
     * duplicate could still slip in through a second process racing between
     * validation and insert. A database constraint makes the rule structural.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (!Schema::hasIndex('budgets', 'budgets_fy_type_category_unique')) {
                $table->unique(['financial_year_id', 'category_type', 'category_id'], 'budgets_fy_type_category_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (Schema::hasIndex('budgets', 'budgets_fy_type_category_unique')) {
                $table->dropUnique('budgets_fy_type_category_unique');
            }
        });
    }
};