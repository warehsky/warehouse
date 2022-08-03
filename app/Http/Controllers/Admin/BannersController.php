<?php

namespace App\Http\Controllers\Admin;

use App\Model\Banners;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BannersController extends Controller
{
    private $types=[null,'верхний', 'нижний', 'без карусели'];
    private $autoplays=['ручная', 'авто'];
    private $publics=['не опубликован', 'опубликован'];
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
        $banners = Banners::all();
        $types = $this->types;
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.banners.index', compact('banners', 'types', 'api_token'));
    }

    /**
     * Show the form for creating new Banner.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('banners_edit')) {
            return redirect()->route('home');
        }
        $types = $this->types;
        $autoplays = $this->autoplays;
        $publics = $this->publics;
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.banners.edit', compact('types', 'autoplays', 'publics', 'api_token'));
    }

    /**
     * Store a newly created Banner in storage.
     *
     * @param  \App\Http\Requests\StoreItemGroupsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        Banners::create($data);
        ///////////////////
        
        ///////////////////
        
        return redirect()->route('banners.index');
    }


    /**
     * Show the form for editing Banner.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('banners_edit')) {
            return redirect()->route('home');
        }
        $banner = Banners::find($id);
        $types = $this->types;
        $autoplays = $this->autoplays;
        $publics = $this->publics;
        return view('Admin.banners.edit', compact('banner', 'types', 'autoplays', 'publics'));
    }

    /**
     * Update Banner in storage.
     *
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        
        $banner = Banners::find($id);

        $banner->update($data);
        
        return redirect()->route('banners.index');
    }

    public function show(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('banners_view')) {
            return redirect()->route('home');
        }
        $banner = Banners::find($id);
        return view('Admin.banners.show', compact('banner'));
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
        $banner = Banners::find($id);
        
        $banner->delete();
            
        return redirect()->route('banners.index');
    }

    /**
     * Delete all selected Item at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        Banners::whereIn('id', request('ids'))->delete();
        return response()->noContent();
    }
    
}
