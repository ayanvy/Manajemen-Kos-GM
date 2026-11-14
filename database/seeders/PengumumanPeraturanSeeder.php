<?php

namespace Database\Seeders;

use App\Models\PeraturanKos;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh sesuai mockup. Jalankan manual:
 *   php artisan db:seed --class=PengumumanPeraturanSeeder
 * Aman dijalankan berulang (tidak membuat data ganda).
 */
class PengumumanPeraturanSeeder extends Seeder
{
    public function run(): void
    {
        $pemilik = User::where('role', User::ROLE_PEMILIK)->first();

        if (! $pemilik) {
            $this->command?->warn('Belum ada akun pemilik. Daftarkan/ubah satu akun menjadi pemilik dulu.');

            return;
        }

        $pengumuman = [
            ['Pembayaran tagihan bulanan', 'Mohon melakukan pembayaran sesuai tanggal jatuh tempo yang tercantum pada sistem.', '2026-09-20 09:00:00'],
            ['Jadwal kebersihan lingkungan kos', 'Kegiatan kebersihan bersama dilaksanakan hari Minggu pukul 08.00.', '2026-09-25 09:00:00'],
        ];

        foreach ($pengumuman as [$judul, $isi, $tgl]) {
            $item = Pengumuman::where('pemilik_id', $pemilik->id)->where('judul', $judul)->first()
                ?? new Pengumuman();
            $item->pemilik_id = $pemilik->id;
            $item->judul = $judul;
            $item->isi = $isi;
            $item->tgl_post = $tgl;
            $item->save();
        }

        $peraturan = [
            'Tamu wajib melapor kepada pemilik/pengelola kos.',
            'Menjaga kebersihan dan ketenangan lingkungan kos.',
            'Pembayaran sewa dilakukan sesuai tanggal jatuh tempo.',
        ];

        foreach ($peraturan as $isi) {
            $item = PeraturanKos::where('pemilik_id', $pemilik->id)->where('isi', $isi)->first();

            if ($item) {
                continue;
            }

            $item = new PeraturanKos();
            $item->pemilik_id = $pemilik->id;
            $item->isi = $isi;
            $item->created_at = $item->updated_at = '2026-09-01 08:00:00';
            $item->save();
        }
    }
}
