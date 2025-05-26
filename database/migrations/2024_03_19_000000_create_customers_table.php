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
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number', 50)->nullable();
            $table->string('billing_address_line1', 255);
            $table->string('billing_address_line2', 255)->nullable();
            $table->string('billing_city', 100);
            $table->string('billing_state', 100);
            $table->string('billing_zip_code', 20);
            $table->string('billing_country', 100);
            $table->string('shipping_address_line1', 255);
            $table->string('shipping_address_line2', 255)->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_state', 100);
            $table->string('shipping_zip_code', 20);
            $table->string('shipping_country', 100);
            $table->decimal('credit_limit', 10, 2)->nullable();
            $table->enum('customer_type', ['Individual', 'Business']);
            $table->string('industry', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
}; 