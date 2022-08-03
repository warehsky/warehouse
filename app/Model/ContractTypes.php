<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ContractTypes extends Model
{
    protected $table = 'contractTypes';
    public $timestamps = false;
    protected $connection = 'mtagent';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'value', 
    ];
    /**
     * 
     * Возвращает типы контрактов
     */
    public static function getContractTypes(){
        $result = ContractTypes::select( 'id', 'title');
        
        $result = $result->get();
        return $result;
    }
}
