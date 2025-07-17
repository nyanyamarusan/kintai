<?php

namespace App\Models;

use App\Models\RestTime;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_rest',
        'total_work',
    ];

    protected $appends = [
        'total_rest_minutes',
        'work_minutes',
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

    public function getFormattedDateAttribute()
    {
        $date = Carbon::parse($this->date);
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        return $date->format('m/d') . '（' . $weekdays[$date->dayOfWeek] . '）';
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

                $start = Carbon::createFromFormat($startFormat, $baseDate . ' ' . $rest->start_time);
                $end = Carbon::createFromFormat($endFormat, $baseDate . ' ' . $rest->end_time);

                if ($end->lessThan($start)) {
                    $end->addDay();
                }
                return $start->diffInMinutes($end);
            }
            return 0;
        });
    }

    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $baseDate = $this->date ?? now()->toDateString();

        $clockInFormat = (strlen($this->clock_in) === 5) ? 'Y-m-d H:i' : 'Y-m-d H:i:s';
        $clockOutFormat = (strlen($this->clock_out) === 5) ? 'Y-m-d H:i' : 'Y-m-d H:i:s';

        $start = Carbon::createFromFormat($clockInFormat, $baseDate . ' ' . $this->clock_in);
        $end   = Carbon::createFromFormat($clockOutFormat, $baseDate . ' ' . $this->clock_out);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $workedMinutes = $start->diffInMinutes($end);

        return max(0, $workedMinutes - $this->total_rest_minutes);
    }
}
