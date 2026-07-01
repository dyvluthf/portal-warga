<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BeritaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'judul' => $this->faker->sentence(),
            'konten' => $this->faker->paragraphs(3, true),
            'kategori' => $this->faker->randomElement(['pengumuman', 'kegiatan', 'info']),
            'status' => 'draft',
            'penulis_id' => User::factory()->superAdmin(),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => 'published',
            'tanggal_publish' => now(),
        ]);
    }
}
