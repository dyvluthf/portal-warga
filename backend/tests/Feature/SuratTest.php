<?php

namespace Tests\Feature;

use App\Models\Surat;
use App\Models\User;
use App\Models\Warga;
use Tests\TestCase;

class SuratTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_admin_can_list_surat()
    {
        Surat::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/surat');

        $response->assertStatus(200);
    }

    public function test_admin_can_approve_surat()
    {
        $surat = Surat::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/surat/{$surat->id}/setujui");

        $response->assertStatus(200);
        $this->assertDatabaseHas('surat', [
            'id' => $surat->id,
            'status' => 'disetujui',
        ]);
    }

    public function test_admin_can_publish_surat()
    {
        $surat = Surat::factory()->disetujui()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/surat/{$surat->id}/terbitkan", [
                'nomor_surat' => '474/100/KT',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('surat', [
            'id' => $surat->id,
            'status' => 'diterbitkan',
        ]);
    }

    public function test_admin_can_reject_surat()
    {
        $surat = Surat::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/surat/{$surat->id}/tolak", [
                'alasan_penolakan' => 'Data tidak lengkap.',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('surat', [
            'id' => $surat->id,
            'status' => 'ditolak',
        ]);
    }

    public function test_reject_fails_without_alasan()
    {
        $surat = Surat::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/surat/{$surat->id}/tolak");

        $response->assertStatus(422);
    }

    public function test_warga_role_cannot_manage_surat()
    {
        $wargaUser = User::factory()->warga()->create();
        $surat = Surat::factory()->create();

        $response = $this->actingAs($wargaUser, 'sanctum')
            ->postJson("/api/v1/surat/{$surat->id}/setujui");

        $response->assertStatus(403);
    }
}
