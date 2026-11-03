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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->enum('expense_type', ['raw_material', 'operational'])->default('operational'); // raw_material = Pembelian Bahan Baku, operational = Listrik, Air, Telpon, dll.
            $table->string('title');
            $table->string('category')->nullable(); // misal: Bahan Baku, Listrik PLN, Air PDAM, Internet/Telpon, Perlengkapan, dll.
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('payment_method')->default('cash'); // cash, transfer, dll.
            $table->string('receipt_proof')->nullable(); // foto bukti nota / struk
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
