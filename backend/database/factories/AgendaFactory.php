<?php

namespace Database\Factories;

use App\Models\RtRw;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => $this->faker->sentence(3),
            'tanggal' => $this->faker->dateTimeBetween('now', '+1 month'),
            'jam' => $this->faker->time('H:i'),
            'lokasi' => $this->faker->address(),
            'deskripsi' => $this->faker->paragraph(),
            'rt_rw_id' => RtRw::factory(),
            'dibuat_oleh' => User::factory()->superAdmin(),
        ];
    }
}
