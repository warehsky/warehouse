<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Clients;
use Carbon\Carbon;

class ClientsController extends BaseController
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
    public function getClients(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $clients = Clients::orderBy('client', 'asc')->get();
        return response()->json(['code'=>200, 'clients'=>$clients]);
    }
    
    /**
     * Сохраняет Клиента
     */
    public function saveClient(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        @$client = $request->input('params')['client'];
        
        if(!$client)
            return json_encode( ['code' => 700, 'msg' => 'Клиент не обновлен - нет данных'], JSON_UNESCAPED_UNICODE );
        if($client['id']>0)
            $_client = Clients::find($client['id']);
        else
            $_client = null;
            \DB::beginTransaction();
            try{
                $data = [
    
                ];
                $client['operatorId'] = \Auth::guard('admin')->user()->id;
                if($client['id']>0){
                    if($_client)
                        $update = $_client->update($client);
                    else
                        $update = false;
                }else{
                    $_order = Clients::create($client);
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
