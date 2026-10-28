<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_reservasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservasi_id')->constrained('reservasi')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamar')->cascadeOnUpdate()->restrictOnDelete();
            // harga disalin saat booking agar riwayat harga tetap
            $table->decimal('harga_per_malam', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            // kamar yang sama tidak boleh dua kali dalam satu reservasi
            $table->unique(['reservasi_id', 'kamar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_reservasi');
    }
};
