<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengumuman dibuat pemilik dan dibaca penghuni yang kamarnya milik pemilik tersebut.
     */
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->text('isi');
            $table->dateTime('tgl_post')->useCurrent();
            $table->timestamps();

            $table->index(['pemilik_id', 'tgl_post']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
