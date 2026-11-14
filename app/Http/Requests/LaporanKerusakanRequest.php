<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Validasi form laporan kerusakan penghuni (buat dan ubah). */
class LaporanKerusakanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPenghuni() ?? false;
    }

    /**
     * @return array<string, Aturan validasi dalam bentuk array atau teks.
     */
    public function rules(): array
    {
        return [
            'deskripsi' => ['required', 'string', 'min:10', 'max:1000'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:3072'],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'deskripsi.required' => 'Deskripsi kerusakan wajib diisi.',
            'deskripsi.min' => 'Deskripsi minimal 10 karakter agar pemilik paham kerusakannya.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
            'foto.file' => 'Foto harus berupa file.',
            'foto.mimes' => 'Foto harus berformat JPG atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 3 MB.',
        ];
    }
}
