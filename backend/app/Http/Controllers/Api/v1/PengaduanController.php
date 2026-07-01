<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\SendFcmNotificationJob;
use App\Models\Pengaduan;
use App\Models\PengaduanTimeline;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PengaduanController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Pengaduan::with('warga.user', 'timeline');
        $user = $request->user();

        if ($user->role === 'warga') {
            $warga = $user->warga;
            $query->where('warga_id', $warga?->id);
        } elseif ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            $query->whereHas('warga', fn($q) => $q->where('rt_rw_id', $admin->rt_rw_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $this->success($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $warga = $user->warga;
        if (!$warga) return $this->error('Hanya untuk akun warga', 403);

        $validated = $request->validate([
            'kategori' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|array|max:3',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'lokasi_lat' => 'nullable|string|max:50',
            'lokasi_lng' => 'nullable|string|max:50',
            'lokasi_manual' => 'nullable|string',
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $fotoPaths[] = $file->store('pengaduan', 'public');
            }
        }

        $pengaduan = Pengaduan::create([
            'warga_id' => $warga->id,
            'kategori' => $validated['kategori'],
            'deskripsi' => $validated['deskripsi'],
            'foto' => $fotoPaths,
            'lokasi_lat' => $validated['lokasi_lat'] ?? null,
            'lokasi_lng' => $validated['lokasi_lng'] ?? null,
            'lokasi_manual' => $validated['lokasi_manual'] ?? null,
        ]);

        PengaduanTimeline::create([
            'pengaduan_id' => $pengaduan->id,
            'status' => 'baru',
            'keterangan' => 'Pengaduan diajukan',
            'created_at' => now(),
        ]);

        if ($warga->rtRw?->ketuaRt?->user) {
            $adminUser = $warga->rtRw->ketuaRt->user;
            SendFcmNotificationJob::dispatch(
                $adminUser, 'Pengaduan Baru',
                "Pengaduan {$pengaduan->kategori} dari {$warga->nama}",
                'pengaduan', (string) $pengaduan->id
            );
        }

        return $this->success($pengaduan->load('warga.user', 'timeline'), 'Pengaduan berhasil dikirim', 201);
    }

    public function show(Pengaduan $pengaduan): JsonResponse
    {
        Gate::authorize('view', $pengaduan);
        $pengaduan->load('warga.user', 'timeline');
        return $this->success($pengaduan);
    }

    public function proses(Pengaduan $pengaduan): JsonResponse
    {
        Gate::authorize('proses', $pengaduan);
        $pengaduan->update(['status' => 'diproses']);
        $this->addTimeline($pengaduan, 'diproses', 'Pengaduan sedang diproses');
        $this->notifyWarga($pengaduan, 'Pengaduan Diproses', "Pengaduan {$pengaduan->kategori} sedang diproses");
        return $this->success($pengaduan->fresh()->load('warga.user', 'timeline'));
    }

    public function selesai(Request $request, Pengaduan $pengaduan): JsonResponse
    {
        Gate::authorize('selesai', $pengaduan);
        $validated = $request->validate(['tanggapan_admin' => 'required|string']);
        $pengaduan->update(['status' => 'selesai', 'tanggapan_admin' => $validated['tanggapan_admin']]);
        $this->addTimeline($pengaduan, 'selesai', $validated['tanggapan_admin']);
        $this->notifyWarga($pengaduan, 'Pengaduan Selesai', "Pengaduan {$pengaduan->kategori} telah selesai. Silakan beri rating.");
        return $this->success($pengaduan->fresh()->load('warga.user', 'timeline'));
    }

    public function tolak(Request $request, Pengaduan $pengaduan): JsonResponse
    {
        Gate::authorize('tolak', $pengaduan);
        $validated = $request->validate(['alasan_penolakan' => 'required|string']);
        $pengaduan->update(['status' => 'ditolak', 'alasan_penolakan' => $validated['alasan_penolakan']]);
        $this->addTimeline($pengaduan, 'ditolak', $validated['alasan_penolakan']);
        $this->notifyWarga($pengaduan, 'Pengaduan Ditolak', "Pengaduan {$pengaduan->kategori} ditolak: {$validated['alasan_penolakan']}");
        return $this->success($pengaduan->fresh()->load('warga.user', 'timeline'));
    }

    public function rating(Request $request, Pengaduan $pengaduan): JsonResponse
    {
        $user = $request->user();
        if ($pengaduan->status !== 'selesai') return $this->error('Pengaduan belum selesai', 422);
        if (!$user->warga || $user->warga->id !== $pengaduan->warga_id) return $this->error('Unauthorized', 403);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string',
        ]);
        $pengaduan->update($validated);
        return $this->success($pengaduan->fresh(), 'Rating berhasil diberikan');
    }

    protected function addTimeline(Pengaduan $pengaduan, string $status, ?string $keterangan): void
    {
        PengaduanTimeline::create([
            'pengaduan_id' => $pengaduan->id,
            'status' => $status,
            'keterangan' => $keterangan,
            'created_at' => now(),
        ]);
    }

    protected function notifyWarga(Pengaduan $pengaduan, string $judul, string $pesan): void
    {
        if ($pengaduan->warga?->user) {
            SendFcmNotificationJob::dispatch(
                $pengaduan->warga->user, $judul, $pesan,
                'pengaduan', (string) $pengaduan->id
            );
        }
    }
}
