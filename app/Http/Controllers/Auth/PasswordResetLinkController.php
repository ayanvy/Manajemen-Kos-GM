<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan halaman permintaan tautan untuk mengatur ulang kata sandi.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Tangani permintaan tautan untuk mengatur ulang kata sandi yang masuk.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        // Mengirimkan tautan untuk mengatur ulang kata sandi kepada pengguna.
        // Setelah tautan dikirim, selanjutnya memeriksa hasilnya untuk menentukan
        // pesan yang akan ditampilkan kepada pengguna.
        // Terakhir, sistem akan memberikan respons yang sesuai.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        // Email tidak ditemukan di database -> pesan khusus, selain itu pakai pesan bawaan Laravel
        $pesan = $status == Password::INVALID_USER
            ? 'Email belum terdaftar.'
            : __($status);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $pesan]);
    }
}