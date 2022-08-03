<?php

namespace App\Http\Controllers\Admin;

use App\Model\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Model\WebUsersDiscount;
use App\Console\Commands\CheckNewItems as NewItems;
use App\Console\Commands\UpdateBestSeller as bestSeller;
use App\Http\Controllers\BaseController as Basectrl;

class OptionsController extends Controller
{
    /**
     * Показ списка опций
     */
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('options_all')) {
            return redirect()->route('home');
        }
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.options.edit', compact('api_token'));
    }

    public function getOptions(Request $request){
       
        $options = Options::select('options.id','options.field','options.description','options.value', 'options.subgroup','options.type')
                            ->where('visible','>',0);
        $Groups=Options::select('options.subgroup');
        if ($request->groupId == "Общие")
        {
            $request->groupId=null;
        }
        $Groups = $Groups->where('groupId',$request->groupId);
        $options = $options->where('groupId',$request->groupId);
        
        $options = $options->get();     
        $Groups = $Groups->distinct()->get()->toArray();
        $Groups = array_column($Groups,'subgroup');
                            
        return json_encode([$options,$Groups], JSON_UNESCAPED_UNICODE);
    }

    public function getOptionGroups()
    {
        $Groups=Options::select('options.groupId')->distinct()->orderBy('options.groupId','ASC')->get()->toArray();
        $Groups = array_column($Groups,'groupId');
        return json_encode($Groups, JSON_UNESCAPED_UNICODE);
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data = $request->except('_method', '_token');
        
        foreach($data as $key=>$d){
            $option = Options::where('field', "".$key."")->first();
            
            if($option){
                $option->value = $d;
                $option->save();
            }
        }
        if (isset($data['bringFriendStatus']) && $data['bringFriendStatus']==0)
            WebUsersDiscount::where('friendId','!=', 0)->update(['status' => 0]);

        $optionLink=$request->optionTitle;
        
        return view('Admin.options.edit', compact('optionLink'));
        // return redirect("/admin/optionsIndex")->with(compact('optionLink'));
    }

    public function newItems(Request $request)
    {
        $option = Options::where('field', "NewItemsDay")->first();
        $option->value = $request->day;
        $option->save();
        $NewItems = new NewItems();
        $countDay = $NewItems->checkNewItems();
        return json_encode(['code'=>200,'msg'=>'Выполнено'], JSON_UNESCAPED_UNICODE);
    }

    public function bestSeller(Request $request)
    {
        $option = Options::where('field', "bestSellerDay")->first();
        $option->value = $request->day;
        $option->save();
        $bestSeller = new bestSeller();
        $bestSeller->updateBestSeller(0);// 0 - обновление вручную
        return json_encode(['code'=>200,'msg'=>'Выполнено'], JSON_UNESCAPED_UNICODE);
    }

}
