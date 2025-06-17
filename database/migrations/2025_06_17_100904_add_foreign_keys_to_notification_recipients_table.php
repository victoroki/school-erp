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
        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->foreign(['notification_id'], 'notification_recipients_ibfk_1')->references(['notification_id'])->on('notifications')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['recipient_id'], 'notification_recipients_ibfk_2')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->dropForeign('notification_recipients_ibfk_1');
            $table->dropForeign('notification_recipients_ibfk_2');
        });
    }
};
