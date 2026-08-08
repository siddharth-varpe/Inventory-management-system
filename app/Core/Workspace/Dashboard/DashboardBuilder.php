<?php

declare(strict_types=1);

namespace App\Core\Workspace\Dashboard;

use App\Core\Workspace\Widgets\WidgetRegistry;
use App\Models\User;

class DashboardBuilder
{
    /**
     * Compose dynamic dashboard layout and widgets for a user and portal.
     */
    public function build(User $user, string $portal = 'stock'): array
    {
        $widgets = WidgetRegistry::getEligibleWidgets($user, $portal);

        return [
            'widgets' => $widgets,
            'layout_style' => $this->determineLayoutStyle($user),
        ];
    }

    protected function determineLayoutStyle(User $user): string
    {
        $userRoles = $user->roles->pluck('name')->toArray();

        if (in_array('CEO', $userRoles) || in_array('Executive', $userRoles)) {
            return 'executive';
        }

        if (in_array('Warehouse Operator', $userRoles) || in_array('Inventory Operator', $userRoles)) {
            return 'operator';
        }

        if (in_array('Warehouse Supervisor', $userRoles)) {
            return 'supervisor';
        }

        return 'manager';
    }
}
