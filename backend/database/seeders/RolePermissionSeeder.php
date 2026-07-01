<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage-warga',
            'manage-berita',
            'manage-pengaduan',
            'manage-keuangan',
            'manage-surat',
            'manage-admin',
            'manage-kelola-rt-rw',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $adminRtRw = Role::create(['name' => 'admin_rt_rw']);
        $adminRtRw->givePermissionTo([
            'manage-warga',
            'manage-berita',
            'manage-pengaduan',
            'manage-surat',
        ]);

        Role::create(['name' => 'warga']);
    }
}
