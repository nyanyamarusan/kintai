<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetectGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return response(app(AdminController::class)->showRequests($request));
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if (!$user->hasVerifiedEmail()) {
                return redirect('/email/verify');
            }

            return response(app(StaffController::class)->showRequests($request));
        }

        return redirect('/login');
    }
}
