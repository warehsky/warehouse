<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderPropertys extends Model
{
    protected $table = 'orderPropertys';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'phone', 'addr', 'deliveryCost', 'status'
    ];
}
