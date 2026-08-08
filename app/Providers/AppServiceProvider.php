<?php

namespace App\Providers;

use App\Integration\Contracts\InventoryIntegrationInterface;
use App\Integration\Contracts\WarehouseIntegrationInterface;
use App\Integration\Services\InventoryIntegrationService;
use App\Integration\Services\WarehouseIntegrationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Helpers/SettingsHelper.php'))) {
            require_once app_path('Helpers/SettingsHelper.php');
        }
        $this->app->bind(InventoryIntegrationInterface::class, InventoryIntegrationService::class);
        $this->app->bind(WarehouseIntegrationInterface::class, WarehouseIntegrationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
