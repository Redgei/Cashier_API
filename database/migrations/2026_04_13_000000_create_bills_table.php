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
        Schema::create('bills', function (Blueprint $table) {
            $table->id('bill_id');
            $table->string('billing_id')->nullable()->unique();
            $table->string('student_id')->nullable()->index();
            $table->string('student_name')->nullable();
            $table->string('fee_name');
            $table->decimal('total_fee', 10, 2);
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->string('receipt_number')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
