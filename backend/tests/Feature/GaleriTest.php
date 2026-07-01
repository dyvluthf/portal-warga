<?php

namespace Tests\Feature;

use App\Models\GaleriAlbum;
use App\Models\User;
use Tests\TestCase;

class GaleriTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_admin_can_list_galeri()
    {
        GaleriAlbum::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/galeri');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_album()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/galeri', [
                'nama_album' => 'Kegiatan 17 Agustus',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('galeri_album', ['nama_album' => 'Kegiatan 17 Agustus']);
    }

    public function test_create_album_fails_without_nama()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/galeri', []);

        $response->assertStatus(422);
    }

    public function test_admin_can_view_album()
    {
        $album = GaleriAlbum::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/galeri/{$album->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_delete_album()
    {
        $album = GaleriAlbum::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/galeri/{$album->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('galeri_album', ['id' => $album->id]);
    }
}
