<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'access admin',
        'manage content', 'manage articles', 'manage media', 'manage events', 'manage profile', 'approve profile content',
        'manage books', 'manage inventory', 'manage orders', 'manage refunds',
        'manage consultations', 'manage bookings',
        'manage enquiries', 'manage subscribers',
        'view reports', 'manage settings', 'manage payment settings', 'manage users', 'view audit logs',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Owner receives everything through Gate::before; permissions are also attached for clarity.
        Role::findOrCreate('owner', 'web')->syncPermissions(self::PERMISSIONS);

        Role::findOrCreate('content-manager', 'web')->syncPermissions([
            'access admin', 'manage content', 'manage articles', 'manage media', 'manage events', 'manage profile',
        ]);

        Role::findOrCreate('operations-manager', 'web')->syncPermissions([
            'access admin', 'manage books', 'manage inventory', 'manage orders',
            'manage consultations', 'manage bookings', 'manage enquiries', 'view reports',
        ]);
    }
}
