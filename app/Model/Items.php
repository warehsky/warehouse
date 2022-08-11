<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    protected $table = 'items';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item', 
    ];

}
