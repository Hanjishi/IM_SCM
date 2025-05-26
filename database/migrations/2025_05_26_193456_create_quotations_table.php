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
        Schema::create('quotations', function (Blueprint $table) {
            $table->bigIncrements('quotation_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('representative_id')->nullable();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->enum('quotation_status', ['Draft', 'Sent', 'Accepted', 'Rejected', 'Expired'])->default('Draft');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->text('terms_conditions')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('representative_id')->references('representative_id')->on('sales_representatives');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
