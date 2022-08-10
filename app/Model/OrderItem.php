<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'orderItem';
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'orderId', 'itemId', 'quantity', 'price', 'operatorId'
    ];
    
	/** */
	public function orders()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }
    /** */
	public function items()
    {
        return $this->belongsTo(Items::class, 'item_id', 'id');
    }
    
    
}
