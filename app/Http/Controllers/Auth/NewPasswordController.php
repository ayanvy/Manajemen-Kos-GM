<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Menampilkan tampilan pengaturan ulang kata sandi.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Menangani permintaan kata sandi baru yang masuk.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', Rules\Password::defaults()],
            'password_confirmation' => ['required', 'same:password'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal :min karakter.',
            'password.mixed' => 'Password harus mengandung huruf besar dan huruf kecil.',
            'password.letters' => 'Password harus mengandung huruf.',
            'password.numbers' => 'Password harus mengandung angka.',
            'password.symbols' => 'Password harus mengandung simbol.',
            'password.uncompromised' => 'Password ini sudah pernah bocor di internet. Pilih password lain.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sesuai.',
        ]);

        if ($validator->fails()) {
            $this->simpanPassword($request);

            return back()
                ->withInput($request->only('email'))
                ->withErrors($validator);
        }

        // Mencoba mengubah kata sandi pengguna.
        // Jika berhasil, kata sandi baru akan disimpan ke database.
        // Jika gagal, sistem akan menampilkan pesan kesalahan yang sesuai.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Jika kata sandi berhasil diubah, pengguna akan diarahkan ke halaman utama setelah login.
        // Jika terjadi kesalahan, pengguna akan dikembalikan ke halaman sebelumnya
        // beserta pesan kesalahannya.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        $pesan = match ($status) {
            Password::INVALID_USER => 'Email belum terdaftar.',
            Password::INVALID_TOKEN => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            default => __($status),
        };

        $this->simpanPassword($request);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $pesan]);
    }

    /**
     * Simpan password terenkripsi untuk satu request berikutnya,
     * supaya field password tidak kosong setelah error.
     */
    private function simpanPassword(Request $request): void
    {
        $request->session()->flash('reset_password', Crypt::encryptString((string) $request->input('password')));
        $request->session()->flash('reset_password_confirmation', Crypt::encryptString((string) $request->input('password_confirmation')));
    }
}