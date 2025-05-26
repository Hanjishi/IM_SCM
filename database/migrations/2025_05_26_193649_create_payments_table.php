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
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->date('payment_date');
            $table->decimal('payment_amount', 15, 2);
            $table->string('payment_method', 50);
            $table->string('transaction_id', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
