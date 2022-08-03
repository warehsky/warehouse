<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class webUsersBringFriend extends Model
{
    protected $table = 'webUsersBringFriend';
    public $timestamps = false;
    protected $fillable = [
        'orderId', 
    ];
}
