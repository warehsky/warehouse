<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WebUsersSendSms extends Model
{
    protected $table = 'webUsersSendSms';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'webUserId',
        'phone',
        'msgSms'
    ];
}
