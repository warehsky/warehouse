<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    protected $table = 'phoneNumber';
    public $primaryKey = "phone";
    protected $keyType = 'string';
    public $fillable = [
        'phone',
        'name',
        'source',
        'unsubscribe'
    ];

   
    
}
