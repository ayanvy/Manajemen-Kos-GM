<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LaporanKerusakan extends Model
{
    use HasFactory;

    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';

    /** Urutan ini juga menjadi aturan "status hanya bisa maju". */
    public const STATUS = [
        self::STATUS_DIKIRIM => 'Dikirim',
        self::STATUS_DIPROSES => 'Diproses',
        self::STATUS_SELESAI => 'Selesai',
    ];

    protected $table = 'laporan_kerusakan';

    // Kolom lain (penghuni_id, pemilik_id, kamar_id, snapshot, tgl_lapor, status, catatan_pemilik,
    // tgl_selesai) sengaja tidak fillable: diisi controller, tidak pernah dari input form penghuni.
    protected $fillable = [
        'deskripsi',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lapor' => 'date',
            'tgl_selesai' => 'datetime',
        ];
    }

    /** Hapus file foto saat laporan dihapus lewat Eloquent (menunggu commit kalau ada transaksi). */
    protected static function booted(): void
    {
        static::deleted(function (LaporanKerusakan $laporan) {
            if ($laporan->foto) {
                $path = $laporan->foto;

                DB::afterCommit(fn () => Storage::disk('local')->delete($path));
            }
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

    /* ---------------------------------------------------------------- scope */

    public function scopeAktif($query)
    {
        return $query->whereIn('status', [self::STATUS_DIKIRIM, self::STATUS_DIPROSES]);
    }

    /**
     * Laporan yang masih bisa ditangani pemilik ini: milik pemilik tersebut dan penghuninya
     * masih ada. Dipakai bersama oleh daftar dan angka di dashboard supaya selalu sama.
     */
    public function scopeKelolaOleh($query, int $pemilikId)
    {
        return $query->where('pemilik_id', $pemilikId)->whereNotNull('penghuni_id');
    }

    /** Laporan milik pemilik saat ini + arsip pemilik sebelumnya (pemilik_id null). */
    public function scopeTampilUntukPemilik($query, int $pemilikId)
    {
        return $query->where(function ($q) use ($pemilikId) {
            $q->where('pemilik_id', $pemilikId)->orWhereNull('pemilik_id');
        });
    }

    /* ----------------------------------------------------------- kepemilikan */

    /** Arsip hanya-baca: akun penghuni ATAU akun pemilik laporan ini sudah dihapus. */
    public function isArsip(): bool
    {
        return $this->penghuni_id === null || $this->pemilik_id === null;
    }

    public function isMilikPemilik(int $pemilikId): bool
    {
        return $this->pemilik_id !== null && (int) $this->pemilik_id === $pemilikId;
    }

    /** Boleh diubah statusnya: bukan arsip dan milik pemilik tersebut. */
    public function dapatDikelolaOleh(int $pemilikId): bool
    {
        return ! $this->isArsip() && $this->isMilikPemilik($pemilikId);
    }

    public function isMilikPenghuni(int $penghuniId): bool
    {
        return $this->penghuni_id !== null && (int) $this->penghuni_id === $penghuniId;
    }

    /* ---------------------------------------------------------------- status */

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

    /** Penghuni hanya boleh mengubah/menghapus selama laporan belum disentuh pemilik. */
    public function bisaDiubahPenghuni(): bool
    {
        return $this->isDikirim();
    }

    private static function urutan(string $status): int
    {
        $nomor = array_search($status, array_keys(self::STATUS), true);

        return $nomor === false ? -1 : $nomor;
    }

    /** Status yang boleh dipilih pemilik dari status sekarang: tetap atau maju, tidak pernah mundur. */
    public function statusTujuan(): array
    {
        return array_filter(
            self::STATUS,
            fn (string $label, string $kode) => self::urutan($kode) >= self::urutan($this->status),
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

    /** Warna badge: success = hijau, warning = oranye, neutral = abu. */
    public function getStatusToneAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SELESAI => 'success',
            self::STATUS_DIPROSES => 'warning',
            default => 'neutral',
        };
    }

    /* ----------------------------------------------------------- tampilan */

    /** Sumber utama adalah snapshot; relasi hanya cadangan kalau snapshot kosong. */
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
        return $this->tgl_selesai ? Tagihan::formatTanggal($this->tgl_selesai) : null;
    }

    /* ------------------------------------------------------------------ foto */

    public function getNamaFileFotoAttribute(): string
    {
        $ext = pathinfo((string) $this->foto, PATHINFO_EXTENSION);

        return 'laporan-'.Str::slug($this->nomor_kamar_tampil).'-'.$this->id.'.'.$ext;
    }

    /** Kirim file foto dari disk privat. Otorisasi dilakukan di controller sebelum memanggil ini. */
    public function responFoto(): Response
    {
        abort_if(! $this->foto, 404, 'Laporan ini tidak memiliki foto.');

        $disk = Storage::disk('local');

        abort_unless($disk->exists($this->foto), 404, 'File foto tidak ditemukan.');

        return $disk->response($this->foto, $this->nama_file_foto);
    }
}
