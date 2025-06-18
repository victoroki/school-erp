<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTeacherStaff extends Migration
{
    public function up()
    {
        // You can leave this empty or add a dummy column if needed
        // Schema::table('staff', function (Blueprint $table) {
        //     $table->boolean('is_teacher')->default(false);
        // });
    }

    public function down()
    {
        // Reverse the dummy change
        // Schema::table('staff', function (Blueprint $table) {
        //     $table->dropColumn('is_teacher');
        // });
    }
}
