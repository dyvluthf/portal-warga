<?php

namespace Database\Factories;

use App\Models\RtRw;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GaleriAlbumFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_album' => $this->faker->sentence(2),
            'rt_rw_id' => RtRw::factory(),
            'dibuat_oleh' => User::factory()->superAdmin(),
        ];
    }
}
