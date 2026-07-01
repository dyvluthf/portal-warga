<?php

namespace Tests\Feature;

use App\Models\Pengaduan;
use App\Models\User;
use App\Models\Warga;
use Tests\TestCase;

class PengaduanTest extends TestCase
{
    public function test_admin_can_list_pengaduan()
    {
        $admin = User::factory()->superAdmin()->create();
        Pengaduan::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/pengaduan');

        $response->assertStatus(200);
    }

    public function test_admin_can_update_pengaduan_status_to_proses()
    {
        $admin = User::factory()->superAdmin()->create();
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/pengaduan/{$pengaduan->id}/proses");

        $response->assertStatus(200);
        $this->assertDatabaseHas('pengaduan', [
            'id' => $pengaduan->id,
            'status' => 'diproses',
        ]);
    }

    public function test_admin_can_complete_pengaduan()
    {
        $admin = User::factory()->superAdmin()->create();
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/pengaduan/{$pengaduan->id}/selesai", [
                'tanggapan_admin' => 'Sudah ditangani.',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pengaduan', [
            'id' => $pengaduan->id,
            'status' => 'selesai',
        ]);
    }

    public function test_admin_can_reject_pengaduan()
    {
        $admin = User::factory()->superAdmin()->create();
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/pengaduan/{$pengaduan->id}/tolak", [
                'alasan_penolakan' => 'Tidak valid.',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pengaduan', [
            'id' => $pengaduan->id,
            'status' => 'ditolak',
        ]);
    }

    public function test_warga_role_cannot_process_pengaduan()
    {
        $wargaUser = User::factory()->warga()->create();
        $pengaduan = Pengaduan::factory()->create();

        $response = $this->actingAs($wargaUser, 'sanctum')
            ->postJson("/api/v1/pengaduan/{$pengaduan->id}/proses");

        $response->assertStatus(403);
    }

    public function test_warga_can_view_own_pengaduan()
    {
        $warga = Warga::factory()->create(['user_id' => User::factory()->warga()->create()->id]);
        $user = $warga->user;
        $pengaduan = Pengaduan::factory()->create(['warga_id' => $warga->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/pengaduan');

        $response->assertStatus(200);
    }
}
