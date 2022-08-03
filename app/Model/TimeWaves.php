<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TimeWaves extends Model
{
    protected $table = 'timeWaves';
    public $timestamps = false;
    protected $fillable = [
        'zoneId',
        'timeFrom',
        'timeTo',
    ];
    protected $casts = [
        'timeFrom' => 'time:H:i',
        'timeTo' => 'time:H:i'
    ];
}
