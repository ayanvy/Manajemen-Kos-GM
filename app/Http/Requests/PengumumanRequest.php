<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPemilik() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'judul' => is_string($this->judul) ? trim($this->judul) : $this->judul,
            'isi' => is_string($this->isi) ? trim($this->isi) : $this->isi,
        ]);
    }

    /**
     * @return array<string, Aturan validasi dalam bentuk array atau teks.
     */
    public function rules(): array
    {
        return [
            'judul' => ['bail', 'required', 'string', 'max:150'],
            'isi' => ['bail', 'required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.max' => 'Judul pengumuman maksimal 150 karakter.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'isi.max' => 'Isi pengumuman maksimal 5000 karakter.',
        ];
    }
}
