<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipe_kamar_id')->constrained('tipe_kamar')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nomor_kamar', 10)->unique();
            $table->unsignedTinyInteger('lantai');
            $table->enum('status', ['tersedia', 'perbaikan'])->default('tersedia');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
