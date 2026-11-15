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

    protected static function booted(): void
    {
        static::saving(function (Kamar $kamar) {
            $kamar->status = $kamar->penghuni_id
                ? self::STATUS_TERISI
                : self::STATUS_TERSEDIA;
        });

        static::deleted(function (Kamar $kamar) {
            // Foto Cloudinary dihapus melalui controller.
            // Foto lokal lama tetap dihapus di sini.
            if (
                $kamar->foto &&
                ! $kamar->foto_public_id &&
                ! str_starts_with($kamar->foto, 'http')
            ) {
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

    public function getFotoUrlAttribute(): string
    {
        if (! $this->foto) {
            return asset('images/kos-hero.jpg');
        }

        return str_starts_with($this->foto, 'http')
            ? $this->foto
            : asset('storage/'.$this->foto);
    }

    public function isTerisi(): bool
    {
        return $this->penghuni_id !== null;
    }

    public function getHargaRupiahAttribute(): string
    {
        return 'Rp '.number_format((float) $this->harga, 0, ',', '.');
    }

    public function getDaftarFasilitasAttribute(): array
    {
        return array_values(array_filter(
            array_map('trim', explode(',', (string) $this->fasilitas))
        ));
    }
}