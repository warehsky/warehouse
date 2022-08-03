<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WebUsers;
use App\Model\WebUsersDiscount;
use App\Model\WebUsersDiscountType;
use App\Model\WebUsersSendSms;
use App\Http\Controllers\BaseController;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;


class PromocodeController extends BaseController
{
    public function index(Request $request)
    {
        
        if (!\Auth::guard('admin')->user()->can('Promocode_view')) {
            return redirect()->route('home');
        }
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.promocode.index',compact('api_token'));
    }


    public function getAllPromocode(Request $request)
    {
        $now = Carbon::now()->timezone('Europe/Moscow')->startOfDay();
        $end = Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $typeCard= (int) $request->selectType ?? 0;
        $typeStatus = (int) $request->selectStatus ?? 0;
        $strWhere="";
        $strJoin = "k.id = i.webUserId";
        switch ($typeCard)
        {    
            case 0: $strWhere.="WHERE i.webUserId!=0 AND i.type=2"; break;
            case 1: $strWhere.="WHERE i.webUserId=0 AND i.friendId=0 AND i.type=2"; break;
            case 2: $strWhere.="WHERE i.friendId!=0 AND i.type=2"; $strJoin="k.id=i.friendId"; break;
            case 3: $strWhere.="WHERE i.type=1"; break;
            case 4: $strWhere.="WHERE i.id!=0"; break;
            case 5: $strWhere.="WHERE i.type=3"; break;
            default: $strWhere.="WHERE i.webUserId!=0 AND i.type!=1"; break;
        }

        switch ($typeStatus)
        {
            case 0: $strWhere.=" AND i.status>0 AND i.orderId=0 AND i.expiration>='$now' AND i.startValidity<='$end'"; break;
            case 1: $strWhere.=" AND i.status>0 AND i.orderId=0 AND i.expiration>='$now' AND i.startValidity>'$now'"; break;
            case 2: $strWhere.=" AND i.orderId!=0"; break;
            case 3: $strWhere.=" AND i.status=0"; break;
            case 4: $strWhere.=" AND i.orderId=0 AND i.expiration<='$now' AND i.status>0"; break;
            case 5: $strWhere.=""; break;
            default: $strWhere.=""; break;
        }

        if ($typeCard==2 && $typeStatus==2)
        {
            $sql = "SELECT i.discountId FROM orders as i 
            JOIN webUsersDiscount AS j ON j.id=i.discountId
            WHERE i.discountId!=0 AND j.friendId!=0
            GROUP BY i.discountId";
            $orderSum = \DB::Connection()->select($sql); 
            $discountId = implode(',',array_column($orderSum,'discountId'));
            $strWhere = "WHERE i.id IN ($discountId)";
        }
        $dateStart=0;
        $dateEnd=0;
        if ($request->dateStart)
        {   
            $dateStart = $request->dateStart;
            $strWhere.=" AND i.startValidity >= '{$dateStart}'";
        }
        
        if ($request->dateEnd)
        {
            $dateEnd = $request->dateEnd;
            $strWhere.=" AND i.startValidity <= '{$dateEnd}'";
        }

       

        $sql = "SELECT i.*,j.title as type,k.phone,SUM(o.sum_last-o.bonus_pay-((o.discount_proc/100)*o.sum_last)) as sumOrder
                FROM mtShop.webUsersDiscount as i 
                JOIN mtShop.webUsersDiscountType as j on i.type=j.id
                LEFT JOIN mtShop.webUsers as k on $strJoin
                LEFT JOIN mtShop.orders as o ON i.id=o.discountId
                $strWhere GROUP BY i.id,k.phone ORDER BY i.expiration DESC";
        $promocode = \DB::connection()->select($sql);
        $sum = 0;

        foreach($promocode as $value)
        {
            $sum+=$value->sumOrder;
        }
        $sum = round($sum, 2);
        //dd(['promocode' => $promocode[0],'sum'=>$sum,'now'=>$now,'end'=>$end], JSON_UNESCAPED_UNICODE);
        return json_encode(['promocode' => $promocode,'sum'=>$sum,'now'=>$now,'end'=>$end], JSON_UNESCAPED_UNICODE);
        //return view('Admin.promocode.index',compact('promocode','typeCard','dateStart','dateEnd','sum','typeStatus','now','end'));
    }

