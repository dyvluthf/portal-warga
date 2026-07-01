<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\RtRw;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Admin::with('user', 'rtRw');

        if ($request->filled('role')) {
            $query->whereHas('user', fn($q) => $q->where('role', $request->role));
        }
        if ($request->filled('rt_rw_id')) {
            $query->where('rt_rw_id', $request->rt_rw_id);
        }

        return $this->success($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:super_admin,admin_rt_rw',
            'rt_rw_id' => 'required_if:role,admin_rt_rw|exists:rt_rw,id|nullable',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => $validated['role'],
            'status_aktif' => true,
        ]);

        $admin = Admin::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'rt_rw_id' => $validated['role'] === 'admin_rt_rw' ? $validated['rt_rw_id'] : null,
            'user_id' => $user->id,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'aksi' => 'create_admin',
            'model' => 'Admin',
            'model_id' => (string) $admin->id,
            'keterangan' => "Membuat admin {$validated['nama']} dengan role {$validated['role']}",
        ]);

        $admin->load('user', 'rtRw');

        return $this->success([
            'admin' => $admin,
            'password_default' => $password,
        ], 'Admin berhasil dibuat', 201);
    }

    public function update(Request $request, Admin $admin): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $admin->user_id,
            'role' => 'in:super_admin,admin_rt_rw',
            'rt_rw_id' => 'exists:rt_rw,id|nullable',
            'status_aktif' => 'boolean',
        ]);

        if (isset($validated['nama'])) {
            $admin->update(['nama' => $validated['nama']]);
            $admin->user->update(['name' => $validated['nama']]);
        }
        if (isset($validated['email'])) {
            $admin->update(['email' => $validated['email']]);
            $admin->user->update(['email' => $validated['email']]);
        }
        if (isset($validated['role'])) {
            $admin->update(['role' => $validated['role']]);
            $admin->user->update(['role' => $validated['role']]);
        }
        if (array_key_exists('rt_rw_id', $validated)) {
            $admin->update(['rt_rw_id' => $validated['rt_rw_id']]);
        }
        if (isset($validated['status_aktif'])) {
            $admin->user->update(['status_aktif' => $validated['status_aktif']]);
            if (!$validated['status_aktif']) {
                $admin->user->tokens()->delete();
            }
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'aksi' => 'update_admin',
            'model' => 'Admin',
            'model_id' => (string) $admin->id,
            'keterangan' => "Mengubah data admin {$admin->nama}",
        ]);

        return $this->success($admin->fresh()->load('user', 'rtRw'), 'Admin diupdate');
    }

    public function destroy(Request $request, Admin $admin): JsonResponse
    {
        $admin->user->update(['status_aktif' => false]);
        $admin->user->tokens()->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'aksi' => 'deactivate_admin',
            'model' => 'Admin',
            'model_id' => (string) $admin->id,
            'keterangan' => "Menonaktifkan admin {$admin->nama}",
        ]);

        return $this->success(null, 'Admin dinonaktifkan');
    }
}
