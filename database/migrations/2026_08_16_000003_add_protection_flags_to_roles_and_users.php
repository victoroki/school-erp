<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('description');
            $table->boolean('is_hidden')->default(false)->after('is_protected');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('is_active');
            $table->boolean('is_hidden')->default(false)->after('is_protected');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['is_protected', 'is_hidden']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_protected', 'is_hidden']);
        });
    }
};
