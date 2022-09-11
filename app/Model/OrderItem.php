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
        return $this->belongsTo(Orders::class, 'orderId', 'id');
    }
    /** */
	public function item()
    {
        return $this->belongsTo(Items::class, 'itemId', 'id')->with('cargo');
    }
    
    
}
