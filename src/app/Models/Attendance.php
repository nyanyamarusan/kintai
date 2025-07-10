<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break_time',
        'work_time',
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
}
