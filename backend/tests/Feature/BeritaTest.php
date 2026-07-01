<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\User;
use Tests\TestCase;

class BeritaTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_admin_can_list_berita()
    {
        Berita::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/berita');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_create_berita()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/berita', [
                'judul' => 'Berita Baru',
                'konten' => 'Konten berita baru',
                'kategori' => 'pengumuman',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('berita', ['judul' => 'Berita Baru']);
    }

    public function test_create_berita_fails_without_judul()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/berita', [
                'konten' => 'Konten saja',
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_publish_berita()
    {
        $berita = Berita::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/berita/{$berita->id}/publish");

        $response->assertStatus(200);
        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_can_draft_berita()
    {
        $berita = Berita::factory()->published()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/berita/{$berita->id}/draft");

        $response->assertStatus(200);
        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_update_berita()
    {
        $berita = Berita::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/berita/{$berita->id}", [
                'judul' => 'Judul Diubah',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('berita', ['id' => $berita->id, 'judul' => 'Judul Diubah']);
    }

    public function test_admin_can_delete_berita()
    {
        $berita = Berita::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/berita/{$berita->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('berita', ['id' => $berita->id]);
    }

    public function test_warga_role_cannot_manage_berita()
    {
        $wargaUser = User::factory()->warga()->create();

        $response = $this->actingAs($wargaUser, 'sanctum')
            ->postJson('/api/v1/berita', ['judul' => 'Test', 'konten' => 'Test']);

        $response->assertStatus(403);
    }
}
