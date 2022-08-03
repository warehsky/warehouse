<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WebUsersDiscountType extends Model
{
    protected $table = 'webUsersDiscountType';
    public $timestamps = false;
    protected $fillable = [
        'title', 
    ];
}
