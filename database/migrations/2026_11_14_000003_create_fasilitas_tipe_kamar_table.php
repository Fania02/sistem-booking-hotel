<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel penghubung: satu tipe kamar punya banyak fasilitas,
     * dan satu fasilitas bisa dimiliki banyak tipe kamar.
     */
    public function up(): void
    {
        Schema::create('fasilitas_tipe_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipe_kamar_id')->constrained('tipe_kamar')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('fasilitas_id')->constrained('fasilitas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tipe_kamar_id', 'fasilitas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas_tipe_kamar');
    }
};
