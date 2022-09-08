<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Cargos;
use Carbon\Carbon;

class CargosController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    /**
     * 
     */
    public function getCargos(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $cargos = Cargos::where("deleted", 0)->orderBy('cargo', 'asc')->get();
        return response()->json(['code'=>200, 'cargos'=>$cargos]);
    }
    
    /**
     * Сохраняет 
     */
    public function saveItem(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        @$item =  $request->input('params')['item'];
        
        if(!$item)
            return json_encode( ['code' => 700, 'msg' => 'Услуга не обновлена - нет данных'], JSON_UNESCAPED_UNICODE );
        if($item['id']>0)
            $_item = Items::find($item['id']);
        else
            $_item = null;
            \DB::beginTransaction();
            try{
                $data = [
    
                ];
                $item['operatorId'] = \Auth::guard('admin')->user()->id;
                if($item['id']>0){
                    if($_item)
                        $update = $_item->update($item);
                    else
                        $update = false;
                }else{
                    $_item = Items::create($item);
                    $update = true;
                }
                
                
    
            }catch(\Exception $e){
                \DB::rollback();
                throw $e;
            }
            \DB::commit();
            return json_encode( ['code' => 200, 'msg' => 'Клиент обновлен'], JSON_UNESCAPED_UNICODE );
    }
}
