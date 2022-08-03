<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\DeliveryZones;
use Carbon\Carbon;
use App\Model\TimeWaves;
use App\Model\TimeWaveDisable;
use App\Model\TimeWavesOrderLimit;

class TimeWavesController extends Controller
{
    public function index()
    { 
        return view('Admin.timeWaves');
    }

    public function getTimeWaves(Request $request)
    {
        $allTimeWaves=TimeWaves::select('timeWaves.*','deliveryZones.description')
        ->join('deliveryZones', 'deliveryZones.id', '=', 'timeWaves.zoneId');
        if ($request->id!=-1)
            $allTimeWaves=$allTimeWaves->where('timeWaves.zoneId',$request->id);
        $allTimeWaves=$allTimeWaves->get();
        $disableTimeWaves=[];
        $allTimeWavesDisable=TimeWaveDisable::select('waveId',\DB::raw('DATE_FORMAT(wdate, "%d.%m.%Y") as wdate'))->where('wdate','>=',Carbon::now()->timezone('Europe/Moscow')->startofDay())->get();
        foreach($allTimeWavesDisable as $value)
            $disableTimeWaves[$value->waveId][]=$value->wdate;

        return json_encode( [$allTimeWaves,$disableTimeWaves], JSON_UNESCAPED_UNICODE ); 
    }
    public function changeStatus(Request $request)
    {
        $timeWave = TimeWaves::find($request->id);
        $timeWave->deleted = !$timeWave->deleted;
        $timeWave->save();
        return json_encode(['code'=>200], JSON_UNESCAPED_UNICODE);
    }

    public function getDeliveryZones()
    {
        $Zones=DeliveryZones::select('*')->get();
        return json_encode( $Zones, JSON_UNESCAPED_UNICODE ); 
    }

    public function timeWaveDisable(Request $request)
    {
        if (!$request->dateDisable)
            return json_encode(['code'=>404,'msg'=>'Дата не указана'], JSON_UNESCAPED_UNICODE);
        
        if (!$request->selectedZone)
            return json_encode(['code'=>404,'msg'=>'Зона не указана'], JSON_UNESCAPED_UNICODE);


        $TimeWaves=TimeWaves::select('id');
        
        if (!in_array('-1',$request->selectedZone))
            $TimeWaves=$TimeWaves->WHEREIN('zoneId',$request->selectedZone);
        
        if($request->waveId)
        {
            $arr = explode(',',$request->waveId);
            $TimeWaves=$TimeWaves->WHEREIN('id',$arr);
        }
        else 
        {
            $timeStart = isset($request->timeStart) ? $request->timeStart : '00:00';
            $timeEnd = isset($request->timeEnd) ? $request->timeEnd : '23:59';
            $TimeWaves=$TimeWaves->WHERETIME('timeFrom', '>=', $timeStart);
            $TimeWaves=$TimeWaves->WHERETIME('timeFrom', '<', $timeEnd);
        }
        if(!$request->dateDisableStart){
            $dateDisableStart = null;
        }else{
            $dateDisableStart = $request->dateDisableStart . ' ' . ($request->timeDisableStart ? $request->timeDisableStart : '00:00');
        }
        $TimeWaves=$TimeWaves->WHERE('deleted', '=', 0);
        $TimeWaves=$TimeWaves->get()->toArray();
        $TimeWaves=array_column($TimeWaves,'id');

        foreach($TimeWaves as $id)
        {
            \DB::table('timeWaveDisable')->insertOrIgnore([
                ['wdate' => $request->dateDisable, 'waveId' => $id, 'dateStart' => $dateDisableStart],
            ]);
        }
        return json_encode(['code'=>200,'msg'=>'Отключено'], JSON_UNESCAPED_UNICODE);
    }

    public function deleteTimeWaveDisable(Request $request)
    {
        if (!$request->wave || !$request->date)
        return json_encode(['code'=>404,'msg'=>'Нет даты или id волны'], JSON_UNESCAPED_UNICODE);       

        TimeWaveDisable::where('waveId',$request->wave)
                        ->where('wdate',date('Y-m-d', strtotime($request->date)))
                        ->delete();
        return json_encode(['code'=>200,'msg'=>'Удалено'], JSON_UNESCAPED_UNICODE);            
    }

    public function indexOrderLimit()
    { 
        $nowTime=Carbon::now()->timezone('Europe/Moscow');
        $api_token = \Auth::guard('admin')->user()->getToken();
        $sql = "SELECT DISTINCT concat(DATE_FORMAT(timeFrom, '%H:%i'),'-', DATE_FORMAT(timeTo, '%H:%i')) as time FROM `timeWaves` 
                LEFT JOIN deliveryZones as dz ON dz.id=`timeWaves`.`zoneId` 
                WHERE dz.deleted=0 
                ORDER BY `timeWaves`.`timeFrom` ASC ";
        $waves=\DB::connection()->select($sql);
        // dd($waves);
        return view('Admin.timeWavesOrderLimit', compact('api_token','nowTime','waves'));
    }

