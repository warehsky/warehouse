<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TradeDirections extends Model
{
    protected $table = 'tradeDirections';
    public $timestamps = false;
    protected $connection = 'mtagent';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'title', 
		'guid', 
		'parent'
    ];

    /**
     * Возвращает всех родителей торгового напрвления вверх по дереву
     * Входные параметры:
     * $tdId - ID торгового напрвления
     * Трассировка прекращается по достижению узла со взведенным флагом
     * stopTrace. Т.е. выше определенного торгового направления обобщения контактов не происходит
     */
    public static function getTdTree($tdId){
        $data = array();
        // идем вверх по дереву, собираем всех родителей
        $parentId = $tdId;
        for(;($parentId and $parentId>0);){
            $sql = "SELECT * FROM tradeDirections where id=".$parentId;
            $r = \DB::connection('mtagent')->select( $sql );
            if($r && (int)$r[0]->stopTrace==0){
                $data = array_merge($data, $r);
                $parentId = $r[0]->parent;
            }else
                $parentId = 0;
        }
        return $data;
    }
    /** 
     * Админ панель
     * Возвращает торговые направления для группы, если группа 0 то корневая группа
     * входные параметры:
     * $id - ID группы торговых направлений
    */
    public static function getTradeDirections($id=0){
        $sql = 'SELECT a.id, a.title, (SELECT count(c.id) FROM `tradeDirections` as c WHERE a.id=c.parent) as childs, (select count(id) from orderAttributes where tradeDirection=a.id) as attrs FROM `tradeDirections` as a ';
        if($id)
            $sql .= 'WHERE a.parent=' . $id . ' ORDER BY a.title'; // отбор по группе
        else
            $sql .= 'WHERE !exists(SELECT b.id FROM `tradeDirections` as b WHERE b.id=a.parent) ORDER BY a.title';  // определяем корневой каталог т.е нет родителя
        $result = \DB::connection('mtagent')->select( $sql );
        return $result;
    }
}
