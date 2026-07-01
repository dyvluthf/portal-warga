<?php

namespace Database\Factories;

use App\Models\RtRw;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KeuanganFactory extends Factory
{
    public function definition(): array
    {
        return [
            'jenis' => 'pemasukan',
            'judul' => $this->faker->sentence(3),
            'jumlah' => $this->faker->numberBetween(10000, 1000000),
            'kategori' => $this->faker->randomElement(['iuran', 'donasi', 'laporan']),
            'keterangan' => $this->faker->sentence(),
            'rt_rw_id' => RtRw::factory(),
            'tanggal' => now(),
            'dicatat_oleh' => User::factory()->superAdmin(),
        ];
    }

    public function pengeluaran(): static
    {
        return $this->state(['jenis' => 'pengeluaran']);
    }
}
