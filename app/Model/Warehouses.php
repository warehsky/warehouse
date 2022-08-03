<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Warehouses extends Model
{
    protected $table = 'warehouses';
    public $timestamps = false;
    protected $connection = 'mtagent';
}
