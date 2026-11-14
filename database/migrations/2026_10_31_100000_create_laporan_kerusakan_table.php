<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laporan kerusakan: dibuat penghuni, ditangani pemilik.
     *
     * Riwayat laporan harus tetap ada walau akun atau kamar dihapus (sama seperti tagihan):
     * foreign key nullable + nullOnDelete, dan nama penghuni serta nomor kamar disimpan
     * sebagai snapshot saat laporan dibuat.
     *
     * Foto bukti kerusakan (opsional) disimpan di disk privat, bukan di folder public.
     */
    public function up(): void
    {
        Schema::create('laporan_kerusakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pemilik_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kamar_id')->nullable()->constrained('kamar')->nullOnDelete();
            $table->string('nama_penghuni'); // snapshot nama saat laporan dibuat
            $table->string('nomor_kamar', 20); // snapshot nomor kamar saat laporan dibuat
            $table->text('deskripsi');
            $table->string('foto')->nullable(); // path relatif di disk "local" (privat)
            $table->date('tgl_lapor');
            $table->enum('status', ['dikirim', 'diproses', 'selesai'])->default('dikirim');
            $table->text('catatan_pemilik')->nullable();
            $table->dateTime('tgl_selesai')->nullable();
            $table->timestamps();

            $table->index(['penghuni_id', 'status']);
            $table->index(['pemilik_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kerusakan');
    }
};
