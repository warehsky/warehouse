<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PickupControll extends Model
{
    protected $table = 'pickupControll';
    public $fillable = [
        'orderId',
        'moderatorId',
        'pickupId'
    ];
}
