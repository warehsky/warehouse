<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    protected $table = 'clients';
    public $timestamps = true;
    protected $fillable = [
        'client',
        'address',
        'nip',
        'phone',
        'operatorId',
        'note'
    ];
    
}
