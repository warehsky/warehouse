<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\VacancySpecialtyTitle;
use App\Model\VacancySpecialty;

class VacancySpecialtyController extends Controller
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
        $AllVacancySpecialty=VacancySpecialtyTitle::select('specialtyTitleId','specialtyTitle')->get();
        return view('Admin.vacancySpecialty.index',compact('AllVacancySpecialty'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'specialtyTitle' => 'required|unique:vacancySpecialtyTitle,specialtyTitle'
        ]);
        $addNewSpecialtyTitle = new VacancySpecialtyTitle;
        $addNewSpecialtyTitle->specialtyTitle =$request->specialtyTitle;
        $addNewSpecialtyTitle->save();
        return redirect('/admin/vacancySpecialty');
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
        $OneSpecialty=VacancySpecialty::where('specialtyTitleId',$id)->get();
        return view('Admin.vacancySpecialty.show',compact('OneSpecialty','id'));
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
        $OneSpecialty = VacancySpecialtyTitle::where('specialtyTitleId',$id)->first();
        return view('Admin.vacancySpecialty.edit',compact('OneSpecialty'));
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
            'specialtyTitle' => 'required',
        ]);
        $OneSpecialty = VacancySpecialtyTitle::where('specialtyTitleId',$id)->update(['specialtyTitle'=>$request['specialtyTitle']]);
        return redirect()->route('vacancySpecialty.index',$id);
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
        $OneSpecialty = VacancySpecialtyTitle::where('specialtyTitleId',$id)->delete();
        return redirect()->route('vacancySpecialty.index');
    }


    //Specialty



    public function destroySpecialty($id)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
            return redirect()->route('home');
        }
        $user = VacancySpecialty::where('specialtyId',$id)->delete();
        return back();
    }

    public function storeSpecialty(Request $request,$id)
    {
        $this->validate($request, [
            'vacancySpecialty' => 'required|unique:vacancySpecialty,specialtyDescription'
        ]);
        $addNewSpecialty = new VacancySpecialty;
        $addNewSpecialty->specialtyDescription=$request->vacancySpecialty;
        $addNewSpecialty->specialtyTitleId=$id;
        $addNewSpecialty->save();
        return redirect('/admin/vacancySpecialty/'.$id);//back();
    }

    public function editSpecialty($id,$editId)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
            return redirect()->route('home');
        }
        $OneSpecialty = VacancySpecialty::where('specialtyId',$editId)->first();
        return view('Admin.vacancySpecialty.editSpecialty',compact('OneSpecialty','id'));
    }

    public function updateSpecialty(Request $request, $id,$editId)
    {
        $this->validate($request, [
            'specialtyDescription' => 'required',
        ]);
        $OnePSpecialty = VacancySpecialty::where('specialtyId',$editId)->update(['specialtyDescription'=>$request['specialtyDescription']]);
        return redirect()->route('vacancySpecialty.show',$id);
    }

}