    public function getOrderLimit(Request $request)
    {
        if ($request->time)
        {
            $times = explode('-',$request->time);
            $timeStart = $times[0];
            $timeEnd = $times[1];
        }
        else
        {
            $timeStart = $request->timeStart ? $request->timeStart : '00:00';
            $timeEnd = $request->timeEnd ? $request->timeEnd : '23:59';
        }
        $date= $request->date ? $request->date : Carbon::now()->timezone('Europe/Moscow');
        $allTimeWaves = $this->getLimit($date,$timeStart,$timeEnd);
        return json_encode($allTimeWaves, JSON_UNESCAPED_UNICODE ); 
    }

    /**
     * Возвращает все волны с ограничениями и количеством заказов в них
     */
    public static function getLimit($date,$timeStart,$timeEnd)
    {
        $sql = "(SELECT count(o.id) as countOrders FROM timeWaves as t 
        left join orders as o ON o.waveId=t.id
        WHERE t.id = timeWaves.id AND o.status!=7 AND o.status!=3 AND o.deliveryDate='$date') as orders,0 as status";

        $allTimeWaves=TimeWaves::select('timeWaves.id',
                                        'timeWaves.zoneId',
                                        'timeWaves.timeFrom',
                                        'timeWaves.timeTo',
                                        'timeWaves.orderLimit',
                                        'deliveryZones.description',
                                        'timeWavesOrderLimit.countOrder',
                                        \DB::RAW($sql))
        ->leftjoin('deliveryZones', 'deliveryZones.id', '=', 'timeWaves.zoneId')
        ->leftjoin('timeWavesOrderLimit', function ($join) use ($date){
            $join->on('timeWavesOrderLimit.waveId', '=', 'timeWaves.id')
                 ->where('timeWavesOrderLimit.date','=', $date);})
        ->where('deliveryZones.deleted',0) 
        ->WHERETIME('timeFrom', '>=', $timeStart)
        ->WHERETIME('timeTo', '<=', $timeEnd)
        ->get();
        return $allTimeWaves;
    }

    /**
     * Создает новую или редактирует запись лимита заказов в timeWavesLimitOrder
     * Вход
     *  data - дата в который нужно установить лимит на волну
     *  timeStart - время начала волны
     *  timeEnd - время окончания волны 
     *  countOrder - лимит заказов 
     */
    public function saveOrderLimit(Request $request)
    {
        if (!$request->date && $request->baseLimit=="false")
            return json_encode(['code'=>404,'msg'=>'Дата не указана'], JSON_UNESCAPED_UNICODE);
        
        $times = explode('-',$request->time);
        $timeStart = $times[0];
        $timeEnd = $times[1];
        
        $timeWaves = TimeWaves::select('timeWaves.id')    
                        ->join('deliveryZones', 'deliveryZones.id', '=', 'timeWaves.zoneId')
                        ->WHERETIME('timeWaves.timeFrom', '=', $timeStart)
                        ->WHERETIME('timeWaves.timeTo', '=', $timeEnd)
                        ->where('timeWaves.deleted',0)
                        ->where('deliveryZones.deleted',0)
                        ->get();


        foreach($timeWaves as $key=>$value)
        if ($request->baseLimit =="false")
            TimeWavesOrderLimit::updateOrCreate(
                ['date' => $request->date,'waveId' => $value->id],
                ['date' => $request->date,'moderatorId' =>  \Auth::guard('admin')->user()->id,'waveId' => $value->id,'countOrder' => $request->countOrder]);
        else 
            TimeWaves::where('id', $value->id)->update(array('orderlimit' => $request->countOrder));

        return json_encode(['code'=>200,'msg'=>'Выполнено'], JSON_UNESCAPED_UNICODE);
    }


    /**
     * Возвращает количество и ограничение по заказам на волны которые будут редактироваться
     */
    public function getEditLimitOrder(Request $request)
    {

        $times = explode('-',$request->time);
        $timeStart = $times[0];
        $timeEnd = $times[1];
        $date= $request->date ? $request->date : Carbon::now()->timezone('Europe/Moscow');

        $val=TimeWaves::select('timeWaves.orderLimit','timeWavesOrderLimit.countOrder')
        ->leftjoin('deliveryZones', 'deliveryZones.id', '=', 'timeWaves.zoneId')
        ->leftjoin('timeWavesOrderLimit', function ($join) use ($date){
            $join->on('timeWavesOrderLimit.waveId', '=', 'timeWaves.id')
                 ->where('timeWavesOrderLimit.date','=', $date);})
        ->where('deliveryZones.deleted',0) 
        ->WHERETIME('timeFrom', '=', $timeStart)
        ->WHERETIME('timeTo', '=', $timeEnd)
        ->get();
        $count = count($val);
        $LimitOrder = [];
        foreach($val as $value)
        {
            if ($value->countOrder && $request->baseLimit=="false")
                $data = $value->countOrder;
            else 
                $data = $value->orderLimit;
            
            if (!in_array($data,$LimitOrder))
                $LimitOrder[]=$data;
        }
        if (!empty($LimitOrder))
            $LimitOrder = implode(';',$LimitOrder);
        return json_encode(['count'=>$count,'limitOrder'=>$LimitOrder], JSON_UNESCAPED_UNICODE); 
    }
}
