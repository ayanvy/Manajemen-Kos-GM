<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PeraturanKosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPemilik() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $judul = is_string($this->judul) ? trim($this->judul) : $this->judul;

        $this->merge([
            'judul' => $judul === '' ? null : $judul,
            'isi' => is_string($this->isi) ? trim($this->isi) : $this->isi,
        ]);
    }

    /**
     * @return array<string, Aturan validasi dalam bentuk array atau teks.
     */
    public function rules(): array
    {
        return [
            'judul' => ['nullable', 'string', 'max:150'],
            'isi' => ['bail', 'required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.max' => 'Judul maksimal 150 karakter.',
            'isi.required' => 'Isi peraturan wajib diisi.',
            'isi.max' => 'Isi peraturan maksimal 1000 karakter.',
        ];
    }
}
