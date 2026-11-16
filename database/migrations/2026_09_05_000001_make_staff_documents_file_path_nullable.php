<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff documents may be created/updated without re-uploading a file
     * (the existing file is kept on edit), so file_path must be nullable
     * to match the validation rules.
     */
    public function up(): void
    {
        Schema::table('staff_documents', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_documents', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
        });
    }
};