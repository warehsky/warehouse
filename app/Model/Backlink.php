<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Backlink extends Model
{
    protected $table = 'backlink';
    protected $fillable = [
        'status'
    ];

    public static function getCountNewMessage()
    {
        return Backlink::where('status',0)->count();
    }

    public function getSource()
    {
        return 2;
    }
}
