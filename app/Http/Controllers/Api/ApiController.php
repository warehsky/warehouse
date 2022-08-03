<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admins;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Model\Items;
use App\Model\TradeMark;
use App\Model\Tagent;
use App\Model\TradePoints;
use App\Model\ContractTypes;
use App\Model\CacheItemGroups;
use App\Model\ItemGroups;
use App\Model\ItemsLink;
use App\Model\ItemsLinkGroup;
use App\Model\ItemsPercent;
use App\Model\Orders;
use App\Model\OrderItem;
use App\Model\ItemsKassa;
use App\Model\ItemsKassaDate;
use App\Model\User;
use App\Model\TagGroups;
use App\Model\Tags;
use App\Model\GroupSuggests;
use App\Model\Statuses;
use App\Model\Payments;
use App\Model\Admin;
use App\Model\Comments;
use App\Model\CommentsFirm;
use App\Model\ChatMessages;
use App\Model\ChatUsers;
use Carbon\Carbon;
use App\Http\Controllers\BaseController;
use App\Model\WebUsers;
use App\Model\TimeWaves;
use App\Model\WebUsersDiscount;
use App\Model\DeliverySots;
use App\Model\DeliveryZones;
use App\Model\WebUsersNote;
use App\Model\PickupStatuses;
use App\Model\OrderLocks;
use App\Model\OrderAddress;
use App\Model\OrderChanges;


