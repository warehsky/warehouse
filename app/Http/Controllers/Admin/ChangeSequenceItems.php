<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ItemsLink;
use App\Model\Tags;
use App\Model\TagGroups;


class ChangeSequenceItems extends Controller
{
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('changeSequenceStock_all')) {
            return redirect()->route('home');
        }
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.changeSequenceItems.index', compact('api_token'));
    }



    public function findTagItems(Request $request)
    {
        if ($request->tag)
        {
            $sql = "SELECT itemsLinks.id, itemsLinks.title, itemsLinks.carouselOrder FROM itemTags as itemTag
                RIGHT JOIN itemsLink as itemsLinks on itemsLinks.id=itemTag.itemId 
                WHERE itemTag.tagId={$request->tag} ORDER BY itemsLinks.carouselOrder";
            $items = \DB::connection()->select($sql);  

            $stockItems=\DB::connection()->select("SELECT itemId FROM itemTags WHERE tagId=330");

            

            $resultItems = array();
            foreach ($items as $key => $value)
            {

                if (is_int(array_search($value->id,array_column($stockItems, 'itemId')))) 
                    $stock="Акция";
                else 
                    $stock="";

                $resultItems[] = [$value->id,$value->title,$value->carouselOrder,$stock,"<input size=6 type='number' class='unShow' value=$value->carouselOrder id='input_$value->id'><button value=$value->id class='btn btn-xs btn-success editSequence unShow'  id='button_$value->id'>Сохранить</button><button value='".$value->id."' id='edit' class='btn btn-xs btn-info'>Редактировать</button>"];
            }
            return json_encode(["items" => $resultItems], JSON_UNESCAPED_UNICODE );
        }
    }



	public function getAllTag(Request $request)
    {

        $data=Tags::select('title','id')->where('groupId',$request->id)->get();
        return response()->json($data);
    }

    public function getAllParentTag()
    {

        $data=$tagGroup=TagGroups::all();
        return response()->json($data);
    }
    

    public function getParentId(Request $request)
    {

        $parentId=Tags::select('groupId')->where('id',$request->tag)->first();
        return response()->json($parentId);
    }
    

    public function update(Request $request)
    {   
        if ($request->input('id') && $request->input('carouselOrder'))
        {
            $item=ItemsLink::find($request['id']);
            $item->update(['carouselOrder'=>$request['carouselOrder']]);
            return json_encode(["code" => 200,"msg"=>'Изменено'], JSON_UNESCAPED_UNICODE );
        }
        else 
            return json_encode(["code" => 404,"msg"=>'Нет ID товара или позиции'], JSON_UNESCAPED_UNICODE );
    }

}
