<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\Options;
use Carbon\Carbon;

class BaseController extends Controller
{
    public $initiatorPlace_warehouse = 1;
    public $initiatorPlace_operator = 2;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    
    /**
     * Возвращает опцию сайта, если нет параметра возвращает все
     */
    public function getOption($par=''){
        if($par=='')
          $option = Options::all();
        else
          $option = Options::getOption($par);   
        
        return $option;
    }

   

    /**
     * Возвращает json из файла
     */
    public function getJson($path){
        if (!\File::exists($path)) {
            throw new Exception("Invalid File");
        }

        $file = \File::get($path); // string
        $json = json_decode($file);
        return $json;
    }
    
}