class ApiController extends Controller
{
    private $noteSuffix = " уточненный дом - ";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
             $this->middleware('adminauth.access-token');
            //  $user = Admin::where('id', 1)->first();
            //  \Auth::guard('admin')->login($user);
    }
    /** 
     * возвращает группы товаров для подгруппы (если подгруппа 0, вернет корневые подгруппы )
     * входные параметры:
     * group - группа товара
     * td - торговое направление (если не 0 то вернет окрас подгруппы чтобы видеть включение групп товаров в торговое направление)
     */
    public function getGoodsGroup(Request $request)
    {   
        
        $gr = (int)$request->input('groupId');
        $res = $this->getGroups($gr, $request->input('tm'));
        
        while(count($res)==1 && $res[0]->childs>1)
            $res = $this->getGroups($res[0]->id, $request->input('tm'));
        if($res)
            $res = json_encode($res);
        else
            $res = response()->json([]);
        
        
        return $res;
    }
    /** 
     * Возвращает товарные позиции для выбранной группы товаров
     * входные параметры:
     * groupId - группа товара
     * price - тип цены
     * area - регион
     * search - поиск по наименованию товара
    */
    public function getGoodsItems(Request $request)
    {
        if((int)$request->input('groupId')>0)
            if($request->input('tm')=='mark')
                $gr = $request->input('groupId');
            else
                $gr = Items::find($request->input('groupId'))->guid;//array_filter(explode(",", Items::getItemGroupWithLevelsId($request->input('group'))));
        else
            $gr = 0;
        $res = Items::getRemains($gr, $request->input('price') ?? 2, $request->input('area') ?? 1, $request->input('search'), $request->input('tm') ?? 'group');
        
        $res->links = $res->links();
        return $res->toJson();
    }

    private function getGroups($gr, $tm){
        $td = 0;
        // $user = Tagent::find(\Auth::user()->tagentId);
        // if($user)
        //     $td = $user->tradeDirection;
        
        if($tm == 'group')
            $res = (new Items)->getItem_Groups($gr, $td);
        else
            $res = TradeMark::getItem_Tm_Groups($gr);
        
        $res = array_values($res);// это чтобы индексы были по порядку
        return $res;
    }
    /** 
     * Возвращает торговые точки web клиента
     * входные параметры:
     * 
    */
    public function getTradePoints(Request $request)
    {
        $tradePoints = TradePoints::getTradePoints(\Auth::user()->clientId);
        return json_encode( $tradePoints );
    }
    /** 
     * Возвращает типы договоров
     * входные параметры:
     * 
    */
    public function getContractTypes(Request $request)
    {
        $contractTypes = ContractTypes::getContractTypes();
        return json_encode( $contractTypes );
    }
    /** 
     * Возвращает группы товара из кеша
     * входные параметры:
     * 
    */
    public function getCacheGroups(Request $request)
    {
        $items = [];
        $itms = Items::select('*')->where('is_group', 1)->get();
        foreach($itms as $itm)
            $items[$itm->id] = $itm;
        $td = 0;
        $user = Tagent::find(\Auth::user()->tagentId);
        if($user)
            $td = $user->tradeDirection;
            
        $rows = CacheItemGroups::where('tdId', $td)->get();
        $groups = [];
        foreach($rows as $r)
            $groups[$r->itemId] = $r;
        $gr = [];
        foreach($groups as $g){
            if(!array_key_exists($g->parentId, $groups))
                $gr[] = ["id" => $g->itemId, 
                         "name" => $items[$g->itemId]->item,
                         "childs" => $this->setChilds($groups, $g->itemId, $items)
                        ];
        }

        return json_encode( $gr );
    }

    private function setChilds($groups, $gr, $items){
        $_gr = [];
        foreach($groups as $g){
            if(array_key_exists($g->parentId, $groups) && $g->parentId == $gr)
                $_gr[] = ["id" => $g->itemId, 
                          "name" => $items[$g->itemId]->item,
                          "childs" => $this->setChilds($groups, $g->itemId, $items)
                         ];
        }
        return $_gr;
    }
    /**
     * возвращает web группы товара в формате json
     */
    public function getItemGroups(Request $request)
    {
        $itemgroups = ItemGroups::getGroupsAll();
        return json_encode( $itemgroups );
    }
    /**
     * создает товар в группе с ссылкой на товар из мобильного агента
     */
    public function setWebGroupItem(Request $request)
    {
        if($request->input('itemId')==null || $request->input('groupId')==null)
            return json_encode( ['code' => 401, 'msg' => 'недостаточно параметров'] );
        $item_mob = Items::findorfail($request->input('itemId'));
        if(!$item_mob)
            return json_encode( ['code' => 401, 'msg' => 'ошибочный код товара из мобильного агента'] );
        $group = ItemGroups::findorfail($request->input('groupId'));
        if(!$group)
            return json_encode( ['code' => 401, 'msg' => 'ошибочный код web группы'] );
        $data = [
                  'title' => $item_mob->item,
                  'longTitle' => $item_mob->item,
                  'parentId' => $group->id,
                  'moderatorId' => \Auth::user()->id,
                  'id' => $item_mob->id
                ];
        $item = ItemsLink::create($data);
        return json_encode( ['code' => 200, 'msg' => 'товар создан'] );
    }

    /**
     * Изменяет значение поля popular в таблице Itemlink
     * Входные данные
     * id - код товара
     * value - новое значение поля popular
     */
    public function changePopularValue(Request $request)
    {
        if (isset($request->id) && isset($request->value))
        {
            $item=ItemsLink::find($request->id);
            $item->popular=$request->value;
            $item->save();
            return json_encode( ['code' => 200, 'msg' => 'popular изменен'] );
        }
        return json_encode( ['code' => 404, 'msg' => 'id or value not found'] );
    }

    /** 
     * Возвращает товарные позиции для выбранной группы товаров web каталога с учетом фильтров
     * входные параметры:
     * parentId - группа товара
     * 
     * search - поиск по наименованию товара
    */
    public function getSearchItems(Request $request)
    {
        //dd($request);
        $areaId = 1; // регион для цены
        $priceType =32; // тип цены
        $strWhere = '';
        if( $request->input('parentId') )
            $strWhere .= ' where i.parentId=' . $request->input('parentId');
        if ( $request->input('id') )
            $strWhere .= ' and i.id LIKE \'%' . $request->input('id').'%\'';
        if ( $request->input('name') )
            $strWhere .= ' and i.title LIKE \'%' . $request->input('name') . '%\'';
        if ( $request->input('id1c') )
            $strWhere .= ' and i.id1c LIKE \'%' . $request->input('id1c') . '%\'';
        if ( $request->input('weightId') )
            $strWhere .= ' and i.weightId LIKE \'%' . $request->input('weightId') . '%\'';
        $sql = "select i.id,i.id1c,i.weightId, i.title,i.popular,  
        CASE
                 WHEN i.prepayment>0 THEN '1'
                 ELSE IFNULL((select sum(quantity) from mtagent.warehouseItems as w where i.id = w.itemId and w.warehouseId in (SELECT WG.warehouseId FROM warehouseGroups as WG WHERE WG.groupId=i.parentId)),'0')
              END as quantity,
                        (select p.value as price from mtagent.prices as p WHERE p.itemId=i.id and p.areaId = {$areaId} and p.priceType={$priceType}) as price
                        FROM mtShop.itemsLink as i  
                        {$strWhere} 
                        ORDER BY i.popular {$request->popularSort}";
        $res = \DB::connection()->select($sql);
        
        
        $items = $this->arrayPaginator($res, $request);
        return response()->json(['items' => array_values($items->items()), 'links' => $items->links()->toHtml()]);
    }
    /**
     * Меняет статус временной заметки для пользователя
     * Входные данные
     * id - код заметки (webUsersNote)
     * status - новый статус заметки
     */
    public function changeNoteStatus(Request $request)
    {
        if ($request->id && isset($request->status))
        {
            $note=WebUsersNote::find($request->id);
            $note->status=$request->status;
            $note->save();
            return json_encode( ['code' => 200, 'mesage' => 'Статус изменен'] );
        }
        else 
            return json_encode( ['code' => 404, 'mesage' => 'Отсутствует код или статус'] );
    }

    /** 
     * Возвращает товарные позиции для выбранной группы товаров используемых в ItemsKassa с учетом фильтров
     * входные параметры:
     * parentId - группа товара
     * 
    */
    public function getAllSearchItemsKassa(Request $request)
    {
        $strWhere = '';
        if( $request->input('parentId') )
            $strWhere .= ' where i.parentId=' . $request->input('parentId');
        if ( $request->input('id') )
            $strWhere .= ' and i.id LIKE \'%' . $request->input('id').'%\'';
        if ( $request->input('name') )
            $strWhere .= ' and i.title LIKE \'%' . $request->input('name') . '%\'';

        $sql = "select i.id, i.title, 
        (select value  from mtagent.prices where areaId = 1 and i.id = itemId and priceType=64) as discountPrice,
        (select value  from mtagent.prices where areaId = 1 and i.id = itemId and priceType=16) as stockPrice,
        (select value  from mtagent.prices where areaId = 1 and i.id = itemId and priceType=32) as price,
        CASE
        WHEN i.prepayment>0 THEN '1'
        ELSE IFNULL((select sum(quantity) from mtagent.warehouseItems as w where i.id = w.itemId and w.warehouseId in (SELECT WG.warehouseId FROM warehouseGroups as WG WHERE WG.groupId=i.parentId)),'0')
            END as quantity

        from  mtShop.itemsLink as i
        {$strWhere} 
        order by i.title";
        $res = \DB::connection()->select($sql);
        $items = $this->arrayPaginatorForManyPath($res, $request);
        return response()->json(['items' => array_values($items->items()), 'links' => $items->links()->toHtml()]);
    }

    /**
     * Функция добавляет пагинацию на массив полученный с базы
     * Входные параметры:
     * array - массив который нужно разбить
     * request - входящие переменные через адресную строку 
     * path - путь для кнопок пагинации
     */
    public function arrayPaginatorForManyPath($array, $request)
    {
        $page = $request->input('page') ?? 1;
        $perPage = 15;
        $offset = ($page * $perPage) - $perPage;
        return new  \Illuminate\Pagination\LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->path, 'query' => $request->query()]);
    }
    /**
     * Получение списка товаров с возможностью фильтрования и сортировки
     */
    public function getItemForLoad(Request $request)
    {
        $strWhere = '';
        if( $request->input('parentTitle') )
        {
            $parent=intval($request->input('parentTitle'));
            if ($parent)
                $strWhere .= 'WHERE j.id =' . $parent .' ';
            else 
                $strWhere .= 'WHERE j.title LIKE \'%' . $request->input('parentTitle').'%\'';
        }
        if ( $request->input('id') )
            if ($strWhere)
                $strWhere .= 'AND i.id LIKE \'%' . $request->input('id').'%\'';
            else
                $strWhere .= 'WHERE i.id LIKE \'%' . $request->input('id').'%\'';

        if ( $request->input('name') )
            if ($strWhere)
                $strWhere .= 'AND i.title LIKE \'%' . $request->input('name') . '%\'';
            else
                $strWhere .= 'WHERE i.title LIKE \'%' . $request->input('name') . '%\'';

        if ( isset($request->mainItem))
            if ($strWhere)
                $strWhere .= 'AND i.mainItem =' . $request->input('mainItem').' ';
            else
                $strWhere .= 'WHERE i.mainItem =' . $request->input('mainItem').' ';
        
        if ($request->unShowUsedItem)
        {
            $usedItems=ItemsLinkGroup::select('itemId')->get()->toArray();
            $manyItems='(';
            foreach($usedItems as $el)
              $manyItems.=$el['itemId'].',';  
              $manyItems = substr($manyItems,0,-1);
              $manyItems.=')';
            if ($strWhere)
                $strWhere .= ' AND i.id NOT IN '.$manyItems.' ';
            else
                $strWhere .= 'WHERE i.id  NOT IN '.$manyItems.' ';
        }


        $dateStart='\'\'';
        if ($request->input('dateStart'))
            $dateStart='\''. $request->input('dateStart').' 00:00:00\'';

        $dateEnd='CURDATE()';
        if ($request->input('dateEnd'))
            $dateEnd='\''. $request->input('dateEnd').' 00:00:00\'';
        
        


        if ( $dateStart || $dateEnd )
            if ($strWhere)
                $strWhere .= ' AND i.created_at BETWEEN ' . $dateStart .' AND '. $dateEnd;
            else
                $strWhere .= ' WHERE i.created_at BETWEEN ' . $dateStart .' AND '. $dateEnd;

        if ($request->idForTag)
        {
            $tagSql = "SELECT g.id FROM itemsLink as i 
                        JOIN mtShop.itemTags as p on p.itemId = i.id
                        JOIN mtShop.tags as t on  t.id=p.tagId
                        JOIN mtShop.tagGroups as g on g.id=t.groupId
                        WHERE i.id = $request->idForTag";
            $tagsForRemove = \DB::connection()->select($tagSql);
            if ($tagsForRemove)
            {
                $tag = array_column($tagsForRemove,'id');
                asort($tag);
                $strTag = implode(",",$tag);
                if ($strWhere)
                    $strWhere .= ' AND ';
                else
                    $strWhere .= ' WHERE ';

                $strWhere .='((SELECT GROUP_CONCAT(g.id ORDER BY g.id ASC) as tags 
                    FROM mtShop.itemsLink as y
                    JOIN mtShop.itemTags as p on p.itemId = y.id
                    JOIN mtShop.tags as t on  t.id=p.tagId
                    JOIN mtShop.tagGroups as g on g.id=t.groupId
                    WHERE y.id = i.id)= "'.$strTag.'")';

            }
        }


        $sql = "SELECT i.id, i.title, i.created_at, i.parentId, i.mainItem, j.title as parentTitle
                FROM mtShop.itemsLink as i
                JOIN mtShop.itemGroups as j on j.id=i.parentId
                 {$strWhere}
                 GROUP BY i.id
                ORDER BY i.{$request->sortField} {$request->sortingMethod}";
        $res = \DB::connection()->select($sql);

        

