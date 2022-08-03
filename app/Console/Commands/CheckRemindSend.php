<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Orders;
use App\Model\OwnMessage;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as Basectrl;

class CheckRemindSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:remindsms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка если есть ожидаемое время визита курьера шлем заказчику смс';

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
        print($this->checkRemindSms());
    }
    /*
    * проверка наличия времени визита курьера и проверка что это текущий день, и отправка смс получателю
    */
    private function checkRemindSms(){
        $from = Carbon::now()->timezone('Europe/Moscow')->startOfDay();
        $to = Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $orders = Orders::
        whereNotNull('visiteTime')->whereBetween('visiteTime', [$from, $to]) // проверка что это текущий день
        ->where('remindSms', 0) // проверка, что смс не отсылали
        ->where('status', 4) // проверка что заказ собран на складе и передан курьеру
        ->get();
        $base = new Basectrl();

        $msg = $base->getOption('smsRemind');
        foreach($orders as $row){
            $phone = $row->phone;
            if($row->phoneConsignee && strlen($row->phoneConsignee)>9) // если задан телефон получателя
                $phone = $row->phoneConsignee;
            $time = Carbon::parse($row->visiteTime)->timezone('Europe/Moscow')->format('H:i');
            if($row->sum_last>0)
                $time .= " сумма заказа {$row->sum_last} р";
            $now = Carbon::now()->timezone('Europe/Moscow');
            $visit = Carbon::parse($row->visiteTime)->timezone('Europe/Moscow');
            if( $now < $visit ){ // если текущее время уже позже времени прибытия смс не посылаем (нет безсмысленно)
                $this->sendSms($phone, $msg.$time, $row);
                OwnMessage::create(['webUserId' => $row->webUserId, 'msg' => $msg.$time." заказ№".$row->id]);
            }
        }
    }
    /**
     * отправка смс 
     * Входные параметры:
     * phone: телефон заказчика
     * msg: сообщение
     * order: объект заказ на основании которого отсылается смс 
    */
    private function sendSms($phone, $msg, $order){
        $base = new Basectrl();
        $res = $base->sendSms($phone, $msg);
        if($res)
            $order->update(['remindSms' => ($order->remindSms + 1)]);
    }
    
    
}
