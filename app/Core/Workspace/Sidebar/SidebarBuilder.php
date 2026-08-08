<?php

declare(strict_types=1);

namespace App\Core\Workspace\Sidebar;

use App\Models\User;

class SidebarBuilder
{
    /**
     * Dynamically build sidebar navigation items based on User Role, Profile, and Active Portal.
     */
    public function build(User $user, string $portal = 'stock'): array
    {
        $userRoles = $user->roles->pluck('name')->toArray();
        $items = [];

        if ($portal === 'stock') {
            $items[] = [
                'name' => 'Dashboard',
                'route' => 'stock.dashboard',
                'icon' => '📊',
                'active_pattern' => 'stock.dashboard',
            ];

            $items[] = [
                'name' => 'Product Catalog',
                'route' => 'stock.catalog',
                'icon' => '📦',
                'active_pattern' => 'stock.catalog*',
            ];

            $items[] = [
                'name' => 'Receive Supplier Stock',
                'route' => 'stock.receive.index',
                'icon' => '📥',
                'active_pattern' => 'stock.receive.*',
            ];

            $items[] = [
                'name' => 'Opening Stock',
                'route' => 'stock.opening-stock.index',
                'icon' => '🚀',
                'active_pattern' => 'stock.opening-stock.*',
            ];

            $items[] = [
                'name' => 'Stock Adjustments',
                'route' => 'stock.adjustments.index',
                'icon' => '⚖️',
                'active_pattern' => 'stock.adjustments.*',
            ];

            $items[] = [
                'name' => 'Batches & Serials',
                'route' => 'stock.batches.index',
                'icon' => '🔢',
                'active_pattern' => 'stock.batches.*',
            ];

            $items[] = [
                'name' => 'Barcode Center',
                'route' => 'stock.barcodes.index',
                'icon' => '🏷️',
                'active_pattern' => 'stock.barcodes.*',
            ];

            if (in_array('Inventory Manager', $userRoles) || in_array('CEO', $userRoles) || in_array('Admin', $userRoles) || in_array('Super Admin', $userRoles)) {
                $items[] = [
                    'name' => 'Inventory Reports',
                    'route' => 'stock.reports.index',
                    'icon' => '📈',
                    'active_pattern' => 'stock.reports.*',
                ];

                $items[] = [
                    'name' => 'Inventory Settings',
                    'route' => 'stock.settings.index',
                    'icon' => '⚙️',
                    'active_pattern' => 'stock.settings.*',
                ];
            }
        } elseif ($portal === 'organize') {
            $items[] = [
                'name' => 'Workspace',
                'route' => 'organize.dashboard',
                'icon' => '🏠',
                'active_pattern' => 'organize.dashboard',
            ];

            $items[] = [
                'name' => 'Pick & Pack Station',
                'route' => 'organize.fulfillment.index',
                'icon' => '📦',
                'active_pattern' => 'organize.fulfillment.*',
            ];

            $items[] = [
                'name' => 'Put-Away Tasks',
                'route' => 'organize.putaway.index',
                'icon' => '📥',
                'active_pattern' => 'organize.putaway.*',
            ];

            $items[] = [
                'name' => 'Warehouse Explorer',
                'route' => 'organize.locations.index',
                'icon' => '🗺️',
                'active_pattern' => 'organize.locations.*',
            ];

            $items[] = [
                'name' => 'Transfer Center',
                'route' => 'organize.transfers.index',
                'icon' => '🔄',
                'active_pattern' => 'organize.transfers.*',
            ];

            $items[] = [
                'name' => 'Exception Center',
                'route' => 'organize.exceptions.index',
                'icon' => '⚠️',
                'active_pattern' => 'organize.exceptions.*',
            ];

            $items[] = [
                'name' => 'Operational Reports',
                'route' => 'organize.reports.index',
                'icon' => '📊',
                'active_pattern' => 'organize.reports.*',
            ];
        } elseif ($portal === 'procurement') {
            $items[] = [
                'name' => 'Procurement Desk',
                'route' => 'procurement.dashboard',
                'icon' => '🏠',
                'active_pattern' => 'procurement.dashboard',
            ];

            $items[] = [
                'name' => 'Supplier Directory',
                'route' => 'procurement.suppliers.index',
                'icon' => '🏢',
                'active_pattern' => 'procurement.suppliers.*',
            ];

            $items[] = [
                'name' => 'Purchase Requisitions',
                'route' => 'procurement.requisitions.index',
                'icon' => '📝',
                'active_pattern' => 'procurement.requisitions.*',
            ];

            $items[] = [
                'name' => 'Purchase Orders',
                'route' => 'procurement.purchase-orders.index',
                'icon' => '📜',
                'active_pattern' => 'procurement.purchase-orders.*',
            ];

            $items[] = [
                'name' => 'Goods Receipt (GRN)',
                'route' => 'procurement.grn.index',
                'icon' => '📦',
                'active_pattern' => 'procurement.grn.*',
            ];

            $items[] = [
                'name' => 'Vendor Performance',
                'route' => 'procurement.vendor-performance.index',
                'icon' => '⭐',
                'active_pattern' => 'procurement.vendor-performance.*',
            ];

            $items[] = [
                'name' => 'Operational Reports',
                'route' => 'procurement.reports.index',
                'icon' => '📈',
                'active_pattern' => 'procurement.reports.*',
            ];
        } elseif ($portal === 'sales') {
            $items[] = [
                'name' => 'CRM Desk',
                'route' => 'sales.dashboard',
                'icon' => '🏠',
                'active_pattern' => 'sales.dashboard',
            ];

            $items[] = [
                'name' => 'Lead Pipeline',
                'route' => 'sales.leads.pipeline',
                'icon' => '🎯',
                'active_pattern' => 'sales.leads.pipeline',
            ];

            $items[] = [
                'name' => 'Lead Directory',
                'route' => 'sales.leads.index',
                'icon' => '📋',
                'active_pattern' => 'sales.leads.index',
            ];

            $items[] = [
                'name' => 'Sales Workspace',
                'route' => 'sales.workspace',
                'icon' => '🛒',
                'active_pattern' => 'sales.workspace',
            ];

            $items[] = [
                'name' => 'Quotations Queue',
                'route' => 'sales.quotations.index',
                'icon' => '📜',
                'active_pattern' => 'sales.quotations.*',
            ];

            $items[] = [
                'name' => 'Sales Orders',
                'route' => 'sales.orders.index',
                'icon' => '📦',
                'active_pattern' => 'sales.orders.*',
            ];

            $items[] = [
                'name' => 'Customer Master',
                'route' => 'sales.customers.index',
                'icon' => '👥',
                'active_pattern' => 'sales.customers.*',
            ];

            $items[] = [
                'name' => 'Commercial Reports',
                'route' => 'sales.reports.index',
                'icon' => '📈',
                'active_pattern' => 'sales.reports.*',
            ];

            $items[] = [
                'name' => 'Customer Groups',
                'route' => 'sales.groups.index',
                'icon' => '🏷️',
                'active_pattern' => 'sales.groups.*',
            ];

            $items[] = [
                'name' => 'Customer Categories',
                'route' => 'sales.categories.index',
                'icon' => '⭐',
                'active_pattern' => 'sales.categories.*',
            ];

            $items[] = [
                'name' => 'Territories',
                'route' => 'sales.territories.index',
                'icon' => '🗺️',
                'active_pattern' => 'sales.territories.*',
            ];
        }

        return $items;
    }
}
