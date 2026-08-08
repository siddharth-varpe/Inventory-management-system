<?php

use App\Providers\AppServiceProvider;
use App\Providers\CoreServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    CoreServiceProvider::class,
    App\Providers\EnterpriseOrchestrationServiceProvider::class,
];
