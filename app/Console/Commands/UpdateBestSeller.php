<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as Basectrl;
use App\Model\ItemGroups;
use App\Model\ItemsLink;

class UpdateBestSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:bestSeller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление хитов продаж';

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
        print($this->updateBestSeller());
    }

    public function updateBestSeller($autoPopular=1)
    {
        $base = new Basectrl();
        $status = $base->getOption('bestSellerStatus');
        if ($status || !$autoPopular)
        {
            $this->updateItemsPopular();
            $this->updateBestSellerTag();
            \Log::channel('cron')->info("update:bestSeller");
        }
    }

    public function updateItemsPopular()
    {
        $base = new Basectrl();
        $countDay = $base->getOption('bestSellerDay');
        $time = Carbon::now()->timezone('Europe/Moscow')->subDays($countDay)->startOfDay()->format("Y-m-d H:i:s");
        
        $sql = "SELECT k.itemId as id, count(k.itemId) as count from orderItem as k 
                JOIN orders as j ON j.id=k.orderId 
                WHERE j.status=4 AND j.created_at>='$time' 
                GROUP BY k.itemId";
        $items = \DB::Connection()->select($sql);
        if ($items)
        {
            ItemsLink::where("popular",'<>', 0)->where('autoPopular',1)->update(['popular' => 0]);
            foreach($items as $value)
                ItemsLink::where("id", $value->id)->where('autoPopular',1)->update(["popular" => $value->count]);
        }
    }

    public function updateBestSellerTag()
    {
        $base = new Basectrl();
        $countAllItems = $base->getOption('countBestSeller');
        $percentGroup = ItemGroups::select('id','percentQuantity')
                                    ->where('parentId',0)
                                    ->where('percentQuantity','!=',0)
                                    ->where('deleted','!=',1)
                                    ->get();
        $delete = "DELETE w FROM itemTags w
                        INNER JOIN itemsLink e ON e.id=w.itemId
                        WHERE w.tagId=331 AND e.autoPopular=1";
        \DB::Connection()->select($delete);
        foreach ($percentGroup as $value)
        {
            $countItems = round($countAllItems * $value->percentQuantity / 100);
            $sql = "SELECT k.id FROM itemsLink as k 
                    JOIN itemGroups as j ON k.parentId=j.id
                    WHERE j.parentId = $value->id  ORDER BY k.popular DESC LIMIT $countItems";
            $items = \DB::Connection()->select($sql);
            $items = implode(',',array_column($items,'id'));

            $insert = "INSERT INTO itemTags (itemId, tagId, moderatorId)
                    SELECT i.id,331,1 FROM itemsLink as i
                    WHERE i.id IN ($items)";
            \DB::Connection()->select($insert);
        }
    }
}


 