    /**
     * Вывод страницы создания промокодов
     */
    public function createPage()
    {
        return view('Admin.promocode.create');
    }

    /**
     * Вывод страницы создания множества дисконтных карт
     */
    public function createManyCard()
    {
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.promocode.createManyCard',compact('api_token'));
    }

    /**
     * Массовое создание промокодов
     */
    public function addManyDiscountCart(Request $request)
    {
        $phoneNotInBase=[];
        $ids=[];
        if ($request->idNotInBase)
            $phoneNotInBase = array_map('intval', explode(',', $request->idNotInBase));
        if ($request->id)
            $ids = array_map('intval', explode(',', $request->id));
        foreach($phoneNotInBase as $phone)
            $ids[] = \DB::table('webUsers')->insertGetId(['phone' => '+'.$phone, 'code' => 0, 'orderId' => 0]);

        $count = count($ids);
        $friend=false;
        if (isset($request->friendPromocode))
            $friend=true;
        $func=$this->generateDiscountCart($ids,$count,$request->discount,$request->type,$request->startValidity,$request->expiration,$request->title,$friend);
        if ($func['code']==200)
        {
            if (isset($request->sendSms))
            {
                $this->sendMassSms($ids,$request->smsMsg,$friend);
            }
            return redirect()->route('promocode.createManyCard')->with('success', "Создан промокод(ы)");
        }
        else 
            if ($func['code']==500)
                return redirect()->route('promocode.createManyCard')->with('error', "Ошибка при указании даты"); 
            else 
                return redirect()->route('promocode.createManyCard')->with('error', "Не указан размер скидки");
 
    }


    /**
     * Создание промокода(ов)
     */
    public function createPromocode(Request $request)
    {
        $func=$this->generateDiscountCart([0],$request->count,$request->discount,$request->type,$request->startValidity,$request->expiration,$request->title,false);
        if ($func['code']==200)
            return redirect()->route('promocode.pageCreate')->with('success', "Создан промокод(ы)");
        else 
            if ($func['code']==500)
                return redirect()->route('promocode.pageCreate')->with('error', "Ошибка при указании даты"); 
            else 
                return redirect()->route('promocode.pageCreate')->with('error', "Не указан размер скидки");
    }

    /**
     * Возвращает все типы для промокодов
     */
    public function showDiscountType()
    {
        $promocodeType = \DB::connection()->select("SELECT * FROM mtShop.webUsersDiscountType");
        return response()->json($promocodeType);
    }

    /**
     * Редактирование названия типа промокода
     */
    public function editDiscountType(Request $request)
    {
        if ($request->id)
        {
            $type=WebUsersDiscountType::find($request->id);
            $type->title=$request->title;
            $type->save();
            return json_encode(['code' => 200, 'mesage' => 'Изменено']);
        }
        return json_encode(['code' => 404, 'mesage' => 'Нет id']);
    }

    /**
     * Удаление типа промокода
     */
    public function deleteDiscountType(Request $request)
    {
        if ($request->id)
        {
            WebUsersDiscountType::find($request->id)->delete();
            return json_encode(['code' => 200, 'mesage' => 'Удалено']);
        }
        return json_encode(['code' => 404, 'mesage' => 'Нет id']);
    }

    /**
     * Создание нового типа промокода
     */
    public function addDiscountType(Request $request)
    {
        if ($request->title)
        {
            $type=new WebUsersDiscountType;
            $type->title=$request->title;
            $type->save();

            return json_encode(['code' => 200, 'mesage' => 'Добавлено']);
        }
        return json_encode(['code' => 404, 'mesage' => 'Нет названия типа']);  
    }

