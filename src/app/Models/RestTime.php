<?php

namespace App\Models;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
    ];

    protected $appends = [
        'formatted_start_time',
        'formatted_end_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time
            ? Carbon::parse($this->start_time)->format('H:i')
            : '';
    }

    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time
            ? Carbon::parse($this->end_time)->format('H:i')
            : '';
    }
}
