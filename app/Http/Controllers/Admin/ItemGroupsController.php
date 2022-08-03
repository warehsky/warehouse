<?php

namespace App\Http\Controllers\Admin;

use App\Model\ItemGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController as Basectrl;
use App\Http\Requests\Admin\StoreItemGroupsRequest;
use App\Http\Requests\Admin\UpdateItemGroupsRequest;
use App\Model\ItemsLink;
use App\Model\ItemsLinkGroup;
use App\Model\Warehouses;
use App\Model\WarehouseGroups;
use Illuminate\Support\Facades\Storage;

class ItemGroupsController extends Controller
{

    const notUseGroupTag = "1";   //Группа(ы) тегов не учитывающихся при группировки товаров (Текстиль) (Пример: 1,45,15,747)

    /**
     * Display a listing of Group.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('itemGroups_view')) {
            return redirect()->route('home');
        }
        $itemgroups = ItemGroups::all();
        $groups = [];
        $group = null;
        if($request->input('parentId')){
            $groups[] = (int)$request->input('parentId');
            $parent = ItemGroups::find($request->input('parentId'));
            $group = ItemGroups::find($request->input('parentId'));
            
            for(;$parent;){
                $parent = ItemGroups::find($parent->parentId);
                
                if($parent)
                    $groups[] = $parent->id;
            }
        }
        $page = $request->input('page') ?? 1;
        $groups = implode(',', $groups);
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.itemgroups.index', compact('itemgroups', 'groups', 'group', 'api_token', 'page'));
    }

    /**
     * Show the form for creating new Group.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('itemGroups_edit')) {
            return redirect()->route('home');
        }
        $itemgroups = ItemGroups::orderBy('title')->get();
        if($request->input('parentId'))
            $parent = ItemGroups::find($request->input('parentId'));
        else
            $parent = null;
        
        return view('Admin.itemgroups.create', compact('itemgroups', 'parent'));
    }

    /**
     * Store a newly created Group in storage.
     *
     * @param  \App\Http\Requests\StoreItemGroupsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreItemGroupsRequest $request)
    {
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        if(is_null($data['parentId']))
            $data['parentId'] = 0;
        $group = ItemGroups::create($data);
        ///////////////////
        if ($request->hasFile('imgIcon')) {
            if ($request->file('imgIcon')->isValid()) {
                $extension = $request->imgIcon->extension();
                $request->imgIcon->move(public_path().'/img/img/catalog/icons', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image icon :(');
        }
        if ($request->hasFile('imgSmall')) {
            if ($request->file('imgSmall')->isValid()) {
                $extension = $request->imgSmall->extension();
                $request->imgSmall->move(public_path().'/img/img/catalog/small', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image small :(');
        }
        if ($request->hasFile('imgBig')) {
            if ($request->file('imgBig')->isValid()) {
                $extension = $request->imgBig->extension();
                $request->imgBig->move(public_path().'/img/img/catalog/big', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image big :(');
        }
        if ($request->hasFile('imgShadow')) {
            if ($request->file('imgShadow')->isValid()) {
                $extension = $request->imgShadow->extension();
                $request->imgShadow->move(public_path().'/img/img/catalog/shadow', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image shadow :(');
        }
        ///////////////////
        
        if($data['parentId']==0)
            return redirect()->route('itemgroups.index');
        else
            return redirect()->route('itemgroups.index', ['parentId' => $data['parentId']]);
    }


    /**
     * Show the form for editing Group.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('itemGroups_edit')) {
            return redirect()->route('home');
        }
        $group = ItemGroups::find($id);
        $itemgroups = ItemGroups::orderBy('title')->get();
        $warehouses = Warehouses::orderBy('warehouse')->get();
        $lwarehouses = WarehouseGroups::where('groupId', $group->id)->pluck('warehouseId')->toArray();
        $countPercent = 100 - ItemGroups::where('id','!=',$id)->sum('percentQuantity');
        $base = new Basectrl();
        $countItemsBestSeller = $base->getOption('countBestSeller');
        return view('Admin.itemgroups.edit', compact('group', 'itemgroups', 'warehouses', 'lwarehouses','countPercent','countItemsBestSeller'));
    }

    /**
     * Update Group in storage.
     *
     * @param  \App\Http\Requests\UpdateItemGroupsRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateItemGroupsRequest $request, $id)
    {
        $lastPercent = 100 - ItemGroups::where('id','!=',$id)->sum('percentQuantity');
        if ($lastPercent<(int)$request->percentQuantity)
            return back()->with('error','Указанный процент товаров больше допустимого');

        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        if(is_null($data['parentId']))
            $data['parentId'] = 0;
        $group = ItemGroups::find($id);

        $group->update($data);
        WarehouseGroups::where('groupId', $id)->delete();
        foreach($request->input('warehouses') as $w){
            $t[]=WarehouseGroups::create(['groupId'=>$id, 'warehouseId'=>$w]);
        }
        if ($request->hasFile('imgIcon')) {
            if ($request->file('imgIcon')->isValid()) {
                $extension = $request->imgIcon->extension();
                $request->imgIcon->move(public_path().'/img/img/catalog/icons', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image icon :(');
        }
        if ($request->hasFile('imgSmall')) {
            if ($request->file('imgSmall')->isValid()) {
                $extension = $request->imgSmall->extension();
                $request->imgSmall->move(public_path().'/img/img/catalog/small', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image small :(');
        }
        if ($request->hasFile('imgBig')) {
            if ($request->file('imgBig')->isValid()) {
                $extension = $request->imgBig->extension();
                $request->imgBig->move(public_path().'/img/img/catalog/big', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image big :(');
        }
        if ($request->hasFile('imgShadow')) {
            if ($request->file('imgShadow')->isValid()) {
                $extension = $request->imgShadow->extension();
                $request->imgShadow->move(public_path().'/img/img/catalog/shadow', $group->id.".".$extension);
            }else
                abort(500, 'Could not upload image shadow :(');
        }
        if($data['parentId']==0)
            return redirect()->route('itemgroups.index');
        else
            return redirect()->route('itemgroups.index', ['parentId' => $data['parentId']]);
    }

    public function show(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('itemGroups_view')) {
            return redirect()->route('home');
        }
        $group = ItemGroups::find($id);
        $parent = ItemGroups::find($group->id);
        return view('Admin.itemgroups.show', compact('group', 'parent'));
    }

    /**
     * Remove Group from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('itemGroups_edit')) {
            return redirect()->route('home');
        }
        $group = ItemGroups::find($id);
        $items = ItemsLink::where('parentId', $id)->count();
        $grcount = ItemGroups::where('parentId', $id)->count();
        
        if($items>0 || $grcount>0)
            return redirect()->route('itemgroups.index');
        $group->delete();
            
        return redirect()->route('itemgroups.index');
    }

    /**
     * Delete all selected Group at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        
        ItemGroups::whereIn('id', request('ids'))->delete();

        return response()->noContent();
    }
    /**
     * Окно управления сопутствующими товарами
     */
    public function suggests(Request $request)
    {
        $itemgroups = ItemGroups::all();
        $groups = [];
        $group = null;
        if($request->input('parentId')){
            $groups[] = (int)$request->input('parentId');
            $parent = ItemGroups::find($request->input('parentId'));
            $group = ItemGroups::find($request->input('parentId'));
            
            for(;$parent;){
                $parent = ItemGroups::find($parent->parentId);
                
                if($parent)
                    $groups[] = $parent->id;
            }
        }
        $page = $request->input('page') ?? 1;
        $groups = implode(',', $groups);
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.itemgroups.suggests', compact('itemgroups', 'groups', 'group', 'api_token', 'page'));
    }

