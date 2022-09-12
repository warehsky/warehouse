<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    protected $table = 'expenseItem';
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expenseId', 'itemId', 'quantity', 'price', 'orderId'
    ];
    
	/** */
    public function expense()
    {
        return $this->belongsTo(Expenses::class, 'expenseId', 'id');
    }
    /** */
    public function items()
    {
        return $this->belongsTo(Items::class, 'itemId', 'id');
    }
    
    
}
