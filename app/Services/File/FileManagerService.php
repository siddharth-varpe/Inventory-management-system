<?php

declare(strict_types=1);

namespace App\Services\File;

use App\Exceptions\DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerService
{
    /**
     * Standard enterprise storage folders.
     */
    public const FOLDERS = [
        'company_logo' => 'uploads/company_logo',
        'branch_logo' => 'uploads/branch_logo',
        'products' => 'uploads/products',
        'invoices' => 'uploads/invoices',
        'purchase_orders' => 'uploads/purchase_orders',
        'sales_orders' => 'uploads/sales_orders',
        'employee_documents' => 'uploads/employee_documents',
        'reports' => 'uploads/reports',
        'imports' => 'uploads/imports',
        'exports' => 'uploads/exports',
        'temp' => 'uploads/temp',
    ];

    /**
     * Upload a file to disk.
     *
     * @param UploadedFile $file
     * @param string $folderKey
     * @param string $disk
     * @return string Path of stored file
     */
    public function uploadFile(UploadedFile $file, string $folderKey = 'temp', string $disk = 'public'): string
    {
        $targetFolder = self::FOLDERS[$folderKey] ?? 'uploads/'.$folderKey;
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        return $file->storeAs($targetFolder, $filename, $disk);
    }

    /**
     * Replace an existing file with a new file.
     *
     * @param UploadedFile $file
     * @param string|null $oldPath
     * @param string $folderKey
     * @param string $disk
     * @return string
     */
    public function replaceFile(UploadedFile $file, ?string $oldPath, string $folderKey = 'temp', string $disk = 'public'): string
    {
        if ($oldPath && Storage::disk($disk)->exists($oldPath)) {
            Storage::disk($disk)->delete($oldPath);
        }

        return $this->uploadFile($file, $folderKey, $disk);
    }

    /**
     * Delete file from disk.
     *
     * @param string|null $path
     * @param string $disk
     * @return bool
     */
    public function deleteFile(?string $path, string $disk = 'public'): bool
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Get public URL for file path.
     *
     * @param string|null $path
     * @param string $disk
     * @return string|null
     */
    public function getFileUrl(?string $path, string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Validate file size and mime types.
     *
     * @param UploadedFile $file
     * @param array<int, string> $allowedExtensions
     * @param int $maxKb
     * @return bool
     */
    public function validateFile(UploadedFile $file, array $allowedExtensions = [], int $maxKb = 10240): bool
    {
        if ($file->getSize() > ($maxKb * 1024)) {
            throw new DomainException("File size exceeds maximum allowed limit of {$maxKb} KB.");
        }

        if (!empty($allowedExtensions)) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, array_map('strtolower', $allowedExtensions), true)) {
                throw new DomainException("Invalid file format [{$ext}]. Allowed formats: ".implode(', ', $allowedExtensions));
            }
        }

        return true;
    }
}
