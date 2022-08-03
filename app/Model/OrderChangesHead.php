<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderChangesHead extends Model
{
    protected $table = 'orderChangesHead';
    public $timestamps = true;
    
    protected $fillable = [
        'orderId'
    ];
    
}
