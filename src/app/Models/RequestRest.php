<?php

namespace App\Models;

use App\Models\Request;
use Illuminate\Database\Eloquent\Model;

class RequestRest extends Model
{
    protected $fillable = [
        'request_id',
        'start_time',
        'end_time',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
