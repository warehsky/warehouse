<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PhoneNumberStatistic extends Model
{
    protected $table = 'phoneNumberStatistic';
    public $fillable = [
        'phone',
        'name',
        'descr'
    ];
}
