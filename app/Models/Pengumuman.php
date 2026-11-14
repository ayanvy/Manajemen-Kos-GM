<?php

namespace App\Models;

use App\Support\TanggalIndonesia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    // pemilik_id dan tgl_post sengaja tidak fillable: diisi controller.
    protected $fillable = [
        'judul',
        'isi',
    ];

    protected function casts(): array
    {
        return [
            'tgl_post' => 'datetime',
        ];
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    /** Pengumuman milik satu pemilik, terbaru di atas. */
    public function scopeMilikPemilik(Builder $query, int $pemilikId): Builder
    {
        return $query->where('pemilik_id', $pemilikId)
            ->orderByDesc('tgl_post')
            ->orderByDesc('id');
    }

    /** Pengumuman yang boleh dilihat penghuni: dari pemilik kamar yang ia tempati. */
    public function scopeUntukPenghuni(Builder $query, User $penghuni): Builder
    {
        return $this->scopeMilikPemilik($query, $penghuni->kamar?->pemilik_id ?? 0);
    }

    public function getTglPostLabelAttribute(): string
    {
        return TanggalIndonesia::panjang($this->tgl_post);
    }

    public function getTanggalHariAttribute(): string
    {
        return $this->tgl_post?->format('d') ?? '-';
    }

    public function getBulanSingkatAttribute(): string
    {
        return $this->tgl_post ? TanggalIndonesia::bulanSingkat($this->tgl_post) : '';
    }

    public function getRingkasanAttribute(): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', $this->isi)), 140);
    }
}
