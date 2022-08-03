<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WebUsersNote extends Model
{
    protected $table = 'webUsersNote';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'webUserId', 
        'note',
        'status',
        'moderatorId'
    ];
}
