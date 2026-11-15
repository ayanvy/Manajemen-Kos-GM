<?php

namespace App\Models;

use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LaporanKerusakan extends Model
{
    use HasFactory;

    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';

    public const STATUS = [
        self::STATUS_DIKIRIM => 'Dikirim',
        self::STATUS_DIPROSES => 'Diproses',
        self::STATUS_SELESAI => 'Selesai',
    ];

    protected $table = 'laporan_kerusakan';

    protected $fillable = [
        'deskripsi',
        'foto',
        'foto_public_id',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lapor' => 'date',
            'tgl_selesai' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (LaporanKerusakan $laporan) {
            $path = $laporan->foto;
            $publicId = $laporan->foto_public_id;

            DB::afterCommit(function () use ($path, $publicId) {
                try {
                    if ($publicId) {
                        app(CloudinaryService::class)
                            ->deleteImage($publicId, 'authenticated');
                    } elseif ($path) {
                        Storage::disk('local')->delete($path);
                    }
                } catch (Throwable $e) {
                    report($e);
                }
            });
        });
    }

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghuni_id');
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DIKIRIM,
            self::STATUS_DIPROSES,
        ]);
    }

    public function scopeKelolaOleh($query, int $pemilikId)
    {
        return $query->where('pemilik_id', $pemilikId)
            ->whereNotNull('penghuni_id');
    }

    public function scopeTampilUntukPemilik($query, int $pemilikId)
    {
        return $query->where(function ($q) use ($pemilikId) {
            $q->where('pemilik_id', $pemilikId)
                ->orWhereNull('pemilik_id');
        });
    }

    public function isArsip(): bool
    {
        return $this->penghuni_id === null || $this->pemilik_id === null;
    }

    public function isMilikPemilik(int $pemilikId): bool
    {
        return $this->pemilik_id !== null
            && (int) $this->pemilik_id === $pemilikId;
    }

    public function dapatDikelolaOleh(int $pemilikId): bool
    {
        return ! $this->isArsip() && $this->isMilikPemilik($pemilikId);
    }

    public function isMilikPenghuni(int $penghuniId): bool
    {
        return $this->penghuni_id !== null
            && (int) $this->penghuni_id === $penghuniId;
    }

    public function isDikirim(): bool
    {
        return $this->status === self::STATUS_DIKIRIM;
    }

    public function isDiproses(): bool
    {
        return $this->status === self::STATUS_DIPROSES;
    }

    public function isSelesai(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }

    public function bisaDiubahPenghuni(): bool
    {
        return $this->isDikirim();
    }

    private static function urutan(string $status): int
    {
        $nomor = array_search($status, array_keys(self::STATUS), true);

        return $nomor === false ? -1 : $nomor;
    }

    public function statusTujuan(): array
    {
        return array_filter(
            self::STATUS,
            fn (string $label, string $kode) =>
                self::urutan($kode) >= self::urutan($this->status),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function dapatPindahKe(string $status): bool
    {
        return array_key_exists($status, $this->statusTujuan());
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusToneAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SELESAI => 'success',
            self::STATUS_DIPROSES => 'warning',
            default => 'neutral',
        };
    }

    public function getNamaPenghuniTampilAttribute(): string
    {
        return $this->nama_penghuni ?: ($this->penghuni?->name ?? '-');
    }

    public function getNomorKamarTampilAttribute(): string
    {
        return $this->nomor_kamar ?: ($this->kamar?->nomor_kamar ?? '-');
    }

    public function getTglLaporLabelAttribute(): string
    {
        return Tagihan::formatTanggal($this->tgl_lapor);
    }

    public function getTglSelesaiLabelAttribute(): ?string
    {
        return $this->tgl_selesai
            ? Tagihan::formatTanggal($this->tgl_selesai)
            : null;
    }

    public function getNamaFileFotoAttribute(): string
    {
        $ext = pathinfo((string) $this->foto, PATHINFO_EXTENSION);

        return 'laporan-' . Str::slug($this->nomor_kamar_tampil)
            . '-' . $this->id . '.' . $ext;
    }

    /** Otorisasi dilakukan controller sebelum method ini dipanggil. */
    public function responFoto(): Response
    {
        abort_if(! $this->foto, 404, 'Laporan ini tidak memiliki foto.');

        if ($this->foto_public_id) {
            $format = strtolower(
                pathinfo($this->foto, PATHINFO_EXTENSION)
            );

            abort_if(! $format, 404, 'Format foto tidak valid.');

            $url = app(CloudinaryService::class)->privateImageUrl(
                $this->foto_public_id,
                $format,
                now()->addMinutes(5)->timestamp
            );

            return redirect()->away($url);
        }

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($this->foto),
            404,
            'File foto tidak ditemukan.'
        );

        return $disk->response($this->foto, $this->nama_file_foto);
    }
}