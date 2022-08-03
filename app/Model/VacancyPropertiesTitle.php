<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VacancyPropertiesTitle extends Model
{
    protected $table = 'vacancyPropertiesTitle';
    public $timestamps = false;
    protected $fillable =
    [
        'title',
    ];
}
