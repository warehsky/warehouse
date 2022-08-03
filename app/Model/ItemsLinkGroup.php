<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemsLinkGroup extends Model
{
    protected $table = 'itemsLinkGroup';
    public $timestamps = false;
    
    protected $fillable = [
        'parentId', 
        'itemId'
    ];
}
