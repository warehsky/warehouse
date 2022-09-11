<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expenses extends Model
{
    protected $table = 'expenses';
    public $timestamps = false;
    

    protected $fillable = [
        'status',
        'clientId',
        'operatorId',
        'note',
        'sum_total'
    ];
    
    
    /** */
    public function expenseItems()
    {
        return $this->hasMany(ExpenseItem::class, 'expenseId')->with("items");
    }
    
}
