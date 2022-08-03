<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemsKassaDate extends Model
{
    protected $table = 'itemsKassaDates';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dateStart', 
        'dateEnd'
    ];
}
