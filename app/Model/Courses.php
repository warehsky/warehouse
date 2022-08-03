<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    protected $table = 'courses';
    public $timestamps = false;
    protected $connection = 'mtagent';

    public static function getCourse(){
        $course = Courses::where('coursedate', \DB::raw('(select MAX(`coursedate`) FROM `courses`)'))->value('course');
        return $course ?? 0;
    }
}
