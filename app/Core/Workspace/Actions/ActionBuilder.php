<?php

declare(strict_types=1);

namespace App\Core\Workspace\Actions;

use App\Models\User;

class ActionBuilder
{
    /**
     * Generate role-tailored quick action buttons.
     */
    public function build(?User $user = null, string $portal = 'stock'): array
    {
        $user = $user ?? (auth()->user() ?? (User::where('email', 'admin@stockmanager.com')->first() ?? User::first()));
        $userRoles = $user ? $user->roles->pluck('name')->toArray() : [];
        $actions = [];

        if ($portal === 'stock') {
            $actions[] = [
                'label' => '📥 Receive Stock',
                'route' => 'stock.receive.index',
                'class' => 'btn-primary',
            ];

            $actions[] = [
                'label' => '🏷️ Barcode Print',
                'route' => 'stock.barcodes.index',
                'class' => 'btn-outline-primary',
            ];

            if (in_array('Inventory Manager', $userRoles) || in_array('Admin', $userRoles) || in_array('Super Admin', $userRoles)) {
                $actions[] = [
                    'label' => '⚖️ Stock Adjustment',
                    'route' => 'stock.adjustments.index',
                    'class' => 'btn-warning-emphasis',
                ];
            }
        } elseif ($portal === 'organize') {
            $actions[] = [
                'label' => '📥 Process Put-Away',
                'route' => 'organize.putaway.index',
                'class' => 'btn-primary',
            ];

            $actions[] = [
                'label' => '📦 Pick & Pack Station',
                'route' => 'organize.fulfillment.index',
                'class' => 'btn-success',
            ];

            $actions[] = [
                'label' => '🔄 Internal Transfer',
                'route' => 'organize.transfers.index',
                'class' => 'btn-outline-dark',
            ];
        }

        return $actions;
    }
}
