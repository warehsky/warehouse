<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiscountController extends Controller
{
    /** 
     * Функция нахождения цен на товары
     * 
     * Функция с помощью запроса к базе формирует список цен для определенного товара, далее если на товар существует дисконтная цена товара и количество больше или равно минимально допустимого для дисконта, то используется дисконтная цена. Если существует акционная цена отличная от нуля, то используется акционная цена. 
     * 
     * @param $order
     *   Массив объектов содержащие:
     *     - itemId: код товара.
     *     - count: количество единиц товара.
     * @return $result 
     *   Ассоциативный массив
     *     - itemId: код товара.
     *     - price: цена за единицу товара.
    */
    private function getPrices($order)
    {
        $AREA = 1;
        foreach ($order as $key => $value)
        {
            $sql = "select  i.id, i.discountBound,p.value as defaultPrice,
                    (select k.value  from mtagent.prices as k where k.areaId = {$AREA} and p.itemId = k.itemId and k.priceType=64) as discountPrice,
                    (select w.value  from mtagent.prices as w where w.areaId = {$AREA} and p.itemId = w.itemId and w.priceType=16) as stockPrice
                    from mtagent.prices as p
                    RIGHT JOIN mtShop.itemsLink as i on i.id=p.itemId
                    WHERE p.areaId = {$AREA} and (p.priceType=32) and p.itemId={$value->itemId}
                    order by i.title";  
            $price[] = \DB::connection()->select($sql)[0];
        }

        $result=[];

        foreach ($order as $key =>$value)
        {
            //Если количество предметов в заказе больше(равно) чем нужно в price заносится цена оптовая  
            //Иначе в price заносится цена обычная 

            if(($price[$key]->discountBound<=$value->count) && ($price[$key]->discountPrice>0 && $price[$key]->discountPrice!=null))
            {      
                $result[$value->itemId]['price']=$price[$key]->discountPrice;
            }
            else 
                $result[$value->itemId]['price']=$price[$key]->defaultPrice;


            //Если существует акционная цена то в stockPrice заносится акционная цена
            if($price[$key]->stockPrice!=null && $price[$key]->stockPrice>0)
            {
                $result[$value->itemId]['stockPrice']=$price[$key]->stockPrice;
            }

        }
        return $result;
    }

    /** 
     * Функция расчета всех скидок
     * 
     * Функция принимает JSON строку содержащую код товара и его количество, вызывается функция нахождения цен у товаров, последовательно вызываются функции скидок, если полученное значение скидки отлично от нуля, название и размер скидки записывается в массив discount. После выполнения всех функций формирующих скидки, находится сумма всех скидок и записывается в поле total.
     * 
     * @param $request
     *   JSON строка состоящая из массива структур содержащие:
     *     - itemId: код товара.
     *     - count: количество единиц товара.
     * @return $discount 
     *   Ассоциативный массив
     *     - title: название скидки.
     *     - value: размер скидки.
     * @return $total: сумма всех скидок. 

    */
    public function computeDiscount(Request $request)
    {
        $order = json_decode($request->input('order')); 

        $prices=$this->getPrices($order);
        $discountData = [];


        //название всех функций, при добавлении новых функций обязательно добавить сюда!!!
        $nameFunctions=['getHalfHundredDiscount','getStock']; 
        
        //Вызов всех функций
        foreach($nameFunctions as $key)
        {
            $func=$key;
            $line = $this->$func($order, $prices);
            if($line['value'])
                $discountData[] = $line;
        }

        $result['discounts']=$discountData;
        $total = 0;

        foreach($discountData as $key=>$value)
            $total += $value['value'];

        $result['total'] = $total;

       // dd(json_encode($result));
        return json_encode($result);
               
    }

    /**
    *Скидка 50%
    *
    *   Если в заказе товаров отпереденного тега больше или равно 2 но меньше 4, дается скидка 50% на самый дешевый товар. Если товаров больше или равно 4 то скидка в 50% дается на два самых дешевых. 
    *
    *   @param $order Массив формирующийся из Json строки 
    *   Массив входных данных
    *       - itemId: код товара.
    *       - count: количество единиц товара. 
    *   @param $prices 
    *   Ассоциативный массив 
    *       itemId(код товара)=>price(цена за единицу товара)
    *   @return $discount
    *   Ассоциативный массив
    *       - title:Название скидки.
    *       - value:Размер скидки.
    */

    public function getHalfHundredDiscount($order,$prices)
    {   
        $TAG=105; //тег на который действует скидка

        
        $discount['title']='Half hundred discount';

        //Формирование из входящего массива объектов ассоциативный массив 
        foreach($order as $key=>$value)
        $orderArray[$value->itemId]=$value->count;

        //формирование строки для запроса (формируется путем совмещения всех itemId через зяпятую) 
        //Пример 3608,3454,2494,34934
        $str='';
        foreach($orderArray as $key=>$value)
        $str.= strval($key).',';
        $str = substr($str,0,-1);

        //Запрос формирует список всех подходящих itemId (по полю tagId) и сортирует их по убыванию цены
        $sql = "SELECT p.itemId FROM mtShop.itemTags as p 
        RIGHT JOIN mtagent.prices as i on i.itemId=p.itemId 
        WHERE i.priceType=32 and p.tagId={$TAG} and (p.itemId in ({$str}))
        ORDER BY i.value DESC";  
        $items = \DB::connection()->select($sql);
        foreach($items as $key=>$value)
            $item[]=$value->itemId;


        //Нахождение общего количества предметов в заказе
        $countItems=0;
        foreach($orderArray as $key=>$value)
        {
           if (in_array($key,$item))
           {
                $countItems+=$value;
           }
        }

        if ($countItems>=2 && $countItems<4) // 2 или 3  единиц товара (Скидка на один самый дешевый товар)
        {
            $discount['value']=$prices[$item[count($item)-1]]['price']/2;
        }
        else
        {
            if ($countItems>=4) //4 или более единиц товара (Скидка на 2 самых дешевых товара)
            {
                if ($orderArray[$item[count($item)-1]]>1) //если у самого дешевого товара количество больше 1
                {
                    $discount['value']=$prices[$item[count($item)-1]]['price'];
                }
                else //если количество самого дешевого товара равна 1, то скидка для двух самых дешевых товаров
                {
                    $discount['value']=$prices[$item[count($item)-1]]['price']/2+$prices[$item[count($item)-2]]['price']/2;
                }
            }
            else    //недостаточно товаров для формирования скидки 
            {
                $discount['value']=0;
            }
        } 


        return $discount;
    }

    public function getStock($order,$prices)
    {
        $total = 0;
        foreach($order as $line)
        {   
            if(array_key_exists($line->itemId, $prices))
            {
                $price = $prices[$line->itemId];
                if(array_key_exists('stockPrice', $price))
                    $total += $price['price']*$line->count-$price['stockPrice']*$line->count;
            }
        }

        $discount['title']='Stock';
        $discount['value']=$total; 

        return $discount;
    }

}

