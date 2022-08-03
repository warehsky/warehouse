<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WarehouseGroups extends Model
{
    protected $table = 'warehouseGroups';
    public $timestamps = false;
    protected $fillable = [
        'groupId',
        'warehouseId'
    ];
}
