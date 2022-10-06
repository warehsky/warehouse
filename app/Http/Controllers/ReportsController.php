<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Operations;
use App\Model\Orders;
use App\Model\OrderItem;
use App\Model\ExpenseItem;
use Carbon\Carbon;

class ReportsController extends BaseController
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
    public function getReportReminds(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $clientId = $request->input('clientId') ?? 0;
        $reminds = OrderItem::select('orderItem.itemId', 'orderItem.orderId', 'orderItem.price', 'orderItem.itemId', 'orderItem.quantity', 'orderItem.note', 'items.item', 'clients.client', \DB::raw('sum(orderItem.quantity) as wcount'), 'orders.created_at')
        ->join('orders', "orders.id", "orderItem.orderId")
        ->join("items", "items.id", "orderItem.itemId")
        ->join("cargos", "cargos.id", "items.cargoId")
        ->join("clients", "clients.id", "orders.clientId") 
        ->where("cargos.evaluationId", 2)
        ->with("item")
        ->groupBy("orders.clientId")
        ->groupBy("orderItem.itemId")
        ->groupBy("orderItem.orderId");
        if($clientId>0)
            $reminds = $reminds->where("orders.clientId", $clientId);
        $reminds = $reminds->orderBy("orders.created_at")->paginate(10);

        $expenses = ExpenseItem::select('*', \DB::raw('sum(quantity) as ecount'))
        ->join('expenses', "expenses.id", "expenseItem.expenseId")
        ->groupBy("expenses.clientId")
        ->groupBy("itemId")
        ->groupBy("price")
        ->get();
        
        $exs = [];
        foreach($expenses as $ex)
            $exs[$ex->orderId.$ex->itemId] = $ex;
        // dd($reminds, $exs);
        foreach($reminds as $i=>$r){
            if(key_exists($r->orderId.$r->itemId, $exs))
                $r->remind = (double)$r->wcount - (double)$exs[$r->orderId.$r->itemId]->ecount;
            else
                $r->remind = (double)$r->wcount;
            if($r->remind <= 0)
                unset($reminds[$i]);
        }
        return view('reports.reminds.index', compact('reminds'));
    }
    
}
