<?php

namespace App\Models;

use App\Models\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RequestRest extends Model
{
    protected $fillable = [
        'request_id',
        'start_time',
        'end_time',
    ];

    protected $appends = [
        'formatted_start_time',
        'formatted_end_time',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
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
