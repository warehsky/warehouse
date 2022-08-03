<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TradePoints extends Model
{
    protected $table = 'trade_points';
    public $timestamps = false;
    protected $connection = 'mtagent';

    /**
    * Получить клиента, владеющего данной торговой точкой
    */
    public function client()
    {
        return $this->belongsTo('App\Model\Clients', 'client_id');
    }
    /**
    * Получить режим работы, торговой точки
    */
    public function schedule()
    {
        return $this->hasMany('App\Model\TradePointSchedules', 'tpId', 'id');
    }
    /**
    * Получить режим работы, торговой точки
    */
    public function scheduleReq()
    {
        return $this->hasMany('App\Model\TradePointScheduleRequest', 'tpId', 'id');
    }
    
    
    /**
     * 
     * возвращает все торговые точки клиента
     * 
     */
    public static function getTradePoints($clientId){
        $sql = 'select t.id, t.trade_point ' . 
        'FROM trade_points as t ' .
        'inner join  clients as c on t.client_id=c.id ' .
        'where  t.client_id=' . $clientId;
        $result = \DB::connection('mtagent')->select( $sql );
        
        return $result;
    }
}
