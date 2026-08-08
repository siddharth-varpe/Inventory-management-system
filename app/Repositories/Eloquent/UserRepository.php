<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends EloquentBaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->findBy('email', $email);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLastLogin(int|string $userId): void
    {
        $this->update($userId, [
            'last_login_at' => now(),
        ]);
    }
}
