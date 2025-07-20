<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function show($id)
    {
        if (Auth::guard('admin')->check()) {
            $attendance = Attendance::with('user', 'restTimes', 'request')
                ->findOrFail($id);
            $user = $attendance->user;
        } else {
            //$user = Auth::user();
            $user = User::find(2);
            $attendance = Attendance::with('user', 'restTimes', 'request')
                ->where('user_id', $user->id)
                ->findOrFail($id);
            $user = $attendance->user;
        }

        return view('show', compact('attendance', 'user'));
    }
}
