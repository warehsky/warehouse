<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\Array_;
use App\Http\Controllers\BaseController;
use Jenssegers\Agent\Agent;
use App\Model\Options;

class MapController extends BaseController
{
    /**
     * Поиск адреса по строке
     *
     * 
     */
    public function search(Request $request)
    {
        if(!$request->input('q'))
            return 400;
        $url = 'http://172.20.20.16/geo-1/search?q='.urlencode($request->input('q')).'&accept-language=ru';
        // $data = array("q" => $request->input('q'), "accept-language" => "ru" );
        $data = [];
        $data_string = json_encode($data);
        $result = $this->get_curl($url, $data_string);
        
        return $result;
    }
    /**
     * Поиск зоны
     *
     * 
     */
    public function getZone(Request $request)
    {
        if(!$request->input('lng') || !$request->input('lat'))
            return 400;
        $url = '172.20.20.16/geo-1/getZone?lng='.($request->input('lng')).'&lat='.($request->input('lat'));
        //$url = "http://172.20.20.25:8080/zone/getZone?lng=37.65332755437737&lat=47.92131606722898";
        // $data = array("q" => $request->input('q'), "accept-language" => "ru" );
        $data = [];
        $data_string = json_encode($data);
        $result = $this->get_curl($url, $data_string);
        
        return $result;
    }
    /**
     * Поиск по координатам
     */
    public function getAddress(Request $request){
        if(!$request->input('lng') || !$request->input('lat'))
            return 400;
        $url = '172.20.20.16/geo-1/getAddress?lng='.($request->input('lng')).'&lat='.($request->input('lat'));
        $data = [];
        $data_string = json_encode($data);
        $result = $this->get_curl($url, $data_string);
        
        return $result;
    }
    /**
     * Сохранить опцию
     */
    public function setOption(Request $request){
        if(!isset($request->field) || !isset($request->value)){
            return response()->json(['message'=>'нет ID опции или значения', 'code'=>700], JSON_UNESCAPED_UNICODE);
        }
        $option = Options::select('id', 'value', 'type')->where('field', "".$request->field."")->first();
        $value='';
        if($option){
            switch($option->type){
                case 'int':
                case 'integer':
                    $value = (int)$request->value;
                break;
                case 'double':
                    $value = (double)$request->value;
                break;
                case 'string':
                    $value = (string)$request->value;
                break;
                default:
                    $value = $request->value;
                break;
            }
            
            $option->update(['value' => $value]);
            $option->save();
            return response()->json(['message'=>'значение опции изменено', 'code'=>200], JSON_UNESCAPED_UNICODE);
        }
        return response()->json(['message'=>'значение опции не изменено', 'code'=>700], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Получить опцию
     */
    public function get_option(Request $request){
        if(!isset($request->opt)){
            return response()->json(['message'=>'нет ID опции', 'code'=>700], JSON_UNESCAPED_UNICODE);
        }
        return response()->json(['opt'=> $this->getOption($request->opt), 'code'=>200], JSON_UNESCAPED_UNICODE);
    }
}
