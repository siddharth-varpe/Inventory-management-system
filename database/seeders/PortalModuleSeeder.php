<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PortalModule;
use App\Models\PortalPermission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPortalAccess;
use Illuminate\Database\Seeder;

class PortalModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $portals = [
            [
                'name' => 'Manage Stock',
                'slug' => 'manage-stock',
                'icon' => 'box-seam',
                'color_theme' => 'emerald',
                'description' => 'Register SKUs, receive supplier stock, perform stock adjustments and manage master inventory.',
                'permissions' => ['view-inventory', 'receive-stock', 'adjust-stock', 'manage-catalog', 'inventory-settings'],
            ],
            [
                'name' => 'Organize Stock',
                'slug' => 'organize-stock',
                'icon' => 'buildings',
                'color_theme' => 'sky',
                'description' => 'Manage warehouses, racks, storage locations, stock transfers and internal movements.',
                'permissions' => ['view-warehouses', 'manage-racks', 'transfer-stock', 'bin-placement'],
            ],
            [
                'name' => 'Send Goods',
                'slug' => 'send-goods',
                'icon' => 'send-check',
                'color_theme' => 'indigo',
                'description' => 'Reserve inventory, prepare dispatches, validate stock availability and coordinate shipments.',
                'permissions' => ['view-dispatches', 'create-dispatch', 'reserve-stock', 'approve-fulfillment'],
            ],
            [
                'name' => 'Bill Customers',
                'slug' => 'bill-customers',
                'icon' => 'receipt',
                'color_theme' => 'amber',
                'description' => 'Generate GST invoices, receive customer payments and manage billing operations.',
                'permissions' => ['create-invoice', 'collect-payment', 'view-billing-reports', 'issue-credit-note'],
            ],
            [
                'name' => 'Order Supplies',
                'slug' => 'order-supplies',
                'icon' => 'cart-plus',
                'color_theme' => 'teal',
                'description' => 'Monitor stock levels, generate purchase requests and manage supplier procurement.',
                'permissions' => ['create-po', 'approve-po', 'vendor-management', 'receive-procurement'],
            ],
            [
                'name' => 'Transport Dept',
                'slug' => 'transport-dept',
                'icon' => 'truck',
                'color_theme' => 'rose',
                'description' => 'Assign vehicles, manage delivery routes and coordinate transport operations.',
                'permissions' => ['manage-fleet', 'assign-routes', 'dispatch-vehicles', 'transport-logs'],
            ],
            [
                'name' => 'Driver Terminal',
                'slug' => 'driver-terminal',
                'icon' => 'phone',
                'color_theme' => 'cyan',
                'description' => 'Delivery checkpoints, OTP verification, proof of delivery and trip completion.',
                'permissions' => ['view-trips', 'verify-otp', 'upload-pod', 'complete-delivery'],
            ],
            [
                'name' => 'Admin Center',
                'slug' => 'admin-center',
                'icon' => 'shield-lock',
                'color_theme' => 'purple',
                'description' => 'Analytics, Finance, Audit Logs, User Management, Approvals and System Administration.',
                'permissions' => ['system-config', 'user-management', 'portal-access-control', 'audit-inspection'],
            ],
            [
                'name' => 'Sales & CRM',
                'slug' => 'sales-crm',
                'icon' => 'graph-up-arrow',
                'color_theme' => 'orange',
                'description' => 'Manage customers, quotations, sales orders and customer relationships.',
                'permissions' => ['view-leads', 'create-quote', 'manage-customers', 'sales-reports'],
            ],
        ];

        $adminUser = User::where('email', 'admin@stockmanager.com')->first();
        $adminRole = Role::where('slug', 'super-admin')->orWhere('slug', 'super_admin')->first();

        foreach ($portals as $portalData) {
            $permissions = $portalData['permissions'];
            unset($portalData['permissions']);

            /** @var PortalModule $module */
            $module = PortalModule::updateOrCreate(
                ['slug' => $portalData['slug']],
                $portalData
            );

            foreach ($permissions as $perm) {
                PortalPermission::firstOrCreate([
                    'portal_module_id' => $module->id,
                    'permission_name' => $perm,
                ]);
            }

            if ($adminUser) {
                UserPortalAccess::firstOrCreate([
                    'user_id' => $adminUser->id,
                    'portal_module_id' => $module->id,
                ], [
                    'role_id' => $adminRole?->id,
                    'status' => 'active',
                    'created_by' => $adminUser->id,
                ]);
            }
        }
    }
}
