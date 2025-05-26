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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('representative_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable()->unique();
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->enum('order_status', ['Pending', 'Confirmed', 'Partially Shipped', 'Shipped', 'Invoiced', 'Canceled'])->default('Pending');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->string('shipping_address_line1', 255);
            $table->string('shipping_address_line2', 255)->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_state', 100);
            $table->string('shipping_zip_code', 20);
            $table->string('shipping_country', 100);
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('representative_id')->references('representative_id')->on('sales_representatives');
            $table->foreign('quotation_id')->references('quotation_id')->on('quotations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
