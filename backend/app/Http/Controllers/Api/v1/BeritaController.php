<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Berita::with('penulis');

        if (!$request->user() || $request->user()->role === 'warga') {
            $query->where('status', 'publish');
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return $this->success(
            $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 12)
        );
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Berita::class);
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'kategori' => 'nullable|string|max:100',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'status' => 'sometimes|in:draft,publish',
        ]);

        $validated['slug'] = Str::slug($validated['judul']) . '-' . Str::random(6);
        $validated['penulis_id'] = $request->user()->id;

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        if (($validated['status'] ?? 'draft') === 'publish') {
            $validated['tanggal_publish'] = now();
        }

        $berita = Berita::create($validated);
        return $this->success($berita->load('penulis'), 'Berita berhasil ditambahkan', 201);
    }

    public function show(Berita $berita): JsonResponse
    {
        $user = request()->user();
        if ((!$user || $user->role === 'warga') && $berita->status !== 'publish') {
            return $this->error('Berita tidak ditemukan', 404);
        }
        $berita->load('penulis');
        return $this->success($berita);
    }

    public function update(Request $request, Berita $berita): JsonResponse
    {
        Gate::authorize('update', $berita);
        $validated = $request->validate([
            'judul' => 'sometimes|string|max:255',
            'konten' => 'sometimes|string',
            'kategori' => 'nullable|string|max:100',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'status' => 'sometimes|in:draft,publish',
        ]);

        if (isset($validated['judul'])) {
            $validated['slug'] = Str::slug($validated['judul']) . '-' . Str::random(6);
        }

        if ($request->hasFile('gambar')) {
            if ($berita->gambar) {
                Storage::disk('public')->delete($berita->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        if (($validated['status'] ?? null) === 'publish' && !$berita->tanggal_publish) {
            $validated['tanggal_publish'] = now();
        }

        $berita->update($validated);
        return $this->success($berita->fresh()->load('penulis'), 'Berita berhasil diperbarui');
    }

    public function publish(Berita $berita): JsonResponse
    {
        Gate::authorize('update', $berita);
        $berita->update([
            'status' => 'publish',
            'tanggal_publish' => now(),
        ]);
        return $this->success($berita->fresh()->load('penulis'), 'Berita berhasil dipublikasikan');
    }

    public function draft(Berita $berita): JsonResponse
    {
        Gate::authorize('update', $berita);
        $berita->update(['status' => 'draft']);
        return $this->success($berita->fresh()->load('penulis'), 'Berita diubah ke draft');
    }

    public function destroy(Berita $berita): JsonResponse
    {
        Gate::authorize('delete', $berita);
        if ($berita->gambar) {
            Storage::disk('public')->delete($berita->gambar);
        }
        $berita->delete();
        return $this->success(null, 'Berita berhasil dihapus');
    }
}
