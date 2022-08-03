<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeliverySots extends Model
{
    protected $table = 'deliverySots';
    public $timestamps = false;
    protected $fillable = [
        'sotPoligon', 'description', 'deleted'
    ];
    
    
}
