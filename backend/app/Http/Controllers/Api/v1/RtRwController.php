<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\RtRw;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RtRwController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', RtRw::class);
        $data = RtRw::with(['ketuaRt', 'sekretaris', 'bendahara'])->get();
        return $this->success($data);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', RtRw::class);
        $validated = $request->validate([
            'nomor_rt' => 'required|string|max:3',
            'nomor_rw' => 'required|string|max:3',
            'nama_kelurahan' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'ketua_rt_id' => 'nullable|exists:warga,id',
            'sekretaris_id' => 'nullable|exists:warga,id',
            'bendahara_id' => 'nullable|exists:warga,id',
        ]);
        $rt = RtRw::create($validated);
        return $this->success($rt->load(['ketuaRt', 'sekretaris', 'bendahara']), 'RT/RW berhasil ditambahkan', 201);
    }

    public function show(RtRw $rtRw): JsonResponse
    {
        Gate::authorize('view', $rtRw);
        $rtRw->load(['ketuaRt', 'sekretaris', 'bendahara']);
        return $this->success($rtRw);
    }

    public function update(Request $request, RtRw $rtRw): JsonResponse
    {
        Gate::authorize('update', $rtRw);
        $validated = $request->validate([
            'nomor_rt' => 'sometimes|string|max:3',
            'nomor_rw' => 'sometimes|string|max:3',
            'nama_kelurahan' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'ketua_rt_id' => 'nullable|exists:warga,id',
            'sekretaris_id' => 'nullable|exists:warga,id',
            'bendahara_id' => 'nullable|exists:warga,id',
        ]);
        $rtRw->update($validated);
        return $this->success($rtRw->fresh(['ketuaRt', 'sekretaris', 'bendahara']), 'RT/RW berhasil diperbarui');
    }

    public function destroy(RtRw $rtRw): JsonResponse
    {
        Gate::authorize('delete', $rtRw);
        $rtRw->delete();
        return $this->success(null, 'RT/RW berhasil dihapus');
    }

    public function warga(RtRw $rtRw): JsonResponse
    {
        Gate::authorize('view', $rtRw);
        $warga = $rtRw->warga()->with('user')->paginate(20);
        return $this->success($warga);
    }
}
