<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PhoneNumberOrderInfo extends Model
{
    protected $table = 'phoneNumberOrderInfo';
    public $primaryKey = "orderId";
    public $timestamps = false;
    public $fillable = [
        'orderId',
        'comment'
    ];
}
