<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\User;
use Tests\TestCase;

class AgendaTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_admin_can_list_agenda()
    {
        Agenda::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/agenda');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_agenda()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/agenda', [
                'nama' => 'Rapat RT',
                'tanggal' => now()->addDays(3)->format('Y-m-d'),
                'jam' => '19:00',
                'lokasi' => 'Balai Warga',
                'deskripsi' => 'Rapat bulanan.',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('agenda', ['nama' => 'Rapat RT']);
    }

    public function test_create_agenda_fails_without_nama()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/agenda', [
                'tanggal' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_update_agenda()
    {
        $agenda = Agenda::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/agenda/{$agenda->id}", [
                'nama' => 'Nama Diubah',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('agenda', ['id' => $agenda->id, 'nama' => 'Nama Diubah']);
    }

    public function test_admin_can_delete_agenda()
    {
        $agenda = Agenda::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/agenda/{$agenda->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('agenda', ['id' => $agenda->id]);
    }
}
