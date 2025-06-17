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
        Schema::create('books', function (Blueprint $table) {
            $table->integer('book_id', true);
            $table->string('title');
            $table->string('author');
            $table->integer('category_id')->nullable()->index('category_id');
            $table->string('isbn', 20)->nullable()->unique('isbn');
            $table->string('publisher', 100)->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('edition', 50)->nullable();
            $table->decimal('price', 10)->nullable();
            $table->integer('pages')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->string('shelf_location', 50)->nullable();
            $table->date('added_date');
            $table->text('description')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
