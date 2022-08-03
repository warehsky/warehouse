<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChatOptions extends Model
{
    protected $table = 'chatOptions';
    public $timestamps = false;
    protected $fillable = [
        'autoreply',
        'chatAnswerId',
    ];

}
