<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\GaleriAlbum;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $rtRwId = $user->role === 'super_admin'
            ? ($request->input('rt_rw_id', optional($user->admin)->rt_rw_id ?? 0))
            : ($user->admin->rt_rw_id ?? $user->warga?->rt_rw_id);

        $albums = GaleriAlbum::withCount('foto')
            ->where('rt_rw_id', $rtRwId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 12);

        return $this->success($albums);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'foto' => 'required|array|min:1',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'caption' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $rtRwId = $user->admin?->rt_rw_id ?? $user->warga?->rt_rw_id;

        $album = GaleriAlbum::create([
            'nama_album' => $validated['nama_album'],
            'rt_rw_id' => $rtRwId,
            'dibuat_oleh' => $user->id,
        ]);

        foreach ($request->file('foto') as $i => $file) {
            $path = $file->store('galeri', 'public');
            $album->foto()->create([
                'path_foto' => $path,
                'caption' => $validated['caption'] ?? null,
                'urutan' => $i,
            ]);
        }

        $album->load('foto');
        return $this->success($album, 'Album berhasil dibuat', 201);
    }

    public function show(GaleriAlbum $galeriAlbum): JsonResponse
    {
        $galeriAlbum->load('foto', 'dibuatOleh');
        return $this->success($galeriAlbum);
    }

    public function destroy(GaleriAlbum $galeriAlbum): JsonResponse
    {
        foreach ($galeriAlbum->foto as $foto) {
            Storage::disk('public')->delete($foto->path_foto);
        }
        $galeriAlbum->delete();
        return $this->success(null, 'Album dihapus');
    }
}
