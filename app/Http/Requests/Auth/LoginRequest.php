<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Periksa apakah pengguna memiliki izin untuk melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Ambil aturan validasi yang digunakan untuk memeriksa data yang dikirim.
     *
     * @return array<string, Aturan validasi yang bisa berupa array atau teks.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Pesan error validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * Coba masuk menggunakan email dan kata sandi yang diberikan.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! User::where('email', $this->input('email'))->exists()) {
            $this->gagal('email', 'Email belum terdaftar.');
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->gagal('password', 'Password salah.');
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Pastikan permintaan login tidak dibatasi oleh sistem karena terlalu banyak percobaan.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Ambil kunci pembatasan jumlah percobaan untuk permintaan tersebut.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Catat percobaan gagal, simpan password sementara, lalu lempar error ke field tertentu.
     */
    private function gagal(string $field, string $pesan): never
    {
        RateLimiter::hit($this->throttleKey());
        $this->simpanPassword();

        throw ValidationException::withMessages([$field => $pesan]);
    }

    /**
     * Simpan password terenkripsi untuk satu request berikutnya,
     * supaya field password tidak kosong setelah error.
     */
    private function simpanPassword(): void
    {
        $this->session()->flash('login_password', Crypt::encryptString((string) $this->input('password')));
    }

    /**
     * Dipanggil kalau validasi rules() gagal (misalnya email kosong).
     */
    protected function failedValidation(Validator $validator)
    {
        $this->simpanPassword();

        parent::failedValidation($validator);
    }
}