<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChatMessages extends Model
{
    protected $table = 'chatMessages';
    public $timestamps = true;
    protected $fillable = [
        'chatUserId',
        'moderatorId',
        'message',
        'sent',
        'wasread'
    ];
    /** */
    public function author()
    {
        return $this->hasOne(ChatUsers::class, 'id')->orderBy('created_at', 'desc');
    }
    /**
     * Возвращает сообщения пользователей чата используя фильтры
     * Входные параметры:
     * dFrom - начало периода
     * dTo - конец периода
     * status - статус сообщения
    */
    public static function getMessages($userId, $dFrom=null, $dTo=null, $status=1){
        $result = ChatMessages::select('*')->with('author')->where('chatUserId', $userId);
        if($dFrom)
            $result = $result->where('created_at', '>=', $dFrom);
        if($dTo)
            $result = $result->where('created_at', '<=', $dTo);
        if($status>=0)
            $result = $result->where('status', $status)->where('moderatorId', 0);
        
        
        return $result;
    }
}
