<?php

declare(strict_types=1);

namespace App\Core\Workspace\Widgets;

use App\Models\User;

class WidgetRegistry
{
    protected static array $registeredWidgets = [];

    /**
     * Register a dynamic UI widget with permission and role metadata.
     */
    public static function register(string $id, array $config): void
    {
        self::$registeredWidgets[$id] = array_merge([
            'id' => $id,
            'title' => 'Widget',
            'required_roles' => [],
            'required_permissions' => [],
            'portal' => 'all',
            'position' => 'main',
            'priority' => 10,
        ], $config);
    }

    /**
     * Get all registered widgets evaluated against the user's roles and permissions.
     */
    public static function getEligibleWidgets(User $user, string $portal = 'all'): array
    {
        self::ensureDefaults();

        $eligible = [];
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach (self::$registeredWidgets as $id => $widget) {
            // Portal filter check
            if ($widget['portal'] !== 'all' && $widget['portal'] !== $portal) {
                continue;
            }

            // Role evaluation check
            if (!empty($widget['required_roles'])) {
                $hasRole = false;
                foreach ($widget['required_roles'] as $role) {
                    if (in_array($role, $userRoles) || in_array('Admin', $userRoles) || in_array('Super Admin', $userRoles)) {
                        $hasRole = true;
                        break;
                    }
                }
                if (!$hasRole) {
                    continue;
                }
            }

            $eligible[] = $widget;
        }

        // Sort widgets by priority ASC
        usort($eligible, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $eligible;
    }

    /**
     * Ensure default component registry items are populated.
     */
    protected static function ensureDefaults(): void
    {
        if (!empty(self::$registeredWidgets)) {
            return;
        }

        self::register('kpi_inventory_value', [
            'title' => 'Total Inventory Valuation',
            'type' => 'kpi_card',
            'required_roles' => ['Inventory Manager', 'CEO', 'Executive', 'Admin', 'Super Admin'],
            'portal' => 'stock',
            'priority' => 1,
        ]);

        self::register('kpi_warehouse_occupancy', [
            'title' => 'Warehouse Capacity & Occupancy',
            'type' => 'kpi_card',
            'required_roles' => ['Warehouse Supervisor', 'Warehouse Operator', 'CEO', 'Executive', 'Admin', 'Super Admin'],
            'portal' => 'organize',
            'priority' => 2,
        ]);

        self::register('pending_putaway_queue', [
            'title' => 'Pending Put-Away Queue',
            'type' => 'table_queue',
            'required_roles' => ['Warehouse Supervisor', 'Warehouse Operator', 'Admin', 'Super Admin'],
            'portal' => 'organize',
            'priority' => 3,
        ]);

        self::register('pending_picking_checklist', [
            'title' => 'Order Picking Verification Checklist',
            'type' => 'checklist',
            'required_roles' => ['Warehouse Supervisor', 'Warehouse Operator', 'Admin', 'Super Admin'],
            'portal' => 'organize',
            'priority' => 4,
        ]);

        self::register('executive_revenue_forecast', [
            'title' => 'Executive Revenue & AI Inventory Forecast',
            'type' => 'chart',
            'required_roles' => ['CEO', 'Executive', 'Admin', 'Super Admin'],
            'portal' => 'all',
            'priority' => 5,
        ]);
    }
}
