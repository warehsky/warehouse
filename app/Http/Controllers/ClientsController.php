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
    

}
