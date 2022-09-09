<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Operations extends Model
{
    protected $table = 'operations';
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
