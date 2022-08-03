<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as Basectrl;

class CheckNewItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:NewItems';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавляет или убирает тег "Новинки" для товаров которые добавлены в базу за последние N дней';

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
        print($this->checkNewItems());
    }

    public function checkNewItems()
    {
        $base = new Basectrl();
        $countDay = $base->getOption('NewItemsDay');
        $time = Carbon::now()->timezone('Europe/Moscow')->subDays($countDay)->startOfDay()->format("Y-m-d H:i:s");
        $delete = "DELETE w FROM itemTags w
                    INNER JOIN itemsLink e ON e.id=w.itemId
                    WHERE w.tagId=332 AND w.moderatorId=1 AND e.created_at<'$time'";
        \DB::Connection()->select($delete);
        $insert = "INSERT INTO itemTags (itemId, tagId, moderatorId)
                    SELECT i.id,332,1 FROM itemsLink as i
                    WHERE 332 NOT IN (SELECT tagId FROM itemTags WHERE itemId=i.id) AND i.created_at>='$time'";
        \DB::Connection()->select($insert);
        \Log::channel('cron')->info("check:NewItems");
    }
}