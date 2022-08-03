<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TimeWavesOrderLimit extends Model
{
    protected $table = 'timeWavesOrderLimit';
    public $timestamps = false;
    protected $fillable = [
        'date',
        'waveId',
        'countOrder',
        'moderatorId'
    ];
    
}
