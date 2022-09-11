<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Evaluations extends Model
{
    protected $table = 'evaluations';
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'evaluation'
    ];

}
