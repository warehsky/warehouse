<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\ChatOptions;
use Carbon\Carbon;

class CheckChatAutoAnswer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:autoanswer';
    protected $autoreply = 0;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка рабочего времени и перевод чата в режим авто ответа';

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
        print($this->checkAutoReply());
    }
    /*
    * проверка рабочего времени и перевода режима работы чата
    */
    private function checkAutoReply(){
        $autoreply = 0;
        $now = Carbon::now();

        $start = Carbon::createFromTimeString('19:00');
        $end = Carbon::createFromTimeString('09:00')->addDay();

        if ($now->between($start, $end)) { // не рабочее время у оператора
            $autoreply = 1;
        }else
            $autoreply = 0;
        $this->updateChatOption($autoreply);
    }
    /*
    * обновление оций чата
    */
    private function updateChatOption($autoreply = 0){
        $options = ChatOptions::find(1);
        if($autoreply == 0 && $options->autoreply == 0) // если рабочее время и автоответ поставил оператор
            return;
        $options->update(['autoreply' => $autoreply]);
        
    }
    
}
