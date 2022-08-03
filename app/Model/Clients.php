<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    protected $table = 'clients';
    public $timestamps = false;
    protected $connection = 'mtagent';
    
    /**
     * Возвращает клиентов региона (только те у кого есть торговые точки)
     * $areaId - регион
     */
    public static function getClients($areaId, $tdId, $updateTm = 0){
        $sql='SELECT a.id as id, client, contractType, IFNULL(roAttr, false) as roAttr, IFNULL(defAttr, false) as defAttr, IFNULL(c.deferringTime,0) as deferringTime, ' . 
        'UNIX_TIMESTAMP(IF(isnull(c.updateTm), a.updateTm, IF(a.updateTm>c.updateTm, a.updateTm, c.updateTm))) as updateTm  ' . 
        'FROM clients as a ' .
        'INNER JOIN trade_points as b ON client_id = a.id ' .
        'LEFT JOIN clientDeferringTime as c ON clientId = a.id  and c.directionId=' . $tdId .
        ' where UNIX_TIMESTAMP(IF(isnull(c.updateTm), a.updateTm, IF(a.updateTm>c.updateTm, a.updateTm, c.updateTm))) >= ' . $updateTm . ' AND areaId =' . $areaId . ' ' .
        'GROUP BY a.id, c.directionId';
        $result = \DB::connection()->select( $sql );
        return $result;
    }
    /**
     * Связанные с клиентом торговые точки
     */
    public function clientItems()
    {
        return $this->hasMany(TradePoints::class, 'client_id');
    }
    /**
     * Связанные с клиентом лицензии
     */
    public function clientLicenses()
    {
        return $this->hasMany(Licenses::class, 'clientId')
        ->where('endTm', '>=', \DB::raw('CURDATE()'));
    }
    /**
     * Связанные с клиентом отсрочки
     */
    public function clientDeferringTime()
    {
        return $this->hasMany(ClientDeferringTime::class, 'clientId');
    }
    /**
     * Возвращает доступные контракты по битовой маске
     */
    public function getContractTypesByType(){
        $result = ContractTypes::select( 'id', 'value', 'title')->get();
        $data=[];
        foreach($result as $c){
            if( $c->value & $this->contractType){
                $data[]= $c->title;
            }  
        }
        return implode(", ", $data);
    }
    /**
     * Админ панель
     * Возвращает клиентов региона (всех с учетом фильтра)
     * $areaId - регион
     */
    public static function getClientsView($id=0, $search=0, $order=0, $dir="asc"){
        $lts = LicenseTypes::all();
        $ltypes = [];
        foreach($lts as $lt)
            $ltypes[$lt->value] = $lt;
        $ars = Areas::all();
        $areas = [];
        foreach($ars as $ar)
            $areas[$ar->id] = $ar;
        if(!$dir)
            $dir="asc";
        $result = Clients::with('clientItems')->with('clientLicenses')->with('clientDeferringTime.tpoint');
        if($id>0)
            $result = $result->where("id", $id);
        if($search){
            if(is_numeric($search))
                $result = $result->where("clients.id", "=", $search);
            else
                $result = $result->where("client", "like", "%$search%");
        }
        if($order)
            $result = $result->orderBy("clients." . $order, $dir);
        else
            $result = $result->orderBy("client");
        
        $result = $result->paginate(config('loadapi.PGINATE_CLIENTS'));
        foreach($result as $item){
            //Добавляем названия типов контрактов
            $item->contractView = $item->getContractTypesByType();
            $item->area = $areas[$item->areaId]->title;
            //Добавляем название типа лицензии
            if(count($item->clientLicenses))
                foreach($item->clientLicenses as $l)
                    $l->title = $ltypes[$l->value]->title;
            //Добавляем лицензии для торговых точек
            foreach($item->clientItems as $i){
                $i->licenses = self::getLicensesTP($i->id, $ltypes);
                $act = TradePointLocations::where("tpId", $i->id)->first();
                $mml = TradePointMMLDirections::where("tpId", $i->id)->get();
                @$i->mml = count($mml);
                if($act)
                    $i->actual = $act->actual;
                else
                    $i->actual = -1;
            }
        }
        return $result;
    }
    /**
     * Админ панель
     * Возвращает лицензии торговой точки
     * $tdId - ID торговой точки
     */
    private static function getLicensesTP($tpId, $ltypes){
        $res = Licenses::select('startTm', 'endTm', 'licenses.updateTm', 'value')
        ->where('endTm', '>=', \DB::raw('CURDATE()'))
        ->where('tpId', $tpId)
        ->get();
        foreach($res as $l)
            $l->title = $ltypes[$l->value]->title;
        return $res;
    }
}