    public function itemsKassa(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('itemKassa_view')) {
            return redirect()->route('home');
        }
        $itemgroups = ItemGroups::all();
        $groups = [];
        $group = null;
        if($request->input('parentId')){
            $groups[] = (int)$request->input('parentId');
            $parent = ItemGroups::find($request->input('parentId'));
            $group = ItemGroups::find($request->input('parentId'));
            
            for(;$parent;){
                $parent = ItemGroups::find($parent->parentId);
                
                if($parent)
                    $groups[] = $parent->id;
            }
        }
        $page = $request->input('page') ?? 1;
        $groups = implode(',', $groups);
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.itemsKassa.index', compact('itemgroups', 'groups', 'group', 'api_token', 'page'));
    }

     /**
     * Возвращает все подтовары для заданного id
     * Входящие параметры
     * id - код главного товара
     */
    public function getChildItems(Request $request)
    {
        if (isset($request->id))
        {
            $sql = "SELECT i.id, i.title FROM mtShop.itemsLink as i
            JOIN mtShop.itemsLinkGroup as j on j.itemId=i.id
            WHERE j.parentId={$request->id}";
            $res = \DB::connection()->select($sql);
            return response()->json($res);
        }
        else 
            return json_encode( ['code' => 404, 'mesage' => 'Нет id главного товара'] );
    }

    /**
     * Делает товар главным или убирает это свойство (Изменение поля MainItem в таблице ItemsLink)
     * Входящие параметры 
     * id - код товара 
    */
    public function changeMainStatusItem(Request $request)
    {
        if (isset($request->id))
        {
            $item=ItemsLink::find($request->id);
            if ($item->mainItem)
                ItemsLinkGroup::where('parentId',$request->id)->delete();
            else
            {
                $find=ItemsLinkGroup::where('itemId',$request->id)->get()->toArray();
                if (count($find)>0)
                    return json_encode( ['code' => 500, 'mesage' => 'Выбранный товар является подтоваром (Главный товар [#'.$find[0]['parentId'].'])'] );
            }
            $item->mainItem=!$item->mainItem;
            $item->save();
            return json_encode( ['code' => 200, 'mesage' => 'Статус изменен'] );
        }        
        else 
            return json_encode( ['code' => 404, 'mesage' => 'Нет id товара'] );
    }

    /**
     * Добавление подтовара к главному
     * Входящие параметры 
     * itemId - код подтовара
     * parentId - код главного товара 
     */
    public function addNewItemInGroup(Request $request)
    {
        if (isset($request->itemId) && isset($request->parentId))
            if ($request->itemId!=$request->parentId)
            {
                $find=ItemsLinkGroup::where('itemId',$request->itemId)->get()->toArray();
                if (count($find)>0)
                    return json_encode( ['code' => 500, 'mesage' => 'Выбранный товар уже используется (Главный товар [#'.$find[0]['parentId'].'])'] );
                    
                $NotUseGroupTag = self::notUseGroupTag;
                $sqlMain = "SELECT DISTINCT k.id as groupId FROM itemTags as i 
                            JOIN tags as j ON i.tagId=j.id
                            JOIN tagGroups as k ON k.id=j.groupId
                            WHERE k.id!=$NotUseGroupTag AND i.itemId = ";   

                $sql = $sqlMain.$request->parentId; 
                $tag=collect(\DB::Connection()->select($sql))->toArray();
                $parentTag = array_column($tag,'groupId');

                $sql = $sqlMain.$request->itemId;
                $Itag=collect(\DB::Connection()->select($sql))->toArray();
                $itemTag = array_column($Itag,'groupId');

                $diffRight = array_diff($parentTag,$itemTag);
                $diffLeft = array_diff($itemTag,$parentTag);
                if (empty($diffLeft) && empty($diffRight))
                {
                    $newItem= new ItemsLinkGroup;
                    $newItem->itemId=$request->itemId;
                    $newItem->parentId=$request->parentId;
                    $newItem->save();
                    return json_encode( ['code' => 200, 'mesage' => 'Товар добавлен'] );
                }
                else 
                    return json_encode( ['code' => 500, 'mesage' => 'Несоответствие тегов'] );
            }
            else 
                return json_encode( ['code' => 500, 'mesage' => 'Товары совпадают'] );
        else 
            return json_encode( ['code' => 404, 'mesage' => 'Нет id главного товара или id подтовара'] );
    }
    /**
     * Удаление подтовара
     * Входящие параметры 
     * itemId - код подтовара
     * parentId - код главного товара
     */
    public function deleteItemInGroup(Request $request)
    {
        if (isset($request->itemId) && isset($request->parentId))
        {
            $item=ItemsLinkGroup::where('itemId',$request->itemId)->where('parentId',$request->parentId)->delete();
            return json_encode( ['code' => 200, 'mesage' => 'Удалено'] );
        }
        else 
            return json_encode( ['code' => 404, 'mesage' => 'Нет id главного товара или id подтовара'] );
    }
}
