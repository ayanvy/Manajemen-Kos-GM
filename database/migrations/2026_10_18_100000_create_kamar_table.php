<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('nomor_kamar', 20);
            $table->string('tipe', 50);
            $table->decimal('harga', 12, 2);
            $table->text('fasilitas')->nullable();
            $table->enum('status', ['tersedia', 'terisi'])->default('tersedia');
            $table->timestamps();

            // Nomor kamar tidak boleh kembar untuk pemilik yang sama.
            $table->unique(['pemilik_id', 'nomor_kamar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
