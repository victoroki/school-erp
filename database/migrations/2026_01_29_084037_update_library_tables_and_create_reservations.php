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
        // Add missing fields to Books table
        Schema::table('books', function (Blueprint $table) {
            $table->string('condition')->default('good')->after('price'); // new, good, fair, poor
            $table->string('cover_url')->nullable()->after('description');
            $table->string('barcode')->nullable()->unique()->after('isbn');
        });

        // Add missing fields to Library Members table
        Schema::table('library_members', function (Blueprint $table) {
            $table->date('membership_expiry_date')->nullable()->after('membership_date');
        });

        // Create Book Reservations table
        if (!Schema::hasTable('book_reservations')) {
            Schema::create('book_reservations', function (Blueprint $table) {
                $table->id('reservation_id');
                $table->integer('book_id')->index();
                $table->integer('member_id')->index();
                $table->date('reservation_date');
                $table->date('expiry_date')->nullable();
                $table->enum('status', ['pending', 'fulfilled', 'cancelled', 'expired'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['condition', 'cover_url', 'barcode']);
        });

        Schema::table('library_members', function (Blueprint $table) {
            $table->dropColumn(['membership_expiry_date']);
        });

        Schema::dropIfExists('book_reservations');
    }
};
