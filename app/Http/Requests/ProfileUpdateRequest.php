<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Ambil aturan untuk memvalidasi data yang dikirim dalam permintaan.
     *
     * @return array<string, Aturan validasi dalam bentuk array atau teks.
     */

    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'min:3', 'max:255'],
            'email' => [
                'bail',
                'required',
                'string',
                'lowercase',
                'email',
                // Wajib ada domain dengan titik dan akhiran huruf, "nama@123" ditolak
                'regex:/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Opsional, tetapi kalau diisi: hanya angka 0-9, minimal 10 dan maksimal 13 digit.
            'phone' => ['bail', 'nullable', 'string', 'regex:/^[0-9]+$/D', 'min:10', 'max:13'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    /**
     * Pesan error dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
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
            'phone.string' => 'Nomor telepon tidak valid.',
            'phone.regex' => 'Nomor telepon harus berupa angka (0-9) tanpa spasi atau tanda lain.',
            'phone.min' => 'Nomor telepon minimal 10 digit.',
            'phone.max' => 'Nomor telepon maksimal 13 digit.',
        ];
    }
}