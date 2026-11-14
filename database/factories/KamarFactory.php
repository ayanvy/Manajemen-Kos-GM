<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kamar>
 */
class KamarFactory extends Factory
{
    protected $model = Kamar::class;

    public function definition(): array
    {
        return [
            'pemilik_id' => User::factory(),
            'penghuni_id' => null,
            'nomor_kamar' => 'A-'.str_pad((string) fake()->unique()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'tipe' => fake()->randomElement(['Standard', 'Deluxe']),
            'harga' => fake()->randomElement([850000, 900000, 1000000]),
            'fasilitas' => 'Kasur, lemari, meja belajar, Wi-Fi',
            'status' => Kamar::STATUS_TERSEDIA,
        ];
    }

    public function terisi(): static
    {
        return $this->state(fn () => [
            'penghuni_id' => User::factory()->state([
                'role' => User::ROLE_PENGHUNI,
            ]),
            'status' => Kamar::STATUS_TERISI,
        ]);
    }
}
