<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'department_id')) {
                // Must match departments.department_id (signed INT) or FK errno 150
                $table->integer('department_id')->nullable()->after('subject_id');
                $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The up() guard can skip FK creation when the column pre-exists
        // (e.g. DB imported from a dump), so only drop what actually exists.
        $foreignKeyExists = !empty(DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
             AND TABLE_NAME = 'subjects'
             AND CONSTRAINT_NAME = 'subjects_department_id_foreign'
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        ));

        if ($foreignKeyExists) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });
        }

        if (Schema::hasColumn('subjects', 'department_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('department_id');
            });
        }
    }
};
