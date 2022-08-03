<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TimeWaveDisable extends Model
{
    protected $table = 'timeWaveDisable';
    public $timestamps = false;
    protected $fillable = [
        'waveId',
        'wdate',
    ];
}
