<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BannerItems extends Model
{
    protected $table = 'bannerItems';
    public $timestamps = false;
    protected $fillable = [
        'bannerId',
        'link',
        'link_mobile',
        'image',
        'image_mobile',
        'alt',
        'alt_mobile',
        'sort',
        'public',
        'moderatorId'
    ];
    
    
}
