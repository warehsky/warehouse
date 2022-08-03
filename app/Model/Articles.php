<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Articles extends Model
{
    protected $table = 'articles';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'image',
        'text',
        'html',
        'css',
        'json',
        'public'
    ];

}
