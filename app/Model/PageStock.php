<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PageStock extends Model
{
    protected $table = 'pageStock';

    protected $fillable = [
        'image','description','status','timeStart','timeEnd','title'
    ];

}
