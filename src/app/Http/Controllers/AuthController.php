<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function loginView()
    {
        return view('auth.login');
    }

    public function adminLoginView()
    {
        return view('auth.admin-login');
    }

    public function login(LoginRequest $request)
    {
        Auth::guard('admin')->logout();
        $request->authenticate();

        return redirect('/attendance');
    }

    public function adminLogin(AdminLoginRequest $request)
    {
        Auth::guard('web')->logout();
        $request->authenticate();

        return redirect('/admin/attendance/list');
    }

    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            $redirect = '/admin/login';
        } elseif (Auth::check()) {
            Auth::logout();
            $redirect = '/login';
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
