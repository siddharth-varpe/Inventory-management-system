<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Cache\EnterpriseCacheManager;
use App\Core\Contracts\CacheManagerInterface;
use App\Core\Contracts\EventBusInterface;
use App\Core\EventBus\EnterpriseEventBus;
use App\Core\NotificationEngine\EnterpriseNotificationEngine;
use App\Core\Synchronization\EnterpriseSyncManager;
use App\Core\WorkflowEngine\WorkflowEngine;
use App\Core\Workspace\Actions\ActionBuilder;
use App\Core\Workspace\Builder\WorkspaceBuilderService;
use App\Core\Workspace\Cache\WorkspaceCacheManager;
use App\Core\Workspace\Dashboard\DashboardBuilder;
use App\Core\Workspace\Sidebar\SidebarBuilder;
use App\Core\Workspace\Widgets\WidgetRegistry;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any Core application singletons and bindings.
     */
    public function register(): void
    {
        $this->app->singleton(EnterpriseCacheManager::class);
        $this->app->bind(CacheManagerInterface::class, EnterpriseCacheManager::class);

        $this->app->singleton(EnterpriseEventBus::class);
        $this->app->bind(EventBusInterface::class, EnterpriseEventBus::class);

        $this->app->singleton(EnterpriseNotificationEngine::class);
        $this->app->singleton(EnterpriseSyncManager::class);
        $this->app->singleton(WorkflowEngine::class);

        // Workspace Engine Bindings
        $this->app->singleton(WidgetRegistry::class);
        $this->app->singleton(SidebarBuilder::class);
        $this->app->singleton(DashboardBuilder::class);
        $this->app->singleton(ActionBuilder::class);
        $this->app->singleton(WorkspaceCacheManager::class);
        $this->app->singleton(WorkspaceBuilderService::class);
    }

    /**
     * Bootstrap any Core application services.
     */
    public function boot(): void
    {
        //
    }
}
