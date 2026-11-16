<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parent record for money received from a sponsor (CDF, county, NGO, church,
 * donor, corporate bursary...) that will be distributed to students.
 *
 * The receipt itself is the single cash event. Each student allocation later
 * creates an ordinary fee payment that carries bulk_receipt_id, so the whole
 * existing allocation/ledger/reversal machinery keeps working and the money is
 * never posted to income twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_bulk_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('sponsor_name', 150);
            $table->string('sponsor_type', 50)->default('other'); // cdf, county_government, ngo, church, donor, scholarship_provider, corporate, other
            $table->string('reference_number', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'check', 'card', 'bank_transfer', 'online'])->default('bank_transfer');
            $table->integer('bank_account_id')->nullable(); // finance-domain posting destination
            $table->string('transaction_id', 100)->nullable();
            $table->date('received_date');
            $table->integer('academic_year_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->text('remarks')->nullable();

            // Reversal of the parent receipt (allocations must be reversed first).
            $table->dateTime('reversed_at')->nullable();
            $table->string('reversal_reason', 500)->nullable();
            $table->integer('reversed_by')->nullable();

            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('academic_year_id')->references('academic_year_id')->on('academic_years')->nullOnDelete();
            $table->foreign('term_id')->references('id')->on('terms')->nullOnDelete();
            $table->foreign('bank_account_id')->references('account_id')->on('bank_accounts')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['received_date']);
            $table->unique(['sponsor_name', 'reference_number'], 'fee_bulk_receipts_sponsor_reference_unique');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            // Links a student-level payment to the parent bulk receipt it came
            // from. Nullable: ordinary counter payments have no parent.
            $table->unsignedBigInteger('bulk_receipt_id')->nullable()->after('collected_by');
            $table->foreign('bulk_receipt_id')->references('id')->on('fee_bulk_receipts')->nullOnDelete();
            $table->index('bulk_receipt_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['bulk_receipt_id']);
            $table->dropColumn('bulk_receipt_id');
        });

        Schema::dropIfExists('fee_bulk_receipts');
    }
};
