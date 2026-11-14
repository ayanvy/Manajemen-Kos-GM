<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kamar dihubungkan ke penghuni. Selama penghuni_id kosong, kamar dianggap tersedia
     * dan penghuni belum bisa melihat informasi kamar.
     */
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->foreignId('penghuni_id')
                ->nullable()
                ->unique() // satu penghuni hanya menempati satu kamar
                ->after('pemilik_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penghuni_id');
        });
    }
};
