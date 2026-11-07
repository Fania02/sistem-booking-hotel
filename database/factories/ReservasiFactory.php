<?php

namespace Database\Factories;

use App\Enums\StatusPembayaran;
use App\Enums\StatusReservasi;
use App\Enums\SumberReservasi;
use App\Models\Reservasi;
use App\Models\Tamu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Reservasi>
 */
class ReservasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Kode booking tidak diisi di sini karena dibuat otomatis oleh model (BK-0001, dst).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tamu_id' => Tamu::factory(),
            'tgl_checkin' => fake()->dateTimeBetween('+1 day', '+1 month')->format('Y-m-d'),
            'tgl_checkout' => fn (array $attributes): string => Carbon::parse($attributes['tgl_checkin'])
                ->addDays(fake()->numberBetween(1, 4))
                ->toDateString(),
            'total_harga' => 0,
            'status_pembayaran' => StatusPembayaran::BelumBayar,
            'sumber' => SumberReservasi::Online,
            'dibuat_oleh' => null,
            'status' => StatusReservasi::Menunggu,
        ];
    }
}
