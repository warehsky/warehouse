<?php

namespace App\Http\Controllers\Api;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportBasic;
use Illuminate\Http\Request;
use App\Model\ItemsLink;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class ReportController extends Controller
{
    /**
     * Возвращает товар у которого нет тега, но есть акционная цена, и товар у которого нет цены но есть акционный товар
     */
    public function getNoStockTagOrPrice()
    {
        //Если нет тега но есть акционная цена
        $noStockTagSQL="SELECT l.id,l.title,id1c,
        (SELECT a.value from mtagent.prices as a where a.itemId=l.id and a.priceType=32) as price,
        (SELECT a.value from mtagent.prices as a where a.itemId=l.id and a.priceType=16) as stockPrice
        FROM mtShop.itemsLink as l 
        WHERE l.id IN (SELECT m2.id FROM (SELECT k.id as id from mtShop.itemsLink as k  JOIN mtShop.itemTags as h on k.id=h.itemId 
        WHERE h.tagId=330) as m1 RIGHT OUTER JOIN
        (SELECT p.itemId as id FROM mtagent.prices as p WHERE p.areaId=1 and p.priceType=16 and p.value>0) as m2 
        on m1.id=m2.id  WHERE m1.id is NULL) and deleted=0 ";

        $noStockTag=[];

        
        $noStockTag = \DB::connection()->select($noStockTagSQL);

        $resultNoStockTag = array();
        foreach ($noStockTag as $key => $value)
            $resultNoStockTag[$key] = [$value->id,$value->id1c,$value->title,$value->price,$value->stockPrice,"<img src='/img/img/items/small/{$value->id}.png' alt='Изображение не найдено'>"];
        
        $noStockPriceSQL="SELECT l.id,l.title, id1c,
        (SELECT a.value from mtagent.prices as a where a.itemId=l.id and a.priceType=32) as price,
        (SELECT a.value from mtagent.prices as a where a.itemId=l.id and a.priceType=16) as stockPrice
            FROM mtShop.itemsLink as l 
                WHERE l.id IN 
                    (SELECT j.itemId  FROM mtShop.itemTags as j JOIN mtagent.prices as i on j.itemId=i.itemId WHERE j.tagId=330) and deleted=0";
        
        $noStockPrice=[];
        
        $noStockPrice = \DB::connection()->select($noStockPriceSQL);
        $resultNoStockPrice = array();
        foreach ($noStockPrice as $key => $value)
        {
            if (!$value->stockPrice)
                $resultNoStockPrice[] = [$value->id,$value->id1c,$value->title,$value->price,$value->stockPrice,"<img src='/img/img/items/small/{$value->id}.png' alt='Изображение не найдено'>"]; 
        }
        
        return json_encode(["noStockTag" => $resultNoStockTag,"noStockPrice"=>$resultNoStockPrice ], JSON_UNESCAPED_UNICODE );
    }

    /**
     * Возвращает список товаров у которых нет описания
     */
    public function getNoDescr()
    {
        $sql="SELECT id,id1c,title,created_at FROM mtShop.itemsLink WHERE (descr='' or descr is NUll) and deleted=0";
        $noDescr=[];
        $noDescr = \DB::connection()->select($sql);

        $resultNoDescr = array();
        foreach ($noDescr as $key => $value)
            $resultNoDescr[$key] = [$value->id,$value->id1c,$value->title,$value->created_at,'<a href="/admin/items/'.$value->id.'/edit?ReportBack=3" class="btn btn-xs btn-info">Редактировать</a>']; 
        return json_encode(["noDescr" => $resultNoDescr], JSON_UNESCAPED_UNICODE );
    }

    /**
     * Возвращает список товаров у которых нет изображения
     * Входящие параметры 
     * back - указатель, искать все товары без изображения или выдать ошибку при нахождении первого товара без изображения 
     */
    public function noImg($back=0)
    {
        
        $allItems=ItemsLink::select('id','id1c','title','created_at')->where('deleted',0)->get()->toArray();
        $resultNoImg=array();
       
        foreach ($allItems as $el)
        {  
            if(!\Storage::disk('public')->exists('/img/img/items/small/'.$el['id'].'.png'))
            {
                $resultNoImg[]=[$el['id'],$el['id1c'],$el['title'],$el['created_at'],'<a href="/admin/items/'.$el['id'].'/edit?ReportBack=4" class="btn btn-xs btn-info">Редактировать</a>'];
                if ($back)
                    return json_encode(["error"=>true],JSON_UNESCAPED_UNICODE);
            }
        }
        return json_encode(["noImg" => $resultNoImg], JSON_UNESCAPED_UNICODE );
    }


    /**
     * Возвращает товар без цены (32)
     * Входящие параметры
     * delete: 0 - вернуть активные товары
     *         1 - вернуть удаленные товары 
     *         2 - вернуть все товары
     */
    public function getNoPrice($delete)
    { 
            if ($delete==1)
                $str="l.deleted=1 and";
            else
                if ($delete==0)
                    $str="l.deleted=0 and";
                else
                    $str="";
            

        $sql="SELECT l.id,id1c,l.title,l.created_at,l.deleted 
        FROM mtShop.itemsLink as l 
        WHERE  $str (l.id IN (
            SELECT m2.id FROM 
                (SELECT k.itemId as id FROM mtagent.prices as k WHERE k.areaId=1 and k.priceType=32) as m1 
                    RIGHT OUTER JOIN
                (SELECT p.id as id FROM mtShop.itemsLink as p) as m2 
                    on m1.id=m2.id  WHERE m1.id is NULL) or l.id IN (SELECT r.itemId FROM mtagent.prices as r where r.areaId=1 and r.priceType=32 and r.value=0))";
        $noPrice32=[];
        $noPrice32 = \DB::connection()->select($sql);
            
        $resultNoPrice32 = array();
        foreach ($noPrice32 as $key => $value)
            {
                if ($value->deleted)
                    $button = "Восстановить";
                else 
                    $button = "Удалить";

                $resultNoPrice32[$key] = [$value->id,$value->id1c,$value->title,$value->created_at,"<button value='".$value->id."' id='changeDeletedStatus' class='btn btn-xs btn-info'>".$button."</button>"];
            }
        return json_encode(["noPrice" => $resultNoPrice32], JSON_UNESCAPED_UNICODE );
    }

    /**
     * Возвращает массив товаров у которых дисконтное кол-во != (0 или 2000000) и нет дисконтной цены
     */

    public function getNoDiscountPrice()
    {
        $sql="SELECT l.id,id1c,l.title,l.discountBound,l.created_at,
        (SELECT a.value from mtagent.prices as a where a.itemId=l.id and a.areaId=1 and a.priceType=64) as discountPrice,
        (SELECT a.value from mtagent.prices as a where a.itemId=l.id and a.areaId=1 and a.priceType=32) as price
        FROM mtShop.itemsLink as l
        WHERE l.id IN (
        SELECT m2.id FROM
        (SELECT k.itemId as id FROM mtagent.prices as k WHERE k.areaId=1 and k.priceType=64) as m1
        RIGHT OUTER JOIN
        (SELECT p.id as id FROM mtShop.itemsLink as p where p.discountBound!=0 and p.discountBound!=2000000) as m2
        on m1.id=m2.id WHERE m1.id is NULL OR m1.id=0) or l.id IN (SELECT i.itemID FROM mtagent.prices as i
        JOIN mtShop.itemsLink as j ON j.id=i.itemId
        WHERE i.areaId = 1 AND i.priceType = 64 AND i.value = 0 and deleted=0 and (j.discountBound!=0 and j.discountBound!=2000000))";

        $noDiscountPrice=[];
        $noDiscountPrice = \DB::connection()->select($sql);
            
        $resultNoDiscountPrice = array();
        foreach ($noDiscountPrice as $key => $value)
            $resultNoDiscountPrice[$key] = [$value->id,$value->id1c,$value->title,$value->price,$value->discountBound,$value->discountPrice,$value->created_at];

        return json_encode(["noDiscountPrice" => $resultNoDiscountPrice], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Изменяет статус deleted в таблице ItemsLink
     * Входящие данные 
     * id - код товара
     */
    public function changeDelStatus(Request $request)
    {
        if (isset($request->id))
        {
            $item=ItemsLink::find($request->id);
            $item->deleted=!$item->deleted;
            $item->save();
            return json_encode( ['code' => 200, 'mesage' => 'Статус изменен'] );
        }
        return json_encode( ['code' => 404, 'mesage' => 'Не найден id товара'] );
    }
    /**
     * Возвращает товары у которых обычная цена меньше или равна дисконтной или акционной
     */
    public function getDiffPrice()
    {
        $sql="SELECT k.id,id1c, k.title,
        (SELECT a.value from mtagent.prices as a where a.itemId=k.id and a.priceType=32) as price,
        (SELECT a.value from mtagent.prices as a where a.itemId=k.id and a.priceType=64) as discountPrice,
        (SELECT a.value from mtagent.prices as a where a.itemId=k.id and a.priceType=16) as stockPrice,
        k.created_at, k.discountBound
        FROM mtShop.itemsLink as k
        WHERE k.id IN 
        (SELECT i.itemId FROM mtagent.prices as i INNER JOIN mtagent.prices as j ON j.itemId=i.itemId WHERE
            i.areaId=1 and j.areaId=1 and i.priceType=32 and (j.priceType=64 or j.priceType=16) and i.value<=j.value and i.value!=0) 
            AND deleted=0 AND discountBound!=0 AND discountBound!=2000000";
        $diffPrice=[];
        $diffPrice = \DB::connection()->select($sql);
            
        $resultdiffPrice = array();
        foreach ($diffPrice as $key => $value)
            $resultdiffPrice[$key] = [$value->id,$value->id1c,$value->title,$value->price,$value->discountPrice,$value->stockPrice,$value->discountBound,$value->created_at];

        return json_encode(["diffPrice" => $resultdiffPrice], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает массив количества заказов с различных устройств
     */
    public function getCountTypeDevice(Request $request)
    {
        $dateStart='\'2018-01-01 00:00:00\'';
        if ($request->input('dateStart'))
            $dateStart='\''. $request->input('dateStart').' 00:00:00\'';

        $dateEnd='CURDATE()';
        if ($request->input('dateEnd'))
            $dateEnd='\''. $request->input('dateEnd').' 23:59:59\'';

            $countOrders = collect(\DB::connection()->select("SELECT dt.id,dt.title,count(o.id) as count FROM deviceType as dt 
                                                                LEFT JOIN orders as o on o.deviceType=dt.id 
                                                                WHERE o.status!=7 AND created_at BETWEEN $dateStart AND $dateEnd AND o.deviceType!=1 
                                                                GROUP BY dt.id"));
           
            $browserSQL="SELECT deviceInfo  FROM `orders` WHERE deviceType=1 AND status!=7 AND created_at BETWEEN $dateStart AND $dateEnd";
            $browser = \DB::connection()->select($browserSQL);

            $countPC=0;
            $countMobile=0;
            foreach($browser as $value)
            {
                if ($value->deviceInfo[10]=='1')
                    $countPC++;
                else 
                    $countMobile++;
            }

            $countAdmin = $countOrders->where('id',2)->values()[0]->count;
            $countAPK = $countOrders->where('id',3)->values()[0]->count;
            $countiOS = $countOrders->where('id',4)->values()[0]->count;

            $sum=$countPC+$countMobile+$countAdmin+$countAPK+$countiOS;
            $result=[];
            if ($sum!=0)
            {
                $result[]=['Сайт (комп.)', $countPC, round($countPC/$sum*100, 1)];
                $result[]=['Сайт (моб.)', $countMobile, round($countMobile/$sum*100, 1)];
                $result[]=[$countOrders->where('id',2)->values()[0]->title, $countAdmin, round($countAdmin/$sum*100, 1)];
                $result[]=[$countOrders->where('id',3)->values()[0]->title, $countAPK, round($countAPK/$sum*100, 1)];
                $result[]=[$countOrders->where('id',4)->values()[0]->title, $countiOS, round($countiOS/$sum*100, 1)];
            } 
            return json_encode(["countDeviceType" => $result], JSON_UNESCAPED_UNICODE );
    }

    /**
     * 
     */
    public function getWebUsersSomeOrders(Request $request)
    {
        $period = 30;
        $dateStart='\'2021-03-01 00:00:00\'';
        if ($request->input('dateStart'))
            $dateStart='\''. $request->input('dateStart').' 00:00:00\'';

        $dateEnd='CURDATE()';
        if ($request->input('dateEnd'))
            $dateEnd='\''. $request->input('dateEnd').' 23:59:59\'';
        $periodStart = "'".Carbon::createFromFormat("Y-m-d H:i:s", trim($dateStart, "'"))->timezone('Europe/Moscow')->subDays($period)->startOfDay()->format("Y-m-d H:i:s")."'";
        $periodEnd = $dateStart;
        if ($request->input('maxCountOrder')!=null)
        {
            $sql = "SELECT id as webUserId, i.phone, i.userName, (SELECT COUNT(id) FROM orders as j  
            WHERE j.webUserId=i.id AND j.created_at BETWEEN $dateStart AND $dateEnd) as counts From webUsers as i WHERE 
            (SELECT COUNT(id) FROM orders as j WHERE j.webUserId=i.id AND j.created_at BETWEEN $periodStart AND $periodEnd)>0 and 
             (SELECT COUNT(id) FROM orders as j WHERE j.webUserId=i.id AND j.created_at BETWEEN $dateStart AND $dateEnd)<=".$request->input('maxCountOrder');
            $firstOrder = \DB::connection()->select($sql);

            $resultfirstOrder = array();
            foreach ($firstOrder as $key => $value)
                $resultfirstOrder[] = [$value->webUserId,$value->phone,$value->userName,$value->counts];
            return json_encode(["resultfirstOrder" => $resultfirstOrder], JSON_UNESCAPED_UNICODE );
        }
       
    }

    /**
     * Возвращает информацию по заказам (сумма за день/период)
     */
    public function infoOrders(Request $request)
    {
        $dateStart='\'2018-01-01 00:00:00\'';
        if ($request->input('dateStart'))
            $dateStart='\''. $request->input('dateStart').' 00:00:00\'';

        $dateEnd='CURDATE()';
        if ($request->input('dateEnd'))
            $dateEnd='\''. $request->input('dateEnd').' 23:59:59\'';

        $sql="SELECT SUM(sum_total-bonus_pay-((discount_proc/100)*sum_total)) as sumForDay, count(created_at) as countOrder,DATE_FORMAT(created_at, '%d.%m.%Y') as date 
                from mtShop.orders where status=4 and created_at BETWEEN $dateStart AND $dateEnd 
                GROUP BY DATE_FORMAT(created_at, '%Y.%m.%d')";    
        $sumOrderForDay = \DB::connection()->select($sql);
        $resultForPeriod=['sumOrders'=>0, 'countOrders'=>0];
        foreach($sumOrderForDay as $item)
        {
            $resultForPeriod['sumOrders']+=$item->sumForDay;
            $resultForPeriod['countOrders']+=$item->countOrder;
        }
        return json_encode(["sumOrderForDay" => $sumOrderForDay,'resultForPeriod' => $resultForPeriod], JSON_UNESCAPED_UNICODE );
    }

    /**
     * Возвращает информацию о кликах по компонентам на сайте
     */
    public function infoSite(Request $request)
    {
        $dateStart='\'2018-01-01 00:00:00\'';
        if ($request->input('dateStart'))
            $dateStart='\''. $request->input('dateStart').' 00:00:00\'';

        $dateEnd='CURDATE()';
        if ($request->input('dateEnd'))
            $dateEnd='\''. $request->input('dateEnd').' 23:59:59\'';
        
        $strWhere ='';
        if ($request->input('idClickObject'))
        {
            $ids=implode(",", $request->idClickObject);
            $strWhere = "AND j.id NOT IN ($ids)";
        }
        $sql="SELECT j.title,Sum(i.value) as count FROM clickObject as j
        JOIN  clickInfo as i ON j.id=i.objectId
        WHERE i.created_at>=$dateStart AND i.created_at<=$dateEnd AND j.id!=13 $strWhere
        GROUP BY j.id ORDER BY count DESC";    
        $sumCountClickSite = \DB::connection()->select($sql);
        return json_encode(["sumCountClickSite" => $sumCountClickSite], JSON_UNESCAPED_UNICODE );
    }
    
    public function getClickObject()
    {
        $sql="SELECT * FROM clickObject WHERE id!=13";    
        $clickObject = \DB::connection()->select($sql);
        return json_encode(["clickObject" => $clickObject], JSON_UNESCAPED_UNICODE );
    }


    /**
     * Возвращает кол-во всех ошибок для индикатора в админке
     */
    public function allReport()
    {
        $val=json_decode($this->getNoStockTagOrPrice());
        $allError=[];
        if (count($val->noStockTag) || count($val->noStockPrice))
            $allError[0]=count($val->noStockTag)+count($val->noStockPrice);
        else   
            $allError[0]=0;

        $val=json_decode($this->getNoPrice(0));
        $allError[1]=count($val->noPrice);

        $val=json_decode($this->getNoDiscountPrice());
        $allError[2]=count($val->noDiscountPrice);
            
        $val=json_decode($this->getNoDescr());
        $allError[3]=count($val->noDescr);

        $val=json_decode($this->noImg(1));
        if ($val->error)
            $allError[4]=1;
        else 
            $allError[4]=0;
        
        $val=json_decode($this->getDiffPrice());
        $allError[5]=count($val->diffPrice);
  
        foreach($allError as $el)
            if($allError)     
                return json_encode(['status'=>false,'ReportErrors'=>$allError], JSON_UNESCAPED_UNICODE);    
        return json_encode(['status'=>true,'ReportErrors'=>$allError], JSON_UNESCAPED_UNICODE);      
    }

    public function checkExcelOrder(Request $request)
    {
        $data = \Excel::toArray('', $request->file)[0]; //Получение данных с файла
        foreach ($data as $key=>$value) //Получение только значений
        {
            $data[$key] = array_slice($value,0,3);
            if ($data[$key][0]==null)
                unset($data[$key]);
        }

        $ids = ItemsLink::select('id1c')->get()->toArray(); //Выборка всех id из базы
        $ids = array_column($ids,'id1c');
        // $newIds = array_column($data,0); // Получение только id
        foreach ($data as $key=>$value)
        {
            unset($data[$key]);
            if (in_array($value[0],$ids))
                continue;
            $data[$key]=[$value[0],$value[1],$value[2]];
        }
        $data = collect(array_values($data));
        return json_encode(['checkExcelLoseItem'=>$data], JSON_UNESCAPED_UNICODE);
    }

}
