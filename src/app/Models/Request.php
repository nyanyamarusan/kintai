<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\RequestRest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'attendance_id',
        'clock_in',
        'clock_out',
        'approved',
        'reason',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestRests()
    {
        return $this->hasMany(RequestRest::class);
    }

    public function getTotalRestMinutesAttribute()
    {
        $this->loadMissing('requestRests');

        return $this->requestRests->sum(function ($rest) {

            if ($rest->start_time && $rest->end_time) {

                $baseDate = $this->attendance->date;

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

        $baseDate = $this->attendance->date;

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
