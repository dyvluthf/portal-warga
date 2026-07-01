<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = null;

        if ($request->filled('email')) {
            $user = User::where('email', $request->email)->first();
        } elseif ($request->filled('nik')) {
            $warga = Warga::where('nik', $request->nik)->first();
            $user = $warga?->user;
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        if (!$user->status_aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan.',
                'data' => null,
            ], 403);
        }

        $user->tokens()->where('name', 'auth-token')->delete();

        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $user->load(['warga', 'admin']),
                'token' => $token,
                'role' => $user->role,
            ],
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->nama,
            'email' => $request->nik . '@warga.local',
            'password' => $request->password,
            'role' => 'warga',
        ]);

        $warga = Warga::create([
            'nik' => $request->nik,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'rt_id' => $request->rt_id,
            'rw_id' => $request->rw_id,
            'no_telp' => $request->no_telp,
            'status_verifikasi' => 'menunggu',
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('auth-token', ['warga'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Akun Anda menunggu verifikasi admin.',
            'data' => [
                'user' => $user->load('warga'),
                'token' => $token,
                'role' => 'warga',
            ],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data' => null,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['warga', 'admin']);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil.',
            'data' => [
                'user' => $user,
                'role' => $user->role,
            ],
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->where('name', 'auth-token')->delete();
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui.',
            'data' => [
                'token' => $token,
            ],
        ]);
    }
}
