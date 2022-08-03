<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\Array_;
use App\Http\Controllers\BaseController;
use Jenssegers\Agent\Agent;
use App\Model\WebUsers;

class ApiWebUsersController extends BaseController
{
    /**
     * Возвращает поля профайла пользователя
     * Входные параметры:
     * phone -    телефон (он же ID)
     * 
     */
    public function getProfile(Request $request)
    {
        $rules = [
            'phone' => 'required'
        ];
        $validation = \Validator::make($request->all(), $rules);

        if($validation->fails()){
            return $validation->messages();
        } else{
            
            $phone = htmlspecialchars($this->getPhone($request->input('phone')));
            if($phone[0] != '+')
                $phone = '+' . $phone;
            $webuser = WebUsers::find($phone);
            
            if(!$webuser)
                return json_encode( ['msg' => 'профаил не найден', 'code' => 400, 'profile' =>[]], JSON_UNESCAPED_UNICODE );
        }
        
        return json_encode( ['msg' => 'профаил найден', 'code' => 200, 'profile' => $webuser], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Сохраняет поля профайла пользователя
     * Входные параметры:
     * userName -     имя
     * email -    эл. почта
     * birthday -    день рождения
     * phone -    телефон (он же ID)
     */
    public function setProfile(Request $request)
    {
        $rules = [
            'email' => 'email',
            'phone' => 'required'
        ];
        $validation = \Validator::make($request->all(), $rules);

        if($validation->fails()){
            return $validation->messages();
        } else{
            $phone = htmlspecialchars($this->getPhone($request->input('phone')));
            if($phone[0] != '+')
                $phone = '+' . $phone;
            $webuser = WebUsers::find($phone);
            // $request->except('phone');
            $request->offsetUnset('phone');
            $data = $request->all();
            if($webuser)
                $webuser->update($data);
            else
                return json_encode( ['msg' => 'профаил не обновлен', 'code' => 400], JSON_UNESCAPED_UNICODE );
        }
        
        return json_encode( ['msg' => 'профаил обновлен', 'code' => 200], JSON_UNESCAPED_UNICODE );
    }
}
