<?php

namespace App\Http\Controllers;

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
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                if ($user->role === 'admin') {
                    return redirect('/admin/attendance/list');
                } else {
                    return redirect('/attendance');
                }
            }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($user && $user->role === 'admin') {
            return redirect('/admin/login');
        } else {
            return redirect('/login');
        }
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect('/attendance');
    }
}
