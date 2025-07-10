<?php

namespace App\Models;

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
        'status',
        'reason',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';

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

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
