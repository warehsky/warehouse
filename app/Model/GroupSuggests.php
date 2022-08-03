<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupSuggests extends Model
{
    protected $table = 'groupSuggests';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'groupId',
        'suggestId', 
    ];
    /**
     * 
     */
    public static function getSuggests($groupId){
        $result = GroupSuggests::select("itemGroups.id", "itemGroups.title")
        ->where("groupId", $groupId)
        ->join("itemGroups", "itemGroups.id", "groupSuggests.suggestId")
        ->orderBy("itemGroups.title")
        ->get();
        return $result;
    }
}
