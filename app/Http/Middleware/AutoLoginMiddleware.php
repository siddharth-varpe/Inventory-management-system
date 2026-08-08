<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginMiddleware
{
    /**
     * Handle an incoming request.
     * Auto-authenticate guest users as the Enterprise Administrator.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            /** @var User|null $user */
            $user = User::where('email', 'admin@stockmanager.com')->first() ?? User::first();
            if ($user) {
                Auth::login($user, true);
            }
        }

        return $next($request);
    }
}
