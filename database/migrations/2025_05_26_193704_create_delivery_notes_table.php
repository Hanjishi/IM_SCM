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
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->bigIncrements('delivery_note_id');
            $table->unsignedBigInteger('order_id');
            $table->date('delivery_date');
            $table->string('shipper', 100)->nullable();
            $table->string('tracking_number', 255)->nullable();
            $table->enum('delivery_status', ['Prepared', 'Shipped', 'Delivered', 'Canceled'])->default('Prepared');
            $table->timestamps();

            $table->foreign('order_id')->references('order_id')->on('sales_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
