<?php

use App\Http\Controllers\Pemilik\KamarController;
use App\Http\Controllers\Pemilik\LaporanKerusakanController as PemilikLaporanController;
use App\Http\Controllers\Pemilik\PengumumanController as PemilikPengumumanController;
use App\Http\Controllers\Pemilik\PenghuniController;
use App\Http\Controllers\Pemilik\PeraturanKosController as PemilikPeraturanController;
use App\Http\Controllers\Pemilik\TagihanController;
use App\Http\Controllers\Pemilik\PembayaranController as PemilikPembayaranController;
use App\Http\Controllers\Pemilik\RiwayatTransaksiController;
use App\Http\Controllers\Penghuni\LaporanKerusakanController as PenghuniLaporanController;
use App\Http\Controllers\Penghuni\PembayaranController as PenghuniPembayaranController;
use App\Http\Controllers\Penghuni\PengumumanController as PenghuniPengumumanController;
use App\Http\Controllers\Penghuni\PeraturanKosController as PenghuniPeraturanController;
use App\Http\Controllers\Penghuni\TagihanController as PenghuniTagihanController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Models\Kamar;
use App\Models\LaporanKerusakan;
use App\Models\Pembayaran;
use App\Models\Pengumuman;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'pemilik') {
            return redirect()->route('pemilik.dashboard');
        }

        return redirect()->route('penghuni.dashboard');
    })->name('dashboard');

    Route::get('/pemilik/dashboard', function () {
        $penghuniTotal = User::where('role', 'penghuni')->count();

        $penghuniBaru = User::where('role', 'penghuni')
            ->latest()
            ->take(5)
            ->get();

        $kamarQuery = Kamar::where('pemilik_id', auth()->id());
        $kamarTotal = (clone $kamarQuery)->count();
        $kamarTerisi = (clone $kamarQuery)->whereNotNull('penghuni_id')->count();
        $kamarTersedia = $kamarTotal - $kamarTerisi;

        // Sama persis dengan daftar di halaman Memverifikasi Pembayaran.
        $menungguVerifikasi = Pembayaran::perluDiverifikasi(auth()->id())->count();

        // Laporan kerusakan yang belum selesai, dan 3 laporan terbaru (daftar sama dengan halaman Mengelola Laporan).
        $laporanAktif = LaporanKerusakan::kelolaOleh(auth()->id())->aktif()->count();
        $laporanTerbaru = LaporanKerusakan::kelolaOleh(auth()->id())
            ->orderByDesc('tgl_lapor')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        return view('pemilik.dashboard', compact(
            'penghuniTotal',
            'penghuniBaru',
            'kamarTotal',
            'kamarTerisi',
            'kamarTersedia',
            'menungguVerifikasi',
            'laporanAktif',
            'laporanTerbaru'
        ));
    })->middleware('role:pemilik')->name('pemilik.dashboard');

    Route::get('/penghuni/dashboard', function () {
    $kamar = Kamar::where('penghuni_id', auth()->id())->first();

    $tagihanQuery = Tagihan::where('penghuni_id', auth()->id());
    $tagihanBelumLunas = (clone $tagihanQuery)
        ->where('status', Tagihan::STATUS_BELUM_DIBAYAR)
        ->count();
    $tagihanTerbaru = (clone $tagihanQuery)
        ->orderByDesc('jatuh_tempo')
        ->orderByDesc('id')
        ->take(3)
        ->get();

    // Pembayaran milik penghuni ini yang sedang menunggu verifikasi pemilik.
    $pembayaranMenunggu = Pembayaran::menunggu()
        ->whereHas('tagihan', fn ($q) => $q->where('penghuni_id', auth()->id()))
        ->count();

    // Tagihan yang pembayarannya ditolak dan belum dikirim ulang (penghuni perlu bertindak).
    $pembayaranDitolak = (clone $tagihanQuery)
        ->where('status', Tagihan::STATUS_BELUM_DIBAYAR)
        ->whereDoesntHave('pembayaran', fn ($q) => $q->where('status', Pembayaran::STATUS_MENUNGGU))
        ->whereHas('pembayaran', fn ($q) => $q->where('status', Pembayaran::STATUS_DITOLAK))
        ->count();

    // Laporan kerusakan milik penghuni ini yang belum selesai.
    $laporanAktif = LaporanKerusakan::where('penghuni_id', auth()->id())->aktif()->count();

    // 3 pengumuman terbaru dari pemilik kamar yang ditempati penghuni ini.
    $pengumumanTerbaru = Pengumuman::untukPenghuni(auth()->user())->take(3)->get();

    return view('penghuni.dashboard', compact(
        'kamar',
        'tagihanBelumLunas',
        'tagihanTerbaru',
        'pembayaranMenunggu',
        'pembayaranDitolak',
        'laporanAktif',
        'pengumumanTerbaru'
    ));
})->middleware('role:penghuni')->name('penghuni.dashboard');

Route::get('/penghuni/kamar', function () {
    $kamar = Kamar::where('penghuni_id', auth()->id())->first();

    return view('penghuni.kamar', compact('kamar'));
})->middleware('role:penghuni')->name('penghuni.kamar');

Route::get('/penghuni/tagihan', [PenghuniTagihanController::class, 'index'])
    ->middleware('role:penghuni')
    ->name('penghuni.tagihan.index');

Route::get('/penghuni/tagihan/{tagihan}', [PenghuniTagihanController::class, 'show'])
    ->middleware('role:penghuni')
    ->name('penghuni.tagihan.show');

