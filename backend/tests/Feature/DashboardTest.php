<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Berita;
use App\Models\Iuran;
use App\Models\Keuangan;
use App\Models\Pengaduan;
use App\Models\Surat;
use App\Models\User;
use App\Models\Warga;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_admin_dashboard_returns_stats()
    {
        $admin = User::factory()->superAdmin()->create();
        Warga::factory()->count(5)->create();
        Berita::factory()->count(3)->published()->create();
        Pengaduan::factory()->count(2)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/dashboard/admin');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_admin_dashboard_requires_auth()
    {
        $response = $this->getJson('/api/v1/dashboard/admin');
        $response->assertStatus(401);
    }
}
