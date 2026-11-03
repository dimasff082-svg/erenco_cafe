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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_month'); // e.g. November 2026
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->date('payment_date');
            $table->string('payment_status')->default('paid'); // paid, pending
            $table->string('payment_method')->default('transfer'); // transfer, cash
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
