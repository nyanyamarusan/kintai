<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SharedAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if (!$user->hasVerifiedEmail()) {
                return redirect('/email/verify');
            }

            return $next($request);
        }

        return redirect('/login');
    }
}
