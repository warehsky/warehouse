<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CommentsFirm extends Model
{
    protected $table = 'commentsfirm';
    public $timestamps = true;
    protected $fillable = [
        'userName',
        'comment',
        'status',
        'moderatorId'
    ];
    /** */
    public function answers()
    {
        return $this->hasMany(CommentFirmAnswers::class, 'commentId')->orderBy('created_at', 'desc');
    }
    /**
     * Возвращает отзывы используя фильтры
     * Входные параметры:
     * dFrom - начало периода
     * dTo - конец периода
     * status - статус отзыва
    */
    public static function getComments($dFrom=null, $dTo=null, $status=1){
        $result = CommentsFirm::select('*')->with('answers');
        if($dFrom)
            $result = $result->where('created_at', '>=', $dFrom);
        if($dTo)
            $result = $result->where('created_at', '<=', $dTo);
        if($status>=0)
            $result = $result->where('status', $status);
        
        return $result;
    }
}
