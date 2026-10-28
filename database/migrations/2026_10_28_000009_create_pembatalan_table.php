<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembatalan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservasi_id')->constrained('reservasi')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('alasan');
            $table->dateTime('tgl_pengajuan');
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->dateTime('tgl_diproses')->nullable();
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembatalan');
    }
};
