<?php

namespace Database\Factories;

use App\Models\RtRw;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    public function definition(): array
    {
        $role = $this->faker->randomElement(['super_admin', 'admin_rt_rw']);

        return [
            'nama' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => $role,
            'user_id' => User::factory()->state(function () use ($role) {
                return ['role' => $role];
            }),
            'rt_rw_id' => $role === 'admin_rt_rw' ? RtRw::factory() : null,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(['role' => 'super_admin']);
    }

    public function adminRtRw(): static
    {
        return $this->state(['role' => 'admin_rt_rw']);
    }
}
