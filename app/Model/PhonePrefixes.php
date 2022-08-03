<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PhonePrefixes extends Model
{
    protected $table = 'phonePrefixes';
    public $timestamps = false;
    protected $fillable = [
        'type',
        'prefix',
        'images',
        'active'
    ];
    /** */
    public static function getPhonePrefixes()
    {
	    $phonePrefixes = PhonePrefixes::select('id', 'type', 'prefix', 'images', 'title')
        ->where('active', '>', 0)
        ->orderBy('sort')
        ->get();
        return $phonePrefixes;
    }
    
}
