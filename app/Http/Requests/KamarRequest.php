<?php

namespace App\Http\Requests;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPemilik() ?? false;
    }

    /**
     * Bersihkan input sebelum divalidasi: spasi berlebih dan titik pemisah ribuan pada harga.
     */
    protected function prepareForValidation(): void
    {
        $harga = $this->input('harga');

        if (is_string($harga)) {
            $harga = preg_replace('/[\s.]/', '', $harga);
        }

        $this->merge([
            'nomor_kamar' => is_string($this->nomor_kamar) ? trim($this->nomor_kamar) : $this->nomor_kamar,
            'tipe' => is_string($this->tipe) ? trim($this->tipe) : $this->tipe,
            'harga' => $harga,
        ]);
    }

    /**
     * @return array<string, Aturan validasi dalam bentuk array atau teks.
     */
    public function rules(): array
    {
        /** @var Kamar|null $kamar */
        $kamar = $this->route('kamar');

        return [
            'nomor_kamar' => [
                'bail',
                'required',
                'string',
                'max:20',
                Rule::unique('kamar', 'nomor_kamar')
                    ->where('pemilik_id', $this->user()->id)
                    ->ignore($kamar?->id),
            ],
            'tipe' => ['bail', 'required', 'string', 'max:50'],
            'harga' => ['bail', 'required', 'numeric', 'min:0', 'max:9999999999'],
            'fasilitas' => ['nullable', 'string', 'max:1000'],
            // Foto kamar opsional: JPG, PNG, atau WebP, maksimal 2 MB.
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
            // Penghuni yang dikonfirmasi menempati kamar ini (kosong = kamar tersedia).
            'penghuni_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', User::ROLE_PENGHUNI),
                Rule::unique('kamar', 'penghuni_id')->ignore($kamar?->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'nomor_kamar.max' => 'Nomor kamar maksimal 20 karakter.',
            'nomor_kamar.unique' => 'Nomor kamar ini sudah dipakai.',
            'tipe.required' => 'Tipe kamar wajib diisi.',
            'tipe.max' => 'Tipe kamar maksimal 50 karakter.',
            'harga.required' => 'Harga sewa wajib diisi.',
            'harga.numeric' => 'Harga sewa harus berupa angka.',
            'harga.min' => 'Harga sewa tidak boleh negatif.',
            'harga.max' => 'Harga sewa terlalu besar.',
            'fasilitas.max' => 'Fasilitas maksimal 1000 karakter.',
            'foto.image' => 'Foto kamar harus berupa gambar.',
            'foto.mimes' => 'Foto kamar harus berformat JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto kamar maksimal 2 MB.',
            'foto.uploaded' => 'Foto kamar gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
            'penghuni_id.exists' => 'Penghuni yang dipilih tidak valid.',
            'penghuni_id.unique' => 'Penghuni ini sudah menempati kamar lain.',
        ];
    }
}
