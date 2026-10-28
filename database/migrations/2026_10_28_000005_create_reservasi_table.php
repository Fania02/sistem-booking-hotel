<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking', 20)->unique();
            $table->foreignId('tamu_id')->constrained('tamu')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tgl_checkin');
            $table->date('tgl_checkout');
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->enum('status_pembayaran', ['belum_bayar', 'dp', 'lunas'])->default('belum_bayar');
            $table->enum('sumber', ['online', 'walk-in'])->default('online');
            // petugas yang membuat (walk-in); kosong jika tamu booking sendiri
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->enum('status', ['menunggu', 'dikonfirmasi', 'checkin', 'selesai', 'batal'])->default('menunggu');
            $table->timestamps();

            $table->index(['tgl_checkin', 'tgl_checkout']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};
