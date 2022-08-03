<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WebUsersSms extends Model
{
    protected $table = 'webUsersSMS';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'text', 
        'phone',
        'moderatorId',
        'created_at'
    ];
}
