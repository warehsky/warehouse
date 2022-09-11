<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Cargos;
use App\Model\Evaluations;
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
    public function index()
    {
        $cargos = Cargos::orderBy('updated_at', 'desc')->paginate(20);

        return view('cargos/index', ['cargos' => $cargos]);

    }
    /**
     * окно редактирования заказа
     */
    public function edit(Request $request, $id){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        if($id)
            $cargo = Cargos::find($id);
        else
            $cargo = null;
        $evaluations = Evaluations::all();
        $data = ['cargo' => $cargo, 'evaluations' => $evaluations];
        return view('cargos.edit', $data);
    }
    /**
     * Сохраняет 
     */
    public function update(Request $request, $id){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        if(!isset($request->cargo))
            return json_encode( ['code' => 700, 'msg' => 'Услуга не обновлена - нет данных'], JSON_UNESCAPED_UNICODE );
        $data = $request->except(['_token', '_method']);
        if($id>0)
            $_item = Cargos::where('id', $id)->update($data);
        else
            $_item = Cargos::create($data);

        return redirect()->route('cargos.index');
    }
    public function destroy(Request $request, $id){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $_item = Cargos::where('id', $id)->update(['deleted' => 1]);
        return redirect()->route('cargos.index');
    }
}
