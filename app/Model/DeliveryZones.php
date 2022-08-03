<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeliveryZones extends Model
{
    protected $table = 'deliveryZones';
    public $timestamps = true;
    protected $fillable = [
        'cost',
        'limit_min',
        'limit',
        'limit_lgot',
        'description',
        'balloon',
        'fill',
        'fillOpacity',
        'stroke',
        'zonePoligon',
        'deleted'
    ];
}