// Pembayaran penghuni. Route statis (bayar, status, riwayat) HARUS di atas {pembayaran}.
Route::middleware('role:penghuni')
    ->prefix('penghuni/pembayaran')
    ->name('penghuni.pembayaran.')
    ->group(function () {
        Route::get('/bayar', [PenghuniPembayaranController::class, 'create'])->name('create');
        Route::post('/', [PenghuniPembayaranController::class, 'store'])->name('store');
        Route::get('/status', [PenghuniPembayaranController::class, 'status'])->name('status');
        Route::get('/riwayat', [PenghuniPembayaranController::class, 'riwayat'])->name('riwayat');
        Route::get('/{pembayaran}', [PenghuniPembayaranController::class, 'show'])
            ->whereNumber('pembayaran')
            ->name('show');
        Route::get('/{pembayaran}/bukti', [PenghuniPembayaranController::class, 'bukti'])
            ->whereNumber('pembayaran')
            ->name('bukti');
    });

// Laporan kerusakan penghuni. Route statis (buat, status) HARUS di atas {laporan}.
Route::middleware('role:penghuni')
    ->prefix('penghuni/laporan')
    ->name('penghuni.laporan.')
    ->group(function () {
        Route::get('/', [PenghuniLaporanController::class, 'index'])->name('index');
        Route::get('/buat', [PenghuniLaporanController::class, 'create'])->name('create');
        Route::post('/', [PenghuniLaporanController::class, 'store'])->name('store');
        Route::get('/status', [PenghuniLaporanController::class, 'status'])->name('status');
        Route::get('/{laporan}', [PenghuniLaporanController::class, 'show'])
            ->whereNumber('laporan')
            ->name('show');
        Route::get('/{laporan}/edit', [PenghuniLaporanController::class, 'edit'])
            ->whereNumber('laporan')
            ->name('edit');
        Route::put('/{laporan}', [PenghuniLaporanController::class, 'update'])
            ->whereNumber('laporan')
            ->name('update');
        Route::delete('/{laporan}', [PenghuniLaporanController::class, 'destroy'])
            ->whereNumber('laporan')
            ->name('destroy');
        Route::get('/{laporan}/foto', [PenghuniLaporanController::class, 'foto'])
            ->whereNumber('laporan')
            ->name('foto');
    });
});

// Route mengelola kamar khusus pemilik
Route::middleware(['auth', 'verified', 'role:pemilik'])
    ->prefix('pemilik')
    ->name('pemilik.')
    ->group(function () {
        Route::resource('kamar', KamarController::class)
            ->except('show')
            ->parameters(['kamar' => 'kamar']);

        Route::resource('tagihan', TagihanController::class)
            ->parameters(['tagihan' => 'tagihan']);

        // Verifikasi pembayaran dan riwayat transaksi.
        Route::get('pembayaran', [PemilikPembayaranController::class, 'index'])
            ->name('pembayaran.index');
        Route::patch('pembayaran/{pembayaran}/terima', [PemilikPembayaranController::class, 'terima'])
            ->whereNumber('pembayaran')
            ->name('pembayaran.terima');
        Route::patch('pembayaran/{pembayaran}/tolak', [PemilikPembayaranController::class, 'tolak'])
            ->whereNumber('pembayaran')
            ->name('pembayaran.tolak');
        Route::get('pembayaran/{pembayaran}/bukti', [PemilikPembayaranController::class, 'bukti'])
            ->whereNumber('pembayaran')
            ->name('pembayaran.bukti');

        Route::get('transaksi', [RiwayatTransaksiController::class, 'index'])
            ->name('transaksi.index');
        Route::get('transaksi/{pembayaran}', [RiwayatTransaksiController::class, 'show'])
            ->whereNumber('pembayaran')
            ->name('transaksi.show');

        // Laporan kerusakan: pemilik melihat dan memperbarui status (hanya maju).
        Route::get('laporan', [PemilikLaporanController::class, 'index'])
            ->name('laporan.index');
        Route::get('laporan/{laporan}', [PemilikLaporanController::class, 'show'])
            ->whereNumber('laporan')
            ->name('laporan.show');
        Route::patch('laporan/{laporan}', [PemilikLaporanController::class, 'update'])
            ->whereNumber('laporan')
            ->name('laporan.update');
        Route::get('laporan/{laporan}/foto', [PemilikLaporanController::class, 'foto'])
            ->whereNumber('laporan')
            ->name('laporan.foto');

        // Pengumuman dan peraturan kos: CRUD penuh oleh pemilik.
        Route::resource('pengumuman', PemilikPengumumanController::class)
            ->parameters(['pengumuman' => 'pengumuman']);

        Route::resource('peraturan', PemilikPeraturanController::class)
            ->except('show')
            ->parameters(['peraturan' => 'peraturan']);

        // Pemilik hanya bisa melihat daftar dan menghapus penghuni (tanpa tambah/edit).
        Route::resource('penghuni', PenghuniController::class)
            ->only(['index', 'destroy'])
            ->parameters(['penghuni' => 'penghuni']);
});

// Pengumuman dan peraturan kos untuk penghuni (hanya lihat).
Route::middleware(['auth', 'verified', 'role:penghuni'])
    ->prefix('penghuni')
    ->name('penghuni.')
    ->group(function () {
        Route::get('pengumuman', [PenghuniPengumumanController::class, 'index'])->name('pengumuman.index');
        Route::get('pengumuman/{pengumuman}', [PenghuniPengumumanController::class, 'show'])
            ->whereNumber('pengumuman')
            ->name('pengumuman.show');
        Route::get('peraturan', [PenghuniPeraturanController::class, 'index'])->name('peraturan.index');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])
        ->name('profile.photo.destroy');
});

require __DIR__.'/auth.php';