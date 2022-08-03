<?php

namespace App\Model;

use App\Http\Controllers\Admins;
use Illuminate\Database\Eloquent\Model;

class OrderLocks extends Model
{
    protected $table = 'orderLocks';
    protected $primaryKey = 'orderId';
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'orderId', 'userId'
    ];
    /** */
	public function order()
    {
        return $this->belongsTo(Orders::class, 'orderId', 'id');
    }
    /** */
	public function user()
    {
        return $this->belongsTo(Admin::class, 'userId', 'id');
    }
    
}