/*
SELECT i.id, i.title, i.created_at, i.parentId, i.mainItem, j.title as parentTitle
FROM mtShop.itemsLink as i
JOIN mtShop.itemGroups as j on j.id=i.parentId 
WHERE i.created_at BETWEEN '' AND CURDATE() and 
((
SELECT GROUP_CONCAT(g.id ORDER BY g.id ASC) as tags 
    FROM mtShop.itemsLink as y
    JOIN mtShop.itemTags as p on p.itemId = y.id
    JOIN mtShop.tags as t on  t.id=p.tagId
    JOIN mtShop.tagGroups as g on g.id=t.groupId
	WHERE y.id = i.id
	)
 = "3,4,6")
GROUP BY i.id
ORDER BY i.created_at desc
*/


        $items = $this->arrayPaginatorForManyPath($res, $request);
        return response()->json(['items' => array_values($items->items()), 'links' => $items->links()->toHtml()]);
    }

   

    /**
     * Добавление картинки к товару
     */

    public function addNewImageForItem(Request $request)
    {
        $this->validate($request, [
            'file' => 'dimensions:max_width=400,max_height=400'
        ]);
        if ($request->hasFile('file')) {
            if ($request->file('file')->isValid()) {
                $extension = $request->file->extension();
                $request->file->move(public_path().'/img/img/items/small', $request->input('id').".".$extension);
            }else
                return 500;
        }
        return 200;

    } 

    /**
     * Массовое добавление картинок к товарам
     */
    public function saveAllImage(Request $request)
    {
        $this->validate($request, [
            'images.*' => 'dimensions:max_width=400,max_height=400'
        ]);
        if (isset($request->images))
        {
            foreach ($request->images as $file) {
                if ($file->isValid()) {
                    $file->move(public_path().'/img/img/items/small', $file->getClientOriginalName());
                }else
                    return 500;
            }   
            return 200;
        }
        else 
            return 404;
        
    }

    /**
     * Удаление картинки для определенного товара
     */
    public function deleteItemImage(Request $request)
    {   
        $dir='/img/img/items/'.$request->id;
        if(\Storage::disk('public')->exists($dir))
        {
            \Storage::disk('public')->delete($dir);
            return 200;
        }
        else
            return 404;
    }
    public function saveAllImageForGallery(Request $request)
    {
        if (!$request->id)
            return 404;
        $dir='/img/img/items/gallery/'.$request->id;
        if(!\Storage::disk('public')->exists($dir)) {
            \Storage::disk('public')->makeDirectory($dir,0777);
        }
        if (isset($request->images))
        {
            foreach ($request->images as $file) {
                if ($file->isValid()) {
                    if (\Storage::disk('public')->exists($file->getClientOriginalName()))
                        \Storage::delete($file->getClientOriginalName());

                    $file->move(public_path().$dir, $file->getClientOriginalName());
                    
                }else
                    return 500;
            }   
            return 200;
        }
        else 
            return 404;
        
    }

    /**
     * Получение всех изображений в папке (Галерея)
     */
    public function getGalleryImage(Request $request)
    {
        $dir='/img/img/items/gallery/'.$request->id;
        $files = \Storage::disk('public')->files($dir);
        return response()->json($files);
    }
    /** 
     * Получение списка предлогаемых в корзине товаров с последовательностью их вывода
    */
    public function getItemsKassa(Request $request)
    {
        $where='';
        if ($request->input('dateId'))
        $where="where dateId={$request->dateId}";

        $sql = "select itemId,itemsorder,i.title, i.parentId, (select u.title from mtShop.itemGroups as u where u.id=i.parentId ) as TagTitle, (select u.parentId from mtShop.itemGroups as u where u.id=i.parentId ) as childId  from mtShop.itemsKassa 
        JOIN mtShop.itemsLink as i on i.id=itemId {$where} order by itemsorder";
        $res = \DB::connection()->select($sql);
        return response()->json($res);
    }

    /** 
     * Получение дат из таблицы ItemsKassaDates  
    */
    public function getDatesItemsKassa()
    {
        $sql = "select * from mtShop.itemsKassaDates WHERE dateEnd>=CURRENT_DATE() order by dateStart";
        $res = \DB::connection()->select($sql);
        return response()->json($res);
    }

    /** 
     * Добавляет товар в ItemsKassa
     * 
     * Входные параметры:
     * id - код товара
    */

    public function addInItemsKassa(Request $request)
    {
        if(!$request->input("id") || !$request->input('dateId'))
            return json_encode( ["mesage" => "Не удалось добавить товар"], JSON_UNESCAPED_UNICODE );

        $data = new ItemsKassa;
        $data->itemId = $request->id;
        $data->dateId = $request->dateId;
        $data->save();

        return json_encode( ["mesage" => "Товар добавлен"], JSON_UNESCAPED_UNICODE );
    }

    /** 
     * Удаляет товар из ItemsKassa
     * 
     * Входные параметры:
     * id - код товара
    */
    public function deleteInItemsKassa(Request $request)
    {
        if(!$request->input("id") || !$request->input('dateId'))
            return json_encode( ["mesage" => "Не удалось удалить товар"], JSON_UNESCAPED_UNICODE );
        ItemsKassa::where('itemId',$request->id)->where('dateId',$request->dateId)->delete();
        return json_encode( ["mesage" => "Товар удален"], JSON_UNESCAPED_UNICODE );
    }

    /** 
     * Изменяет переменную itemsorder (Порядок товара) в таблице ItemsKassa("Возможно вы забыли купить" в корзине)
     * 
     * Входные параметры:
     * id - код товара
    */
    public function changeItemsorderInItemsKassa(Request $request)
    {
        if(!$request->input("id") || !$request->input('dateId'))
            return json_encode( ["mesage" => "Не удалось изменить порядок товар"], JSON_UNESCAPED_UNICODE );

        $item = ItemsKassa::where('itemId',$request->id)->where('dateId',$request->dateId)->first();
        $item->itemsorder=$request->itemsort;
        $item->save();
        return json_encode( ["mesage" => "Порядок изменен"], JSON_UNESCAPED_UNICODE );
    }


    /** 
     * Возвращает товарные позиции для выбранной группы товаров web каталога
     * входные параметры:
     * parentId - группа товара
     * 
     * search - поиск по наименованию товара
    */
    public function getItems(Request $request)
    {
        $areaId = 1; // регион для цены
        $priceType =32; // тип цены
        $strWhere = '';
        if( $request->input('parentId') )
            $strWhere = ' and i.parentId=' . $request->input('parentId');
        $sql = "select p.value as price, i.id, i.title, i.longTitle, i.descr, 
                (select sum(quantity) from mtagent.warehouseItems as w where p.itemId = w.itemId) as quantity
                from mtagent.prices as p 
                RIGHT JOIN mtShop.itemsLink as i on i.id=p.itemId
                WHERE p.areaId = {$areaId} and p.priceType={$priceType} {$strWhere} 
                order by i.title";
        
        $res = \DB::connection()->select( $sql );
        foreach($res as $item){
            $pers = ItemsPercent::getPercent($item->id);
            $item->price = round($item->price + $item->price*$pers, 2);
        }
        $items = $this->arrayPaginator($res, $request);
        return response()->json(['items' => array_values($items->items()), 'links' => $items->links()->toHtml()]);
    }
    public function arrayPaginator($array, $request)
    {
        $page = $request->input('page') ?? 1;
        $perPage = 20;
        $offset = ($page * $perPage) - $perPage;

        return new  \Illuminate\Pagination\LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => '/admin/itemgroups', 'query' => $request->query()]);
    }
    /**
     * Возвращает группы тегов товаров
     * входные параметры: нет
     */
    public function getTagGroups(Request $request){
        $taggroups = TagGroups::select('id', 'title')->get();
        return json_encode( ['taggroups' => $taggroups] );
    }
    /**
     * Возвращает теги для группы тегов товаров
     * входные параметры:
     * groupId - ID группы
     * 
     */
    public function getTags(Request $request){
        $tags = Tags::select('id', 'title')
        ->where('groupId', $request->input('groupId') ?? 0)
        ->get();
        return json_encode( ['tags' => $tags] );
    }
    /**
     *  Добавляет группу тегов товаров, имя группы должно быть уникальным, если имя не уникально, возвращаем ошибку
     *  входные параметры: 
     *  title - имя группы тегов товаров
     */
    public function addTagGroup(Request $request){
        if(!$request->input('title'))
            return json_encode( ['code' => 400, 'mesage' => 'нет имени группы тегов товаров'], JSON_UNESCAPED_UNICODE );
        $taggroups = TagGroups::where('title', $request->input('title'))->get();
        if( $taggroups && count($taggroups) == 0 ){
            $data = [
                'title' => $request->input('title'),
                'moderatorId' => \Auth::user()->id,
              ];
            TagGroups::create($data);
        }else{
            return json_encode( ['code' => 400, 'mesage' => 'группа с таким именем уже существует'], JSON_UNESCAPED_UNICODE );
        }
        return json_encode( ['code' => 200, 'mesage' => 'добавлена группа ' . $request->input('title')] );
    }
    /**
     *  Удаляет группу тегов товаров, удалить можно только пустую группу
     *  входные параметры: 
     *  groupId - ID группы тегов товаров
     */
    public function delTagGroup(Request $request){
        if(!$request->input('groupId'))
            return json_encode( ['code' => 400, 'mesage' => 'нет кода группы тегов товаров'], JSON_UNESCAPED_UNICODE );
        $tags = Tags::where('groupId', $request->input('groupId'))->get();
        if( $tags && count($tags) > 0 ){
            return json_encode( ['code' => 400, 'mesage' => 'у группы есть теги товаров, удалить можно только пустую группу'], JSON_UNESCAPED_UNICODE );
        }else{
            $taggroup = TagGroups::findOrfail($request->input('groupId'));
            $title = $taggroup->title();
            $taggroup->delete();
        }
        return json_encode( ['code' => 200, 'mesage' => 'удалена группа ' . $title] );
    }
    /**
     * Возвращает заказы за указанный период
     * входные параметры:
     * dFrom - дата начала периода
     * dTo - дата конца периода 
     * status - статус заказа
     * wId - строка со списком id волн в формате "id1,id2,..."
     * orderId - ID заказа
     */
    public function getOrders(Request $request){
        $base = new BaseController;
        $phone = $base->getPhone($request->input('phone'));
        $strWhere = "";
        if($phone){
            if(is_numeric($phone[0]))
                $phone = '+' . $phone;
            else
                if($phone[1] != '+')
                    $phone = substr_replace($phone, '+', 1,0);
            $timeRange = \DB::connection()->select("select min(o.created_at) as minFrom, max(o.created_at) as maxTo from mtShop.orders as o where o.phone='{$phone}'");
            $dFrom = $request->input('dFrom')
            ?Carbon::parse($request->input('dFrom'))->timezone('Europe/Moscow')->startOfDay()->timestamp
            :Carbon::parse($timeRange[0]->minFrom)->timezone('Europe/Moscow')->startOfDay()->timestamp;
            $dTo = $request->input('dTo')
            ?Carbon::parse($request->input('dTo'))->timezone('Europe/Moscow')->endOfDay()->timestamp
            :Carbon::parse($timeRange[0]->maxTo)->timezone('Europe/Moscow')->endOfDay()->timestamp;
            $strWhere = " and phone='". $phone . "'";
        }
        else {
            $dFrom = $request->input('dFrom') ? Carbon::parse($request->input('dFrom'))->timezone('Europe/Moscow')->startOfDay()->timestamp : Carbon::now()->timezone('Europe/Moscow')->startOfDay()->timestamp;
            $dTo = $request->input('dTo') ? Carbon::parse($request->input('dTo'))->timezone('Europe/Moscow')->endOfDay()->timestamp :  Carbon::now()->timezone('Europe/Moscow')->endOfDay()->timestamp;
        }
        if($dTo < $dFrom)
            return json_encode( [] );
        if($request->input('status') > 0){
            $stat = $request->input('status');
            if(is_array($stat))
                $stat=implode(",",$stat);
            $strWhere .= " and o.status in({$stat})";
        }
        else
            $strWhere .= " and o.status<>7 ";
        if($request->input('wId'))
            $strWhere .= " and waveId in({$request->input('wId')}) ";
        $nopay = $request->input('nopay')??0;
        if($nopay){ // заказы со статусом отгружен, оплата безнал, а оплаты нет
            $dFrom = Carbon::now()->timezone('Europe/Moscow')->subDay(30)->startOfDay()->timestamp;
            $dTo = Carbon::now()->timezone('Europe/Moscow')->endOfDay()->timestamp;
            $strWhere .= " and o.status=4 and o.payment=2 and o.sum_pay=0 ";
        }
        $no = " UNIX_TIMESTAMP(o.created_at) ";
        $orderstimeout = $request->input('orderstimeout')??0;
        if($orderstimeout){ // заказы со статусом к оплате и просроченным временем платежа
            $no = " UNIX_TIMESTAMP(o.updated_at) not ";
            $dFrom = Carbon::now()->timezone('Europe/Moscow')->startOfDay()->timestamp;
            $dTo = Carbon::now()->timezone('Europe/Moscow')->endOfDay()->timestamp;
            $strWhere .= " and o.status=6 ";
        }
        $orderId = $request->input('orderId') ?? 0;
        $number = $request->input('number') ?? 0;
        $corrects = $request->input('corrects') ?? 0;

        if($orderId)
            $where = " where o.id={$orderId} ";
        else
            if($number)
                $where = " where o.number='{$number}' ";
            else
                if($corrects){
                    $where = " where (select count(id) from orderChanges as ch where ch.orderId=o.id)>0";
                }
                else
                    $where = " where {$no}between {$dFrom} and {$dTo} {$strWhere} ";
        $sql = "select o.id, o.number, o.created_at as date_time_created, o.lat, o.lng, if(o.sum_last>0,o.sum_last,o.sum_total) as sum_total,o.deviceType,o.deviceInfo, o.workerId,
                o.name, o.phone, o.phoneConsignee, o.addr, o.deliveryCost, o.deliveryZone, o.deliveryZoneIn, o.status, o.note, o.pension, o.payment, o.waveId, o.nopacks,
                DATE_FORMAT(o.deliveryFrom, '%H:%i:%s') as deliveryFrom, DATE_FORMAT(o.deliveryTo, '%H:%i:%s') as deliveryTo, 
                DATE_FORMAT(case when o.deliveryDate='0000-00-00' then o.deliveryFrom else o.deliveryDate end, '%Y-%m-%d') as deliveryDate,
                (select count(phone) from mtShop.orders where phone=o.phone group by phone) as ordersCount,
                o.bonus, o.bonus_pay , o.gift, discountId, o.pickupStatus, o.course,
                (select max(att.title) from mtagent.orderAttributes as att where att.value=o.gift and att.tradeDirection=".config('shop.GIFT_TD').") as giftTitle
                from mtShop.orders as o {$where} order by o.created_at desc";
        $_orders = \DB::connection()->select( $sql );
        
        if(!$_orders)
            $_orders = [];
        $orders = [];
        $pickupStatuses = PickupStatuses::all()->keyBy->id->toArray(); // статусы сборки на складе
        
        foreach($_orders as $o){
            // $items = OrderItem::getOrderItems($o->id);
            // if(!$items)
            //     $items = [];
            
            $o->itemsVisible = false;
            $o->edit = false;
            $o->items = 'none';
            if($o->workerId && (int)$o->workerId>0){
                $worker = Admin::find($o->workerId);
                if($worker)
                    $o->worker = $worker->name;
            }
            $o->proc = $base->getOption('procOrderBonus');
            $tel = $base->getPhone($o->phone);
            $o->phone = $base->getPhoneMask($base->getPhone($o->phone));
            if(strlen($o->phoneConsignee)>3)
                $o->phoneConsignee = $base->getPhoneMask($base->getPhone($o->phoneConsignee));

            $webuser = WebUsers::where('phone', 'like','%'.$tel.'%')->with('notes')->first();
            
            if($webuser){
                $o->bonusUser = $webuser->bonus;
                $o->noteUser = $webuser->noteUser;
                $o->tasksUser = $webuser->notes;
                $o->notePermanent = $webuser->note;
            }
            else{
                $o->bonusUser = 0;
                $o->noteUser = '';
                $o->tasksUser = [];
                $o->notePermanent = '';
            }
            if(!$o->deliveryDate)
                $o->deliveryDate = Carbon::now()->timezone('Europe/Moscow')->format('Y-m-d');
            if($o->discountId > 0) // если использована дисконтная карта
                $o->discount = WebUsersDiscount::find($o->discountId);
            else
                $o->discount = null;
            if( key_exists($o->pickupStatus, $pickupStatuses))
                $o->titlePickupStatus = $pickupStatuses[$o->pickupStatus]['title'];
            else
                $o->titlePickupStatus = "";
            $o->sum_total_grn = Orders::getSumTotalGrn($o->id);
            $orders[] = $o;
            
            // $orders[] = ['id' => 'i'.$o->id, 'items' => $items, 'itemsVisible' => false];
            $weightCount = OrderItem::join('itemsLink', 'orderItem.itemId', 'itemsLink.id')
            ->where('orderId', $o->id)
            ->where('weightId', '<>', '')
            ->count();
            if($weightCount){
                $weightedCount = OrderItem::join('itemsLink', 'orderItem.itemId', 'itemsLink.id')
                ->where('orderId', $o->id)
                ->where('workerId', '>', 0)
                ->where('weightId', '<>', '')
                ->count();
            }else
                $weightedCount = 0;
            $o->weightCount = $weightCount;
            $o->weightedCount = $weightedCount;
            $address = OrderAddress::find($o->id);
            $spos = strpos($o->note, $this->noteSuffix);
            if($spos !== false)
                $o->note = substr($o->note, 0, $spos);
            if($address){
                $o->address = $address;
                if(strlen($address->houseReal)>0)
                    $o->noteSuffix = $this->noteSuffix.$address->houseReal;
            }
        }
        if(auth()->guard('admin')->user()->can('testing'))
            $statuses = Statuses::all();
        else
            $statuses = Statuses::where('id', '<>', 7)->get();

        $payments = Payments::getPayments();
        return json_encode( compact('orders', 'statuses', 'payments') );
    }
    /**
     * Возвращает список товаров в заказе
     * входные параметры:
     * orderId - ID заказа
    */
    public function getOrderItems(Request $request){
        return json_encode( OrderItem::getOrderItems($request->orderId ?? 0 ), JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает список товаров в заказе для печати на складе
     * входные параметры:
     * orderId - ID заказа
    */
    public function getOrderItemsWeight(Request $request){
        if(!$request->orderId)
            return json_encode( [], JSON_UNESCAPED_UNICODE );
        $order = Orders::find($request->orderId);
        if(!$order) // заказ не найден
            return json_encode( [], JSON_UNESCAPED_UNICODE );
        $sot = DeliverySots::find($order->deliveryZoneIn);
        return json_encode( ['order' => $order, 'Sot' => $sot, 'items' => OrderItem::getOrderItemsWeight($request->orderId ?? 0 )], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Сохраняет заказ
     * входные параметры:
     * order - заказ шапка
     * items - товарные позиции
     */
    public function setOrder(Request $request){
        @$order = $request->input('params')['order'];
        if(!$order)
            return json_encode( ['code' => 400, 'msg' => 'заказ не обновлен - нет данных'], JSON_UNESCAPED_UNICODE );
        $changes = OrderChanges::where('orderId', $order['id'])
        // ->where('initiatorPlace', 1)
        ->where('closed', 0)
        ->get();
        if(count($changes)>0) // если есть изменения 
            return response()->json(['code' => 400, 'msg' => 'есть не обработанные изменения', 'data' => $changes], JSON_UNESCAPED_UNICODE);
        $base = new BaseController;
        $_order = null;
        $promocode = isset($order['promocode']) ? $order['promocode'] : 0;
        if($order['waveId']){
            $wave = TimeWaves::find($order['waveId']);
        }
        \DB::beginTransaction();
        $deliveryFrom = Carbon::parse($order['deliveryDate']." ".$wave->timeFrom)->timezone('Europe/Moscow')->format("Y-m-d H:i:s");
        $deliveryTo = Carbon::parse($order["deliveryDate"]." ".$wave->timeTo)->timezone('Europe/Moscow')->format("Y-m-d H:i:s");
        if($order['lng'] > 43){
            $tmp = $order['lat'];
            $order['lat'] = $order['lng'];
            $order['lng'] = $tmp;
        }
        
        try {
            $wuser = WebUsers::find($base->getPhone($order['phone']));
            if(!$wuser){
                $wuserId = \DB::table('webUsers')->insertGetId(['phone' => $base->getPhone($order['phone']), 'code' => 0, 'orderId' => 0]);
                $wuser = WebUsers::where("id",$wuserId)->get()[0];
            }else
                $wuserId = $wuser->id;
            if($wuser->pension != $order['pension'])
                $wuser->update(['pension' => $order['pension']]);
            $data=[
                "name" => $order['name'],
                "phone" => $base->getPhone($order['phone']),
                "phoneConsignee" => $order['phoneConsignee'],
                "addr" => $order['addr'],
                "deliveryCost" => $order['deliveryCost'],
                "deliveryZone" => $order['deliveryZone'],
                "deliveryZoneIn" => $order['deliveryZoneIn'],
                "status" => $order['status'],
                "note" => $order['note'],
                "deliveryFrom" => $deliveryFrom,
                "deliveryTo" => $deliveryTo,
                "lat" => $order['lat'],
                "lng" => $order['lng'],
                "payment" => $order['payment'],
                "pension" => $order['pension'],
                "deviceType" => $order['deviceType'],
                "deviceInfo" => $order['deviceInfo'],
                "waveId" => $order['waveId'] ?? 1,
                "deliveryDate" => $order['deliveryDate'],
                "bonus_pay" => $order['bonus_pay'] ?? 0,
                "gift" => $order['gift'] ?? 0,
                'webUserId' => $wuserId ?? 0,
                "course" => $order['course'] ?? 0
            ];
            $orderId = $order['id'];
            $data['userId'] = \Auth::guard('admin')->user()->mobUserId;
            if($order['id']>0){
                $_order = Orders::find((int)$order['id']);
                if($_order){
                    $update = $_order->update($data);
                }
                else
                    $update = false;
            }else{
                $data["date_time_created"] = $order['date_time_created'];
                $data["sum_total"] = $order['sum_total'];
                $data["guid"] = strtolower( (string)\Str::uuid() );
                $_order = Orders::create($data);
                
                //$order = $_order;
                $orderId = $_order->id;
                if($_order){
                    $update=true;
                }
                else
                    $update = false;
            }
            
            if($update){
                $note = $order['note'];
                $spos = strpos($note, $this->noteSuffix);
                if($spos)
                    $note = substr($note, 0, $spos);
                if(isset($order['address'])){
                    OrderAddress::setAddress($orderId, $order['address']);
                    $note .= $this->noteSuffix.$order['address']['houseReal'];
                }
                $sum = 0;
                $ids = [];
                if($order['status'] !=3){ // если отказ заказчика список товаров не трогаем (список не присылается)
                    OrderItem::save_OrderItems($order['items'], $orderId, $sum, $ids);
                    $orderItems = OrderItem::where('orderId', $orderId)->get();
                    
                    foreach($orderItems as $orderItem){
                        if(!in_array($orderItem->itemId, $ids)){
                            OrderItem::where('orderId', $orderId)->where('itemId', $orderItem->itemId)->delete();
                        }
                    }
                    $deliveryCost = $base->getDeliveryCost($sum, $_order->deliveryZone, $_order->pension);
                    $max_bonus_pay = $this->getBonuses($order['phone'], $sum);
                    $discountId = 0;
                    $discount_proc = 0;
                    $discount_sum = 0;
                    WebUsersDiscount::where('orderId', $_order->id)->update(['orderId' => 0]);
                    if($promocode){ // если есть промокод бонусы обнуляем и применяем промокод (или дисконтную карту)
                        $max_bonus_pay = 0;
                        $order['bonus_pay'] = 0;
                        $create_at = Carbon::parse($_order->create_at)->timezone('Europe/Moscow')->format("Y-m-d H:i:s");
                        $userDiscount = WebUsersDiscount::getPromoCode($promocode, $base->getPhone($order['phone']), $_order->id, $create_at);
                        if($userDiscount){
                            $discountId = $userDiscount->id;
                            if($userDiscount->type==3)
                                $discount_sum = $userDiscount->discount;
                            else
                                $discount_proc = $userDiscount->discount;
                            if( $userDiscount->type != 1 && $userDiscount->friendId==0){
                                $userDiscount->update(['orderId' => $_order->id]);
                            }
                        }
                    }
                    $data=[
                        
                        "sum_total" => $sum,
                        "deliveryCost" => $deliveryCost,
                        'bonus_pay' => $order['bonus_pay'] > $max_bonus_pay ? $max_bonus_pay : $order['bonus_pay'],
                        'discountId' => $discountId,
                        'discount_proc' => $discount_proc,
                        'discount_sum' => $discount_sum,
                        'note' => $note
                    ];
                    $update = $_order->update($data);
                }
            }
        } catch(\Exception $e)
        {
            \DB::rollback();
            throw $e;
        }
        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        
        \DB::commit();
        if(($_order->status==2 || $_order->status==6) && $_order->deviceType!=2){ // если заказ создан не из админки и присвоен статус "отправлен в 1С" или "к оплате" шлем смс
            if($_order->payment==2)
                $ms = " просмотр и оплата в личном кабинете ";
            else
                $ms = $base->getOption('smsCodeView');
            $msg = $base->getOption('smsCode') . $_order->id . $ms . " https://mt.delivery/ownorders/{$_order->id}";
            $opt = \App\Model\OrderOptions::where('orderId', 41160)->where('title', 'nosms')->get()->count();
            if(!$opt || empty($opt) || $opt==0)//проверка дополнительных опций
                $base->sendSms($base->getPhone($_order->phone), trim($msg));
        }
        $this->setUserLastOrder($_order);
        $this->orderClone($_order); // сохраняем копию заказа, что бы был первоначальный вариант заказа
        $base->orderUnlock($_order->id); // разблокировать заказ
        return json_encode( ['code' => $update?200:400, 'msg' => $update?'заказ обновлен':'заказ не обновлен', 'order' => $update ? $_order : null ], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Сохраняет заказ при редактировании рабочим склада
     * входные параметры:
     * orderId - ID заказа
     * items - товарные позиции
     */
    public function saveWarehouseOrder(Request $request){
        $orderId = (int)$request->input('params')['orderId'];
        if(!$orderId)
            return json_encode( ['code' => 400, 'msg' => 'заказ не обновлен, нет ID'], JSON_UNESCAPED_UNICODE );
        $order = Orders::find($orderId);
        if(!$order)
            return json_encode( ['code' => 400, 'msg' => 'заказ не обновлен, не найден в БД'], JSON_UNESCAPED_UNICODE );
        if($order->status!=1) // заказ уже обработан и отправлен в 1С
            return json_encode( ['code' => 400, 'msg' => 'заказ не обновлен, у заказа статус обработан'], JSON_UNESCAPED_UNICODE );
        \DB::beginTransaction();
            $data['workerId'] = \Auth::guard('admin')->user()->id;
            $order->update($data);
            $sum = 0;
            $ids = [];
            OrderItem::save_OrderWarehouseItems($request->input('params')['items'], $orderId, $sum, $ids);
        \DB::commit();
        return json_encode( ['code' => 200, 'msg' => 'заказ обновлен'], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Сохраняет заказ при добавлении редактировании рабочим склада пакетов
     * входные параметры:
     * orderId - ID заказа
     * items - товарные позиции
     */
    public function saveWarehouseOrderPacks(Request $request){
        $orderId = (int)$request->input('params')['orderId'];
        if(!$orderId)
            return json_encode( ['code' => 400, 'msg' => 'заказ не обновлен, нет ID'], JSON_UNESCAPED_UNICODE );
        $order = Orders::find($orderId);
        if(!$order)
            return json_encode( ['code' => 400, 'msg' => 'заказ не обновлен, не найден в БД'], JSON_UNESCAPED_UNICODE );
        
        \DB::beginTransaction();
            $data['workerId'] = \Auth::guard('admin')->user()->id;
            $data['nopacks'] = $request->input('params')['nopacks'];
            $order->update($data);
            $sum = 0;
            $ids = [];
            OrderItem::save_OrderPacks($request->input('params')['items'], $orderId, $sum, $ids);
            // удаление пакетов
            $orderItems = OrderItem::select("orderItem.itemId")
            ->join('itemsLink', "orderItem.itemId", "itemsLink.id")
            ->where('itemsLink.parentId', 179)
            ->where('orderItem.orderId', $orderId)->get();
            foreach($orderItems as $orderItem){
                if(!in_array($orderItem->itemId, $ids)){
                    OrderItem::where('orderId', $orderId)->where('itemId', $orderItem->itemId)->delete();
                }
            }
        \DB::commit();
        return json_encode( ['code' => 200, 'msg' => 'заказ обновлен'], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает группы сопутствующих товаров для группы
     * входные параметры:
     * groupId - ID группы товаров
     */
    public function getSuggests(Request $request){
        if(!$request->input("groupId"))
            return json_encode( [], JSON_UNESCAPED_UNICODE );
        $result = GroupSuggests::getSuggests($request->input("groupId"));
        return json_encode( $result, JSON_UNESCAPED_UNICODE );
    }
    /**
     * Добавляет группу сопутствующих товаров для группы
     * входные параметры:
     * groupId - ID группы товаров
     * suggestId - ID группы сопутствующих товаров
     */
    public function addSuggest(Request $request){
        if(!$request->input("groupId") || !$request->input("suggestId"))
            return json_encode( ["mesage" => "не удалось добавить погруппу сопутствующих товаров"], JSON_UNESCAPED_UNICODE );
        $data = $request->all();
        $result = GroupSuggests::create($data);
        
        return json_encode( ["mesage" => "добавлена погруппа сопутствующих товаров"], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Удаляет группу сопутствующих товаров для группы
     * входные параметры:
     * groupId - ID группы товаров
     * suggestId - ID группы сопутствующих товаров
     */
    public function delSuggest(Request $request){
        if(!$request->input("groupId") || !$request->input("suggestId"))
            return json_encode( ["mesage" => "не удалось удалить погруппу сопутствующих товаров"], JSON_UNESCAPED_UNICODE );
        $result = GroupSuggests::where("groupId", $request->input("groupId"))
        ->where("suggestId", $request->input("suggestId"))
        ->delete();
        return json_encode( ["mesage" => "удалена погруппа сопутствующих товаров"], JSON_UNESCAPED_UNICODE );
    }
    /**
     * изменяет статус отзыва о товаре
     * Входные параметры:
     * id - ID отзыва
     * comment - отзыв
     * estimate - оценка товара
     * status - статус
     */
    public function setComment(Request $request)
    {
        if(!$request->input('id')) // нет отзыва
            return json_encode( ['msg'=>'отзыв не обновлен, нет id', 'code' => 400], JSON_UNESCAPED_UNICODE );
        $data = ['moderatorId' =>  \Auth::guard('admin')->user()->id];
        if(!$request->input('comment') && !empty($request->input('comment'))) 
            $data['comment'] = $request->input('comment');
        if(!$request->input('estimate') && !empty($request->input('estimate'))) 
            $data['estimate'] = $request->input('estimate');
        if($request->input('status')!==null ) 
            $data['status'] = $request->input('status');
        
        $comment = Comments::find($request->input('id'));
        if($comment)
            $comment->update($data);
        else
            return json_encode( ['msg'=>'отзыв не обновлен, id не найден', 'code' => 400], JSON_UNESCAPED_UNICODE );
        
        return json_encode( ['msg' => 'отзыв обновлен', 'code' => 200], JSON_UNESCAPED_UNICODE );
    }
    /**
     * изменяет статус отзыва о товаре
     * Входные параметры:
     * id - ID отзыва
     * comment - отзыв
     * status - статус
     */
    public function setCommentFirm(Request $request)
    {
        if(!$request->input('id')) // нет отзыва
            return json_encode( ['msg'=>'отзыв не обновлен, нет id', 'code' => 400], JSON_UNESCAPED_UNICODE );
        $data = ['moderatorId' =>  \Auth::guard('admin')->user()->id];
        if(!$request->input('comment') && !empty($request->input('comment'))) 
            $data['comment'] = $request->input('comment');
        if($request->input('status')!==null ) 
            $data['status'] = (int)$request->input('status');
        
        $comment = CommentsFirm::find($request->input('id'));
        
        if($comment)
            $tt = $comment->update($data);
        else
            return json_encode( ['msg'=>'отзыв не обновлен, id не найден', 'code' => 400], JSON_UNESCAPED_UNICODE );
        
        return json_encode( ['msg' => 'отзыв обновлен', 'code' => 200], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает кол-во не обработанных заказов и кол-во не прочитанных сообщений чата
     * Входные параметры:
     * isusers - признак возвращать или нет пользователей чата, по умолчанию не возвращать
    */
    public function getAlerts(Request $request){
        $isusers = $request->input("isusers") ?? 0;
        $mesages = ChatMessages::where('chatUserId', '>', 0)->where('moderatorId',0)->where('status', 0)->count();
        $orders  = Orders::where('status', 1)->count();
        $orderstimeout  = Orders::select('id')->where('status', 6)
        ->where(\DB::raw(' case when updated_at BETWEEN STR_TO_DATE( DATE_FORMAT( CONCAT(CURDATE(), " 00:00:00"), "%m/%d/%Y %H:%i:%s"), "%m/%d/%Y %H:%i:%s" ) AND STR_TO_DATE( DATE_FORMAT( CONCAT(CURDATE(), " 23:59:59"), "%m/%d/%Y %H:%i:%s"), "%m/%d/%Y %H:%i:%s" )  then 0 else 1 end '), '>', 0)
        ->get();
        $data = ["mesages" => $mesages, "orders" => $orders, "orderstimeout" => $orderstimeout];
        if($isusers > 0){
            $users = ChatUsers::with('lastmes')->withCount('noreadmes')->get();
            
            $data['users'] = $users;
        } 
        $locks = OrderLocks::select('orderId')->pluck('orderId');
        $data['locks'] = $locks;
        $data['nopays'] = Orders::where('status', 4)
                          ->where('payment', 2)
                          ->where('sum_pay', 0)
                          ->whereBetween('created_at', array(Carbon::now()->timezone('Europe/Moscow')->subDay(30)->format("Y-m-d H:i:s"), Carbon::now()->timezone('Europe/Moscow')->format("Y-m-d H:i:s")))
                          ->count();
        $corrects = OrderChanges::select('orderId', 'pickupStatus')->join('orders', 'orders.id', 'orderId')->where('closed', 0)->groupBy('orderId')->get('orderId');
        $pickupStatuses = PickupStatuses::all();
        foreach($pickupStatuses as $p)
            $pStatuses[$p->id] = array_reverse(explode(',',$p->color));
        $base = new BaseController;
        $data['corrects'] = [];
        foreach($corrects as $o){
            $_unclosedCor = 0;
            $color='';
            // if($o->pickupStatus == 3){
                $unclosedCor = OrderChanges::where('orderId', $o->orderId)
                ->where('initiatorPlace', $base->initiatorPlace_operator)
                ->where('closed', 0)
                ->count();
                if($unclosedCor > 0){
                    $_unclosedCor = $base->initiatorPlace_operator;
                    $color = $pStatuses[3][$base->initiatorPlace_operator-1];
                }
                else{
                    $unclosedCor = OrderChanges::where('orderId', $o->orderId)
                    ->where('initiatorPlace', $base->initiatorPlace_warehouse)
                    ->where('closed', 0)
                    ->count();
                    if($unclosedCor > 0){
                        $_unclosedCor = $base->initiatorPlace_warehouse;
                        $color = $pStatuses[3][$base->initiatorPlace_warehouse-1];
                    }
                }
            // }
            $data['corrects'][$o->orderId] =  $color;
        }
        return json_encode( $data, JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возобновить просроченный заказ (оплата картой)
     * Входные параметры:
     * orderId: ID заказа
    */
    public function refreshOrder(Request $request){
        $orderId = $request->input("orderId") ?? 0;
        if(!$orderId)
            return response()->json( ['message' => 'нет ID заказа', 'code' => 700], JSON_UNESCAPED_UNICODE );
        $order = Orders::find($orderId);
        if(!$order)
            return response()->json( ['message' => 'заказ не найден', 'code' => 700], JSON_UNESCAPED_UNICODE );
        $order->update(['updated_at' => Carbon::now()->timezone('Europe/Moscow')->format('Y-m-d %H:%i:%s')]);
        return response()->json( ['message' => 'заказ обновлен', 'code' => 200], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Сохраняет копию заказа до отправки в 1С
    */
    public function orderClone($order){
        if(!$order || ($order->status !=2 && $order->status !=6))
            return;
        $id = $order->id;
        \DB::beginTransaction();
        try{
            
            $sql = "INSERT INTO `ordersShadow`(`id`, `number`, `name`, `phone`, `phoneConsignee`, `addr`, `deliveryCost`, `status`, `deliveryZone`, `deliveryZoneIn`, `userId`, 
                   `note`, `sum_total`, `sum_last`, `sms`, `lat`, `lng`, `guid`, `deliveryFrom`, `deliveryTo`, `deviceType`, `deviceInfo`, `pension`, `payment`, `order_number`, 
                   `bonus`, `bonus_pay`, `action`, `orderNumber`, `sum_pay`, `visiteTime`, `remindSms`, `created_at`, `updated_at`) SELECT `id`, `number`, `name`, `phone`, `phoneConsignee`, 
                   `addr`, `deliveryCost`, `status`, `deliveryZone`, `deliveryZoneIn`, `userId`, `note`, `sum_total`, `sum_last`, `sms`, `lat`, `lng`, `guid`, `deliveryFrom`, 
                   `deliveryTo`, `deviceType`, `deviceInfo`, `pension`, `payment`, `order_number`, `bonus`, `bonus_pay`, `action`, `orderNumber`, `sum_pay`, `visiteTime`, `remindSms`, `created_at`, 
                   `updated_at` FROM `orders` WHERE orders.id={$id} ON DUPLICATE KEY UPDATE `number`=VALUES(number), `name`=VALUES(name), `phone`=VALUES(phone), `phoneConsignee`=VALUES(phoneConsignee), 
                   `addr`=VALUES(addr), `deliveryCost`=VALUES(deliveryCost), `status`=VALUES(status), `deliveryZone`=VALUES(deliveryZone), `deliveryZoneIn`=VALUES(deliveryZoneIn), 
                   `userId`=VALUES(userId), `note`=VALUES(note), `sum_total`=VALUES(sum_total), `sum_last`=VALUES(sum_last), `sms`=VALUES(sms), `lat`=VALUES(lat), `lng`=VALUES(lng), `guid`=VALUES(guid), 
                   `deliveryFrom`=VALUES(deliveryFrom), `deliveryTo`=VALUES(deliveryTo), `deviceType`=VALUES(deviceType), `deviceInfo`=VALUES(deviceInfo), 
                   `pension`=VALUES(pension), `payment`=VALUES(payment), `order_number`=VALUES(order_number), `bonus`=VALUES(bonus), `bonus_pay`=VALUES(bonus_pay), `action`=VALUES(action), 
                   `orderNumber`=VALUES(orderNumber), `sum_pay`=VALUES(sum_pay), `visiteTime`=VALUES(visiteTime), `remindSms`=VALUES(remindSms), `created_at`=VALUES(created_at), 
                   `updated_at`=VALUES(updated_at)";
            
            \DB::connection()->select( $sql );
            $sql = "INSERT INTO `orderItemShadow`(`id`, `orderId`, `itemId`, `warehouse_id`, `quantity`, `quantity_base`, `quantity_warehouse`, `price`, `priceType`, `percent`, `workerId`, `manually`) 
                   SELECT `id`, `orderId`, `itemId`, `warehouse_id`, `quantity`, `quantity_base`, `quantity_warehouse`, `price`, `priceType`, `percent`, `workerId`, `manually` FROM `orderItem` WHERE orderItem.orderId={$id} 
                   ON DUPLICATE KEY UPDATE `warehouse_id`=VALUES(warehouse_id), `quantity`=VALUES(quantity), `quantity_base`=VALUES(quantity_base), `quantity_warehouse`=VALUES(quantity_warehouse), `price`=VALUES(price), `priceType`=VALUES(priceType), `percent`=VALUES(percent), `workerId`=VALUES(workerId), `manually`=VALUES(manually)";
            \DB::connection()->select( $sql );

        }catch(\Exception $e){
            \DB::rollback();
            throw $e;
        }
        \DB::commit();
    }
    /**
     * Сохраняет время последнего заказа 
    */
    private function setUserLastOrder($order){
        if(!$order || strlen($order->phone)<10)
            return;
        $base = new BaseController;
        $last = Orders::select(\DB::raw('max(created_at) as last'))
        ->where('phone', 'like', '%'.$order->phone.'%')
        ->where('id', '<>', $order->id)
        ->where('status', 4)
        ->first();
        
        if($last)
            $lastOrder = $last->last;
        else
            $lastOrder = '2020-01-01';
        WebUsers::where('phone', 'like', '%'.$base->getPhone($order->phone).'%')
        ->update(['lastOrder' => $lastOrder]);
    }
    
    public function testPay(Request $request){
        
    }
    /**
     * Возвращает доступные бонусы в заказе 
    */
    private function getBonuses($phone, $sum){
        $base = new BaseController;
        $phone = $base->getPhone($phone);
        $webuser = WebUsers::where('phone', 'like','%'.$phone.'%')->first();
        if(!$webuser)
            return 0;
        $proc = $base->getOption('procOrderBonus');
        $maxbonus = $sum*$proc/100;
        if($maxbonus>$webuser->bonus)
            $maxbonus = $webuser->bonus;
        
        return round($maxbonus);
    }
    /**
     * Возвращает список всех волн с количеством заказов на каждую волну за период
     * Входные параметры:
     * d1,d2 - две даты периода в формате "Y-m-d"
     * wId - строка со списком id волн в формате "id1,id2,..."
     */
    public function getWavesReport(Request $request){
        $d1 = $request->d1 ? Carbon::parse($request->d1)->timezone('Europe/Moscow')->format("Y-m-d") : Carbon::now()->timezone('Europe/Moscow')->format("Y-m-d");
        $d2 = $request->d2 ? Carbon::parse($request->d2)->timezone('Europe/Moscow')->format("Y-m-d") : Carbon::now()->timezone('Europe/Moscow')->format("Y-m-d");
        if(!$request->wId)
            return json_encode( ['msg' => 'нет волн', 'waves' => []], JSON_UNESCAPED_UNICODE );
        
        $sql = "select GROUP_CONCAT(DISTINCT(timeWaves.id)) as wId, timeWaves.timeFrom, timeWaves.timeTo, count(orders.id) as count from mtShop.orders inner join timeWaves on timeWaves.id=orders.waveId where orders.status<>7 and deliveryDate between '{$d1}' and '{$d2}' and waveId in({$request->wId}) group by timeWaves.timeFrom+timeWaves.timeTo;";
        \Log::channel('test')->info($sql);
        $orders = \DB::connection()->select( $sql );
        if($orders)
            return json_encode( ['msg' => 'список волн', 'waves' => $orders], JSON_UNESCAPED_UNICODE );
        else
            return json_encode( ['msg' => 'нет заказов', 'waves' => []], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает список акционных подарков
     * Входные параметры:
     * s - сумма по накладной
     * 
     */
    public function getGifts(Request $request){
        $td = config('shop.GIFT_TD');
        $strWhere = "";
        if($request->s){
            $s = (double)$request->s;
            $strWhere = " and minSum=(select max(minSum) from mtagent.orderAttributes as b where minSum<={$s})";
        }
        $sql = "select id, title, value, minSum from mtagent.orderAttributes as a where tradeDirection={$td}".$strWhere;
        
        $gifts = \DB::connection()->select( $sql );
        return json_encode( ['gifts' => $gifts], JSON_UNESCAPED_UNICODE );
    }
    /**
     * устанавливает полигон и другие параметры для соты
     * Входные параметры:
     * id - ID соты
     * geometry - координаты полигона 
     * description - описание для соты
     * deleted - признак сота удалена
     */
    public function setSotPoligon(Request $request)
    {
        if(!isset($request->id) || !isset($request->geometry)){
            return response()->json(['message'=>'нет ID соты или полигона', 'code'=>700], JSON_UNESCAPED_UNICODE);
        }
        if((int)$request->id <= 0){
            $sot = DeliverySots::where('sotPoligon', $request->geometry)->first();
            if(empty($sot)){
                $sot = DeliverySots::create(['sotPoligon' => $request->geometry, 'description' => $request->description ?? '']);
                return response()->json(['message'=>'полигон создан', 'code'=>200, 'sota' => $sot], JSON_UNESCAPED_UNICODE);
            }else
                return response()->json(['message'=>'полигон существует', 'code'=>201, 'sota' => $sot], JSON_UNESCAPED_UNICODE);
        }else{
            $sot = DeliverySots::find($request->id);
            if($sot){
                $data = ['sotPoligon' => $request->geometry];
                if(isset($request->description))
                    $data['description'] = $request->description;
                if(isset($request->deleted))
                    $data['deleted'] = $request->deleted;
                $sot->update($data);
                return response()->json(['message'=>'полигон обновлен', 'code'=>200, 'sota' => $sot], JSON_UNESCAPED_UNICODE);
            }
        }
        return response()->json(['message'=>'нет полигона', 'code'=>700], JSON_UNESCAPED_UNICODE);
    }
    /**
     * устанавливает полигон и другие параметры для зоны
     * Входные параметры:
     * id - ID зоны
     * geometry - координаты полигона 
     * description - описание для зоны
     * deleted - признак зона удалена
     */
    public function setZone(Request $request)
    {
        if(!isset($request->id) || !isset($request->geometry)){
            return response()->json(['message'=>'нет ID зоны или полигона', 'code'=>700], JSON_UNESCAPED_UNICODE);
        }
        if((int)$request->id < 0){
            $zone = DeliveryZones::where('zonePoligon', $request->geometry)->first();
            if(empty($zone)){
                $zone = DeliveryZones::create(['zonePoligon' => $request->geometry, 'description' => $request->description ?? '']);
                return response()->json(['message'=>'зона создана', 'code'=>200, 'zone' => $zone], JSON_UNESCAPED_UNICODE);
            }else
                return response()->json(['message'=>'зона существует', 'code'=>201, 'zone' => $zone], JSON_UNESCAPED_UNICODE);
        }else{
            $zone = DeliveryZones::find($request->id);
            if($zone){
                $data = ['zonePoligon' => $request->geometry];
                if(isset($request->description))
                    $data['description'] = $request->description;
                if(isset($request->deleted))
                    $data['deleted'] = $request->deleted;
                if(isset($request->fillOpacity))
                    $data['fillOpacity'] = $request->fillOpacity;
                if(isset($request->strokeColor))
                    $data['stroke'] = $request->strokeColor;
                if(isset($request->strokeOpacity))
                    $data['strokeOpacity'] = $request->strokeOpacity;
                if(isset($request->fillColor))
                    $data['fill'] = $request->fillColor;
                $zone->update($data);
                return response()->json(['message'=>'зона обновлена', 'code'=>200, 'zone' => $zone], JSON_UNESCAPED_UNICODE);
            }
        }
        return response()->json(['message'=>'нет полигона', 'code'=>700], JSON_UNESCAPED_UNICODE);
    }
}
