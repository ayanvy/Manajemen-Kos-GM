<?php

namespace App\Http\Requests;

use App\Models\Kamar;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPemilik() ?? false;
    }

    /** Bersihkan titik pemisah ribuan pada jumlah. */
    protected function prepareForValidation(): void
    {
        $jumlah = $this->input('jumlah');

        if (is_string($jumlah)) {
            $jumlah = preg_replace('/[\s.]/', '', $jumlah);
        }

        $this->merge(['jumlah' => $jumlah]);
    }

    /**
     * @return array<string, Aturan validasi dalam bentuk array atau teks.
     */
    public function rules(): array
    {
        /** @var Tagihan|null $tagihan */
        $tagihan = $this->route('tagihan');

        return [
            // Hanya penghuni yang menempati kamar milik pemilik ini.
            'penghuni_id' => [
                'bail',
                'required',
                Rule::exists('users', 'id')->where('role', User::ROLE_PENGHUNI),
                function (string $attribute, mixed $value, \Closure $fail) use ($tagihan) {
                    $punyaKamar = Kamar::where('pemilik_id', $this->user()->id)
                        ->where('penghuni_id', $value)
                        ->exists();

                    // Saat edit, penghuni lama tetap boleh walau kamarnya sudah dikosongkan.
                    if (! $punyaKamar && (int) $tagihan?->penghuni_id !== (int) $value) {
                        $fail('Penghuni yang dipilih belum menempati kamar Anda.');
                    }
                },
            ],
            'periode_bulan' => [
                'bail',
                'required',
                'date_format:Y-m',
                function (string $attribute, mixed $value, \Closure $fail) use ($tagihan) {
                    if (! is_string($value) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
                        return;
                    }

                    $ada = Tagihan::where('penghuni_id', $this->input('penghuni_id'))
                        ->where('periode', Tagihan::periodeDariBulan($value))
                        ->when($tagihan, fn ($q) => $q->where('id', '!=', $tagihan->id))
                        ->exists();

                    if ($ada) {
                        $fail('Penghuni ini sudah punya tagihan untuk periode tersebut.');
                    }
                },
            ],
            'jumlah' => ['bail', 'required', 'numeric', 'min:1', 'max:9999999999'],
            'jatuh_tempo' => ['bail', 'required', 'date'],
        ];
    }

    /**
     * Data siap simpan: periode_bulan diubah menjadi teks "September 2026".
     * Status tidak ikut: tagihan baru memakai default database (belum_dibayar), dan berubah
     * jadi lunas hanya lewat verifikasi pembayaran.
     */
    public function dataTagihan(): array
    {
        $data = $this->validated();

        $data['periode'] = Tagihan::periodeDariBulan($data['periode_bulan']);
        unset($data['periode_bulan']);

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'penghuni_id.required' => 'Penghuni wajib dipilih.',
            'penghuni_id.exists' => 'Penghuni yang dipilih tidak valid.',
            'periode_bulan.required' => 'Periode wajib diisi.',
            'periode_bulan.date_format' => 'Format periode tidak valid.',
            'jumlah.required' => 'Jumlah tagihan wajib diisi.',
            'jumlah.numeric' => 'Jumlah tagihan harus berupa angka.',
            'jumlah.min' => 'Jumlah tagihan harus lebih dari 0.',
            'jumlah.max' => 'Jumlah tagihan terlalu besar.',
            'jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'jatuh_tempo.date' => 'Tanggal jatuh tempo tidak valid.',
        ];
    }
}
