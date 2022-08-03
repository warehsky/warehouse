<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\CacheCheck;
use App\Model\TradeDirections;
use App\Model\Items;
use App\Model\TradeMark;
use App\Model\Tagent;

class CreateTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tree:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Пересчет дерева каталога товаров (если необходимо)';

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
        if( $this->checkRefresh() ){
            if($this->treesRefresh()){
                $this->updateRefresh();
                echo "выполнен пересчет дерева";
            }else
                echo "ошибка выполнения пересчета дерева";
        }else{
            echo "пересчет дерева не требуется\n";
        }
    }
    /*
    * проверка необходимости пересчета
    */
    private function checkRefresh(){
        return true;  // !!!!!!!!!!!!!!!!!!!!!!!!!!!
        $checks = CacheCheck::all();
        foreach($checks as $check){
            $sql = "select max(updateTm) as updateTm, count(*) as count from {$check->tbl}";
            $r = \DB::connection('mtagent')->select( $sql );
            if($r && ($r[0]->updateTm > $check->lastUpdate || $r[0]->count != $check->count)){
                return true;
            }
        }
        return false;
    }
    /*
    * обновление данных о необходимости пересчета
    */
    private function updateRefresh(){
        $checks = CacheCheck::all();
        foreach($checks as $check){
            $sql = "select max(updateTm) as updateTm, count(*) as count from {$check->tbl}";
            $r = \DB::connection('mtagent')->select( $sql );
            if($r && ($r[0]->updateTm > $check->lastUpdate || $r[0]->count != $check->count)){
                $sql = "UPDATE cacheCheck SET lastUpdate='{$r[0]->updateTm}', count={$r[0]->count} WHERE tbl='$check->tbl'";
                \DB::connection()->select( $sql );
            }
        }
        return true;
    }
    /**
     * Пересчет деревьев по каждому тоговому напрвлению
     */
    private function treesRefresh(){
        $tds = TradeDirections::pluck('id')->toArray();
        $tdId = 140;
        //foreach($tds as $tdId){
            $this->groups = [];
            $this->oneTdRefresh(0, $tdId);
            $this->treeSave($tdId);
        //}
        dd($this->groups);
        return true;
    }
    /**
     * Пересчет дерева по одному торговому направлению
     */
    private function oneTdRefresh($grId, $tdId){
        $oItems = new Items;
        $groups = $oItems->getItem_Groups($grId, $tdId);
        $groups = array_values($groups);
        if($grId>0 && count($groups)>0)
            $items = Items::getGroupItemsCount($groups[0]->id, $tdId);
        else
            $items = 0;
        while(count($groups)==1 && $groups[0]->childs>1){
            if($groups[0]->color == 'active' && $items>0){ // у группы есть товары
                break;
            }
            $groups = array_values($oItems->getItem_Groups($groups[0]->id, $tdId));
            if(count($groups)>0)
                $items = Items::getGroupItemsCount($groups[0]->id, $tdId);
            else
                break;
        }
        $this->groups = array_merge($this->groups, $groups);
        foreach($groups as $gr){
            $groups = $this->oneTdRefresh($gr->id, $tdId);
            $groups = array_values($groups);
            $this->groups = array_merge($this->groups, $groups);
        }
        
        return $groups;
    }
    /**
     * Сохраняет пересчитанное дерево в БД
     */
    private function treeSave($tdId){
        if(!$this->groups) return false;
        \DB::beginTransaction();
        try{
            $sql = "delete from cacheItemGroups where tdId={$tdId}";
            \DB::connection()->select( $sql );
            $sql = "";
            foreach($this->groups as $index=>$gr){
                $comma = ",";
                if($index%20==0){
                    if($index>0)
                        \DB::connection()->select( $sql );
                    $sql = "INSERT INTO cacheItemGroups (`tdId`, `itemId`, `parentId`) VALUES ";
                    $comma = "";
                }
                $sql .= $comma . "({$tdId}, {$gr->id}, {$gr->parentId})";
            }
            if($sql != "")
                \DB::connection()->select( $sql );
        }catch(\Exception $e){
            \DB::rollback();
            throw $e;
        }
        \DB::commit();
    }
}
