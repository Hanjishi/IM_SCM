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
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->bigIncrements('program_id');
            $table->string('program_name', 100);
            $table->text('description');
            $table->decimal('points_multiplier', 5, 2)->default(1.00);
            $table->decimal('minimum_purchase_amount', 15, 2)->default(0.00);
            $table->integer('points_expiry_days')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_programs');
    }
};
