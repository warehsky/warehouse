<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LoadImageController extends Controller
{
    const URL = 'img/img/pics/';

    public function index()
    {
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.loadOneImage',compact('api_token'));
    }

    public function loadImage(Request $request)
    {
        $this->validate($request, ['image' => 'required']);
        $name = Carbon::now()->timezone('Europe/Moscow')->format("YmdHis");
        $extension = $request->image->extension();
        $name=$name.'.'.$extension;
        $request->image->move(public_path()."/".self::URL, $name);
        $url = env('SHOP_URL').self::URL.$name;
        return json_encode( $url, JSON_UNESCAPED_UNICODE ); ;
    }
    public function getAllImage()
    {
        $files = array_reverse(Storage::disk('public')->files("/".self::URL));
        $allData = [];
        foreach($files as $value)
            $allData[] = ['img'=>$value,'url'=>env('SHOP_URL').$value,'name'=>str_replace(self::URL, " ", $value)];
        
        return json_encode( $allData, JSON_UNESCAPED_UNICODE ); 
    }

    public function deleteImage(Request $request)
    {
        $dir = '/'.self::URL.$request->id;
        if(Storage::disk('public')->exists($dir))
        {
            Storage::disk('public')->delete($dir);
            return json_encode(['code'=>200,'msg'=>'Удалено'] , JSON_UNESCAPED_UNICODE ); 
        }
        return json_encode(['code'=>404,'msg'=>'Изображение не найдено'] , JSON_UNESCAPED_UNICODE ); 
    }

}
