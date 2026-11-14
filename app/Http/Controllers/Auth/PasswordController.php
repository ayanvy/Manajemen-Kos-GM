<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Perbarui kata sandi pengguna.
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['bail', 'required', 'current_password'],
            'password' => ['bail', 'required', Password::min(8)],
            'password_confirmation' => ['bail', 'required', 'same:password'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak sesuai.',
        ]);

        if ($validator->fails()) {
            // Simpan isian sementara supaya field yang sudah benar tidak terhapus
            $request->session()->flash('update_current_password', Crypt::encryptString((string) $request->input('current_password')));
            $request->session()->flash('update_password', Crypt::encryptString((string) $request->input('password')));
            $request->session()->flash('update_password_confirmation', Crypt::encryptString((string) $request->input('password_confirmation')));

            return back()->withErrors($validator, 'updatePassword');
        }

        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('status', 'password-updated');
    }
}