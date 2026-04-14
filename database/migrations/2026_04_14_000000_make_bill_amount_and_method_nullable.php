<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE bills MODIFY total_amount DECIMAL(10,2) NULL');
        DB::statement('ALTER TABLE bills MODIFY payment_method VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE bills SET total_amount = 0 WHERE total_amount IS NULL");
        DB::statement("UPDATE bills SET payment_method = 'Cash' WHERE payment_method IS NULL");
        DB::statement('ALTER TABLE bills MODIFY total_amount DECIMAL(10,2) NOT NULL');
        DB::statement('ALTER TABLE bills MODIFY payment_method VARCHAR(255) NOT NULL');
    }
};
