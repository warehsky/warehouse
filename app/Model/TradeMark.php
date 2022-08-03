<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TradeMark extends Model
{
    protected $table = 'trade_mark';
    public $timestamps = false;
    protected $connection = 'mtagent';
    
    /** 
     * Админ панель
     * Возвращает группы торговых марок для подгруппы, если подруппа 0 то корневую 
     * 
     * $gr - id группы
    */
    public static function getItem_Tm_Groups($gr)
    {
        if($gr)
            $sql = 'SELECT a.id as id, a.supplier as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId, (select count(id) as children from trade_mark where  guid_parent = a.guid  ) as childs  ' 
            . 'FROM trade_mark as a left join trade_mark as b on a.guid_parent=b.guid '
            . 'where b.id= ' . $gr . ' order by a.supplier';
        else
            $sql = 'SELECT a.id as id, a.supplier as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId, (select count(id) as children from trade_mark where  guid_parent = a.guid  ) as childs  ' 
            . 'FROM trade_mark as a left join trade_mark as b on a.guid_parent=b.guid '
            . 'where !exists(SELECT d.id FROM `trade_mark` as d WHERE d.guid=a.guid_parent) order by a.supplier';
        $result = \DB::connection('mtagent')->select( $sql );
        // идем вверх по дереву, собираем всех родителей
        foreach($result as $res){
            $parsname = [];
            $parentId = $res->parentId;
            for(;($parentId and $parentId>0);){
                $sql = 'SELECT a.id as id, a.supplier as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId  ' 
                . 'FROM trade_mark as a left join trade_mark as b on a.guid_parent=b.guid '
                . 'where a.id= ' . $parentId . ' order by a.supplier';
                $r = \DB::connection('mtagent')->select( $sql );
                if($r){
                    $parentId = $r[0]->parentId;
                    $parsname[] = '<span class="gr-path">' . $r[0]->title . '</span>';
                }else
                    $parentId = 0;
            }
            $res->parsname = implode(" | ", array_reverse($parsname));
        }
        return $result;
    }
    /** 
     * Админ панель
     * Возвращает торговые марки без подрупп 
     * 
     * $gr - id группы
    */
    public static function getItems_Tm()
    {
        ini_set('memory_limit','512M');
        $sql = 'SELECT a.id as id, a.supplier as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId  ' 
        . 'FROM trade_mark as a left join trade_mark as b on a.guid_parent=b.guid '
        . ' order by a.supplier';
        $result = \DB::connection('mtagent')->select( $sql );
        // идем вверх по дереву, собираем всех родителей
        foreach($result as $res){
            $res->parents = "";
            $parsname = [];
            $parentId = $res->parentId;
            if($res->parentId>0)
            for(;($parentId and $parentId>0);){
                $sql = 'SELECT a.id as id, a.supplier as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId  ' 
                . 'FROM trade_mark as a left join trade_mark as b on a.guid_parent=b.guid '
                . 'where a.id= ' . $parentId . ' order by a.supplier';
                $r = \DB::connection('mtagent')->select( $sql );
                if($r){
                    $parentId = $r[0]->parentId;
                    $parsname[] = $r[0]->title;
                    $res->parents .= ($res->parents===""? $res->parentId . "," : ",") . $parentId;
                }
                else
                    $parentId = 0;
            }
            $res->parsname = implode("|", $parsname);
            if($res->parents <> ""){
                $p = explode(",", $res->parents);
                $res->parents = "";
                for($i = count($p)-2; $i >= 0; $i--)
                    $res->parents .= ($res->parents===""? "" : ",") . $p[$i];
            }
        }
        return $result;
    }
}
