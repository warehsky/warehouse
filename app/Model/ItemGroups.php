<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemGroups extends Model
{
    protected $table = 'itemGroups';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'longTitle',
        'descr',
        'parentId',
        'moderatorId',
        'sort',
        'waitshow',
        'percentQuantity'
    ];
    /**
     * возвращает группы иерархически только одного уровня вложенности
    */
    public static function getGroupsMap($id=0){
        $groups = ItemGroups::select('id', 'title', 'longTitle')
        ->where('parentId', $id)
        ->where('deleted', 0)
        ->orderBy('sort')
        ->get();
        foreach($groups as $g){
            $g->children = ItemGroups::select('id', 'title', 'longTitle')
            ->where('parentId', $g->id)
            ->where('deleted', 0)
            ->get();
            if(\Storage::disk('public')->exists('img/img/catalog/icons/'.$g->id.'.svg'))
                $g->image = 'img/img/catalog/icons/'.$g->id.'.svg';
            else
                $g->image = "/img/icon_default.svg";
            foreach($g->children as $c){
                if(\Storage::disk('public')->exists('img/img/catalog/icons/'.$c->id.'.svg'))
                    $c->image = 'img/img/catalog/icons/'.$c->id.'.svg';
                else
                    $c->image = "/img/icon_default.svg";
            }
            if(\Storage::disk('public')->exists('img/img/catalog/small/'.$g->id.'.png'))
                $g->image_small = 'img/img/catalog/small/'.$g->id.'.png';
            else
                $g->image_small = "/img/icon_default.svg";
            foreach($g->children as $c){
                if(\Storage::disk('public')->exists('img/img/catalog/small/'.$c->id.'.png'))
                    $c->image_small = 'img/img/catalog/small/'.$c->id.'.png';
                else
                    $c->image_small = "/img/icon_default.svg";
            }
        }

        return $groups;
    }
    /**
     * возвращает все группы иерархически
     */
    public static function getGroupsAll($id=0){
        
        $groups = ItemGroups::where('parentId', $id)->where('deleted', 0)->get();
        foreach($groups as $g){
            $g->children = ItemGroups::where('parentId', $g->id)->get();
            if(count($g->children)>0)
                $g->children = self::getGroupsAll($g->id);
        }
        return $groups;
    }
    /**
     * возвращает все группы 
     */
    public static function getGroups($id=0){
        $groups = ItemGroups::where('parentId', $id)->get();
        $_groups = $groups->toArray();
        foreach($groups as $g){
            $g->children = ItemGroups::where('parentId', $g->id)->get();
            if(count($g->children)>0){
                $g->children = self::getGroups($g->id);
                $_groups = array_merge($_groups, $g->children);
            }
        }
        return $_groups;
    }
}
