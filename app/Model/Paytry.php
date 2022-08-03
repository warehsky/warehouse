<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Paytry extends Model
{
    protected $table = 'paytry';
    public $timestamps = true;
    protected $fillable = [
        'orderId',
        'orderNumber',
        'order_id'
    ];
}
