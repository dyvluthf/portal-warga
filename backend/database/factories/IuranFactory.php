<?php

namespace Database\Factories;

use App\Models\Warga;
use Illuminate\Database\Eloquent\Factories\Factory;

class IuranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warga_id' => Warga::factory(),
            'bulan' => $this->faker->date('Y-m'),
            'nominal' => 50000,
            'status' => 'belum_bayar',
        ];
    }

    public function lunas(): static
    {
        return $this->state([
            'status' => 'lunas',
            'metode' => 'tunai',
            'tanggal_bayar' => now(),
        ]);
    }
}
