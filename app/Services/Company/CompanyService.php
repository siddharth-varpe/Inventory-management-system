<?php

declare(strict_types=1);

namespace App\Services\Company;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\CompanyServiceInterface;
use App\Services\File\FileManagerService;
use Illuminate\Http\UploadedFile;

class CompanyService extends BaseService implements CompanyServiceInterface
{

    /**
     * FileManagerService instance.
     *
     * @var FileManagerService
     */
    protected FileManagerService $fileManager;

    /**
     * CompanyService constructor.
     *
     * @param CompanyRepositoryInterface $repository
     * @param FileManagerService $fileManager
     */
    public function __construct(CompanyRepositoryInterface $repository, FileManagerService $fileManager)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->fileManager = $fileManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryProfile(): Company
    {
        /** @var CompanyRepositoryInterface $companyRepo */
        $companyRepo = $this->repository;
        $company = $companyRepo->getPrimaryCompany();

        if (!$company) {
            /** @var Company $company */
            $company = $companyRepo->create([
                'name' => 'StockManager Enterprise ERP',
                'legal_name' => 'StockManager Enterprise Inc.',
                'email' => 'contact@stockmanager-erp.com',
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'status' => 'active',
            ]);
        }

        return $company;
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(array $data, ?UploadedFile $logoFile = null): Company
    {
        $company = $this->getPrimaryProfile();

        if ($logoFile) {
            $this->fileManager->validateFile($logoFile, ['jpg', 'jpeg', 'png', 'svg', 'webp'], 2048);
            $data['logo'] = $this->fileManager->replaceFile($logoFile, $company->logo, 'company_logo');
        }

        $data['updated_by'] = auth()->id();
        $this->repository->update($company->id, $data);

        return $this->getPrimaryProfile();
    }
}
