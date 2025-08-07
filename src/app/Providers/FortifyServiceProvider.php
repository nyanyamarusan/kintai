<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');

            if ($request->is('admin/login')) {
                Auth::guard('admin')->logout();
                if (Auth::guard('admin')->attempt($credentials)) {
                    return Auth::guard('admin')->user();
                }
            } else {
                Auth::guard('web')->logout();
                if (Auth::guard('web')->attempt($credentials)) {
                    return Auth::guard('web')->user();
                }
            }

            return null;
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
                return view('auth.admin-login');
            }
            return view('auth.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        RateLimiter::for('login', function (Request $request) {
            if ($request->is('admin/login')) {
                $email = (string) $request->email;
                return Limit::perMinute(5)->by('admin|'.$email.'|'.$request->ip());
            }

            $email = (string) $request->email;
            return Limit::perMinute(10)->by('user|'.$email.'|'.$request->ip());
        });
    }
}
