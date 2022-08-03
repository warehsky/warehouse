<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TagGroups extends Model
{
    protected $table = 'tagGroups';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'moderatorId'
    ];
}
