<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChatUsers extends Model
{
    protected $table = 'chatUsers';
    public $timestamps = true;
    protected $fillable = [
        'author'
    ];
    /**
     * Возвращает пользователей чата используя фильтры
     * Входные параметры:
     * dFrom - начало периода
     * dTo - конец периода
    */
    public static function getUsers($dFrom=null, $dTo=null){
        $result = ChatUsers::select('*');
        if($dFrom)
            $result = $result->where('created_at', '>=', $dFrom);
        if($dTo)
            $result = $result->where('created_at', '<=', $dTo);
        
        
        return $result;
    }
    /** */
    public function lastmes()
    {
        return $this->hasOne(ChatMessages::class, 'chatUserId')->latest();
    }
    /** */
    public function noreadmes()
    {
        return $this->hasMany(ChatMessages::class, 'chatUserId')->where('moderatorId', 0)->where('status', 0);
    }
}