    /**
     * Продливает время действия промокода
     * Входящие параметры
     * id - id промокода
     * newDate - новое время
     */
    public function extendPromocode(Request $request)
    {
        if ($request->id && $request->newDate)
        {
            $promo=WebUsersDiscount::find($request->id);
            if ($request->newDate>$promo->expiration)
            {
                $promo->expiration=$request->newDate;
                $promo->save();
                return json_encode(['code' => 200, 'mesage' => 'Изменено']);
            }
            else
                return json_encode(['code' => 500, 'mesage' => 'Введенная дата раньше или равна существующей']);
        }
        return json_encode(['code' => 404, 'mesage' => 'Нет id или времени']);
    }


    public function UploadFileExcel(Request $request)
    {
        $data = \Excel::toArray('', $request->file)[0];

        $column = isset($request->column) ? $request->column-1 : 0;
      
        $data = array_column($data,$column);

        $lineFirst = isset($request->lineFirst) ? $request->lineFirst : 1;

        $lineLast = isset($request->lineLast) ? $request->lineLast : count($data);

        $data = array_slice($data, $lineFirst-1, ($lineLast - ($lineFirst-1)));
       
        foreach ($data as $key=>$row) {
            $pos = mb_strpos($row, '+');
            if (is_int($pos))
            {
                for ($i = $pos+1; $i <= mb_strlen($row); $i++) {
                    if (!ctype_digit(mb_substr($row,$i,1)))
                    {
                        $data[$key]=mb_substr($row, $pos, $i-$pos); 
                        break;
                    }
                }
            }
            else
                unset($data[$key]);
        }
        $webUser=[];
        $webUserNotInBase=[];
        $k=0;
       
        foreach($data as $value)
        {
            $webUserId=WebUsers::select('id')->where('phone','LIKE',"%$value%")->get()->toArray();
            if(!empty($webUserId))
                $webUser[]=array('id'=>(string)$webUserId[0]['id'],'phone'=>$value);
            else   
            {
                $webUserNotInBase[]=array('id'=>(string)$k,'phone'=>$value);;
                $k++;
            }
        }
        return response()->json([$webUser,$webUserNotInBase]);
    }

    /*
    Возвращает id и номера всех пользователей сделавших заказ за последний месяц
    */
    public function activeWebUsers()
    {
        $date=Carbon::now()->timezone('Europe/Moscow')->subDays(31)->startOfDay();
        $data=\DB::connection()->select("SELECT DISTINCT phone,webUserId FROM orders 
        WHERE status=4 AND created_at>='$date'");
        $webUser=[];
        foreach($data as $value)
        {
                $webUser[]=array('id'=>(string)$value->webUserId,'phone'=>$value->phone);
        }
        return response()->json($webUser);
    }


     /**
     *  Создает дисконтную карту 
     *  Входящие параметры: 
     *  id - (int) код пользователя (
     *      Если создается дисконтная карта то записывается в поле webUserId, 
     *      если промокод по акции "Приведи друга то в поле friendId")
     *  discount (int) - процент скидки
     *  startValidity (date) - дата начала действия карты
     *  expiration (date) - дата окончания действия карты 
     *  friend (int) - флаг который показывает какую карту нужно создавать
     *      (0 - обычная дисконтная карта; 1 - акция "Приведи друга")
     *  msgSms (string) - строка которая будет отправлена человеку id которого был указан
     *      (Если передать пустую строку, отправки сообщения не будет) 
     */
    
    public function createDiscountCart($id,$discount,$startValidity,$expiration,$friend=0,$msgSms)
    {
        if (!$id) return ['code' => 500, 'msg' => 'Нет id'];
        if (!$discount || $discount<=0) return ['code' => 501, 'msg' => 'Нет процента скидки'];
        if (!$expiration || !$startValidity) return ['code' => 502, 'msg' => 'Ошибка даты'];

        $func=$this->generateDiscountCart([$id],1,$discount,2,$startValidity,$expiration,'',$friend);
        if ($func['code']==200)
        {
            if ($msgSms)
                $this->sendMassSms([$id],$msgSms,$friend); 
            return ['code' => 200];
        }
        else 
            if ($func['code']==500)
                return ['code' => 503, 'msg' => 'Ошибка при создании'];
    }


}