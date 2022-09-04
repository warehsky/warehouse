<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Expenses;
use App\Model\ExpenseItem;
use Carbon\Carbon;

class ExpensesController extends BaseController
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
    public function index(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return redirect()->route('home');
        }
        $paginate = $this->getOption('orders_paginate');
        $expenses = Expenses::orderBy('updated_at', 'desc')->paginate($paginate);
        return view('expenses.index', compact('expenses'));
    }
    /**
     * окно редактирования заказа
     */
    public function edit(Request $request, $id){
        
        $dFrom = $request->input('dFrom')&&!empty($request->input('dFrom'))?$request->input('dFrom'):Carbon::parse(Carbon::now());
        $dTo = $request->input('dTo')&&!empty($request->input('dTo'))?$request->input('dTo'):Carbon::parse(Carbon::now());
        $status = $request->input('status') ?? 0;
        $api_token = \Auth::guard('admin')->user()->getToken();
        
        
        $data = ['orderId' => $id, 'dFrom' => $dFrom, 'dTo' => $dTo, 'status' => $status, 'api_token' => $api_token];
        return view('expenses.order', $data);
    }
    /**
     * Возвращает ордер по ID
    */
    public function getExpense(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $id = $request->input('expenseId') ?? 0;
        $expense = Expenses::where("id", $id)->with("expenseItems")->first();
        
        return response()->json(['code'=>200, 'expense'=>$expense]);
    }
    /**
     * Сохраняет ордер
     */
    public function saveExpense(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        @$expense = $request->input('params')['expense'];
        
        if(!$expense)
            return json_encode( ['code' => 700, 'msg' => 'ордер не обновлен - нет данных'], JSON_UNESCAPED_UNICODE );
        if($expense['id']>0)
            $_expense = Expenses::find($expense['id']);
        else
            $_expense = null;
        if($_expense && ($_expense["status"] == 100 ))
            return json_encode( ['code' => 700, 'msg' => 'ордер не обновлен - редактирование запрещено'], JSON_UNESCAPED_UNICODE );
        \DB::beginTransaction();
        try{
            $data = [

            ];
            $expense['operatorId'] = \Auth::guard('admin')->user()->id;
            if($expense['id']>0){
                if($_expense)
                    $update = $_expense->update($expense);
                else
                    $update = false;
            }else{
                $_expense = Expenses::create($expense);
                $update = true;
            }
            if($update){
                // Сохраняем перечень услуг
                $sum = 0;
                $ids = [];
                foreach($expense['expense_items'] as $item){
                    // @$itm = json_decode($item);
                    // if(!$itm) continue;
                    $price = $item['price'];
                    $sum += $price * $item['quantity'];
                    $sql = "INSERT INTO expenseItem (`expenseId`, `itemId`, `price`, `quantity`) " .
                    "VALUES ({$_expense['id']}, {$item['itemId']}, {$price}, {$item['quantity']}) ".
                    "ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `quantity`=VALUES(`quantity`)";
                    $result = \DB::connection()->select( $sql );
                    $ids[] = $item['itemId'];
                }
                $expenseItems = ExpenseItem::where('expenseId', $expense['id'])->get();
                foreach($expenseItems as $expenseItem){
                    if(!in_array($expenseItem->itemId, $ids)){
                        ExpenseItem::where('expenseId', $expense['id'])->where('itemId', $expenseItem->itemId)->delete();
                    }
                }
                $expense=[
                    
                    "sum_total" => $sum,
                ];
                $update = $_expense->update($expense);
            }

        }catch(\Exception $e){
            \DB::rollback();
            throw $e;
        }
        \DB::commit();
        return json_encode( ['code' => 200, 'msg' => 'Заказ обновлен'], JSON_UNESCAPED_UNICODE );
    }
}
