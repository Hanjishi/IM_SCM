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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loyalty_points_id');
            $table->enum('type', ['earn', 'redeem']);
            $table->integer('points');
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->foreign('loyalty_points_id')->references('points_id')->on('loyalty_points')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
