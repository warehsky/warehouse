<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemsKassa extends Model
{
    protected $table = 'itemsKassa';
    protected $primaryKey = 'itemId';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'itemId',
        'itemsorder', 
        'dateId'
    ];
}