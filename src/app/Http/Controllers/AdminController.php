<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestRequest;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use App\Services\IndexDateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index(Request $request, IndexDateService $indexDateService)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $day = $request->input('day', now()->day);

        $dates = $indexDateService->getPreviousCurrentNextDate($year, $month, $day);

        $carbon = $dates['current'];
        $prevDay = $carbon->copy()->subDay();
        $nextDay = $carbon->copy()->addDay();

        $attendances = Attendance::with('user', 'restTimes')
            ->whereBetween('date', [$prevDay->toDateString(), $nextDay->toDateString()])
            ->get()
            ->groupBy(function ($attendance) {
                return Carbon::parse($attendance->date)->toDateString();
            });

        return view('admin-index', compact('attendances',
            'dates', 'year', 'month', 'day', 'prevDay', 'nextDay'));
    }

    public function showRequests(Request $request)
    {
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

    public function update(RequestRequest $request)
    {
        $attendance = Attendance::with('restTimes')->findOrFail($request->attendance_id);

        $attendanceData = $request->only([
            'clock_in',
            'clock_out',
            'reason',
        ]);

        $attendance->update($attendanceData);

        $attendance->restTimes()->delete();
        $restInputs = $request->input('rest', []);

        foreach ($restInputs as $rest) {
            if (!empty($rest['start_time']) && !empty($rest['end_time'])) {
                $attendance->restTimes()->create([
                    'start_time' => $rest['start_time'],
                    'end_time' => $rest['end_time'],
                ]);
            }
        }

        return redirect('admin/attendance/list');
    }
}
