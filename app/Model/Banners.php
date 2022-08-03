<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Banners extends Model
{
    protected $table = 'banners';
    public $timestamps = false;
    protected $fillable = [
        'type',
        'autoplay',
        'public',
        'moderatorId'
    ];
    /** */
    public function items()
    {
        return $this->hasMany(BannerItems::class, 'bannerId')->where('public', 1)->orderBy('sort');
    }
    
}
