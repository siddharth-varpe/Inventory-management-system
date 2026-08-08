<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Authenticate a user with email & password credentials.
     *
     * @param array<string, mixed> $credentials
     * @param bool $remember
     * @return bool
     */
    public function login(array $credentials, bool $remember = false): bool;

    /**
     * Register a new user account.
     *
     * @param array<string, mixed> $userData
     * @return User
     */
    public function register(array $userData): User;

    /**
     * Logout current user session.
     *
     * @return void
     */
    public function logout(): void;

    /**
     * Send password reset link to user email.
     *
     * @param string $email
     * @return string
     */
    public function sendPasswordResetLink(string $email): string;

    /**
     * Reset user password using token.
     *
     * @param array<string, mixed> $credentials
     * @return string
     */
    public function resetPassword(array $credentials): string;

    /**
     * Resend verification notification email.
     *
     * @param User $user
     * @return void
     */
    public function sendEmailVerificationNotification(User $user): void;
}
