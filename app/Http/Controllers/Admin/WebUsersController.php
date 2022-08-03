<?php

namespace App\Http\Controllers\Admin;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Model\WebUsers;
use App\Model\WebUsersDiscount;
use App\Model\WebUsersNote;
use App\Model\WebUsersSms;
use App\Model\Orders;
use App\Http\Controllers\BaseController;

class WebUsersController extends BaseController
{
    public function showWebUsers()
    {
        if (! \Auth::guard('admin')->user()->can('webUsers_view')) {
            return redirect()->route('home');}
        $allUsers=WebUsers::paginate(20);
        $historyOrder=[];
        $lastOrderUser=[];
        foreach($allUsers as $key)
        {
            $key->phone=$this->getPhone($key->phone);
            $lastOrderUser[$key->phone]=Orders::select('sum_total','updated_at')
                ->where('status','4')
                ->where('phone',$key->phone)
                ->orderBy('updated_at','desc')
                ->first();
            $count=$this->checkCountPrevOrders($key->phone,31);
            if ($count)
            {
                $historyOrder[$key->phone]=$count;
            }
        }
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.webUsers.index', compact('allUsers','historyOrder','lastOrderUser','api_token'));
    }

    public function show($id)
    {
        $user=WebUsers::find($id);
        $lastOrderUser='';
        $historyOrder='';
        $maskPhone=$this->getPhoneMask($user->phone);
        $lastOrderUser=Orders::select('sum_total','updated_at')
                ->where('status','4')
                ->where('phone',$maskPhone)
                ->orderBy('updated_at','desc')
                ->first();
        $historyOrder=$this->checkCountPrevOrders($user->phone,31);

        $promocodeType = \DB::connection()->select("SELECT * FROM mtShop.webUsersDiscountType");
        
        $api_token = \Auth::guard('admin')->user()->getToken();
        $now = Carbon::now()->timezone('Europe/Moscow')->startOfDay();
        $end = Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        return view('Admin.webUsers.show', compact('user','lastOrderUser','historyOrder','maskPhone','api_token','promocodeType','now','end'));
    }

    public function addDiscount(Request $request)
    {
        $func=$this->generateDiscountCart([$request->id],1,$request->discount,$request->type,$request->dateStart,$request->dateEnd,'',false);
        if ($func['code']==200)
            return redirect('admin/WebUsers/'.$this->getPhone($request->phone))->with('success', 'Сохранение успешно');
        else 
            if ($func['code']==500)
                return redirect('admin/WebUsers/'.$this->getPhone($request->phone))->with('danger', "Ошибка при указании даты"); 
            else 
                return redirect('admin/WebUsers/'.$this->getPhone($request->phone))->with('danger', "Не указан размер скидки");
    }




    public function changeStatusDiscount(Request $request)
    {
        $discount=WebUsersDiscount::find($request->id);
        $discount->status=!$discount->status;
        $discount->save();
        return json_encode(['code' => 200]);
    }

    public function WebUsersSMS($id,Request $request)
    {
        if ($this->sendSms($id,$request->textSMS))
        {
            $NewSms = new WebUsersSMS;
            $NewSms->text=$request->textSMS;
            $NewSms->phone=$id;
            $NewSms->moderatorId=\Auth::guard('admin')->user()->id;
            $NewSms->created_at=Carbon::now()->timezone('Europe/Moscow');
            $NewSms->save();
            return redirect('admin/WebUsers/'.$this->getPhone($id))->with('success', 'Сообщение отправлено');     
        }
        return redirect('admin/WebUsers/'.$this->getPhone($id))->with('danger', 'Ошибка при отправке сообщения');
    }

    public function WebUserNote(Request $request)
    {
        if ($request->input('id'))
        {
            $user=Webusers::where('id',$request->id)->first();
            $user->note=$request->note;
            $user->save();
            return 200;
        } 
        return 500;
    }

    public function WebUserNoteDelete($id)
    {
        if ($id)
        {
            $user=Webusers::where('id',$id)->first();
            $user->note="";
            $user->save();
            return back();
        } 
    }

    public function getHistoryNoteForPerson(Request $request)
    {
        if ($request->id)
        {
            $id=(int) $request->id;
            $history=WebUsersNote::select('webUsersNote.id','webUsersNote.note','admins.name','webUsersNote.created_at','webUsersNote.updated_at','webUsersNote.status')->
            where('webUserId',$id)
            ->join('admins', 'moderatorId', 'admins.id')->orderBy('created_at', 'DESC') 
            ->get()->toArray();
            return response()->json($history);
        }
    }

    public function getHistorySms(Request $request)
    {
        if ($request->phone)
        {
            $sql="select `webUsersSMS`.`id`, `webUsersSMS`.`text`, `admins`.`name`, `webUsersSMS`.`created_at` from `webUsersSMS` 
            inner join `admins` on `moderatorId` = `admins`.`id` 
            where `phone` = {$request->phone} 
            order by `created_at` desc";
            $history=\DB::connection()->select($sql);
            return response()->json($history);
        }
    }



