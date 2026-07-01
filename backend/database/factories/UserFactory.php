<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'warga',
            'status_aktif' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(['role' => 'super_admin']);
    }

    public function adminRtRw(): static
    {
        return $this->state(['role' => 'admin_rt_rw']);
    }

    public function warga(): static
    {
        return $this->state(['role' => 'warga']);
    }

    public function nonaktif(): static
    {
        return $this->state(['status_aktif' => false]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole($user->role);
        });
    }
}
