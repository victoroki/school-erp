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
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->increments('requisition_item_id');
            $table->unsignedInteger('requisition_id');
            $table->unsignedInteger('item_id')->nullable();
            $table->string('item_name');
            $table->integer('quantity_needed');
            $table->decimal('estimated_price', 10, 2)->default(0);
            $table->text('purpose')->nullable();
            $table->integer('quantity_fulfilled')->default(0);
            $table->datetime('fulfilled_at')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
