<?php

namespace App\Http\Controllers\Admin;

use App\Model\DeliveryZones;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeliveryZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('deliveryZone_view')) {
            return redirect()->route('home');
        }
        $Zones=DeliveryZones::all();
        return view('Admin.deliveryZone.index',compact('Zones'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! \Auth::guard('admin')->user()->can('deliveryZone_edit')) {
            return redirect()->route('home');
        }
        return view('Admin.deliveryZone.create');
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
            'cost' => 'required|numeric',
            'limit_min' => 'required|numeric',
            'limit' => 'required|numeric',
            'limit_lgot'=> 'required|numeric']);

        $zone = $request->except(['_method', '_token']);
        DeliveryZones::create($zone);

        return redirect()->route('deliveryZone.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! \Auth::guard('admin')->user()->can('deliveryZone_view')) {
            return redirect()->route('home');
        }
        $Zone=DeliveryZones::find($id);
        return view('Admin.deliveryZone.show',compact('Zone'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! \Auth::guard('admin')->user()->can('deliveryZone_edit')) {
            return redirect()->route('home');
        }
        $Zone=DeliveryZones::find($id);
        return view('Admin.deliveryZone.edit',compact('Zone'));
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
        $this->validate($request, [
            'cost' => 'required|numeric',
            'limit_min' => 'required|numeric',
            'limit' => 'required|numeric',
            'limit_lgot'=> 'required|numeric']);

        $data = $request->except(['_method', '_token']);
        if(!key_exists('deleted', $data) || $data['deleted'] != "on")
            $data['deleted'] = 0;
        else
            $data['deleted'] = 1;
        $zone = DeliveryZones::find($id);
        $zone->update($data);
        
        return redirect()->route('deliveryZone.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! \Auth::guard('admin')->user()->can('deliveryZone_edit')) {
            return redirect()->route('home');
        }
        DeliveryZones::find($id)->delete();
        return redirect()->route('deliveryZone.index');
    }
    public function zonesEditor(){
        if (! \Auth::guard('admin')->user()->can('deliveryZone_edit')) {
            return redirect()->route('home');
        }
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view("Admin.deliveryZone.zonesEditor", compact('api_token'));
    }
}
