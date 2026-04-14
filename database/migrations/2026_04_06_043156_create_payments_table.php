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
            $table->id('payment_id'); // Auto-incrementing primary key named 'payment_id'
            $table->string('billing_id')->unique(); // String, not a foreign key
            $table->string('student_id'); // String, not a foreign key
            $table->string('student_name');
            $table->string('year_level');
            $table->string('fee_name');
            $table->decimal('total_fee', 10, 2); // Using decimal for currency
            $table->decimal('total_amount', 10, 2); // Using decimal for currency
            $table->string('payment_method');
            $table->string('status')->nullable(); // Make the status column nullable
            $table->decimal('balance', 10, 2)->nullable(); // Make the balance column nullable
            $table->string('receipt_number')->nullable()->unique(); // <-- added column
            $table->timestamps(); // created_at and updated_at columns
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