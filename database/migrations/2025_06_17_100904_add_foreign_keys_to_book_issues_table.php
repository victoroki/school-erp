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
        Schema::table('book_issues', function (Blueprint $table) {
            $table->foreign(['book_id'], 'book_issues_ibfk_1')->references(['book_id'])->on('books')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['member_id'], 'book_issues_ibfk_2')->references(['member_id'])->on('library_members')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['issued_by'], 'book_issues_ibfk_3')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['received_by'], 'book_issues_ibfk_4')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_issues', function (Blueprint $table) {
            $table->dropForeign('book_issues_ibfk_1');
            $table->dropForeign('book_issues_ibfk_2');
            $table->dropForeign('book_issues_ibfk_3');
            $table->dropForeign('book_issues_ibfk_4');
        });
    }
};
