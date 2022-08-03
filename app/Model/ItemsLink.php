<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemsLink extends Model
{
    protected $table = 'itemsLink';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'longTitle',
        'descr',
        'parentId',
        'moderatorId',
        'mult',
        'id',
        'carouselOrder',
        'prepayment',
        'popular',
        'autoPopular'
    ];
    
    /** 
     * Возвращает товарные позиции для выбранной группы товаров web каталога
     * входные параметры:
     * groupId - группа товара
     * 
     * text - поиск по наименованию товара
    */
    public static function getItems($groupId, $text = '', $tags = [], $page=0, $sort='popular', $price_from=0, $price_to=2000000, $ids=[], $carusel=0){
        $areaId = config('shop.areaId', 1); // регион для цены
        $priceType =config('shop.priceType', 16); // тип цены обычная
        $priceType2 =config('shop.priceType2', 32); // тип цены акция
        $priceType3 =config('shop.priceType3', 64); // тип цены скидка за кол-во
        $warehouses = config('shop.warehouses', '39'); // тип цены скидка за кол-во
        
        $strWhere = '';
        if($text != '')
            $strWhere = " and i.title like '%" . $text . "%' ";
        
        if($groupId){
            if((int)$groupId>0)
                $strWhere .= " and i.parentId={$groupId} ";
            else{
                if(strlen($groupId)>1 && mb_strpos($groupId, "]")){
                    $_group = str_replace("[", "(", $groupId);
                    $_group = str_replace("]", ")", $_group);
                    $strWhere .= " and i.parentId in{$_group} ";
                }
            }
        }
        
        if($ids && count($ids)>0)
            $strWhere .= " and i.id in(" . implode(',', $ids) . ") ";
        $tags_count = count($tags);
        if($tags && $tags_count>0){
            $_tags = implode(",", $tags);
            
            $tag_items = self::getTags($_tags);
            
            $tagGroups = self::getTagGroups($_tags, $groupId, $text);
        }else{
            $sql = "select it.tagId  
                from mtagent.prices as p 
                RIGHT JOIN mtShop.itemsLink as i on i.id=p.itemId  
                INNER JOIN mtShop.itemTags as it on it.itemId=i.id 
                WHERE p.areaId = {$areaId} and p.priceType={$priceType}   {$strWhere} 
                GROUP BY it.tagId";
                
            $tgs = \DB::connection()->select( $sql );
            
            if(count($tgs)==0)
                $tagGroups = [];
            else{
                $_tgs = [];
                foreach($tgs as $tg){
                    $_tgs[] = $tg->tagId;
                }
                $tagGroups = self::getTagGroups(implode(",", $_tgs), $groupId, $text);
            }
        }
        $num = env("GOODS_IN_PAGE", 50);
        if($page <= 0)
            $page = 1;
        $start = $page * $num - $num;
        if($start < 0)
            $start = 0;
        
        switch($sort){
            case 'popular':
                $sortby = 'i.popular DESC';
            break;
            case 'priceup':
                $sortby = 'p.value ASC';
            break;
            case 'pricedown':
                $sortby = 'p.value DESC';
            break;
            default:
                $sortby = 'i.popular DESC';
        }
        
        $sql = "select p.value as price, i.id, i.id as itemId, i.title, i.longTitle, i.descr, i.parentId, i.discountBound, i.pickup, i.prepayment, i.mult, i.parentId, i.weightId,
                (select case when i.prepayment>0 then 1 else case when EXISTS(select id from mtShop.warehouseGroups where groupId=i.parentId and warehouseId=0) then 100 else sum(quantity) end end from mtagent.warehouseItems as w INNER JOIN mtShop.warehouseGroups as wg on w.`warehouseId`=wg.warehouseId where w.itemId = i.id and wg.groupId=i.parentId) as quantityAll,
                (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType2} and pt.areaId = {$areaId} and pt.itemId=i.id) as discountPrice, 
                (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType3} and pt.areaId = {$areaId} and pt.itemId=i.id) as stockPrice 
                from mtagent.prices as p 
                RIGHT JOIN mtShop.itemsLink as i on i.id=p.itemId 
                inner join mtShop.itemGroups as ig on i.parentId=ig.id 
                WHERE i.deleted=0 and p.areaId = {$areaId} and p.priceType={$priceType} and p.value between {$price_from} and {$price_to}  {$strWhere} 
                order by if(quantityAll>0, 1, 0) desc, {$sortby} ";
        $items = \DB::connection()->select( $sql );

        // file_put_contents("/home/viktor/work/logs/log2.txt", print_r("", true));
        if(count($items)>0){
            $minprice = $items[0]->price;
            $maxprice = $items[0]->price;
        }else{
            $minprice = 0;
            $maxprice = 0;
        }
        foreach($items as $ind=>$item){
            if($minprice > $item->price)
                $minprice = $item->price;
            if($maxprice < $item->price)
                $maxprice = $item->price;
            
            
            
            if(\Storage::disk('public')->exists('img/img/items/small/'.$item->id.'.png'))
                $item->image = "/img/img/items/small/" . $item->id . ".png";
            else{
                
                if(\Storage::disk('public')->exists('img/img/catalog/shadow/'.$item->parentId.'.png'))
                    $item->image = 'img/img/catalog/shadow/'.$item->parentId.'.png';
                else{
                    $parentId = $item->parentId;
                    $item->image = "/img/item_default.png";
                    for(;$parentId>0;){
                        $group = ItemGroups::find($parentId);
                        if($group){
                            $parentId = $group->parentId;
                            
                            if(\Storage::disk('public')->exists('img/img/catalog/shadow/'.$parentId.'.png')){
                                $item->image = 'img/img/catalog/shadow/'.$parentId.'.png';
                                break;
                            }
                        }else
                            $parentId = 0;// выход из цыкла
                    }
                }
            }
        }
        $items = array_values($items);
        return ['items' => $items, 'inpage' => $num, 'taggroups' => $tagGroups, 'minprice' => $minprice, 'maxprice' => $maxprice];
    }
    private static function getTagGroups($tags, $groupId=0, $text=''){
        $strWhere = "";
        if($groupId){
            if((int)$groupId>0)
                $strWhere .= " i.parentId={$groupId} and ";
            else{
                if(strlen($groupId)>1 && mb_strpos($groupId, "]")){
                    $_group = str_replace("[", "(", $groupId);
                    $_group = str_replace("]", ")", $_group);
                    $strWhere .= " i.parentId in{$_group} and ";
                }
            }
        }
        if($text != '')
            $strWhere = "  i.title like '%" . $text . "%' and";
        $tagGroups =  \DB::connection()->select( "SELECT groupId FROM tags WHERE id IN ({$tags}) group by groupId" );
        foreach($tagGroups as $tagGroup){
            $group = TagGroups::find($tagGroup->groupId);
            $tagGroup->title = $group->title;
            $sql = "SELECT max(t.id) as id, max(t.title) as title FROM `itemTags` as l INNER JOIN itemsLink as i ON l.itemId=i.id INNER JOIN tags as t ON t.id=l.tagId  ";
            $sql .= "WHERE {$strWhere} t.groupId={$tagGroup->groupId} and t.visible=1 group by t.id order by t.sort, t.title";
            
            $tagGroup->tagItems =  \DB::connection()->select( $sql );
        }
        return $tagGroups;
    }
    /* Возвращает теги с привязкой к товару выделенные для поиска*/
    private static function getTags($tags){
        $sql = "SELECT itemId, GROUP_CONCAT(', ', tagId) as tags, count(itemId) as count FROM itemTags WHERE tagId IN ({$tags}) GROUP BY itemId ";
        $result = \DB::connection()->select( $sql );
        $items = [];
        if($result)
            foreach($result as $row){
                $items[$row->itemId] = $row;
            }
        return $items;
    }
    /**
     * возвращает цену товара с учетом скидки 
    */
    public static function getItemPrice($itemId, $quantity){
        if(!$itemId) // товар не передан
            return 0;
        $areaId = config('areaId', 1); // регион для цены
        $priceType =config('priceType', 32); // тип цены обычная
        $priceType2 =config('priceType2', 64); // тип цены акция
        $priceType3 =config('priceType3', 16); // тип цены скидка за кол-во
        $sql = "select i.discountBound, 
                (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType} and pt.areaId = {$areaId} and pt.itemId=i.id) as price, 
                (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType2} and pt.areaId = {$areaId} and pt.itemId=i.id) as discountPrice, 
                (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType3} and pt.areaId = {$areaId} and pt.itemId=i.id) as stockPrice 
                from mtShop.itemsLink as i where i.id={$itemId}";
        $items = \DB::connection()->select( $sql );
        if(!$items || count($items)<=0) // товар не найден
            return 0;
        $item = $items[0];
        if($item->stockPrice) // товар акционный кол-во не учитываем
            return ['price' => $item->stockPrice, 'priceType' => $priceType3];
        if($item->discountBound==0 || $item->discountBound==2000000 || $item->discountBound > $quantity || !$item->discountPrice) 
            return ['price' => $item->price, 'priceType' => $priceType];
        else
            return ['price' => $item->discountPrice, 'priceType' => $priceType2];
        
    }
    /** 
     * Возвращает товарные позиции для пакетов (товары которых нет на сайте)
     * входные параметры:
     * 
     * 
     * 
    */
    public static function getPacks($groupId = 179){
        $areaId = config('shop.areaId', 1); // регион для цены
        $priceType =config('shop.priceType', 16); // тип цены обычная
        $priceType2 =config('shop.priceType2', 32); // тип цены акция
        $priceType3 =config('shop.priceType3', 64); // тип цены скидка за кол-во
        $warehouses = config('shop.warehouses', '39'); // тип цены скидка за кол-во

        $sql = "select p.value as price, i.id, i.title, i.descr, i.parentId, i.discountBound, i.pickup, i.prepayment, i.mult, i.parentId, i.weightId,
                '1000' as quantityAll,
                (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType2} and pt.areaId = {$areaId} and pt.itemId=i.id) as discountPrice, 
                (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType3} and pt.areaId = {$areaId} and pt.itemId=i.id) as stockPrice 
                from mtagent.prices as p 
                RIGHT JOIN mtShop.itemsLink as i on i.id=p.itemId 
                WHERE p.areaId = {$areaId} and p.priceType={$priceType} and i.parentId={$groupId}";
        $items = \DB::connection()->select( $sql );
        return $items;
    }
}
