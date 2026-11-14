<?php

namespace App\Models;

use App\Support\TanggalIndonesia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeraturanKos extends Model
{
    use HasFactory;

    protected $table = 'peraturan_kos';

    protected $fillable = [
        'judul',
        'isi',
    ];

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    /** Peraturan satu pemilik, urut dari yang paling awal dibuat (menjadi nomor 1, 2, 3, ...). */
    public function scopeMilikPemilik(Builder $query, int $pemilikId): Builder
    {
        return $query->where('pemilik_id', $pemilikId)->orderBy('id');
    }

    /** Peraturan yang berlaku untuk penghuni: dari pemilik kamar yang ia tempati. */
    public function scopeUntukPenghuni(Builder $query, User $penghuni): Builder
    {
        return $this->scopeMilikPemilik($query, $penghuni->kamar?->pemilik_id ?? 0);
    }

    public function getDiperbaruiLabelAttribute(): string
    {
        return TanggalIndonesia::panjang($this->updated_at);
    }
}
