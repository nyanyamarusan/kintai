<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\RequestRest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'target_date',
        'request_at',
        'new_clock_in',
        'new_clock_out',
        'approved',
        'reason',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestRests()
    {
        return $this->hasMany(RequestRest::class);
    }
}
