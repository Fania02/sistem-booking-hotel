<?php

namespace Database\Factories;

use App\Models\Tamu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tamu>
 */
class TamuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'nama' => fake()->name(),
            'nik' => fake()->unique()->numerify('################'),
            'no_hp' => '08'.fake()->numerify('##########'),
            'alamat' => fake()->address(),
        ];
    }
}
