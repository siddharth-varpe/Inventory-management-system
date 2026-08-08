<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Company;

interface CompanyRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get primary company profile.
     *
     * @return Company|null
     */
    public function getPrimaryCompany(): ?Company;
}
