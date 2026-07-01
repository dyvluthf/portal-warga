<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\WargaImport;
use App\Exports\WargaExport;
use App\Models\Warga;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class WargaController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Warga::class);
        $query = Warga::with('user', 'rtRw');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        if ($request->filled('rt_rw_id')) {
            $query->where('rt_rw_id', $request->rt_rw_id);
        }

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }

        $user = $request->user();
        if ($user->role === 'admin_rt_rw') {
            $admin = $user->admin;
            $query->where('rt_rw_id', $admin->rt_rw_id);
        }

        return $this->success($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Warga::class);
        $validated = $request->validate([
            'nik' => 'required|string|size:16|unique:warga,nik',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'rt_rw_id' => 'nullable|exists:rt_rw,id',
            'no_telp' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $validated['nik'] . '@warga.local',
            'password' => Hash::make('password'),
            'role' => 'warga',
        ]);
        $user->assignRole('warga');

        if ($request->hasFile('foto_profil')) {
            $validated['foto_profil'] = $request->file('foto_profil')->store('foto_profil', 'public');
        }

        $validated['user_id'] = $user->id;
        $validated['status_verifikasi'] = 'terverifikasi';
        $warga = Warga::create($validated);

        return $this->success($warga->load('user', 'rtRw'), 'Warga berhasil ditambahkan', 201);
    }

    public function show(Warga $warga): JsonResponse
    {
        Gate::authorize('view', $warga);
        $warga->load('user', 'rtRw');
        return $this->success([
            'warga' => $warga,
            'riwayat_surat' => [],
            'riwayat_iuran' => [],
        ]);
    }

    public function update(Request $request, Warga $warga): JsonResponse
    {
        Gate::authorize('update', $warga);
        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'alamat' => 'nullable|string',
            'rt_rw_id' => 'nullable|exists:rt_rw,id',
            'no_telp' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status_verifikasi' => 'sometimes|in:menunggu,terverifikasi,ditolak',
        ]);

        if ($request->hasFile('foto_profil')) {
            if ($warga->foto_profil) {
                Storage::disk('public')->delete($warga->foto_profil);
            }
            $validated['foto_profil'] = $request->file('foto_profil')->store('foto_profil', 'public');
        }

        $warga->update($validated);
        return $this->success($warga->fresh()->load('user', 'rtRw'), 'Warga berhasil diperbarui');
    }

    public function destroy(Warga $warga): JsonResponse
    {
        Gate::authorize('delete', $warga);
        if ($warga->foto_profil) {
            Storage::disk('public')->delete($warga->foto_profil);
        }
        $warga->user()->delete();
        $warga->delete();
        return $this->success(null, 'Warga berhasil dihapus');
    }

    public function import(Request $request): JsonResponse
    {
        Gate::authorize('create', Warga::class);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new WargaImport;
        Excel::import($import, $request->file('file'));

        return $this->success([
            'success_count' => $import->successCount,
            'failed_rows' => $import->failedRows,
        ], 'Import selesai diproses');
    }

    public function export(Request $request)
    {
        Gate::authorize('viewAny', Warga::class);
        return Excel::download(new WargaExport($request->rt_rw_id), 'data-warga.xlsx');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $warga = $user->warga;
        if (!$warga) {
            return $this->error('Hanya untuk akun warga', 404);
        }
        $warga->load('rtRw');
        return $this->success($warga);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $warga = $user->warga;
        if (!$warga) {
            return $this->error('Hanya untuk akun warga', 404);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto_profil')) {
            if ($warga->foto_profil) {
                Storage::disk('public')->delete($warga->foto_profil);
            }
            $validated['foto_profil'] = $request->file('foto_profil')->store('foto_profil', 'public');
        }

        $warga->update($validated);
        if (isset($validated['nama'])) {
            $user->update(['name' => $validated['nama']]);
        }

        return $this->success($warga->fresh()->load('rtRw'), 'Profil berhasil diperbarui');
    }
}
