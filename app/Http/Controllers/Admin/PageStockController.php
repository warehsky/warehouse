<?php

namespace App\Http\Controllers\Admin;

use App\Model\PageStock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('pageStock_view')) {
            return redirect()->route('home');
        }
        $ActiveStock=PageStock::where('status',1)->OrderBy('created_at', 'desc')->get();
        $DeactiveStock=PageStock::where('status',0)->OrderBy('created_at', 'desc')->get();
        return view('Admin.pageStock.index',compact('ActiveStock','DeactiveStock'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! \Auth::guard('admin')->user()->can('pageStock_edit')) {
            return redirect()->route('home');
        }
        return view('Admin.pageStock.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'image' => 'required']);
        if ($request->timeStart>$request->timeEnd)
            return redirect()->route('pageStock.create')->with('error', 'Дата начала позже даты окончания'); 
        
            $addNewStore = new PageStock;
            $addNewStore->description=$request->description;
            $addNewStore->title=$request->title;
            $addNewStore->timeStart=$request->timeStart;
            $addNewStore->timeEnd=$request->timeEnd;
            $addNewStore->save();
            $name = $request->image->getClientOriginalName();
            $request->image->move(public_path()."/img/img/pageStock/", $name);
            $addNewStore->update(['image'=>"/img/img/pageStock/".$name]);
            if ($request->status)
                $addNewStore->status=1;
            else
                $addNewStore->status=0;
            
            $addNewStore->save();
            
            return redirect()->route('pageStock.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! \Auth::guard('admin')->user()->can('pageStock_edit')) {
            return redirect()->route('home');
        }
        $EditStock=PageStock::find($id);
        return view('Admin.pageStock.edit',compact('EditStock'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, ['title' => 'required']);
        $item=PageStock::find($id);
        if ($request->timeStart>$request->timeEnd)
            return redirect()->back()->with('error', 'Дата начала позже даты окончания'); 
        if ($request['status']==false)
            $status=0;
        else 
            $status=1;
        $item->update([
                'description'=>$request->description,
                'status'=>$status,
                'title'=>$request->title,
                'timeStart'=>$request->timeStart,
                'timeEnd'=>$request->timeEnd      
        ]);
        if ($request->hasFile('image')) {
                $nameImage = $request->image->getClientOriginalName();
                $request->image->move(public_path()."/img/img/pageStock/", $nameImage);
                $item->update(['image'=>"/img/img/pageStock/".$nameImage]);
        }
        return redirect()->route('pageStock.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! \Auth::guard('admin')->user()->can('pageStock_edit')) {
            return redirect()->route('home');
        }
        PageStock::find($id)->delete();
        return redirect()->route('pageStock.index');
    }

    public function changeStatus($id)
    {
        if (! \Auth::guard('admin')->user()->can('pageStock_publication')) {
            return redirect()->route('home');
        }
        $fin=PageStock::where('id',$id)->first();
        $fin->status=!$fin->status;
        $fin->save();
        return redirect()->route('pageStock.index');
    }
}