<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VacancyGroupsSpecialty extends Model
{
    protected $table = 'vacancyGroupsSpecialty';
    public $timestamps = false; 

    protected $fillable =
    [
        'vacancyId',
        'specialtyId',
    ];
}
