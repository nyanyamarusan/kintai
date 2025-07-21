<?php

namespace App\Http\Controllers;

use App\Models\Request as AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function showRequests(Request $request)
    {
        Auth::guard('admin')->check();

        $tab = request()->get('tab', 'pending');

        if ($tab === 'approved') {
            $requests = AttendanceRequest::where('approved', true)
            ->with('attendance.user')->get();
        } else {
            $requests = AttendanceRequest::where('approved', false)
            ->with('attendance.user')->get();
        }

        return view('request', compact('requests', 'tab'));
    }
}
