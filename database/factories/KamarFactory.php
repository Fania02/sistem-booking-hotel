<?php

namespace Database\Factories;

use App\Enums\StatusKamar;
use App\Models\Kamar;
use App\Models\TipeKamar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kamar>
 */
class KamarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipe_kamar_id' => TipeKamar::factory(),
            'nomor_kamar' => fake()->unique()->numerify('###'),
            'lantai' => fake()->numberBetween(1, 5),
            'status' => StatusKamar::Tersedia,
        ];
    }

    /**
     * Indicate that the room is under maintenance.
     */
    public function perbaikan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusKamar::Perbaikan,
        ]);
    }
}
