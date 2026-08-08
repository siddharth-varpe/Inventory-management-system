<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds for roles and permissions.
     */
    public function run(): void
    {
        // 1. Base Permissions List
        $permissions = [
            // User & System Permissions
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'settings.view', 'settings.edit',
            'audit.view', 'activity.view',

            // Master Data Permissions
            'category.view', 'category.create', 'category.edit', 'category.delete', 'category.restore',
            'brand.view', 'brand.create', 'brand.edit', 'brand.delete', 'brand.restore',
            'unit.view', 'unit.create', 'unit.edit', 'unit.delete', 'unit.restore',
            'tax.view', 'tax.create', 'tax.edit', 'tax.delete', 'tax.restore',
            'attribute.view', 'attribute.create', 'attribute.edit', 'attribute.delete', 'attribute.restore',

            // Inventory Portal Permissions
            'product.view', 'product.create', 'product.update', 'product.delete',
            'stock.receive', 'stock.adjust',
            'barcode.print', 'inventory.report', 'inventory.import', 'inventory.export',

            // Sales & CRM Portal Permissions
            'sales.view', 'sales.create', 'sales.edit', 'sales.delete',
            'sales.customer.view', 'sales.customer.create', 'sales.customer.edit', 'sales.customer.delete',
            'crm.lead.view', 'crm.lead.create', 'crm.lead.edit', 'crm.lead.delete', 'crm.dashboard.view',
        ];

        foreach ($permissions as $permissionName) {
            $slug = Str::slug($permissionName, '.');
            $module = explode('.', $permissionName)[0] ?? 'system';

            Permission::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => ucwords(str_replace('.', ' ', $permissionName)),
                    'slug' => $slug,
                    'module' => $module,
                    'description' => 'Permission to ' . str_replace('.', ' ', $permissionName),
                ]
            );
        }

        // 2. Base Roles List
        $roles = [
            'super_admin' => 'Super Administrator with full system privilege.',
            'admin' => 'Administrator with management privilege.',
            'manager' => 'Department Manager with operational privilege.',
            'sales_manager' => 'Sales Manager overseeing customer relationships and sales teams.',
            'sales_executive' => 'Sales Executive managing customer accounts.',
            'regional_manager' => 'Regional Manager managing regional sales territories.',
            'staff' => 'Standard Staff User.',
        ];

        foreach ($roles as $roleName => $description) {
            $slug = Str::slug($roleName);

            $role = Role::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => ucwords(str_replace('_', ' ', $roleName)),
                    'slug' => $slug,
                    'description' => $description,
                ]
            );

            // Assign permissions to super_admin and admin
            if (in_array($roleName, ['super_admin', 'admin'])) {
                $role->permissions()->sync(Permission::all());
            } elseif (in_array($roleName, ['sales_manager', 'sales_executive', 'regional_manager'])) {
                $salesPerms = Permission::where('module', 'sales')->orWhere('module', 'crm')->get();
                $role->permissions()->sync($salesPerms);
            }
        }
    }
}
