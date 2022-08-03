<?php

namespace App\Http\Controllers\Admin;
use App\Model\Vacancy;
use App\Model\VacancyGroups;
use App\Model\VacancyGroupsSpecialty;
use App\Model\VacancySpecialty;
use App\Model\VacancyProperties;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class VacancyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_view')) {
            return redirect()->route('home');
        }
            $AllVacancy= Vacancy::all();
            return view('Admin.vacancy.index',compact('AllVacancy'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
            return redirect()->route('home');
        }
        return view('Admin.vacancy.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'vacancyTitle' => 'required|unique:vacancy,vacancyTitle',
            'vacancyDescription' => 'required',
        ]);
      //  dd($request);
        $addNewVacancy = new Vacancy;
        $addNewVacancy->vacancyTitle=$request->vacancyTitle;
        $addNewVacancy->vacancyDescription=$request->vacancyDescription;
       
        if ($request->vacancyRequired)
            $addNewVacancy->vacancyRequired=1;
        else 
            $addNewVacancy->vacancyRequired=0;

        $addNewVacancy->save();

        if ($request->hasFile('vacancyImage')) 
        {
            $nameImage = $request->vacancyImage->getClientOriginalName();
            $request->vacancyImage->storeAs("/img/img/vacances/", $nameImage);
            $addNewVacancy->update(['vacancyImage'=>"/img/img/vacances/".$nameImage]);
        }

        $id=Vacancy::where('vacancyTitle',$request->vacancyTitle)->first();
        return redirect('/admin/vacancy/'.$id->id.'/edit');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_view')) {
            return redirect()->route('home');
        }
        $OneVacancy=Vacancy::getItem($id);
        return view('Admin.vacancy.show',compact('OneVacancy'));
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
        return redirect()->route('home');
        }
        $AllProperty=$this->getAllProperties();
        $AllSpecialty=$this->getAllSpecialty();
        $EditVacancy=Vacancy::getItem($id);
        return view('Admin.vacancy.edit',compact('EditVacancy','AllProperty','AllSpecialty'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'vacancyTitle' => 'required',
            'vacancyDescription' => 'required',
        ]);
        $item=Vacancy::find($id);
        $item->update([
            'vacancyTitle'=>$request['vacancyTitle'],   
            'vacancyDescription'=>$request['vacancyDescription'],]);

        VacancyGroups::where('vacancyId', $id)->delete();
        if (isset($request['property']))
        foreach($request->input('property') as $w){
            $t[]=VacancyGroups::create(['vacancyId'=>$id, 'propertiesID'=>$w]);
        }
        VacancyGroupsSpecialty::where('vacancyId', $id)->delete();
        if (isset($request['specialty']))
        foreach($request->input('specialty') as $w){
            $t[]=VacancyGroupsSpecialty::create(['vacancyId'=>$id, 'specialtyId'=>$w]);
        }

        if ($request->hasFile('vacancyImage')) {

            $nameImage = $request->vacancyImage->getClientOriginalName();
            $request->vacancyImage->move(public_path()."/img/img/vacances/", $nameImage);
            $item->update(['vacancyImage'=>"/img/img/vacances/".$nameImage]);
        }
        return redirect()->route('vacancy.show',$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
            return redirect()->route('home');
        }
        $user = Vacancy::find($id);
        $user->delete();
        return redirect()->route('vacancy.index');
    }

    
    public function getAllProperties()
    {
        $select=VacancyProperties::leftjoin('vacancyPropertiesTitle', 'vacancyProperties.titleId', '=', 'vacancyPropertiesTitle.titleId')
                ->select('vacancyProperties.description','vacancyProperties.propertiesId','vacancyPropertiesTitle.title','vacancyPropertiesTitle.titleId')
                ->get();
        $result=array();
        foreach ($select as $key)
        {
            $result[$key->title][$key->propertiesId]=$key->description;
        }
        return $result;
    }

    public function getAllSpecialty()
    {
        $select=VacancySpecialty::leftjoin('vacancySpecialtyTitle', 'vacancySpecialty.specialtyTitleId', '=', 'vacancySpecialtyTitle.specialtyTitleId')
                ->select('vacancySpecialty.specialtyId','vacancySpecialty.specialtyDescription','vacancySpecialtyTitle.specialtyTitle')
                ->get();
        $result=array();
        foreach ($select as $key)
        {
            $result[$key->specialtyTitle][$key->specialtyId]=$key->specialtyDescription;
        }
        return $result;
    }

    public function changeRequired($id)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_view')) {
            return redirect()->route('home');
        }
        $fin=Vacancy::where('id',$id)->first();
        $fin->vacancyRequired=!$fin->vacancyRequired;
        $fin->save();
        return redirect()->route('vacancy.index');
    }

    public function MassDestroy(Request $request)
    {
        Vacancy::whereIn('id', request('ids'))->delete();

        return back();
    }

    public function helpPageVacancy()
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
            return redirect()->route('home');
        }
        return view('Admin.vacancy.help');
    }

}