<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyamakan tabel tamu dengan validasi di TamuRequest:
     * alamat boleh kosong dan no_hp maksimal 20 karakter.
     */
    public function up(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->text('alamat')->nullable()->change();
            $table->string('no_hp', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tamu', function (Blueprint $table) {
            $table->text('alamat')->nullable(false)->change();
            $table->string('no_hp', 15)->change();
        });
    }
};
