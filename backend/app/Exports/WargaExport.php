<?php

namespace App\Exports;

use App\Models\Warga;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WargaExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(protected $rtRwId = null) {}

    public function query()
    {
        $query = Warga::with('rtRw');
        if ($this->rtRwId) {
            $query->where('rt_rw_id', $this->rtRwId);
        }
        return $query;
    }

    public function headings(): array
    {
        return ['NIK', 'Nama', 'Alamat', 'RT/RW', 'No. Telepon', 'Status Verifikasi'];
    }

    public function map($warga): array
    {
        return [
            $warga->nik,
            $warga->nama,
            $warga->alamat,
            $warga->rtRw ? "RT {$warga->rtRw->nomor_rt}/RW {$warga->rtRw->nomor_rw}" : '-',
            $warga->no_telp,
            $warga->status_verifikasi,
        ];
    }
}
