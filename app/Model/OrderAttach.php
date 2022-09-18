<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderAttach extends Model
{
    protected $table = 'orderAttach';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'orderId', 'attach'
    ];

}
