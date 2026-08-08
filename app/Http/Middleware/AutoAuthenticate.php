<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutoAuthenticate
{
    /**
     * Handle an incoming request.
     * Auto-authenticate guest users as the Enterprise Administrator.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (!Auth::check()) {
            /** @var User|null $user */
            $user = User::where('email', 'admin@stockmanager.com')->first() ?? User::first();
            if ($user) {
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->save();
                }
                Auth::login($user, true);
            }
        } else {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user && !$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
            }
        }

        return $next($request);
    }
}
