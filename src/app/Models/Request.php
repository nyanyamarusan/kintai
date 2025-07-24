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

    protected $appends = [
        'formatted_clock_in',
        'formatted_clock_out',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestRests()
    {
        return $this->hasMany(RequestRest::class);
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
        return $this->approved === false;
    }
}
