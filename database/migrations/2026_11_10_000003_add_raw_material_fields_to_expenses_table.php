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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('raw_material_id')
                ->nullable()
                ->after('expense_type')
                ->constrained('raw_materials')
                ->nullOnDelete();
            $table->decimal('raw_material_quantity', 12, 2)
                ->nullable()
                ->after('raw_material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['raw_material_id']);
            $table->dropColumn(['raw_material_id', 'raw_material_quantity']);
        });
    }
};
