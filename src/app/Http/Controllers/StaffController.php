<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();
        return view('attendance', compact('user'));
    }
}
