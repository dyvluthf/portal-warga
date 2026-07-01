<?php

namespace Database\Factories;

use App\Models\Warga;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengaduanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warga_id' => Warga::factory(),
            'kategori' => $this->faker->randomElement(['kebersihan', 'keamanan', 'infrastruktur', 'sosial']),
            'deskripsi' => $this->faker->paragraph(),
            'status' => 'menunggu',
        ];
    }

    public function diproses(): static
    {
        return $this->state(['status' => 'diproses']);
    }

    public function selesai(): static
    {
        return $this->state([
            'status' => 'selesai',
            'tanggapan_admin' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(1, 5),
        ]);
    }
}
