<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => 'required|string|size:16|unique:warga,nik',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'rt_id' => 'nullable|exists:rt_rw,id',
            'rw_id' => 'nullable|exists:rt_rw,id',
            'no_telp' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus 16 karakter',
            'nik.unique' => 'NIK sudah terdaftar',
            'nama.required' => 'Nama wajib diisi',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ];
    }
}
