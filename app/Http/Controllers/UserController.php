<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;

class UserController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('authapi');
    }
    /**
     * 
     */
    public function getSchedule(Request $request){
        $u = \Auth::user();
        $d_ret = ['week' => $u->week, 'daySchedule' => $u->daySchedule];
        return $this->setJsonAnswer($d_ret);
    }
    /*
        Возвращает данные пользователя, а именно:
            priceTypes - типы цен, с которыми работает пользователь
            tradeDirection - код торгового направления. Смена кода вызовет сброс складов, номенклатуры, иерархии групп, mml
            generation - код поколения. Смена кода поколения вызывает сброс всех инкрементальных данных
            tradePerm  - права на формирование заказов и ПКО,
            storeCheckPerm -  права на формирование сторечека (для мерчандайзеров).
        Версия: 0.27.0+
    */
    public function getUserProfile()
    {
        $u = \Auth::user();
        $d_ret = ["priceTypes" => (string)$u->priceTypes,
                  "tradeDirection" => (string)$u->tradeDirection,
                  "generation" => (string)$u->gen,
                  "tradePerm" => ($u->tradePerm>0),
                  "storeCheckPerm" => ($u->storeCheckPerm>0),
                  "f2percent" => (string)$u->f2percent,
                  "f2time" => (string)$u->f2time
                 ];
        return $this->setJsonAnswer($d_ret);
    }
}
