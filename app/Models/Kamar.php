<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Kamar extends Model
{
    use HasFactory;

    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_TERISI = 'terisi';

    public const STATUS = [
        self::STATUS_TERSEDIA => 'Tersedia',
        self::STATUS_TERISI => 'Terisi',
    ];

    protected $table = 'kamar';

    protected $fillable = [
        'nomor_kamar',
        'tipe',
        'harga',
        'fasilitas',
        'penghuni_id',
        'status',
    ];

    /** Status selalu mengikuti penghuni: ada penghuni = terisi, tidak ada = tersedia. */
    protected static function booted(): void
    {
        static::saving(function (Kamar $kamar) {
            $kamar->status = $kamar->penghuni_id ? self::STATUS_TERISI : self::STATUS_TERSEDIA;
        });

        // Hapus file foto dari disk saat kamar dihapus.
        static::deleted(function (Kamar $kamar) {
            if ($kamar->foto) {
                Storage::disk('public')->delete($kamar->foto);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghuni_id');
    }

    /** URL foto kamar; kalau pemilik belum mengunggah foto, pakai gambar bawaan. */
    public function getFotoUrlAttribute(): string
    {
        return $this->foto
            ? asset('storage/'.$this->foto)
            : asset('images/kos-hero.jpg');
    }

    public function isTerisi(): bool
    {
        return $this->penghuni_id !== null;
    }

    public function getHargaRupiahAttribute(): string
    {
        return 'Rp '.number_format((float) $this->harga, 0, ',', '.');
    }

    /** Fasilitas disimpan sebagai teks dipisah koma, ini mengubahnya jadi array rapi. */
    public function getDaftarFasilitasAttribute(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->fasilitas))));
    }
}
