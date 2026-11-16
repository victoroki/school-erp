<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 3 — payment idempotency.
 *
 * The mobile app generates a stable client UUID for every fee payment it
 * records (including while offline). This column lets the SERVER deduplicate
 * the same logical payment if it is submitted more than once — e.g. a retry
 * after a lost response — so an accountant can never double-charge a student.
 *
 * Design:
 *  - `client_reference` is a nullable unique string on `fee_payments`. It is
 *    nullable so existing (legacy) rows, which were created before idempotency
 *    existed, remain valid (MySQL/SQLite permit multiple NULLs in a unique
 *    index). New mobile payments always populate it.
 *  - It is an ADDITIVE, backwards-compatible change: no existing payment is
 *    altered or removed.
 *
 * The mobile payload key is `client_uuid`; the column is named
 * `client_reference` to make its purpose self-evident next to the
 * `transaction_id` (provider reference) already on the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('fee_payments', 'client_reference')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->string('client_reference', 64)
                    ->nullable()
                    ->after('transaction_id')
                    ->unique();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fee_payments', 'client_reference')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->dropUnique(['client_reference']);
                $table->dropColumn('client_reference');
            });
        }
    }
};
