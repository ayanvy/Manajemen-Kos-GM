<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Format tanggal berbahasa Indonesia tanpa bergantung pada locale server. */
class TanggalIndonesia
{
    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const BULAN_SINGKAT = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /** Contoh: 25 September 2026 */
    public static function panjang(?CarbonInterface $tanggal): string
    {
        if ($tanggal === null) {
            return '-';
        }

        return $tanggal->format('d').' '.self::BULAN[$tanggal->month].' '.$tanggal->year;
    }

    /** Contoh: Sep */
    public static function bulanSingkat(CarbonInterface $tanggal): string
    {
        return self::BULAN_SINGKAT[$tanggal->month];
    }
}
