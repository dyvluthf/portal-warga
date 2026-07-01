<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warga;
use Database\Factories\WargaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_super_admin_can_login()
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 'message', 'data' => ['user', 'token', 'role'],
            ]);
    }

    public function test_warga_can_login_with_nik()
    {
        $warga = Warga::factory()->create();
        $user = $warga->user;

        $response = $this->postJson('/api/v1/auth/login', [
            'nik' => $warga->nik,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.role', 'warga');
    }

    public function test_login_fails_with_wrong_password()
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_when_account_inactive()
    {
        $user = User::factory()->superAdmin()->nonaktif()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_warga_can_register()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'nik' => '3201010101010099',
            'nama' => 'Test User',
            'alamat' => 'Jl. Testing No.1',
            'no_telp' => '081234567899',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => '3201010101010099@warga.local',
            'role' => 'warga',
        ]);

        $this->assertDatabaseHas('warga', [
            'nik' => '3201010101010099',
            'status_verifikasi' => 'menunggu',
        ]);
    }

    public function test_register_fails_with_duplicate_nik()
    {
        Warga::factory()->create(['nik' => '3201010101010099']);

        $response = $this->postJson('/api/v1/auth/register', [
            'nik' => '3201010101010099',
            'nama' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.role', 'super_admin');
    }

    public function test_unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->superAdmin()->create();
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_refresh_token()
    {
        $user = User::factory()->superAdmin()->create();
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token']]);
    }
}
