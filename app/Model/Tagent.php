<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Tagent extends Model
{
    protected $table = 'users';
    
    protected $fillable = [
        'name', 'email', 'password',
    ];
    protected $connection = 'mtagent';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public $timestamps = false;
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    
}
