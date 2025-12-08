<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view services', 'create services', 'edit services', 'delete services',
            'view products', 'create products', 'edit products', 'delete products',
            'view files', 'create files', 'edit files', 'delete files',
            'view courses', 'create courses', 'edit courses', 'delete courses',
            'view tickets', 'create tickets', 'edit tickets', 'delete tickets',
            'view users', 'create users', 'edit users', 'delete users',
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',
            'view payments', 'create payments', 'edit payments', 'delete payments',
            'view dashboard', 'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            'view services', 'view products', 'view files', 'view courses',
            'view tickets', 'edit tickets', 'view users',
            'view invoices', 'view payments', 'view dashboard',
        ]);

        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'view services', 'view products', 'view files', 'view courses',
            'view tickets', 'create tickets',
        ]);
    }
}
