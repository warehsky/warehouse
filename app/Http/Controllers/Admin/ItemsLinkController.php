<?php

namespace App\Http\Controllers\Admin;

use App\Model\ItemGroups;
use App\Model\Items;
use App\Model\ItemsLink;
use App\Model\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreItemsLinkRequest;
use App\Http\Requests\Admin\UpdateItemsLinkRequest;
use Illuminate\Support\Facades\Storage;

class ItemsLinkController extends Controller
{
    /**
     * Display a listing of Item.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('items_view')) {
            return redirect()->route('home');
        }
        $items = ItemsLink::paginate(20);
        
        /*$itemgroups = ItemGroups::all();
        $groups = [];
        foreach($itemgroups as $g){
            $groups[$g->id] = $g;
        }
        */
        return view('Admin.items.index', compact('items'));//, 'groups'));
    }
////
    /**
     * Show the form for creating new Item.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('items_edit')) {
            return redirect()->route('home');
        }
        $itemgroups = ItemGroups::orderBy('title')->get();
        if($request->input('parentId')){
            $parent = ItemGroups::find($request->input('parentId'));
            if($parent)
                $parent = $parent->id;
            else
                $parent = 0;
        }
        else
            $parent = 0;
        return view('Admin.items.create', compact('itemgroups', 'parent'));
    }

    /**
     * Store a newly created Item in storage.
     *
     * @param  \App\Http\Requests\StoreItemsLinkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreItemsLinkRequest $request)
    {
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        
        $item = ItemsLink::create($data);
        ///////////////////
        if ($request->hasFile('imgIcon')) {
            if ($request->file('imgIcon')->isValid()) {
                $extension = $request->imgIcon->extension();
                $request->imgIcon->move(public_path().'/img/img/items/icons', $item->id.".".$extension);
            }else
                abort(500, 'Could not upload image icon :(');
        }
        if ($request->hasFile('imgSmall')) {
            if ($request->file('imgSmall')->isValid()) {
                $extension = $request->imgSmall->extension();
                $request->imgSmall->move(public_path().'/img/img/items/small', $item->id.".".$extension);
            }else
                abort(500, 'Could not upload image small :(');
        }
        if ($request->hasFile('imgBig')) {
            if ($request->file('imgBig')->isValid()) {
                $extension = $request->imgBig->extension();
                $request->imgBig->move(public_path().'/img/img/items/big', $item->id.".".$extension);
            }else
                abort(500, 'Could not upload image big :(');
        }
        ///////////////////
        
        if (isset($_GET['groupRoute']))
            return redirect("/admin/itemgroups?id={$_GET['id']}&name={$_GET['name']}&page={$_GET['page']}&parentId={$_GET['parentId']}&popularSort={$_GET['popularSort']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}");
        else 
            return redirect("/admin/items?id={$_GET['ids']}&name={$_GET['name']}&longName={$_GET['longName']}&page={$_GET['page']}&date={$_GET['date']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}");
    }


    /**
     * Show the form for editing Item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('items_edit')) {
            return redirect()->route('home');
        }
        $item = ItemsLink::find($id);
        $itemgroups = ItemGroups::orderBy('title')->get();
        $item_mob = Items::findorfail($item->id);
        $group_mob = "";
        $_group_mob = Items::where('guid', $item_mob->guid_parent)->first();
        
        for(;$_group_mob;){
            $group_mob = " |{$_group_mob->item}| " . $group_mob;
            $_group_mob = Items::where('guid', $_group_mob->guid_parent)->first();
        }
        return view('Admin.items.edit', compact('item', 'itemgroups', 'item_mob', 'group_mob'));
    }

    /**
     * Update Item in storage.
     *
     * @param  \App\Http\Requests\UpdateItemsLinkRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateItemsLinkRequest $request, $id)
    {
        
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;

        if(!key_exists('autoPopular', $data) || $data['autoPopular'] != "on")
        {
            \DB::table('itemTags')->insertOrIgnore([
                ['itemId' => $id, 'tagId' => 331, 'moderatorId' => 1],
            ]);
            $data['autoPopular'] = 0;
        }
        else
        {
            $delete = "DELETE w FROM itemTags w
                        WHERE w.tagId=331 AND w.itemId=$id";
            \DB::Connection()->select($delete);
            $data['autoPopular'] = 1;
        }
        $item = ItemsLink::find($id);
        $validator = \Validator::make($request->all(), [
            'id' => 'bail|exists:itemsLink',
            'mult' => [
                'required',
                function ($attribute, $value, $fail) use ($item) {
                    if ($value > 1 && $item->discountBound > 0 && $item->discountBound < 2000000 && ($item->discountBound % $value) > 0) {
                        $fail('Кратность должна быть кратной дисконту или дисконт должен отсутствовать.');
                    }
                },
            ],
            
        ]);
        if ($validator->fails()) {
            if (isset($_GET['ReportBack']))
                return back()->withErrors($validator);
            if (isset($_GET['groupRoute']))
                return redirect("/admin/items/$id/edit?id={$_GET['ids']}&name={$_GET['name']}&page={$_GET['page']}&parentId={$_GET['parentId']}&popularSort={$_GET['popularSort']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}")
                    ->withErrors($validator)
                    ->withInput();
            else 
                return redirect("/admin/items/$id/edit?id={$_GET['ids']}&name={$_GET['name']}&longName={$_GET['longName']}&page={$_GET['page']}&date={$_GET['date']}&sorting={$_GET['sorting']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}")
                        ->withErrors($validator)
                        ->withInput();
        }
        $item->update($data);
        /*if ($request->hasFile('imgIcon')) {
            if ($request->file('imgIcon')->isValid()) {
                $extension = $request->imgIcon->extension();
                $request->imgIcon->move(public_path().'/img/img/items/icons', $item->id.".".$extension);
            }else
                abort(500, 'Could not upload image icon :(');
        }*/
        if ($request->hasFile('imgSmall')) {
            if ($request->file('imgSmall')->isValid()) {
                $extension = $request->imgSmall->extension();
                $request->imgSmall->move(public_path().'/img/img/items/small', $item->id.".".$extension);
            }else
                abort(500, 'Could not upload image small :(');
        }
        if ($request->hasFile('imgBig')) {
            if ($request->file('imgBig')->isValid()) {
                $extension = $request->imgBig->extension();
                $request->imgBig->move(public_path().'/img/img/items/big', $item->id.".".$extension);
            }else
                abort(500, 'Could not upload image big :(');
        }
        if (isset($_GET['ReportBack']))
            return redirect("/admin/report?ReportBack={$_GET['ReportBack']}");
        if (isset($_GET['groupRoute']))
            return redirect("/admin/itemgroups?id={$_GET['ids']}&name={$_GET['name']}&page={$_GET['page']}&parentId={$_GET['parentId']}&popularSort={$_GET['popularSort']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}");
        else 
            return redirect("/admin/items?id={$_GET['ids']}&name={$_GET['name']}&longName={$_GET['longName']}&page={$_GET['page']}&date={$_GET['date']}&sorting={$_GET['sorting']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}");
    }

    public function show(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('items_view')) {
            return redirect()->route('home');
        }
        $item = ItemsLink::find($id);
        $parent = ItemGroups::find($item->parentId);
        $tags = Tags::getTags($id);
        return view('Admin.items.show', compact('item', 'parent', 'tags'));
    }

    /**
     * Remove Item from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('items_edit')) {
            return redirect()->route('home');
        }
        $item = ItemsLink::find($id);

        $item->delete();
        if (isset($_GET['groupRoute']))
            return redirect("/admin/itemgroups?id={$_GET['id']}&name={$_GET['name']}&page={$_GET['page']}&parentId={$_GET['parentId']}&popularSort={$_GET['popularSort']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}");
        else 
            return redirect("/admin/items?id={$_GET['id']}&name={$_GET['name']}&longName={$_GET['longName']}&page={$_GET['page']}&date={$_GET['date']}&sorting={$_GET['sorting']}&id1c={$_GET['id1c']}&weightId={$_GET['weightId']}");
    }


    public function itemsSearch(Request $request)
    {
        $id=$request->id;
        $name=$request->name;
        $longName=$request->longName;
        $page=$request->page;
        $date=$request->date;
        $sorting=$request->sorting;
        $id1c=$request->id1c;
        $weightId=$request->weightId;
        
        $items=ItemsLink::where(function($query) use ($id,$name,$longName,$date,$id1c,$weightId)
            {   
                if($id)
                    $query->where("id", "LIKE", "%{$id}%");
                if($id1c)
                    $query->where("id1c", "LIKE", "%{$id1c}%");
                if($weightId)
                    $query->where("weightId", "LIKE", "%{$weightId}%");
                if($name)
                    $query->where("title", "LIKE", "%{$name}%");
                if($longName)
                    $query->where("longTitle", "LIKE", "%{$longName}%");
                if($date)
                    $query->whereDate('created_at', '=', $date);
    
            })->orderBy('created_at', $sorting)
            ->paginate(20);

        return view('Admin.items.pagination_data', compact('items','id','name','longName','page','date','sorting','id1c','weightId'))->render();

    }

    public function loadImageItems()
    {
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.loadImage', compact('api_token'));
    }

    public function itemsMain()
    {
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.items.itemsMain', compact('api_token'));
    }

    public function loadImageForGallery()
    {
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.gallery', compact('api_token'));
    }

}
