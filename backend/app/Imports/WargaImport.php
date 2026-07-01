<?php

namespace App\Imports;

use App\Models\Warga;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WargaImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;
    public array $failedRows = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $nik = trim($row['nik'] ?? '');

            if (empty($nik) || strlen($nik) !== 16) {
                $this->failedRows[] = ['row' => $rowNum, 'nik' => $nik, 'error' => 'NIK harus 16 karakter'];
                continue;
            }

            if (Warga::where('nik', $nik)->exists()) {
                $this->failedRows[] = ['row' => $rowNum, 'nik' => $nik, 'error' => 'NIK sudah terdaftar'];
                continue;
            }

            try {
                $user = User::create([
                    'name' => $row['nama'] ?? 'Tanpa Nama',
                    'email' => $nik . '@warga.local',
                    'password' => Hash::make('password'),
                    'role' => 'warga',
                ]);
                $user->assignRole('warga');

                Warga::create([
                    'nik' => $nik,
                    'nama' => $row['nama'] ?? 'Tanpa Nama',
                    'alamat' => $row['alamat'] ?? null,
                    'no_telp' => $row['no_telp'] ?? null,
                    'rt_rw_id' => !empty($row['rt_rw_id']) ? (int) $row['rt_rw_id'] : null,
                    'user_id' => $user->id,
                    'status_verifikasi' => 'terverifikasi',
                ]);

                $this->successCount++;
            } catch (\Exception $e) {
                $this->failedRows[] = ['row' => $rowNum, 'nik' => $nik, 'error' => $e->getMessage()];
            }
        }
    }
}
