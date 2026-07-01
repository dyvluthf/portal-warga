<?php

namespace Database\Factories;

use App\Models\Warga;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuratFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warga_id' => Warga::factory(),
            'jenis_surat' => $this->faker->randomElement(['sktm', 'sku', 'suket']),
            'data_form' => ['keperluan' => $this->faker->sentence()],
            'status' => 'menunggu',
        ];
    }

    public function disetujui(): static
    {
        return $this->state(['status' => 'disetujui']);
    }

    public function diterbitkan(): static
    {
        return $this->state([
            'status' => 'diterbitkan',
            'nomor_surat' => '474/' . $this->faker->numberBetween(1, 999) . '/KT',
        ]);
    }
}
