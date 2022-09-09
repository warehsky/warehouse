<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Operations;
use Carbon\Carbon;

class OperationsController extends BaseController
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
    public function getOperations(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $operations = Operations::where("deleted", 0)->orderBy('operation', 'asc')->get();
        return response()->json(['code'=>200, 'operations'=>$operations]);
    }
    public function index()
    {
        $operations = Operations::orderBy('updated_at', 'desc')->paginate(20);

        return view('operations/index', ['operations' => $operations]);

    }
    /**
     * окно редактирования заказа
     */
    public function edit(Request $request, $id){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        if($id)
            $operation = Operations::find($id);
        else
            $operation = null;
        $data = ['operation' => $operation];
        return view('operations.edit', $data);
    }
    /**
     * Сохраняет 
     */
    public function update(Request $request, $id){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        if(!isset($request->operation))
            return json_encode( ['code' => 700, 'msg' => 'Услуга не обновлена - нет данных'], JSON_UNESCAPED_UNICODE );
        $data = $request->except(['_token', '_method']);
        if($id>0)
            $_item = Operations::where('id', $id)->update($data);
        else
            $_item = Operations::create($data);

        return redirect()->route('operations.index');
    }
    public function destroy(Request $request, $id){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $_item = Operations::where('id', $id)->update(['deleted' => 1]);
        return redirect()->route('operations.index');
    }
}
