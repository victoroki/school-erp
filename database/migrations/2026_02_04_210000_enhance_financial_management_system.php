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
        // Update Expenses table
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'status')) {
                $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'rejected'])->default('pending')->after('amount');
            }
            if (!Schema::hasColumn('expenses', 'bank_account_id')) {
                $table->integer('bank_account_id')->nullable()->index()->after('category_id');
            }
            if (!Schema::hasColumn('expenses', 'supplier_id')) {
                $table->integer('supplier_id')->nullable()->index()->after('bank_account_id');
            }
            if (!Schema::hasColumn('expenses', 'requested_by')) {
                $table->integer('requested_by')->nullable()->index()->after('supplier_id');
            }
            if (!Schema::hasColumn('expenses', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('expense_date');
            }
            if (!Schema::hasColumn('expenses', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('description');
            }
            if (!Schema::hasColumn('expenses', 'attachment')) {
                $table->string('attachment')->nullable()->after('reference_number');
            }
            if (!Schema::hasColumn('expenses', 'receipt_number')) {
                $table->string('receipt_number', 50)->nullable()->after('attachment');
            }
        });

        // Update Income table
        Schema::table('income', function (Blueprint $table) {
            if (!Schema::hasColumn('income', 'status')) {
                $table->enum('status', ['active', 'cancelled'])->default('active')->after('amount');
            }
            if (!Schema::hasColumn('income', 'bank_account_id')) {
                $table->integer('bank_account_id')->nullable()->index()->after('category_id');
            }
            if (!Schema::hasColumn('income', 'payer_name')) {
                $table->string('payer_name')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('income', 'attachment')) {
                $table->string('attachment')->nullable()->after('reference_number');
            }
            if (!Schema::hasColumn('income', 'receipt_number')) {
                $table->string('receipt_number', 50)->nullable()->after('attachment');
            }
        });

        // Update Bank Accounts table
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'account_holder')) {
                $table->string('account_holder')->nullable()->after('account_type');
            }
            if (!Schema::hasColumn('bank_accounts', 'minimum_balance')) {
                $table->decimal('minimum_balance', 15, 2)->default(0)->after('current_balance');
            }
            if (!Schema::hasColumn('bank_accounts', 'interest_rate')) {
                $table->decimal('interest_rate', 5, 2)->default(0)->after('minimum_balance');
            }
            if (!Schema::hasColumn('bank_accounts', 'currency')) {
                $table->string('currency', 3)->default('KES')->after('interest_rate');
            }
            if (!Schema::hasColumn('bank_accounts', 'status')) {
                $table->enum('status', ['active', 'inactive', 'closed'])->default('active')->after('currency');
            }
        });

        // Create Financial Years table
        if (!Schema::hasTable('financial_years')) {
            Schema::create('financial_years', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->timestamps();
            });
        }

        // Create Budgets table
        if (!Schema::hasTable('budgets')) {
            Schema::create('budgets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
                $table->integer('category_id');
                $table->string('category_type'); // 'income' or 'expense'
                $table->decimal('amount', 15, 2);
                $table->decimal('alert_threshold', 5, 2)->default(80); // percentage
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }

        // Create Bank Reconciliations table
        if (!Schema::hasTable('bank_reconciliations')) {
            Schema::create('bank_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->integer('bank_account_id');
                $table->date('statement_date');
                $table->decimal('statement_balance', 15, 2);
                $table->decimal('system_balance', 15, 2);
                $table->enum('status', ['pending', 'completed'])->default('pending');
                $table->integer('reconciled_by')->nullable();
                $table->timestamps();
            });
        }
        
        // Petty Cash logs
        if (!Schema::hasTable('petty_cash_logs')) {
            Schema::create('petty_cash_logs', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->decimal('amount', 10, 2);
                $table->string('description');
                $table->string('type'); // 'top-up', 'expense'
                $table->string('reference')->nullable();
                $table->integer('recorded_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_logs');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('financial_years');
        
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['account_holder', 'minimum_balance', 'interest_rate', 'currency', 'status']);
        });

        Schema::table('income', function (Blueprint $table) {
            $table->dropColumn(['status', 'bank_account_id', 'payer_name', 'attachment', 'receipt_number']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['status', 'bank_account_id', 'supplier_id', 'requested_by', 'payment_date', 'rejected_reason', 'attachment', 'receipt_number']);
        });
    }
};
