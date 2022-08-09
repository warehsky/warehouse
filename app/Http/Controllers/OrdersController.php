<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Orders;
use Carbon\Carbon;

class OrdersController extends BaseController
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
        $orders = Orders::orderBy('updated_at', 'desc')->paginate($paginate);
        return view('orders.index', compact('orders'));
    }
    /**
     * окно редактирования заказа
     */
    public function create(Request $request){
        $id=0;
        $phone = $request->input('phone') ?? '';
        $dFrom = $request->input('dFrom')&&!empty($request->input('dFrom'))?$request->input('dFrom'):Carbon::parse(Carbon::now());
        $dTo = $request->input('dTo')&&!empty($request->input('dTo'))?$request->input('dTo'):Carbon::parse(Carbon::now());
        $status = $request->input('status') ?? 0;
        $api_token = \Auth::guard('admin')->user()->getToken();
        
        
        $data = ['orderId' => $id, 'dFrom' => $dFrom, 'dTo' => $dTo, 'status' => $status, 'api_token' => $api_token];
        return view('orders.order', $data);
    }
    /**
     * 
    */
    public function getOrder(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $id = $request->input('orderId') ?? 0;
        $order = Orders::find($id);
        return response()->json(['code'=>200, 'order'=>$order]);
    }
}
