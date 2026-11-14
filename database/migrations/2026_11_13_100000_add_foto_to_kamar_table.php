<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto kamar (opsional), disimpan sebagai path relatif di disk "public".
     * Kalau kosong, tampilan memakai gambar bawaan.
     */
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('fasilitas');
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
