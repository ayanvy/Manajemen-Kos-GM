<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel pembayaran sesuai rancangan RAT: tagihan_id, diverifikasi_oleh, tgl_bayar, jumlah,
     * bukti_pembayaran, status, alasan_penolakan, tgl_verifikasi (ditambah kolom metode).
     *
     * Satu tagihan boleh punya banyak pembayaran (mis. pembayaran pertama ditolak, lalu penghuni
     * mengunggah bukti baru). Bukti disimpan di disk privat, bukan di folder public.
     */
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('metode', 30);
            $table->date('tgl_bayar');
            $table->decimal('jumlah', 12, 2);
            $table->string('bukti_pembayaran'); // path relatif di disk "local" (privat)
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->text('alasan_penolakan')->nullable();
            $table->dateTime('tgl_verifikasi')->nullable();
            $table->timestamps();

            $table->index(['tagihan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
