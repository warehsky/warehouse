<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VacancyGroups extends Model
{
    protected $table = 'vacancyGroups';
    public $timestamps = false; 

    protected $fillable =
    [
        'vacancyId',
        'propertiesID',
    ];
}
