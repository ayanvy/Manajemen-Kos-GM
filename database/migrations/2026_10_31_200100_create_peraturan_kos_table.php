<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Peraturan kos: satu baris = satu aturan. Nomor urut di tampilan mengikuti urutan id.
     * Kolom judul opsional (UI hanya menampilkan satu kalimat aturan).
     */
    public function up(): void
    {
        Schema::create('peraturan_kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 150)->nullable();
            $table->text('isi');
            $table->timestamps(); // updated_at dipakai sebagai "Diperbarui"

            $table->index('pemilik_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peraturan_kos');
    }
};
