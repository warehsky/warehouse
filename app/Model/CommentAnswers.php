<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CommentAnswers extends Model
{
    protected $table = 'commentAnswers';
    public $timestamps = true;
    protected $fillable = [
        'commentId',
        'answer',
        'status',
        'moderatorId'
    ];
}
