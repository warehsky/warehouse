<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VacancySpecialtyTitle extends Model
{
    protected $table = 'vacancySpecialtyTitle';
    public $timestamps = false;
    protected $fillable =
    [
        'specialtyTitle',
    ];
}
