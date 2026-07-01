<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required_without:nik|email',
            'nik' => 'required_without:email|string|size:16',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email atau NIK harus diisi',
            'nik.required_without' => 'Email atau NIK harus diisi',
            'nik.size' => 'NIK harus 16 karakter',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ];
    }
}
