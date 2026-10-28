<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tamu', function (Blueprint $table) {
            $table->id();
            // nullable: tamu walk-in boleh tidak punya akun
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('nama', 100);
            $table->string('nik', 16)->nullable()->unique();
            $table->string('no_hp', 15);
            $table->text('alamat');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tamu');
    }
};
