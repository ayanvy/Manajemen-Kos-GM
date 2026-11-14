<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman pendaftaran akun baru.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Tangani permintaan pendaftaran akun baru.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['bail', 'required', 'string', 'min:3', 'max:255'],
            'email' => [
                'bail', 'required', 'string', 'lowercase', 'email',
                // Wajib ada domain dengan titik dan akhiran huruf, "nama@123" ditolak
                'regex:/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/',
                'max:255', 'unique:'.User::class,
            ],
            'password' => ['bail', 'required', Rules\Password::min(8)],
            'password_confirmation' => ['bail', 'required', 'same:password'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama tidak valid.',
            'name.min' => 'Nama minimal 3 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email tidak valid.',
            'email.lowercase' => 'Email harus ditulis dengan huruf kecil.',
            'email.email' => 'Format email tidak valid. Contoh: nama@gmail.com',
            'email.regex' => 'Format email tidak valid. Contoh: nama@gmail.com',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sesuai.',
        ]);

        if ($validator->fails()) {
            // Simpan password sementara supaya field yang sudah benar tidak terhapus
            $request->session()->flash('register_password', Crypt::encryptString((string) $request->input('password')));
            $request->session()->flash('register_password_confirmation', Crypt::encryptString((string) $request->input('password_confirmation')));

            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors($validator);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_PENGHUNI,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}