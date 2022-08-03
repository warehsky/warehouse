<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // проверка рабочего времени и перевод режима работы чата в режим автоответа
        $schedule->command('chat:autoanswer')
                 ->hourly(); //запускать каждый час
        // отправка смс уведомлений
        $schedule->command('check:remindsms')
                 ->everyMinute(); //запускать каждые 2 мин
        //Проверка акции "Приведи друга"
       $schedule->command('check:bringFriendStock')
                ->everyFiveMinutes(); //запускать каждые 5 мин
        // проверка товара на тег "Новинки"
        $schedule->command('check:NewItems')
                 ->daily(); //запускать каждый день в полночь        
        // построение рейтинга товаров и распределение тега "Хиты продаж"
        $schedule->command('update:bestSeller')
                 ->daily(); //запускать каждый день в полночь        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
