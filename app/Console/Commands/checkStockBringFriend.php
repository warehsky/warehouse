<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\PromocodeController;
use App\Http\Controllers\BaseController as Basectrl;
use App\Model\Options;
use App\Model\Orders;
use App\Model\Settings;
use App\Model\webUsersBringFriend;
use App\Model\WebUsersDiscount;
use Carbon\Carbon;

class checkStockBringFriend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:bringFriendStock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка заказов на промокод из акции "Приведи друга", и отправка соответсвующих дисконтных карт';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        print($this->checkBringFriend());
        \Log::channel('cron')->info("check:bringFriendStock");
    }
    /**
     * Функция проверяет заказы которые изменили статус с определенного времени
     * (время запоминается каждый раз при начале наботы функции в базе mtShop таблице settings поле bringFriendLastCheck)
     * Если были найдены не обработанные заказы которые имеют статус "Отгружен (4)".
     * Следует проверка был ли использован на этом заказе промокод из акции "Приведи друга"
     * Если был использован, тому человеку который привел генерируется и отправляется дисконтная карта
     * В любом другом случае проверяется наличие промокода акции "Приведи друга" у клиента сделавшего заказ
     * Если у него нет такого промокода, он генерируется и высылается ему сообщением 
    */

    private function checkBringFriend()
    {
        $base = new Basectrl();
        $time = $base->getOption('bringFriendLastCheck');
        Options::where('field','bringFriendLastCheck')->update(['value' => Carbon::now()->timezone('Europe/Moscow')->format('Y-m-d H:i:s')]);
        if ($base->getOption('bringFriendStatus'))
        {
            $orders= Orders::select('id','webUserId','discountId')
                            ->where('status',4)
                            ->where('updated_at','>=',$time)
                            ->whereRaw("id NOT IN (SELECT orderId FROM webUsersBringFriend)")->get()->toArray();
            $now = Carbon::now()->timezone('Europe/Moscow')->startOfDay();
            foreach ($orders as $order)
            {
                if ($order['discountId']!=0)
                {
                    $promocode = WebUsersDiscount::find($order['discountId']);
                    if ($promocode['friendId']!=0)
                    {
                        //Генерация и отправка дисконтной карты клиенту который привел друга
                        $to = Carbon::now()->timezone('Europe/Moscow')->addDays($base->getOption('webUserDays'))->startOfDay()->format("Y-m-d H:i:s");
                        
                        (new PromocodeController)->createDiscountCart(
                                                $promocode['friendId'], //id который привел
                                                $base->getOption('webUserDiscount'), //скидка
                                                $now, //дата от
                                                $to, //дата до
                                                0, //обычная карта
                                                $base->getOption('webUserSms')); //текст смс
                    }
                }

                    //Генерация и отправка промокода по акции "Приведи друга" клиенту которому нужно выслать акцию
                    $webUserPromocode = WebUsersDiscount::where('friendId',$order['webUserId'])
                                                        ->where('expiration','>=',Carbon::now()->timezone('Europe/Moscow')->startOfDay())
                                                        ->where('status',1)
                                                        ->get()->toArray();
                    if (empty($webUserPromocode))
                    {
                        $base = new Basectrl();
                        $to = Carbon::now()->timezone('Europe/Moscow')->addDays($base->getOption('bringFriendDays'))->startOfDay()->format("Y-m-d H:i:s");
                        (new PromocodeController)->createDiscountCart(
                                                $order['webUserId'], //id который должен получить промокод для акции друга
                                                $base->getOption('bringFriendDiscount'), //скидка
                                                $now, //дата от
                                                $to, //дата до
                                                1, //акция "Приведи друга"
                                                $base->getOption('bringFriendSMS')); //текст смс
                    }
                
                 
                
                $checkOrder = new webUsersBringFriend;
                $checkOrder->orderId = $order['id'];
                $checkOrder->save();
            }
        }
    }
}
