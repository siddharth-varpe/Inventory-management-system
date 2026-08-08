<?php

declare(strict_types=1);

namespace App\Services\Brand;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\BrandServiceInterface;
use App\Services\File\FileManagerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class BrandService extends BaseService implements BrandServiceInterface
{

    /**
     * FileManagerService instance.
     *
     * @var FileManagerService
     */
    protected FileManagerService $fileManager;

    /**
     * BrandService constructor.
     *
     * @param BrandRepositoryInterface $repository
     * @param FileManagerService $fileManager
     */
    public function __construct(BrandRepositoryInterface $repository, FileManagerService $fileManager)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->fileManager = $fileManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createBrand(array $data, ?UploadedFile $logoFile = null): Brand
    {
        if (empty($data['code'])) {
            $data['code'] = 'BRD-'.strtoupper(Str::random(6));
        }

        if ($logoFile) {
            $this->fileManager->validateFile($logoFile, ['png', 'jpg', 'jpeg', 'svg', 'webp'], 2048);
            $data['logo'] = $this->fileManager->uploadFile($logoFile, 'temp');
        }

        $data['created_by'] = auth()->id();

        /** @var Brand */
        return $this->repository->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateBrand(int|string $id, array $data, ?UploadedFile $logoFile = null): bool
    {
        /** @var Brand $brand */
        $brand = $this->getById($id);

        if ($logoFile) {
            $this->fileManager->validateFile($logoFile, ['png', 'jpg', 'jpeg', 'svg', 'webp'], 2048);
            $data['logo'] = $this->fileManager->replaceFile($logoFile, $brand->logo, 'temp');
        }

        $data['updated_by'] = auth()->id();

        return $this->repository->update($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function restoreBrand(int|string $id): bool
    {
        /** @var BrandRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->restore($id);
    }
}
