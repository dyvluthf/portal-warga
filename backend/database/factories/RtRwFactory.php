<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RtRwFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nomor_rt' => (string) $this->faker->numberBetween(1, 20),
            'nomor_rw' => (string) $this->faker->numberBetween(1, 10),
            'nama_kelurahan' => $this->faker->city(),
            'kecamatan' => $this->faker->city(),
        ];
    }
}
