<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Update user last login timestamp.
     *
     * @param int|string $userId
     * @return void
     */
    public function updateLastLogin(int|string $userId): void;
}
