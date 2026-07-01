<?php

namespace Database\Factories;

use App\Models\RtRw;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WargaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('33################'),
            'nama' => $this->faker->name(),
            'alamat' => $this->faker->address(),
            'no_telp' => $this->faker->phoneNumber(),
            'status_verifikasi' => 'terverifikasi',
            'user_id' => User::factory()->warga(),
            'rt_rw_id' => RtRw::factory(),
        ];
    }

    public function menunggu(): static
    {
        return $this->state(['status_verifikasi' => 'menunggu']);
    }

    public function ditolak(): static
    {
        return $this->state(['status_verifikasi' => 'ditolak']);
    }
}
