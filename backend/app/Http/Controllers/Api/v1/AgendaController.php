<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\SendFcmNotificationJob;
use App\Models\Agenda;
use App\Models\AgendaKehadiran;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $rtRwId = $user->role === 'super_admin'
            ? ($request->input('rt_rw_id', optional($user->admin)->rt_rw_id ?? 0))
            : ($user->admin->rt_rw_id ?? $user->warga?->rt_rw_id);

        $query = Agenda::with('dibuatOleh')
            ->where('rt_rw_id', $rtRwId)
            ->filterPeriode($request->periode)
            ->orderBy('tanggal');

        return $this->success($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $user = $request->user();
        $rtRwId = $user->admin?->rt_rw_id;

        $agenda = Agenda::create([
            'nama' => $validated['nama'],
            'tanggal' => $validated['tanggal'],
            'jam' => $validated['jam'],
            'lokasi' => $validated['lokasi'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'rt_rw_id' => $rtRwId,
            'dibuat_oleh' => $user->id,
        ]);

        $wargaList = \App\Models\Warga::where('rt_rw_id', $rtRwId)
            ->whereHas('user')
            ->with('user')
            ->get();

        foreach ($wargaList as $warga) {
            if ($warga->user) {
                SendFcmNotificationJob::dispatch(
                    $warga->user,
                    'Agenda Baru',
                    "Kegiatan: {$agenda->nama} pada {$agenda->tanggal}",
                    'agenda',
                    (string) $agenda->id
                );
            }
        }

        return $this->success($agenda, 'Agenda dibuat', 201);
    }

    public function show(Agenda $agenda): JsonResponse
    {
        $agenda->load('dibuatOleh', 'kehadiran.warga.user');
        return $this->success($agenda);
    }

    public function update(Request $request, Agenda $agenda): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'string|max:255',
            'tanggal' => 'date',
            'jam' => 'string',
            'lokasi' => 'string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $agenda->update($validated);
        return $this->success($agenda->fresh(), 'Agenda diupdate');
    }

    public function destroy(Request $request, Agenda $agenda): JsonResponse
    {
        $agenda->delete();
        return $this->success(null, 'Agenda dihapus');
    }

    public function rsvp(Request $request, Agenda $agenda): JsonResponse
    {
        $user = $request->user();
        $warga = $user->warga;
        if (!$warga) return $this->error('Hanya untuk akun warga', 403);

        $validated = $request->validate([
            'status_rsvp' => 'required|in:hadir,tidak_hadir',
        ]);

        AgendaKehadiran::updateOrCreate(
            ['agenda_id' => $agenda->id, 'warga_id' => $warga->id],
            ['status_rsvp' => $validated['status_rsvp']]
        );

        return $this->success(null, 'RSVP berhasil');
    }

    public function peserta(Agenda $agenda): JsonResponse
    {
        $agenda->load('kehadiran.warga');
        $hadir = $agenda->kehadiran->where('status_rsvp', 'hadir')->values();
        $tidakHadir = $agenda->kehadiran->where('status_rsvp', 'tidak_hadir')->values();

        return $this->success([
            'hadir' => $hadir,
            'tidak_hadir' => $tidakHadir,
        ]);
    }
}
