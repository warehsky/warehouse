<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OwnMessage extends Model
{
    protected $table = 'ownMessage';
    public $timestamps = true;

    protected $fillable = [
        'msg','readit','webUserId'
    ];

}
