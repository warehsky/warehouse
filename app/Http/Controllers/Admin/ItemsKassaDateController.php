<?php

namespace App\Http\Controllers\Admin;

use App\Model\ItemsKassaDate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemsKassaDateController extends Controller
{
    public function index()
    {
        $sql = "select * from mtShop.itemsKassaDates WHERE dateEnd>=CURRENT_DATE() order by dateStart";
        $activeInterval = \DB::connection()->select($sql);
        $sql = "select * from mtShop.itemsKassaDates WHERE dateEnd<CURRENT_DATE() order by dateStart";
        $deactiveInterval = \DB::connection()->select($sql);
        return view('Admin.itemsKassa.date',compact('activeInterval','deactiveInterval'));
    }

    /**
     * Добавление нового интервала
     * 
     * Входные параметры:
     * dateStart - дата начала интервала 
     * dateEnd - дата окончания интервала
     */
    public function store(Request $request)
    {
        if(!$request->input("dateStart") || !$request->input('dateEnd'))
            return redirect()->route('itemsKassaDate')->with('error', 'Не удалось добавить новый период');
        if($request->input('dateStart')>$request->input('dateEnd'))
            return redirect()->route('itemsKassaDate')->with('error', 'Дата начала позже даты окончания');
        $cross=$this->findCountCrossInterval($request->dateStart,$request->dateEnd);
        if (!$cross)
        {
            $newDate = new ItemsKassaDate;
            $newDate->dateStart=$request->dateStart;
            $newDate->dateEnd=$request->dateEnd;
            $newDate->save();
            return redirect()->route('itemsKassaDate')->with('message', 'Новый интервал создан');
        }
        else 
            return redirect()->route('itemsKassaDate')->with('error', 'Временные интервалы пересекаются');
    }

    /**
     * Находжение количества пересечений интервалов дат (ItemsKassaDate)
     * 
     * Входные данные: 
     * dateStart - дата начала интервала 
     * dateEnd - дата окончания интервала
     * 
     * Выходные параметры: количество найденных пересечений
    */
    private function findCountCrossInterval($dateStart,$dateEnd,$id=null)
    {
        $nowDay='';
        if ($id!=null)
            $nowDay="AND id!={$id}";
        $sql="SELECT id FROM `itemsKassaDates` 
        WHERE (`dateStart` BETWEEN '{$dateStart}' and '{$dateEnd}' 
        OR `dateEnd` BETWEEN '{$dateStart}' and '{$dateEnd}' 
        OR  (`dateStart`<'{$dateStart}' AND `dateEnd`>'{$dateEnd}')) {$nowDay}";
        $res = \DB::connection()->select($sql);
        return $res;
    }
    /**
     * Удаление интервала
     * 
     * Входные параметры:
     * id - id интервала в таблице itemKassaDate 
     */
    public function delete($id)
    {
        if(!$id)
            return redirect()->route('itemsKassaDate')->with('error', 'Нет id временного интервала');
        ItemsKassaDate::find($id)->delete();
            return redirect()->route('itemsKassaDate')->with('message', 'Интервал удален');

    }

    /**
     * Обновление интервала
     * 
     * Входные параметры:
     * dateStart - новая дата начала интервала 
     * dateEnd - новая дата окончания интервала
     * id - id интервала в таблице itemKassaDate
     */
    public function update($id,Request $request)
    {
        if(!$id)
            return redirect()->route('itemsKassaDate')->with('error', 'Нет id временного интервала');
        if(!$request->input("dateStart") || !$request->input('dateEnd'))
            return redirect()->route('itemsKassaDate')->with('error', 'Не удалось добавить новый период');
        if($request->input('dateStart')>$request->input('dateEnd'))
            return redirect()->route('itemsKassaDate')->with('error', 'Дата начала позже даты окончания');
        
        $cross=$this->findCountCrossInterval($request->dateStart,$request->dateEnd,$id);
        if(!$cross)
        {
            $date=ItemsKassaDate::find($id);
            $date->dateStart=$request->dateStart;
            $date->dateEnd=$request->dateEnd;
            $date->save();
            return redirect()->route('itemsKassaDate')->with('message', 'Интервал успешно изменен');
        }
        return redirect()->route('itemsKassaDate')->with('error', 'Временные интервалы пересекаются');
    }
}
