<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderOptions extends Model
{
    protected $table = 'orderOptions';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'orderId', 'title', 'value'
    ];
}
