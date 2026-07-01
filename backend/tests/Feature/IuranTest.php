<?php

namespace Tests\Feature;

use App\Models\Iuran;
use App\Models\User;
use App\Models\Warga;
use Tests\TestCase;

class IuranTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_admin_can_list_iuran()
    {
        Iuran::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/iuran');

        $response->assertStatus(200);
    }

    public function test_admin_can_mark_iuran_as_lunas()
    {
        $warga = Warga::factory()->create();
        $iuran = Iuran::factory()->create(['warga_id' => $warga->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/iuran/{$warga->id}/tandai-lunas", [
                'bulan' => now()->format('Y-m'),
                'nominal' => 50000,
                'metode' => 'tunai',
            ]);

        $response->assertStatus(200);
    }

    public function test_admin_can_verify_iuran()
    {
        $iuran = Iuran::factory()->create(['status' => 'menunggu_verifikasi']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/iuran/{$iuran->id}/verifikasi");

        $response->assertStatus(200);
        $this->assertDatabaseHas('iuran', [
            'id' => $iuran->id,
            'status' => 'lunas',
        ]);
    }

    public function test_get_iuran_laporan()
    {
        Iuran::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/iuran/laporan');

        $response->assertStatus(200);
    }

    public function test_warga_role_cannot_manage_iuran()
    {
        $wargaUser = User::factory()->warga()->create();

        $response = $this->actingAs($wargaUser, 'sanctum')
            ->getJson('/api/v1/iuran/laporan');

        $response->assertStatus(403);
    }
}
