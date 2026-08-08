<?php

declare(strict_types=1);

namespace App\Core\Workspace\Builder;

use App\Core\Workspace\Actions\ActionBuilder;
use App\Core\Workspace\Cache\WorkspaceCacheManager;
use App\Core\Workspace\Dashboard\DashboardBuilder;
use App\Core\Workspace\DTO\WorkspaceConfigDTO;
use App\Core\Workspace\Sidebar\SidebarBuilder;
use App\Models\User;
use App\Models\WorkspaceProfile;

class WorkspaceBuilderService
{
    public function __construct(
        protected SidebarBuilder $sidebarBuilder,
        protected DashboardBuilder $dashboardBuilder,
        protected ActionBuilder $actionBuilder,
        protected WorkspaceCacheManager $cacheManager
    ) {}

    /**
     * Build dynamic workspace configuration for the authenticated user and portal.
     */
    public function buildWorkspace(?User $user = null, string $portal = 'stock'): WorkspaceConfigDTO
    {
        $user = $user ?? (auth()->user() ?? (User::where('email', 'admin@stockmanager.com')->first() ?? User::first()));

        // Determine role profile
        $userRoles = $user ? $user->roles->pluck('name')->toArray() : ['Operator'];
        $primaryRole = $userRoles[0] ?? 'Operator';

        $profile = WorkspaceProfile::where('role_name', $primaryRole)->first();
        $profileCode = $profile->code ?? strtolower(str_replace(' ', '_', $primaryRole));
        $profileName = $profile->name ?? "{$primaryRole} Workspace";
        $layoutType = $profile->layout_type ?? $this->resolveLayoutType($userRoles);

        // Build dynamic sidebar navigation
        $sidebarItems = $this->sidebarBuilder->build($user, $portal);

        // Build dynamic dashboard layout & widgets
        $dashboardData = $this->dashboardBuilder->build($user, $portal);

        // Build dynamic quick actions
        $quickActions = $this->actionBuilder->build($user, $portal);

        return new WorkspaceConfigDTO(
            profileCode: $profileCode,
            profileName: $profileName,
            layoutType: $layoutType,
            sidebarItems: $sidebarItems,
            dashboardWidgets: $dashboardData['widgets'] ?? [],
            quickActions: $quickActions,
            kpis: [],
            userContext: [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $userRoles,
                'portal' => $portal,
            ]
        );
    }

    protected function resolveLayoutType(array $roles): string
    {
        if (in_array('CEO', $roles) || in_array('Executive', $roles)) {
            return 'executive';
        }
        if (in_array('Warehouse Supervisor', $roles)) {
            return 'supervisor';
        }
        if (in_array('Warehouse Operator', $roles) || in_array('Inventory Operator', $roles)) {
            return 'operator';
        }
        return 'manager';
    }
}
