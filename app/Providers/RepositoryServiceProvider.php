<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\ProductAttributeRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\TaxRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\DepartmentRepository;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\ProductAttributeRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\TaxRepository;
use App\Repositories\Eloquent\UnitRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WarehouseRepository;
use App\Services\Auth\AuthService;
use App\Services\Brand\BrandService;
use App\Services\Branch\BranchService;
use App\Services\Category\CategoryService;
use App\Services\Company\CompanyService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\BrandServiceInterface;
use App\Services\Contracts\BranchServiceInterface;
use App\Services\Contracts\CategoryServiceInterface;
use App\Services\Contracts\CompanyServiceInterface;
use App\Services\Contracts\DepartmentServiceInterface;
use App\Services\Contracts\ProductAttributeServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\Contracts\TaxServiceInterface;
use App\Services\Contracts\UnitServiceInterface;
use App\Services\Department\DepartmentService;
use App\Services\Product\ProductService;
use App\Services\ProductAttribute\ProductAttributeService;
use App\Services\Tax\TaxService;
use App\Services\Unit\UnitService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All container singletons / bindings to register.
     *
     * @var array<string, string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        CompanyRepositoryInterface::class => CompanyRepository::class,
        BranchRepositoryInterface::class => BranchRepository::class,
        DepartmentRepositoryInterface::class => DepartmentRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        BrandRepositoryInterface::class => BrandRepository::class,
        UnitRepositoryInterface::class => UnitRepository::class,
        TaxRepositoryInterface::class => TaxRepository::class,
        ProductAttributeRepositoryInterface::class => ProductAttributeRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        InventoryRepositoryInterface::class => InventoryRepository::class,
        WarehouseRepositoryInterface::class => WarehouseRepository::class,

        AuthServiceInterface::class => AuthService::class,
        CompanyServiceInterface::class => CompanyService::class,
        BranchServiceInterface::class => BranchService::class,
        DepartmentServiceInterface::class => DepartmentService::class,
        CategoryServiceInterface::class => CategoryService::class,
        BrandServiceInterface::class => BrandService::class,
        UnitServiceInterface::class => UnitService::class,
        TaxServiceInterface::class => TaxService::class,
        ProductAttributeServiceInterface::class => ProductAttributeService::class,
        ProductServiceInterface::class => ProductService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
