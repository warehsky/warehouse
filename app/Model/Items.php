<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    protected $table = 'items';
    public $timestamps = false;
    protected $connection = 'mtagent';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item', 
    ];

    protected $gr_color = array();
    protected $td = 0;
    
    /** 
     * Админ панель
     * Возвращает группы номенклатуры для подгруппы, если подруппа 0 то корневую, если задано торговое направление, то раскрашивает цветами 
     * группы включенные в торговое направление
     * $gr - id группы
     * $td - id торгового направления
    */
    public function getItem_Groups($gr, $td)
    {
        $this->td = $td;
        if($td)
            $sql = 'SELECT a.id as id, a.item as title, a.guid, CAST(IFNULL(b.id, 0) as CHAR) as parentId, d.groupId, 0 as color  ' 
            . 'FROM items as a left join items as b on a.guid_parent=b.guid '
            . "LEFT JOIN activeItemGroups as d ON d.groupId = a.id  and d.tradeDirectionId=" . $td
            . ' where a.is_group>0 order by a.item';
        else
            if($gr)
                $sql = 'SELECT a.id as id, a.item as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId, (select count(id) as children from items where is_group>0 and guid_parent = a.guid  ) as childs, null as groupId, 0 as color  ' 
                . 'FROM items as a left join items as b on a.guid_parent=b.guid '
                . 'where b.id= ' . $gr . ' and a.is_group>0 order by a.item';
            else
                $sql = 'SELECT a.id as id, a.item as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId, (select count(id) as children from items where is_group>0 and guid_parent = a.guid  ) as childs, null as groupId, 0 as color  ' 
                . 'FROM items as a left join items as b on a.guid_parent=b.guid '
                . 'where !exists(SELECT d.id FROM `items` as d WHERE d.guid=a.guid_parent) and a.is_group>0 order by a.item';
        
        $result = \DB::connection($this->connection)->select( $sql );
        
        if($td){
            $groups = array();
            $res = array();
            if($result)
                foreach ($result as $key => $item) {
                    $groups[$item->id] = $item;
                    if($gr == $item->parentId)
                        $res[] = $item;
                }
            $this->gr_color = $groups;
            if($gr and $gr==$groups[$gr]->groupId){
                foreach($res as $r){
                    $r->color = "active";
                    $r->childs = count($this->getChilds($r->id));
                    $r->items = Items::getGroupItemsCount($r->id, $td);
                }
            }else
                $this->setItemGroupColor( $res , $td);
        }else{
          $res = $result;  
        }
        // идем вверх по дереву, собираем всех родителей
        foreach($res as $rs){
            $parsname = [];
            $parentId = $rs->parentId;
            for(;($parentId and $parentId>0);){
                $sql = 'SELECT a.id as id, a.item as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId  ' 
                . 'FROM items as a left join items as b on a.guid_parent=b.guid '
                . 'where a.id= ' . $parentId ;
                $r = \DB::connection($this->connection)->select( $sql );
                if($r){
                    $parentId = $r[0]->parentId;
                    $parsname[] = '<span class="gr-path">' . $r[0]->title . '</span>';
                }else
                    $parentId = 0;
            }
            $rs->parsname = implode(" | ", array_reverse($parsname));
        }
        // удалить пустые
        // foreach($res as $index=>$rs){
        //     if($rs->color == "disabled" || ($rs->childs==0 && $rs->items==0))
        //         unset($res[$index]);
        // }
        return $res;
    }
    /**
     * Админ панель
     * остатки складов
     * Входные праметры
     * $group - ID группы
     * $price - ID цены
     * $area - регион
     * $search - строка для поиска по названию
     */
    public static function getRemains($group=0, $price=2, $area=1, $search='', $tm='group'){
        $result = Items::select( 'items.id', 'items.item', 'items.guid_parent', 'items.guid',
                \DB::raw('(select max(prices.value) from prices where items.id = prices.itemId and prices.areaId = '.$area.' and prices.priceType='.$price.') as price'), 
                \DB::raw('(select sum(quantity) from warehouseItems where items.id = warehouseItems.itemId) as quantity'), 
                \DB::raw('(select max(id) from items as b where items.guid_parent = b.guid ) as parentId') );
        if($group){
            if($tm == 'group')
                $result = $result->whereIn('items.guid_parent', [$group]);
            else
                $result = $result->where('trade_mark_id', '=', $group);
        }else
            return [];
        if(strlen($search))
            $result = $result->where('items.item', 'like', '%'.$search.'%');
        $result = $result->where('items.is_group', '=', 0);
        $result = $result->orderBy('items.guid_parent')->orderBy('items.item')
                ->paginate(config('loadapi.PGINATE_WAREHOUSE'));
        
        // идем вверх по дереву, собираем всех родителей
        foreach($result as $rs){
            $parIds = [];
            $parentId = $rs->parentId;
            for(;($parentId and $parentId>0);){
                $sql = 'SELECT a.id as id, a.item as title, CAST(IFNULL(b.id, 0) as CHAR) as parentId  ' 
                . 'FROM items as a left join items as b on a.guid_parent=b.guid '
                . 'where a.id= ' . $parentId ;
                $r = \DB::connection('mtagent')->select( $sql );
                if($r){
                    $parentId = $r[0]->parentId;
                    $parIds[] = $r[0]->id;
                }else
                    $parentId = 0;
            }
            $rs->parIds = implode(",", array_reverse($parIds));
        }
        return $result;
    }
    /** 
     * Админ панель
     * $gr_color - все подгруппы
     * $groups - подгруппа которую нужно раскрасить
    */
    private function setItemGroupColor($groups, $td){
        
        if(count($groups)){
            foreach($groups as $group){
                $group->childs = count($this->getChilds($group->id));
                $group->color = $this->getItemGroupColor($group->id);
                $group->items = Items::getGroupItemsCount($group->id, $td);
            }
            
        }
    }
    /**
     * Админ панель
     * получить всех детей узла
     */
    private function getChilds($node){
        $childs = array();
        foreach( $this->gr_color as $key=>$gr ){
            if( $gr->parentId == $node )
                $childs[$key] = $gr;
        }
        return $childs;
    }
    /** 
     * Админ панель
     * Обход детей и получение цвета узла   
     * 
    */
    private function getItemGroupColor( $id ){
        $color = '';
        if($this->gr_color[$id]->groupId<>''){ // включен в торговом направлении
            return 'active';
        }else{
            $childs = $this->getChilds($id);
            if(count($childs) == 0){  //нет детей
                return 'disabled';
            }else
                foreach($childs as $group){
                    $color1 = $this->getItemGroupColor($group->id);
                    if($color == '')
                        $color = $color1;
                    else
                        if($color1 <> $color)
                            return 'mixed';
                }
        }
        return $color;
    }
    /**
     * Возвращает номенклатуру для группы и для указанного торгового напрвления
     */
    public static function getGroupItemsCount($grId, $tdId){
        $sql = "select count(a.id) count " .
        'FROM items as a left join items as b on a.guid_parent=b.guid ' .
        'inner join activeItemGroups act on b.id=act.groupId ' .
        'where b.id= ' . $grId . ' and a.is_group=0 and act.tradeDirectionId=' . $tdId;
        $result = \DB::connection('mtagent')->select( $sql );
        return $result[0]->count;
    }
}
