<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $table = 'comments';
    public $timestamps = true;
    protected $fillable = [
        'userName',
        'comment',
        'estimate',
        'itemId',
        'status',
        'moderatorId'
    ];
    /** */
    public function answers()
    {
        return $this->hasMany(CommentAnswers::class, 'commentId')->orderBy('created_at', 'desc');
    }
    /**
     * Возвращает отзывы используя фильтры
     * Входные параметры:
     * dFrom - начало периода
     * dTo - конец периода
     * itemId - ID товара
     * status - статус отзыва
    */
    public static function getComments($dFrom=null, $dTo=null, $itemId=0, $status=1){
        if($itemId && $itemId>0)
            $result = Comments::select('*')->with('answers')->where('itemId', $itemId);
        else
            $result = Comments::select('*')->with('answers');
        if($dFrom)
            $result = $result->where('created_at', '>=', $dFrom);
        if($dTo)
            $result = $result->where('created_at', '<=', $dTo);
        if($status>=0)
            $result = $result->where('status', $status);
        
        
        return $result;
    }
}
