<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_categories', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('name');
            }
            if (!Schema::hasColumn('expense_categories', 'color_code')) {
                $table->string('color_code', 20)->default('#6c757d')->after('description');
            }
        });

        Schema::table('income_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('income_categories', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('name');
            }
            if (!Schema::hasColumn('income_categories', 'color_code')) {
                $table->string('color_code', 20)->default('#28a745')->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn(['status', 'color_code']);
        });

        Schema::table('income_categories', function (Blueprint $table) {
            $table->dropColumn(['status', 'color_code']);
        });
    }
};
