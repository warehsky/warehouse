<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $table = 'tags';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'groupId',
        'moderatorId'
    ];
    /**
     * Возвращает теги присвоенные товару
     */
    public static function getTags($id){
        $tags = Tags::select('tags.id', 'tags.title', 'tagGroups.id as gid', 'tagGroups.title as gtitle')
        ->join('tagGroups', 'tagGroups.id', 'tags.groupId')
        ->join('itemTags', 'itemTags.tagId', 'tags.id')
        ->where('itemTags.itemId', $id)
        ->orderBy('tagGroups.title')
        ->orderBy('tags.title')
        ->get();

        return $tags;
    }
}
