<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link ledger rows back to the record that created them.
     *
     * Reversals used to depend on string-prefixing the description
     * ("Expense #12 ..."), which is fragile if descriptions are edited or
     * collide (audit C-2). New rows carry source_type/source_id; lookups in
     * BankLedger::findFor prefer them and fall back to the legacy description
     * pattern for rows written before this migration.
     */
    public function up(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_transactions', 'source_type')) {
                $table->string('source_type', 30)->nullable()->after('description');
            }
            if (!Schema::hasColumn('bank_transactions', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('bank_transactions', 'source_id')) {
                $table->dropColumn('source_id');
            }
            if (Schema::hasColumn('bank_transactions', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};