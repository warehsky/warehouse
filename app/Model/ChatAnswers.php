<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChatAnswers extends Model
{
    protected $table = 'chatAnswers';
    public $timestamps = false;
    protected $fillable = [
        'answer',
    ];

}
