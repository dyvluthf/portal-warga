<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Berita;
use App\Models\RtRw;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@portal.test',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status_aktif' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        $adminUser = User::create([
            'name' => 'Admin RT 01',
            'email' => 'admin@portal.test',
            'password' => Hash::make('password'),
            'role' => 'admin_rt_rw',
            'status_aktif' => true,
        ]);
        $adminUser->assignRole('admin_rt_rw');

        Admin::create([
            'nama' => 'Admin RT 01',
            'email' => 'admin@portal.test',
            'role' => 'admin_rt_rw',
            'user_id' => $adminUser->id,
        ]);

        $rtRw = RtRw::create([
            'nomor_rt' => '01',
            'nomor_rw' => '02',
            'nama_kelurahan' => 'Kelurahan Contoh',
            'kecamatan' => 'Kecamatan Contoh',
        ]);

        $adminUser->admin->update(['rt_rw_id' => $rtRw->id]);

        $wargaData = [
            ['nik' => '3201010101010001', 'nama' => 'Ahmad Fauzi', 'alamat' => 'Jl. Merdeka No.1', 'no_telp' => '081234567890'],
            ['nik' => '3201010101010002', 'nama' => 'Siti Nurhaliza', 'alamat' => 'Jl. Merdeka No.2', 'no_telp' => '081234567891'],
            ['nik' => '3201010101010003', 'nama' => 'Budi Santoso', 'alamat' => 'Jl. Merdeka No.3', 'no_telp' => '081234567892'],
            ['nik' => '3201010101010004', 'nama' => 'Dewi Sartika', 'alamat' => 'Jl. Merdeka No.4', 'no_telp' => '081234567893'],
            ['nik' => '3201010101010005', 'nama' => 'Eko Prasetyo', 'alamat' => 'Jl. Merdeka No.5', 'no_telp' => '081234567894'],
        ];

        foreach ($wargaData as $data) {
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['nik'] . '@warga.local',
                'password' => Hash::make('password'),
                'role' => 'warga',
            ]);
            $user->assignRole('warga');

            $warga = Warga::create([
                'nik' => $data['nik'],
                'nama' => $data['nama'],
                'alamat' => $data['alamat'],
                'no_telp' => $data['no_telp'],
                'rt_rw_id' => $rtRw->id,
                'user_id' => $user->id,
                'status_verifikasi' => 'terverifikasi',
            ]);

            if ($warga->id === 1) {
                $rtRw->update(['ketua_rt_id' => $warga->id]);
            } elseif ($warga->id === 2) {
                $rtRw->update(['sekretaris_id' => $warga->id]);
            } elseif ($warga->id === 3) {
                $rtRw->update(['bendahara_id' => $warga->id]);
            }
        }

        Berita::create([
            'judul' => 'Informasi Pembayaran Iuran Warga Bulan Ini',
            'slug' => 'informasi-pembayaran-iuran',
            'konten' => 'Pembayaran iuran warga untuk bulan ini dapat dilakukan melalui Bendahara RT. Batas pembayaran tanggal 10 setiap bulan.',
            'kategori' => 'Informasi',
            'status' => 'publish',
            'penulis_id' => $adminUser->id,
            'tanggal_publish' => now(),
        ]);

        Berita::create([
            'judul' => 'Kegiatan Kerja Bakti Minggu Ini',
            'slug' => 'kegiatan-kerja-bakti',
            'konten' => 'Akan diadakan kerja bakti pada hari Minggu, 10 Juli 2026 pukul 07.00 WIB. Dimohon partisipasi seluruh warga.',
            'kategori' => 'Kegiatan',
            'status' => 'publish',
            'penulis_id' => $adminUser->id,
            'tanggal_publish' => now(),
        ]);

        Berita::create([
            'judul' => 'Pengumuman Rapat RT (Draft)',
            'slug' => 'pengumuman-rapat-rt',
            'konten' => 'Rapat RT akan diadakan... konten masih dalam penyusunan.',
            'kategori' => 'Pengumuman',
            'status' => 'draft',
            'penulis_id' => $adminUser->id,
        ]);
    }
}
