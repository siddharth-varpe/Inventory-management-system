<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\UserStatus;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Logging\EnterpriseLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthService implements AuthServiceInterface
{
    /**
     * UserRepository instance.
     *
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     * AuthService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function login(array $credentials, bool $remember = false): bool
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            EnterpriseLogger::security("Login attempt failed: user not found", ['email' => $credentials['email']]);
            return false;
        }

        if ($user->status === UserStatus::SUSPENDED || $user->status === UserStatus::INACTIVE) {
            EnterpriseLogger::security("Login attempt rejected: account status {$user->status->value}", ['user_id' => $user->id]);
            throw new DomainException("Your account is currently {$user->status->value}. Please contact system support.");
        }

        if (Auth::attempt($credentials, $remember)) {
            request()->session()->regenerate();
            $this->userRepository->updateLastLogin($user->id);
            EnterpriseLogger::security("User logged in successfully", ['user_id' => $user->id]);
            return true;
        }

        EnterpriseLogger::security("Login attempt failed: invalid password", ['user_id' => $user->id]);
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function register(array $userData): User
    {
        $userData['password'] = Hash::make($userData['password']);
        $userData['status'] = UserStatus::ACTIVE->value;

        /** @var User $user */
        $user = $this->userRepository->create($userData);

        // Default role assignment
        $user->assignRole('user');

        event(new Registered($user));

        EnterpriseLogger::security("New user account registered", ['user_id' => $user->id, 'email' => $user->email]);

        Auth::login($user);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function logout(): void
    {
        $userId = Auth::id();
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        EnterpriseLogger::security("User logged out", ['user_id' => $userId]);
    }

    /**
     * {@inheritdoc}
     */
    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        EnterpriseLogger::security("Password reset link requested", ['email' => $email, 'status' => $status]);

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword(array $credentials): string
    {
        $status = Password::reset($credentials, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();

            EnterpriseLogger::security("Password reset successfully", ['user_id' => $user->id]);
        });

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function sendEmailVerificationNotification(User $user): void
    {
        $user->sendEmailVerificationNotification();
        EnterpriseLogger::security("Email verification notification sent", ['user_id' => $user->id]);
    }
}
