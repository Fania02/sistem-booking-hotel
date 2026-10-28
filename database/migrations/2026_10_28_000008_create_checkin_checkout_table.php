<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkin_checkout', function (Blueprint $table) {
            $table->id();
            // satu reservasi paling banyak satu data check-in/out
            $table->foreignId('reservasi_id')->unique()->constrained('reservasi')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('waktu_checkin')->nullable();
            $table->foreignId('diproses_checkin_oleh')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->dateTime('waktu_checkout')->nullable();
            $table->foreignId('diproses_checkout_oleh')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_checkout');
    }
};
