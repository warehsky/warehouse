<?php

namespace App\Http\Controllers\Admin;

use App\Model\Backlink;
use App\Model\PhoneNumber;
use App\Model\WebUsers;
use App\Model\phoneNumberBackLink;
use App\Model\PhoneNumberOrderInfo;
use App\Model\PhoneNumberStatistic;
use Illuminate\Http\Request;
use App\Exports\ExportBasic;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Model\Orders;
use Illuminate\Database\Eloquent\Collection;

class PhoneNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('phoneNumber_all')) {
            return redirect()->route('home');
        }
        $allPhone=PhoneNumber::leftjoin('phoneNumberSource', 'phoneNumber.source', '=', 'phoneNumberSource.id')
                ->select('phoneNumber.phone','phoneNumber.name','phoneNumberSource.title as source','phoneNumber.unsubscribe')
                ->get();
        return view('Admin.phoneNumber.index',compact('allPhone'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! \Auth::guard('admin')->user()->can('phoneNumber_all')) {
            return redirect()->route('home');
        }
        return view('Admin.phoneNumber.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
               $this->validate($request, [
            'phone' => 'required|numeric|unique:phoneNumber,phone',
            'name' => 'required']);

            $addNewPhone = new PhoneNumber;
            $addNewPhone->phone=$request->phone;
            $addNewPhone->name=$request->name;
            $addNewPhone->source=1;
            if ($request->unsubscribe)
            $addNewPhone->unsubscribe=1;
            else
            $addNewPhone->unsubscribe=0;
            
            $addNewPhone->save();
            
            return redirect()->route('phoneNumber.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! \Auth::guard('admin')->user()->can('phoneNumber_all')) {
            return redirect()->route('home');
        }
        $EditPhone=PhoneNumber::find($id);
        return view('Admin.phoneNumber.edit',compact('EditPhone'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, ['name' => 'required', 'phone'=>'required']);

        if($request['unsubscribe']!=null) $status=1; else $status=0;

        $item=PhoneNumber::find($id);
        $item->update(['name'=>$request['name'], 'phone'=>$request['phone'], 'unsubscribe'=>$status]);

        return redirect()->route('phoneNumber.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! \Auth::guard('admin')->user()->can('phoneNumber_all')) {
            return redirect()->route('home');
        }
        PhoneNumber::find($id)->delete();
        return redirect()->route('phoneNumber.index');
    }

    public function changeUnsubscribe($id)
    {
        if (! \Auth::guard('admin')->user()->can('phoneNumber_all')) {
            return redirect()->route('home');
        }
        $fin=PhoneNumber::find($id);
        $fin->unsubscribe=!$fin->unsubscribe;
        $fin->save();
        return redirect('/admin/phoneNumber');
    }

    public function updateDataNumbers()
    {
        if (! \Auth::guard('admin')->user()->can('phoneNumber_all')) {
            return redirect()->route('home');
        }
        $contactPhone=Backlink::select('phone','name')->get()->unique('phone'); //приориетет на имя
        $webUserPhone=WebUsers::select('phone','userName as name')->get();
        $allPhone=PhoneNumber::select('phone','name')->get();

        foreach($contactPhone as $key)
        {
            $key->phone=str_replace([')','(','-',' '],'',$key->phone);
        }

        $mergePhone=collect($contactPhone)->merge($webUserPhone)->unique('phone');

        foreach($mergePhone as $key)
        {
            $addNewPhone=new PhoneNumber;
            if($allPhone->contains($key['phone']))
            {
                if($key['name']!=null)
                {
                    $all=PhoneNumber::where('phone',$key['phone']);
                    $all->update(['name'=>$key['name']]);   
                }     
            }
            else 
            {
                if ($key['name']!=null){
                    $addNewPhone->name=$key['name'];}
                $addNewPhone->phone=$key['phone'];
                $addNewPhone->source=$key->getSource();
                $addNewPhone->unsubscribe=true;
                $addNewPhone->save();
            }    
        }
        return redirect('/admin/phoneNumber');
    }

    
    public function massDestroy(Request $request)
    {
        PhoneNumber::whereIn('phone', request('ids'))->delete();
        return back();
    }
    

    public function FilterIndex(Request $request)
    {
        if ($request->code==0 && $request->subscribe==2)
        return redirect('admin\phoneNumber');

        $allPhone=PhoneNumber::leftjoin('phoneNumberSource', 'phoneNumber.source', '=', 'phoneNumberSource.id')
        ->select('phoneNumber.phone','phoneNumber.name','phoneNumberSource.title as source','phoneNumber.unsubscribe')
        ->where(function($query) use ($request)
        {   
            switch ($request->code) {
                case 1:
                    //номер рф
                    $query->where("phone", "LIKE", '%+7%');
                    break;
                case 2:
                    //номер феникс
                    $query->where("phone", "LIKE", '%+38071%');
                    break;
                case 3:
                    //номер мтс
                    $query->where("phone", "LIKE", '%+38066%') 
                    ->orWhere('phone', 'LIKE', '%+38050%')
                    ->orWhere('phone', 'LIKE', '%+38099%')
                    ->orWhere('phone', 'LIKE', '%+38095%');
                    break;
                case 4:
                    //городской номер
                    $query->where("phone", "LIKE", '%+38062%');
                    break;
            }
            

        })
        ->where(function($querySubscribe) use ($request)
        {
            if ($request->subscribe!=2)
            {
                $querySubscribe->where('unsubscribe',$request->subscribe);
            }

        })
        ->get();
        return view('Admin.phoneNumber.index',compact('allPhone'));
    }


    //Получение коллекции данных для статистики звонков
    private function getStaticticData($start_date,$end_date,$sortingMethod,$phone)
    {
        $strWherePhoneI='';
        $strWherePhoneK='';
        if ($phone)
        {
            $strWherePhoneI="AND i.phone LIKE '%$phone%'";
            $strWherePhoneK="AND k.phone LIKE '%$phone%'";
        }
        $sql="SELECT   
                    i.id as code,
                    i.created_at,
                    '' as time,
                    i.name,
                    i.phone,
                    i.deviceType,
                    concat(DATE_FORMAT(i.deliveryDate, '%d.%m.%Y'),' ',DATE_FORMAT(k.timeFrom, '%H.%i'),'-',DATE_FORMAT(k.timeTo, '%H.%i'),' [#',k.id,']' ) as deliveryDate,
                    j.title as status,
                    i.deviceInfo, '' as id
        FROM orders as i
        JOIN mtShop.statuses as j ON j.id = i.status
        JOIN mtShop.timeWaves as k ON i.waveId = k.id
        WHERE i.created_at>='$start_date' AND i.created_at<='$end_date' AND i.status!=7 $strWherePhoneI
        UNION
        SELECT '',k.created_at,'',k.name,k.phone,k.descr as deviceType,'', '','',k.id FROM mtShop.phoneNumberStatistic as k
        WHERE k.created_at>='$start_date' AND k.created_at<='$end_date' $strWherePhoneK
        Order by created_at $sortingMethod";
        $data=collect(\DB::select($sql));
        foreach($data as $key=>$value)
        {
            switch ($value->deviceType) {
                case 1:
                    if ($value->deviceInfo[10]=='1')
                    $data[$key]->deviceType='Оформлен заказ на сайте с телефона';
                else 
                    $data[$key]->deviceType='Оформлен заказ на сайте с компьютера';
                    break;
                case 2:
                    $data[$key]->deviceType='Оформлен заказ через звонок на 377';
                    break;
                case 3:
                    $data[$key]->deviceType='Оформлен заказ с Android приложения';
                    break;
                case 4:
                    $data[$key]->deviceType='Оформлен заказ с iOS приложения';
                    break;
            }
            unset($data[$key]->deviceInfo);
        }
        return $data;
    }

    //Экспорт статистики звонков в excel
    public function exportPhoneNumberStatistic(Request $request)
    {
        $startDate = $request->dateStart ? $request->dateStart.' 00:00:00' : '2021-01-01 00:00:00';
        $endDate = $request->dateEnd ? $request->dateEnd.' 23:59:59' : Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $data=$this->getStaticticData($startDate,$endDate,'asc','');

        

        foreach ($data as $key=>$value)
        {
            $data[$key]->time=substr($value->created_at, 11,5);
            $data[$key]->created_at=substr($value->created_at, 8,2).'.'.substr($value->created_at, 5,2).'.'.substr($value->created_at, 0,4);
            unset($data[$key]->id);
        }
        $countOrder = \DB::select("SELECT count(i.id) as id FROM orders as i 
                                    WHERE i.created_at<'$startDate' and i.status!=7");
        //dd($countOrder);
        $countCall = \DB::select("SELECT count(i.id) as id FROM phoneNumberStatistic as i 
                                    WHERE i.created_at<'$startDate'");
            
        $count=$countCall[0]->id+$countOrder[0]->id;
        $dataDate=$data->unique('created_at');
        $newData = new Collection();
        foreach ($dataDate as $value)
        {
            $newCollection=$data->whereIn('created_at',$value->created_at);
            $i=1;
            foreach($newCollection as $k=>$val)
            {
                $newCollection[$k]->code=$i;
                $i++;
                
            }
            $count+=$newCollection->count();
            $newCollection->push(['code'=>$count]);
            
            $newData=collect($newData)->merge($newCollection);
        }
        $newData=$newData->prepend(new Collection(['№ п/п','Дата','Время','Имя','Контактный номер','Примечание','Дата доставки','Итог']));
        
        return Excel::download(new ExportBasic($newData), 'Статистика звонков.xlsx');
    }

    //Запрос данных из функции getStaticticData и формирования массива с пагинацией для страницы
    public function getPhoneNumberStatistic(Request $request)
    {
        $startDate = $request->dateStart ? $request->dateStart.' 00:00:00' : '2021-01-01 00:00:00';
        $endDate = $request->dateEnd ? $request->dateEnd.' 23:59:59' : Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $sortingMethod = $request->sortingMethod ? $request->sortingMethod : 'desc';
        $data=$this->getStaticticData($startDate,$endDate,$sortingMethod,$request->phone);
        $data=$data->toArray();
        $items = $this->arrayPaginatorForManyPath($data, $request);
        return response()->json([array_values($items->items()), 'links' => $items->links()->toHtml()]);
    }

    //Создание пагинации для массива
    private function arrayPaginatorForManyPath($array, $request)
    {
        $page = $request->input('page') ?? 1;
        $perPage = 30;
        $offset = ($page * $perPage) - $perPage;
        return new  \Illuminate\Pagination\LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->path, 'query' => $request->query()]);
    }

    //index для страницы Статистика звонков 
    public function phoneNumberStatistic()
    {
        $nowTime=Carbon::now()->timezone('Europe/Moscow');
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.phoneNumber.statistic', compact('api_token','nowTime'));
    }

    //Обновление записи в дополнительной таблицы phoneNumberStatistic 
    public function updatePhoneNumberStatistic(Request $request)
    {
        $data=PhoneNumberStatistic::find($request->id);
        $data->descr=$request->descr;
        $data->phone=$request->phone;
        $data->name=$request->name;
        $data->created_at=$request->date;
        $data->save();
        return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE);
    }

    //Создание записи в дополнительной таблицы phoneNumberStatistic 
    public function createPhoneNumberStatistic(Request $request)
    {
        if (!$request->descr)
            return json_encode(['code' => 404], JSON_UNESCAPED_UNICODE);
        if (!$request->phone)
            return json_encode(['code' => 404], JSON_UNESCAPED_UNICODE);
        if (!$request->name)
            return json_encode(['code' => 404], JSON_UNESCAPED_UNICODE);
        if ($request->date)
            $date = $request->date.':00';
        else 
            $date = Carbon::now()->timezone('Europe/Moscow');

        $data = new PhoneNumberStatistic;
        $data->descr=$request->descr;
        $data->phone=$request->phone;
        $data->name=$request->name;
        $data->created_at=$date;
        $data->save();

        return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE);
    }

    //Удаление записи в дополнительной таблицы phoneNumberStatistic 
    public function deletePhoneNumberStatistic(Request $request)
    {
        PhoneNumberStatistic::where('id',$request->id)->delete();
        return json_encode(['code'=>200,'msg'=>'Удалено'], JSON_UNESCAPED_UNICODE);            
    }

    //Создание комментария в дополнительной таблицы phoneNumberOrderInfo (Под удаление)
    public function addOrderInfo(Request $request)
    {
        $data = PhoneNumberOrderInfo::where('orderId',$request->orderId)->First();
        if (!$data)
        {
            $data = new PhoneNumberOrderInfo;
            $data->orderId=$request->orderId;
        }
        $data->comment=$request->comment;
        $data->source=$request->source;
        $data->save();

        return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE);
    }


    ////
    ////Обратная связь
    ////

    

    //Index для страницы Обратная связь 377
    public function backLinkPhoneIndex()
    {
        $nowTime=Carbon::yesterday()->timezone('Europe/Moscow');
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.phoneNumber.backLinkPhone', compact('api_token','nowTime'));
    }

    //Получение коллекции данных для обратной связи 377
    private function getBackLinkData($start_date,$end_date,$sortingMethod,$phone)
    {
        $strWherePhone='';
        if ($phone)
        {
            $strWherePhone="AND i.phone LIKE '%$phone%'";
        }

        $sql = "SELECT '' as code,i.id,i.deliveryDate as created_at,i.name,i.phone,j.comment,j.recall,i.webUserId,k.note,j.source,
                (SELECT count(id) as count FROM orders WHERE webUserId = k.id) as count,j.guilty as guilty,g.title as title   
                FROM orders as i 
                LEFT JOIN phoneNumberBackLink as j ON j.orderId=i.id
                LEFT JOIN webUsers as k ON k.id=i.webUserId
                LEFT JOIN phoneNumberGuilty as g ON g.id = j.guilty
                WHERE i.deliveryDate>='$start_date' AND i.deliveryDate<='$end_date' AND i.status=4  $strWherePhone
                Order by i.deliveryDate $sortingMethod";
        $data=collect(\DB::select($sql));

        return $data;
    }

    //Запрос данных из функции getBackLinkData и формирования массива с пагинацией для страницы
    public function getPhoneNumberBackLink(Request $request)
    {
        $startDate = $request->dateStart ? $request->dateStart.' 00:00:00' : '2021-01-01 00:00:00';
        $endDate = $request->dateEnd ? $request->dateEnd.' 23:59:59' : Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $sortingMethod = $request->sortingMethod ? $request->sortingMethod : 'desc';

        $data=$this->getBackLinkData($startDate,$endDate,$sortingMethod,$request->phone);
        $data=$data->toArray();

        $items = $this->arrayPaginatorForManyPath($data, $request);

        $sql = "SELECT webUserId,note FROM webUsersNote WHERE status=1 ";
        $dataNote=\DB::select($sql);

        $sql = "SELECT id,title as guilty FROM phoneNumberGuilty";
        $guiltyOption = \DB::select($sql);

        return response()->json([array_values($items->items()), 'links' => $items->links()->toHtml(), $dataNote,$guiltyOption]);
    }

    //Создание записи в дополнительной таблицы phoneNumberBackLink
    public function addPhoneNumberBackLink(Request $request)
    {
        $data = phoneNumberBackLink::where('orderId',$request->orderId)->First();
        if (!$data)
        {
            $data = new PhoneNumberBackLink;
            $data->orderId=$request->orderId;
        }
        $data->comment=$request->comment;
        $data->source=$request->source;
        $data->recall=$request->recall;
        $data->guilty=$request->guilty;
        $data->save();

        return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE);
    }

    //Экспорт статистики звонков в excel
    public function exportPhoneNumberBackLink(Request $request)
    {
        $startDate = $request->dateStart ? $request->dateStart.' 00:00:00' : '2021-01-01 00:00:00';
        $endDate = $request->dateEnd ? $request->dateEnd.' 23:59:59' : Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $data=$this->getBackLinkData($startDate,$endDate,'asc','');
        foreach ($data as $key=>$value)
        {
            $data[$key]->created_at=substr($value->created_at, 8,2).'.'.substr($value->created_at, 5,2).'.'.substr($value->created_at, 0,4);
            if ($value->recall==1)
                $data[$key]->recall='Положительный';
            else 
                if($value->recall==2)
                    $data[$key]->recall='Не отвечает';
                else 
                    if ($value->recall==0)
                        $data[$key]->recall='Отрицательный';
                    else
                        $data[$key]->recall=' ';
                        
            unset($data[$key]->webUserId);
            unset($data[$key]->guilty);
            unset($data[$key]->note);
        }

        $dataDate=$data->unique('created_at');
        $newData = new Collection();
        foreach ($dataDate as $value)
        {
            $newCollection=$data->whereIn('created_at',$value->created_at);
            $i=1;
            foreach($newCollection as $k=>$val)
            {
                $newCollection[$k]->code=$i;
                $i++;
                if (!$newCollection[$k]->source && $newCollection[$k]->count>1)
                    $newCollection[$k]->source='Повторный заказ';
                unset($newCollection[$k]->count);
            }
            $newCollection->push(['code'=>' ']);
            $newData=collect($newData)->merge($newCollection);
        }
        $newData=$newData->prepend(new Collection(['№ п/п','№ Заказа','Дата доставки','Имя','Контактный номер','Комментарий','Отзыв','Источник','Виновный']));
        return Excel::download(new ExportBasic($newData), 'Обратная связь 377.xlsx');
    }

}
