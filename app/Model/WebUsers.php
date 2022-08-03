<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WebUsers extends Model
{
    protected $table = 'webUsers';
    protected $primaryKey = 'phone';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'phone', 
        'code',
        'orderId',
        'pension',
        'autoown',
        'autologin',
        'autoownperm',
        'note',
        'noteUser'
    ];
    
    public function getSource()
    {
        return 3;
    }

    /** Скидка */
    public function discounts()
    {
        return $this->hasMany(WebUsersDiscount::class, 'webUserId', 'id')->where('orderId', '=', 0)->where('expiration', '>=', Carbon::now()->timezone('Europe/Moscow')->startOfDay()->format('Y-m-d'));
    }
    /** Заметки */
    public function notes()
    {
        return $this->hasMany(WebUsersNote::class, 'webUserId', 'id')->where('status', '=', 1);
    }
}
