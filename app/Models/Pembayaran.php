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

class Pembayaran extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS = [
        self::STATUS_MENUNGGU => 'Menunggu',
        self::STATUS_DITERIMA => 'Diterima',
        self::STATUS_DITOLAK => 'Ditolak',
    ];

    public const METODE = [
        'transfer_bank' => 'Transfer Bank',
        'e_wallet' => 'E-Wallet',
    ];

    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_id',
        'metode',
        'tgl_bayar',
        'jumlah',
        'bukti_pembayaran',
        'bukti_public_id',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tgl_bayar' => 'date',
            'tgl_verifikasi' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (Pembayaran $pembayaran) {
            $path = $pembayaran->bukti_pembayaran;
            $publicId = $pembayaran->bukti_public_id;

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

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function scopeUntukPemilik($query, int $pemilikId)
    {
        return $query->whereHas('tagihan', fn ($q) => $q->where('pemilik_id', $pemilikId));
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }

    public function scopePerluDiverifikasi($query, int $pemilikId)
    {
        return $query->menunggu()
            ->untukPemilik($pemilikId)
            ->whereHas('tagihan', fn ($q) => $q->whereNotNull('penghuni_id'));
    }

    public function isMenunggu(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    public function isDiterima(): bool
    {
        return $this->status === self::STATUS_DITERIMA;
    }

    public function isDitolak(): bool
    {
        return $this->status === self::STATUS_DITOLAK;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusToneAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DITERIMA => 'success',
            self::STATUS_DITOLAK => 'danger',
            default => 'warning',
        };
    }

    public function getMetodeLabelAttribute(): string
    {
        return self::METODE[$this->metode] ?? $this->metode;
    }

    public function getJumlahRupiahAttribute(): string
    {
        return 'Rp '.number_format((float) $this->jumlah, 0, ',', '.');
    }

    public function getTglBayarLabelAttribute(): string
    {
        return Tagihan::formatTanggal($this->tgl_bayar);
    }

    public function getNamaFileBuktiAttribute(): string
    {
        $ext = pathinfo((string) $this->bukti_pembayaran, PATHINFO_EXTENSION);

        return 'bukti-'.Str::slug($this->tagihan?->periode ?? 'pembayaran').'-'.$this->id.'.'.$ext;
    }

    /** Otorisasi dilakukan controller sebelum method ini dipanggil. */
    public function responBukti(bool $unduh = false): Response
    {
        abort_if(! $this->bukti_pembayaran, 404, 'Pembayaran ini tidak memiliki bukti.');

        if ($this->bukti_public_id) {
            $format = strtolower(
                pathinfo($this->bukti_pembayaran, PATHINFO_EXTENSION)
            );

            abort_if(! in_array($format, ['jpg', 'jpeg', 'png', 'pdf'], true), 404);

            $url = app(CloudinaryService::class)->privateImageUrl(
                $this->bukti_public_id,
                $format,
                now()->addMinutes(5)->timestamp,
                $unduh,
                $unduh ? $this->nama_file_bukti : null
            );

            return redirect()->away($url);
        }

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($this->bukti_pembayaran),
            404,
            'File bukti tidak ditemukan.'
        );

        return $unduh
            ? $disk->download($this->bukti_pembayaran, $this->nama_file_bukti)
            : $disk->response($this->bukti_pembayaran, $this->nama_file_bukti);
    }
}