<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use App\Model\WebUsers;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as Basectrl;

class MessengerBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:code {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Возвращает код для авторизации пользователя';

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
        print($this->checkUserCode());
    }

    public function checkUserCode()
    {
        $phone = $this->argument('phone');

        $code = rand(100000, 999999);
        $wuser = WebUsers::where('phone', $phone)->first();
        if (!$wuser) 
        {
            WebUsers::create(['phone' => $phone, 'code' => $code, 'orderId' => 0]);
            return json_encode(['message' => '', 'code' => $code], JSON_UNESCAPED_UNICODE);
        }
        else 
        {
            $now = Carbon::now()->timezone('Europe/Moscow');
            $last = Carbon::parse(($wuser->createTm))->timezone('Europe/Moscow');
            $diff = $now->diffInSeconds($last) / 60;

            $base = new Basectrl();
            $waite = (int)$base->getOption('codeWaite');// частота посыла смс с одного номера

            if ($diff < $waite)
                return json_encode(['message' => 'Код подтверждения не отправлен, прошло менее ' . $waite . ' мин', 'code' => 0], JSON_UNESCAPED_UNICODE);
            else 
            {
                $wuser->update(['code' => $code]);
                return json_encode(['message' => '', 'code' => $code], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    
}
