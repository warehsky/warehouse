<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use PhpOption\Option;

class Options extends Model
{
    protected $table = 'options';
    public $timestamps = false;
    protected $fillable = [
        'value',
        'field',
        'type'
    ];
    /**
     * Возвращает одну опцию
     */
    public static function getOption($opt){
        $value = Options::select('id', 'value', 'type')->where('field', "".$opt."")->first();
        $val='';
        if($value){
            switch($value->type){
                case 'int':
                case 'integer':
                    $val = (int)$value->value;
                break;
                case 'double':
                    $val = (double)$value->value;
                break;
                case 'string':
                    $val = (string)$value->value;
                break;
                default:
                    $val = $value->value;
                break;
            }
        }
        
        return $val;
    }

}
