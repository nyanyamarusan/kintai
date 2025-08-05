<?php

namespace App\Models;

use App\Models\Request;
use App\Models\RestTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'reason',
    ];

    protected $appends = [
        'total_rest_minutes',
        'total_work_minutes',
        'formatted_clock_in',
        'formatted_clock_out',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restTimes()
    {
        return $this->hasMany(RestTime::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function getStatusAttribute()
    {
        if ($this->clock_out) {
            return '退勤済';
        }

        if ($this->restTimes()->whereNull('end_time')->exists()) {
            return '休憩中';
        }

        if ($this->clock_in) {
            return '出勤中';
        }

        return '勤務外';
    }

    public function getTotalRestMinutesAttribute()
    {
        $this->loadMissing('restTimes');

        return $this->restTimes->sum(function ($rest) {

            if ($rest->start_time && $rest->end_time) {

                $baseDate = $this->date ?? now()->toDateString();

                $startFormat = (strlen($rest->start_time) === 5) ? 'Y-m-d H:i' : 'Y-m-d H:i:s';
                $endFormat = (strlen($rest->end_time) === 5) ? 'Y-m-d H:i' : 'Y-m-d H:i:s';

                $start = Carbon::createFromFormat($startFormat, $baseDate.' '.$rest->start_time);
                $end = Carbon::createFromFormat($endFormat, $baseDate.' '.$rest->end_time);

                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                return $start->diffInMinutes($end);
            }

            return 0;
        });
    }

    public function getTotalWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $baseDate = $this->date ?? now()->toDateString();

        $clockInFormat = (strlen($this->clock_in) === 5) ? 'Y-m-d H:i' : 'Y-m-d H:i:s';
        $clockOutFormat = (strlen($this->clock_out) === 5) ? 'Y-m-d H:i' : 'Y-m-d H:i:s';

        $start = Carbon::createFromFormat($clockInFormat, $baseDate.' '.$this->clock_in);
        $end = Carbon::createFromFormat($clockOutFormat, $baseDate.' '.$this->clock_out);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $workedMinutes = $start->diffInMinutes($end);

        return max(0, $workedMinutes - $this->total_rest_minutes);
    }

    public function getFormattedTotalRestAttribute()
    {
        $minutes = $this->total_rest_minutes;

        return $minutes ? $this->minutesToTime($minutes) : '';
    }

    protected function minutesToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%d:%02d', $hours, $mins);
    }

    public function getFormattedTotalWorkAttribute()
    {
        $minutes = $this->total_work_minutes;

        return $minutes ? $this->minutesToTime($minutes) : '';
    }

    public function getFormattedClockInAttribute()
    {
        return $this->clock_in
            ? Carbon::parse($this->clock_in)->format('H:i')
            : '';
    }

    public function getFormattedClockOutAttribute()
    {
        return $this->clock_out
            ? Carbon::parse($this->clock_out)->format('H:i')
            : '';
    }

    public function getIsPendingRequestAttribute()
    {
        return optional($this->requests()->latest()->first())->approved === false;
    }
}
