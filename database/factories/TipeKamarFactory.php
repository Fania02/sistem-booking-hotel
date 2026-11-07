<?php

namespace Database\Factories;

use App\Models\TipeKamar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipeKamar>
 */
class TipeKamarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_tipe' => fake()->unique()->words(2, true),
            'harga_per_malam' => fake()->numberBetween(25, 150) * 10000,
            'kapasitas' => fake()->numberBetween(1, 5),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
