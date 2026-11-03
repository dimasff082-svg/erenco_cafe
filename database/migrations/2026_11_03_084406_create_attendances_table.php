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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['check_in', 'break_start', 'break_end', 'check_out']);
            // check_in = Absen Pagi, break_start = Absen Mulai Istirahat, break_end = Absen Selesai Istirahat, check_out = Absen Pulang
            $table->time('time');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('distance_meters', 8, 2)->nullable(); // Jarak dari kantor dalam meter (maks. 50 meter)
            $table->string('photo_path'); // Foto bukti kehadiran
            $table->string('status')->default('present'); // present, verified, rejected
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
