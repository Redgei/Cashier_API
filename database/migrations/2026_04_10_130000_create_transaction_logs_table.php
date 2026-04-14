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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id('transaction_log_id');
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->string('billing_id')->nullable()->index();
            $table->string('student_id')->index();
            $table->string('student_name');
            $table->string('year_level');
            $table->string('fee_name');
            $table->string('event_type', 20)->index();
            $table->decimal('total_fee', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('balance', 10, 2)->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('payment_method');
            $table->string('status')->nullable();
            $table->string('receipt_number')->nullable()->index();
            $table->timestamp('payment_created_at')->nullable();
            $table->timestamp('payment_updated_at')->nullable();
            $table->dateTime('action_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
