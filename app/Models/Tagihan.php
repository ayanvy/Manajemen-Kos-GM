<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Tagihan extends Model
{
    use HasFactory;

    public const STATUS_BELUM_DIBAYAR = 'belum_dibayar';
    public const STATUS_LUNAS = 'lunas';

    public const STATUS = [
        self::STATUS_BELUM_DIBAYAR => 'Belum dibayar',
        self::STATUS_LUNAS => 'Lunas',
    ];

    public const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    protected $table = 'tagihan';

    // pemilik_id sengaja tidak di sini: selalu diisi dari user yang login.
    // nama_penghuni/nama_pemilik hanya diisi oleh controller, tidak pernah dari input form.
    protected $fillable = [
        'penghuni_id',
        'nama_penghuni',
        'nama_pemilik',
        'periode',
        'jumlah',
        'jatuh_tempo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'jatuh_tempo' => 'date',
        ];
    }

    /**
     * Daftar path bukti yang dicatat sementara saat penghapusan. Ini properti PHP biasa,
     * bukan atribut Eloquent, jadi tidak pernah ikut tersimpan atau masuk ke query.
     *
     * @var array<int, string>
     */
    protected array $buktiUntukDihapus = [];

    /**
     * Hanya berjalan saat tagihan dihapus lewat Eloquent ($tagihan->delete()). Menghapus akun
     * penghuni/pemilik hanya mengosongkan FK (nullOnDelete) tanpa menyentuh model Tagihan,
     * jadi riwayat pembayaran arsip tidak ikut terhapus.
     * Barisnya dihapus oleh cascade di database; di sini hanya file bukti yang dibersihkan.
     */
    protected static function booted(): void
    {
        static::deleting(function (Tagihan $tagihan) {
            // Query baru ke database (bukan cache relasi), dicatat sebelum cascade menghapus barisnya.
            $tagihan->buktiUntukDihapus = $tagihan->pembayaran()
                ->pluck('bukti_pembayaran')
                ->filter()
                ->values()
                ->all();
        });

        static::deleted(function (Tagihan $tagihan) {
            // Pakai daftar yang sudah dicatat; relasi pembayaran tidak dibaca lagi di sini.
            $paths = $tagihan->buktiUntukDihapus;
            $tagihan->buktiUntukDihapus = [];

            if ($paths !== []) {
                // Tunggu commit: kalau transaksi di luar rollback, callback dibuang dan file tetap ada.
                DB::afterCommit(fn () => Storage::disk('local')->delete($paths));
            }
        });
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }

    /** Ada pembayaran yang sedang menunggu verifikasi pemilik. */
    public function sedangDiverifikasi(): bool
    {
        return $this->pembayaran()->where('status', Pembayaran::STATUS_MENUNGGU)->exists();
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghuni_id');
    }

    /**
     * Arsip hanya-baca: akun penghuni ATAU akun pemilik tagihan ini sudah dihapus
     * (foreign key menjadi null lewat nullOnDelete).
     */
    public function isArsip(): bool
    {
        return $this->penghuni_id === null || $this->pemilik_id === null;
    }

    /** Tagihan ini masih dimiliki akun pemilik tersebut (arsip tidak pernah dianggap milik siapa pun). */
    public function isMilikPemilik(int $pemilikId): bool
    {
        return $this->pemilik_id !== null && (int) $this->pemilik_id === $pemilikId;
    }

    /** Boleh diedit/dihapus: bukan arsip dan milik pemilik tersebut. */
    public function dapatDikelolaOleh(int $pemilikId): bool
    {
        return ! $this->isArsip() && $this->isMilikPemilik($pemilikId);
    }

    /** Tagihan milik pemilik saat ini + arsip pemilik sebelumnya (pemilik_id null). */
    public function scopeTampilUntukPemilik($query, int $pemilikId)
    {
        return $query->where(function ($q) use ($pemilikId) {
            $q->where('pemilik_id', $pemilikId)->orWhereNull('pemilik_id');
        });
    }

    /** Sumber utama nama riwayat adalah snapshot; relasi hanya cadangan kalau snapshot kosong. */
    public function getNamaPenghuniTampilAttribute(): string
    {
        return $this->nama_penghuni ?: ($this->penghuni?->name ?? '-');
    }

    public function getNamaPemilikTampilAttribute(): string
    {
        return $this->nama_pemilik ?: ($this->pemilik?->name ?? '-');
    }

    public function isLunas(): bool
    {
        return $this->status === self::STATUS_LUNAS;
    }

    /** Belum dibayar dan sudah melewati jatuh tempo (hanya tampilan, tidak disimpan di database). */
    public function isTerlambat(): bool
    {
        return ! $this->isLunas() && $this->jatuh_tempo->endOfDay()->isPast();
    }

    public function getJumlahRupiahAttribute(): string
    {
        return 'Rp '.number_format((float) $this->jumlah, 0, ',', '.');
    }

    public function getJatuhTempoLabelAttribute(): string
    {
        return self::formatTanggal($this->jatuh_tempo);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isTerlambat()) {
            return 'Terlambat';
        }

        return self::STATUS[$this->status] ?? $this->status;
    }

    /** Warna badge: success = hijau, warning = oranye, danger = merah. */
    public function getStatusToneAttribute(): string
    {
        if ($this->isLunas()) {
            return 'success';
        }

        return $this->isTerlambat() ? 'danger' : 'warning';
    }

    /** "2026-09" (dari input type=month) menjadi "September 2026". */
    public static function periodeDariBulan(string $bulan): string
    {
        [$tahun, $nomor] = array_map('intval', explode('-', $bulan));

        return self::BULAN[$nomor].' '.$tahun;
    }

    /** "September 2026" menjadi "2026-09", dipakai untuk mengisi input type=month saat edit. */
    public function getPeriodeBulanAttribute(): ?string
    {
        [$nama, $tahun] = array_pad(explode(' ', (string) $this->periode, 2), 2, null);
        $nomor = array_search($nama, self::BULAN, true);

        return ($nomor && $tahun) ? sprintf('%04d-%02d', $tahun, $nomor) : null;
    }

    public static function formatTanggal($tanggal): string
    {
        return $tanggal->day.' '.self::BULAN[$tanggal->month].' '.$tanggal->year;
    }
}