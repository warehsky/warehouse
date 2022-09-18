<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Orders extends Model
{
    protected $table = 'orders';
    public $timestamps = false;
    

    protected $fillable = [
        'status',
        'clientId',
        'operatorId',
        'note',
        'sum_total',
        'data',
        'operationId'
    ];
    
    
    /** */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'orderId')->with('item');
    }
    /** */
    public function orderAttach()
    {
        return $this->hasMany(OrderAttach::class, 'orderId');
    }
}
