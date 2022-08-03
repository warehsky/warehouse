<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $table = 'vacancy';
    public $timestamps = false; 

    protected $fillable =
    [
        'vacancyTitle',
        'vacancyDescription',
        'vacancyImage',
    ];


    public static function getItem($id)
    {
        $select0=Vacancy::where('id',$id)->first()->toArray();
        if (!file_exists(public_path($select0['vacancyImage'])) || $select0['vacancyImage']==null)
        {
            $select0['vacancyImage']="/img/img/vacances/default-vacancy.svg";
        }
        $select1=Vacancy::leftjoin('vacancyGroups', 'vacancy.id', '=', 'vacancyGroups.vacancyId')
            ->leftjoin('vacancyProperties', 'vacancyGroups.propertiesId', '=', 'vacancyProperties.propertiesId')
            ->leftjoin('vacancyPropertiesTitle', 'vacancyProperties.titleId', '=', 'vacancyPropertiesTitle.titleId')
            ->select('vacancyProperties.description', 'vacancyProperties.propertiesId', 'vacancyPropertiesTitle.title','vacancyPropertiesTitle.titleId')
            ->where('vacancy.id',$id)
            ->get();

        $select2=Vacancy::leftjoin('vacancyGroupsSpecialty', 'vacancy.id', '=', 'vacancyGroupsSpecialty.vacancyId')
           ->leftjoin('vacancySpecialty', 'vacancyGroupsSpecialty.specialtyId', '=', 'vacancySpecialty.specialtyId')
           ->leftjoin('vacancySpecialtyTitle', 'vacancySpecialtyTitle.specialtyTitleId', '=', 'vacancySpecialty.specialtyTitleId')
           ->select('vacancySpecialty.specialtyId', 'vacancySpecialty.specialtyDescription', 'vacancySpecialtyTitle.specialtyTitle')
           ->where('vacancy.id',$id)
           ->get();

        $result= array_merge($select0);
        foreach ($select1 as $key)
        {
            if(!is_null($key->propertiesId))
            $result['Property'][$key->title][$key->propertiesId]=$key->description;
        }
        foreach ($select2 as $key)
        {
            if(!is_null($key->specialtyId))
            $result['Specialty'][$key->specialtyTitle][$key->specialtyId]=$key->specialtyDescription;
        }
        return $result;
    }

}