     /**
     * Получение истории скидок для определенного пользователя
     * Входные данные 
     * id - код покупателя
     */
    public function getStockForPerson(Request $request)
    {
        $id=(int) $request->id;

        $stock = \DB::connection()->select("SELECT i.*, j.title as type, j.id as typeId FROM mtShop.webUsersDiscount as i  JOIN mtShop.webUsersDiscountType as j ON i.type=j.id WHERE i.webUserId=$id ORDER BY i.created_at DESC");
        return response()->json($stock);
    }
    /**
     * Добавление новой временной заметки для пользователя
     * Входные параметры
     * id - код покупателя
     * note - текст заметки
     */
    public function addNewNoteForWebUser(Request $request)
    {
        if ($request->input('note') && $request->input('id'))
        {
            $note=new WebUsersNote;
            $note->webUserId=$request->id;
            $note->note=$request->note;
            $note->status=1;
            $note->moderatorId=\Auth::guard('admin')->user()->id;
            $note->save();
            return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE );
        }
        else 
            return json_encode(['code' => 404], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Редактирование временной заметки для пользователя
     * Входные параметры
     * id - код заметки
     * note - новый текст заметки
     */
    public function updateNoteForWebUser(Request $request)
    {
        if ($request->input('note') && $request->input('id'))
        {
            $note=WebUsersNote::where('id',$request->id)->first();
            $note->note=$request->note;
            $note->moderatorId=\Auth::guard('admin')->user()->id;
            $note->save();
            return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE );
        }
        else 
            return json_encode(['code' => 404], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Изменение статуса временной заметки в таблице WebUsersNote
     * Входящие параметры 
     * id - код заметки
     */
    public function changeStatusWebUsersNote(Request $request)
    {
        if ($request->input('id'))
        {
            $note=WebUsersNote::where('id',$request->id)->first();
            $note->status=!$note->status;
            $note->save();
            return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE );
        }
        else 
            return json_encode(['code' => 404], JSON_UNESCAPED_UNICODE );
    }

    /**
     * Выборка всех довозов 
     * Если передать поле id выборка всех довозов у определенного пользователя
     * Входящие параметры
     * id - код покупателя
     */
    public function getAllLostDelivery(Request $request)
    {
        $sql = "SELECT 
                        da.id, 
                        da.orderId,
                        i.title,
                        da.confirmQuantity,
                        da.quantity,
                        a.name as workhouse,
                        a1.name as creator,
                        a2.name as closer,
                        DATE_FORMAT(da.deliveryDate,'%d.%m.%Y') as deliveryDate,
                        CONCAT(DATE_FORMAT(t.timeFrom, '%H:%i'),'-',DATE_FORMAT(t.timeTo,'%H:%i')) as timeWave,
                        da.status,da.addedId,DATE_FORMAT(da.created_at,'%d.%m.%Y %H:%i') as created_at,
                        GROUP_CONCAT(tw.id ,'/',DATE_FORMAT(tw.timeFrom, '%H:%i'),'-',DATE_FORMAT(tw.timeTo,'%H:%i') ORDER BY tw.id ASC) as waveId 
                FROM deliveryAdd as da
                LEFT JOIN orders as o on o.id=da.orderId
                LEFT JOIN itemsLink as i on da.itemId=i.id
                LEFT JOIN admins as a on a.id=da.confirmId 
                LEFT JOIN admins as a1 on a1.id=da.createId 
                LEFT JOIN admins as a2 on a2.id=da.closeId
                LEFT JOIN timeWaves as t on t.id=da.waveId
                JOIN deliveryZones as dz on o.deliveryZone=dz.id
                JOIN timeWaves as tw on tw.zoneId=dz.id
                ";
        $sqlWhere='';
        if ($request->id)
            $sqlWhere=" WHERE o.webUserId =".$request->id;

        if ($request->status)
        {
            if ($sqlWhere)
                $sqlWhere .= " AND "; 
            else 
                $sqlWhere.=" WHERE ";
            $sqlWhere.="da.status =".$request->status;
        }
        $sql.=$sqlWhere.' GROUP BY da.id ORDER BY da.id DESC ';
        $res = \DB::connection()->select($sql);  
        foreach ($res as $key=>$value)
        {
            $res[$key]->waveId = explode(',',$value->waveId);//array_map('intval',explode(',',$value->waveId));
        }
        return json_encode($res, JSON_UNESCAPED_UNICODE );
    }

    public function changeWebUserAutoown(Request $request)
    {
        $id=$request->id;
        if (isset($id))
        {   
            $id="+".$this->getPhone($id);
            $fin=WebUsers::where('id',$id)->first();
            if(isset($fin))
            { 
                $fin->autoown=!$fin->autoown;
                $fin->save();
            }
            return json_encode($fin->autoown);
            //return redirect()->route('getAllWebUsers');
        }
    }

    public function getSettings(Request $request)
    {
        if ($request->id)
        {
            $settings = WebUsers::Select('phone','autologin','autoown','autoownperm')->where('id',$request->id)->first();
            if ($settings)
            {
                $settings = $settings->toArray();
                $settings['historyMonth'] = $this->checkCountPrevOrders($settings['phone'],31);
                return json_encode(['status'=>200, 'settings'=>$settings]);
            }
        }
        else
            return json_encode(['status'=>404, 'Error'=>'Not found id']);
    }
}