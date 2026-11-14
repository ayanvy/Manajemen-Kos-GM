<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel tagihan sesuai rancangan RAT: penghuni_id, pemilik_id, periode, jumlah, jatuh_tempo, status.
     * Penghuni dan pemilik sama-sama berada di tabel users (dibedakan lewat kolom role).
     *
     * Riwayat tagihan harus tetap ada walau akun dihapus: foreign key nullable + nullOnDelete,
     * dan nama disimpan sebagai snapshot (nama_penghuni, nama_pemilik) saat tagihan dibuat.
     */
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pemilik_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_penghuni'); // snapshot nama saat tagihan dibuat
            $table->string('nama_pemilik');  // snapshot nama pemilik yang menerbitkan
            $table->string('periode', 30); // contoh: "September 2026"
            $table->decimal('jumlah', 12, 2);
            $table->date('jatuh_tempo');
            $table->enum('status', ['belum_dibayar', 'lunas'])->default('belum_dibayar');
            $table->timestamps();

            // Satu penghuni hanya punya satu tagihan per periode.
            // MySQL mengizinkan banyak nilai NULL pada unique, jadi arsip dengan penghuni_id null tidak saling bentrok.
            $table->unique(['penghuni_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
