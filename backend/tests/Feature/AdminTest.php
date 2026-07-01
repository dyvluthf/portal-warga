<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\RtRw;
use App\Models\User;
use Tests\TestCase;

class AdminTest extends TestCase
{
    private User $superAdmin;
    private User $adminRtRw;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->superAdmin()->create();
        $this->adminRtRw = User::factory()->adminRtRw()->create();
    }

    public function test_super_admin_can_list_admins()
    {
        Admin::factory()->count(3)->create();

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/v1/admin');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_admin()
    {
        $rt = RtRw::factory()->create();

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/admin', [
                'nama' => 'Admin Baru',
                'email' => 'adminbaru@portal.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'admin_rt_rw',
                'rt_rw_id' => $rt->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('admins', ['email' => 'adminbaru@portal.test']);
        $this->assertDatabaseHas('users', ['email' => 'adminbaru@portal.test']);
    }

    public function test_non_super_admin_cannot_manage_admins()
    {
        $response = $this->actingAs($this->adminRtRw, 'sanctum')
            ->getJson('/api/v1/admin');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_admin_detail()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson("/api/v1/admin/{$admin->id}");

        $response->assertStatus(200);
    }

    public function test_super_admin_can_update_admin()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson("/api/v1/admin/{$admin->id}", [
                'nama' => 'Nama Diubah',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'nama' => 'Nama Diubah']);
    }

    public function test_super_admin_can_delete_admin()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->deleteJson("/api/v1/admin/{$admin->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }
}
