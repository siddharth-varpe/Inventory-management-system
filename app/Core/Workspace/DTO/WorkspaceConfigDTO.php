<?php

declare(strict_types=1);

namespace App\Core\Workspace\DTO;

class WorkspaceConfigDTO
{
    public function __construct(
        public string $profileCode,
        public string $profileName,
        public string $layoutType, // operator, manager, supervisor, executive, admin
        public array $sidebarItems = [],
        public array $dashboardWidgets = [],
        public array $quickActions = [],
        public array $kpis = [],
        public array $userContext = []
    ) {}

    public function toArray(): array
    {
        return [
            'profile_code' => $this->profileCode,
            'profile_name' => $this->profileName,
            'layout_type' => $this->layoutType,
            'sidebar_items' => $this->sidebarItems,
            'dashboard_widgets' => $this->dashboardWidgets,
            'quick_actions' => $this->quickActions,
            'kpis' => $this->kpis,
            'user_context' => $this->userContext,
        ];
    }
}
