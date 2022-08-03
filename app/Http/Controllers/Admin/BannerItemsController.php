<?php

namespace App\Http\Controllers\Admin;

use App\Model\Banners;
use App\Model\BannerItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BannerItemsController extends Controller
{
    private $publics=['не опубликован', 'опубликован'],
     $rules = [
        'link' => 'required',
        'link_mobile' => 'required',
        'alt' => 'required',
        'alt_mobile' => 'required', 
    ];
    /**
     * Display a listing of Banners.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('banners_view')) {
            return redirect()->route('home');
        }
        $bannerId = $request->input('bannerId') ?? 0;
        if($bannerId > 0)
            $bannerItems = BannerItems::where('bannerId', $request->input('bannerId'))->orderBy('sort')->get();
        else
            $bannerItems = BannerItems::all();
        
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.bannerItems.index', compact('bannerItems', 'api_token', 'bannerId'));
    }

    /**
     * Show the form for creating new BannerItem.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('banners_edit')) {
            return redirect()->route('home');
        }
        $publics = $this->publics;
        $bannerId = $request->input('bannerId') ?? 0;
        if($bannerId==0)
            return redirect()->back();
        return view('Admin.bannerItems.edit', compact('publics', 'bannerId'));
    }

    /**
     * Store a newly created BannerItem in storage.
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = \Validator::make($request->all(), $this->rules);

        if($validation->fails())
            return redirect()->back()->withErrors($validation->messages());
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        $bannerItem = BannerItems::create($data);
        ///////////////////
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $extension = $request->image->extension();
                $request->image->storeAs("/img/img/banners/{$bannerItem->bannerId}/", $bannerItem->id.".".$extension);
                $bannerItem->update(['image'=>"/img/img/banners/{$bannerItem->bannerId}/". $bannerItem->id.".".$extension]);
            }else
                abort(500, 'Could not upload image banner :(');
        }
        if ($request->hasFile('image_mobile')) {
            if ($request->file('image_mobile')->isValid()) {
                $extension = $request->image_mobile->extension();
                $request->image_mobile->storeAs("/img/img/banners/{$bannerItem->bannerId}/", "m".$bannerItem->id.".".$extension);
                $bannerItem->update(['image_mobile'=>"/img/img/banners/{$bannerItem->bannerId}/m". $bannerItem->id.".".$extension]);
            }else
                abort(500, 'Could not upload image banner :(');
        }
        ///////////////////
        
        return redirect()->route('bannerItems.index', ['bannerId'=>$bannerItem->bannerId]);
    }


    /**
     * Show the form for editing BannerItem.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('banners_edit')) {
            return redirect()->route('home');
        }
        $bannerItem = BannerItems::find($id);
        $publics = $this->publics;
        
        return view('Admin.bannerItems.edit', compact('bannerItem', 'publics'));
    }

    /**
     * Update BannerItem in storage.
     *
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = \Validator::make($request->all(), $this->rules);

        if($validation->fails())
            return redirect()->back()->withErrors($validation->messages());
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        
        $bannerItem = BannerItems::find($id);

        $bannerItem->update($data);
        ///////////////////
        
        if ($request->hasFile('image')) {
            if ($request->file('image')->isValid()) {
                $fname = $request->image->getClientOriginalName();
                $extension = $request->image->extension();
                $request->image->move(public_path()."/img/img/banners/{$bannerItem->bannerId}/", $fname);
                $bannerItem->update(['image'=>"/img/img/banners/{$bannerItem->bannerId}/". $fname]);
            }else
                abort(500, 'Could not upload image banner :(');
        }
        
        if ($request->hasFile('image_mobile')) {
            if ($request->file('image_mobile')->isValid()) {
                $fname = "m".$request->image_mobile->getClientOriginalName();
                $extension = $request->image_mobile->extension();
                $request->image_mobile->move(public_path()."/img/img/banners/{$bannerItem->bannerId}/", $fname);
                $bannerItem->update(['image_mobile'=>"/img/img/banners/{$bannerItem->bannerId}/". $fname]);
            }else
                abort(500, 'Could not upload image banner :(');
        }
        ///////////////////
        return redirect()->route('bannerItems.index', ['bannerId'=>$bannerItem->bannerId]);
    }

    public function show(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('banners_view')) {
            return redirect()->route('home');
        }
        $bannerItem = BannerItems::find($id);
        return view('Admin.bannerItems.show', compact('bannerItem'));
    }

    /**
     * Remove Banner from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('banners_edit')) {
            return redirect()->route('home');
        }
        $bannerItem = BannerItems::find($id);
        $bannerId = $bannerItem->bannerId;
        $bannerItem->delete();
            
        return redirect()->route('bannerItems.index', ['bannerId'=>$bannerId]);
    }

    /**
     * Delete all selected Item at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        BannerItems::whereIn('id', request('ids'))->delete();
        return response()->noContent();
    }
}
