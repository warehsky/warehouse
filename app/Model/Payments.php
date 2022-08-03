<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    protected $table = 'payments';
    public $timestamps = false;
    protected $fillable = [
        'title',
        'sort',
    ];
    /**
     * Возвращает способы оплаты
     */
    public static function getPayments(){
        return Payments::orderBy('sort')->get();
    }
}
