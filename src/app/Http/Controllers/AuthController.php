<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    public function store(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        event(new Registered($user));
        Auth::login($user);

        return redirect('/email/verify');
    }

    public function login(LoginRequest $request)
    {
        $request->authenticate();

        return redirect('/attendance');
    }

    public function adminLogin(AdminLoginRequest $request)
    {
        $request->authenticate();

        return redirect('/admin/attendance/list');
    }

    public function logout(Request $request)
    {
        $redirect = '/login';

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            $redirect = '/admin/login';
        } elseif (Auth::check()) {
            Auth::logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect($redirect);
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect('/attendance');
    }
}
