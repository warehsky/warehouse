<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class phoneNumberBackLink extends Model
{
    protected $table = 'phoneNumberBackLink';
    public $primaryKey = "orderId";
    public $timestamps = false;
    public $fillable = [
        'orderId',
        'comment',
        'recall',
        'source',
        'guilty'
    ];
}
