<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CommentFirmAnswers extends Model
{
    protected $table = 'commentFirmAnswers';
    public $timestamps = true;
    protected $fillable = [
        'commentId',
        'answer',
        'status',
        'moderatorId'
    ];
}
