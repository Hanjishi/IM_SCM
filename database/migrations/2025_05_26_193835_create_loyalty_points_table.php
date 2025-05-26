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
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->bigIncrements('points_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('program_id');
            $table->integer('points_balance');
            $table->integer('points_earned');
            $table->integer('points_redeemed');
            $table->date('last_earned_date')->nullable();
            $table->date('last_redeemed_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('program_id')->references('program_id')->on('loyalty_programs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
    }
};
