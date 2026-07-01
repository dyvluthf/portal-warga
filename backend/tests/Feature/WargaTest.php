<?php

namespace Tests\Feature;

use App\Models\RtRw;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WargaTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_admin_can_list_warga()
    {
        Warga::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/warga');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['data', 'current_page', 'total']]);
    }

    public function test_admin_can_filter_warga_by_search()
    {
        Warga::factory()->create(['nama' => 'Budi Santoso']);
        Warga::factory()->create(['nama' => 'Siti Aminah']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/warga?search=Budi');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_admin_can_filter_warga_by_rt()
    {
        $rt1 = RtRw::factory()->create();
        $rt2 = RtRw::factory()->create();
        Warga::factory()->create(['rt_rw_id' => $rt1->id]);
        Warga::factory()->count(2)->create(['rt_rw_id' => $rt2->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/warga?rt_rw_id={$rt1->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_admin_can_create_warga()
    {
        $rt = RtRw::factory()->create();
        $nik = '3201010101010101';

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/warga', [
                'nik' => $nik,
                'nama' => 'Warga Baru',
                'alamat' => 'Jl. Baru No.1',
                'rt_rw_id' => $rt->id,
                'no_telp' => '081234567890',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('warga', ['nik' => $nik]);
        $this->assertDatabaseHas('users', ['email' => "{$nik}@warga.local"]);
    }

    public function test_create_warga_fails_with_invalid_nik()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/warga', [
                'nik' => '123',
                'nama' => 'Warga Baru',
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_view_warga_detail()
    {
        $warga = Warga::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/warga/{$warga->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.warga.nik', $warga->nik);
    }

    public function test_admin_can_update_warga()
    {
        $warga = Warga::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/warga/{$warga->id}", [
                'nama' => 'Nama Diubah',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('warga', [
            'id' => $warga->id,
            'nama' => 'Nama Diubah',
        ]);
    }

    public function test_admin_can_delete_warga()
    {
        $warga = Warga::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/warga/{$warga->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('warga', ['id' => $warga->id]);
        $this->assertDatabaseMissing('users', ['id' => $warga->user_id]);
    }

    public function test_warga_role_cannot_list_warga()
    {
        $wargaUser = User::factory()->warga()->create();

        $response = $this->actingAs($wargaUser, 'sanctum')
            ->getJson('/api/v1/warga');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_warga()
    {
        $response = $this->getJson('/api/v1/warga');
        $response->assertStatus(401);
    }
}
