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
        Schema::create('returns', function (Blueprint $table) {
            $table->bigIncrements('return_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('return_date');
            $table->text('reason_for_return');
            $table->string('product_condition', 100);
            $table->enum('resolution_status', ['Pending', 'Approved', 'Rejected', 'Refunded', 'Replaced'])->default('Pending');
            $table->decimal('refund_amount', 15, 2)->default(0.00);
            $table->string('refund_method', 50)->nullable();
            $table->decimal('restocking_fee', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('order_id')->references('order_id')->on('sales_orders');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
