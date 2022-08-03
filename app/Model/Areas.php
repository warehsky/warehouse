<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Areas extends Model
{
    protected $table = 'areas';
    public $timestamps = false;
    protected $connection = 'mtagent';
}
