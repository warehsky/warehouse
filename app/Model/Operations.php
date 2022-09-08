<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Operatins extends Model
{
    protected $table = 'items';
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'operation' 
    ];

}
