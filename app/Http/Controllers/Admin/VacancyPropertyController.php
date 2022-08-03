<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Model\VacancyPropertiesTitle;
use App\Model\VacancyProperties;


use App\Http\Controllers\Controller;

class VacancyPropertyController extends Controller
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
            $AllVacancyProperty=VacancyPropertiesTitle::select('titleId','title')->get();
            return view('Admin.vacancyProperty.index',compact('AllVacancyProperty'));
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
            'vacancyPropertyTitle' => 'required|unique:vacancyPropertiesTitle,title'
        ]);
            $addNewPropertiesTitle = new VacancyPropertiesTitle;
            $addNewPropertiesTitle->title=$request->vacancyPropertyTitle;
            $addNewPropertiesTitle->save();
            return redirect('/admin/vacancyProperty');
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
        $OneProperty=VacancyProperties::where('titleId',$id)->get();
        return view('Admin.vacancyProperty.show',compact('OneProperty','id'));
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
        $OneProperty = VacancyPropertiesTitle::where('titleId',$id)->first();
        return view('Admin.vacancyProperty.edit',compact('OneProperty'));
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
            'title' => 'required',
        ]);
        $OneProperty = VacancyPropertiesTitle::where('titleId',$id)->update(['title'=>$request['title']]);
        return redirect()->route('vacancyProperty.index',$id);
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
        $user = VacancyPropertiesTitle::where('titleId',$id)->delete();
        return redirect()->route('vacancyProperty.index');
    }








    public function destroyProperty($id)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
            return redirect()->route('home');
        }
        $user = VacancyProperties::where('propertiesId',$id)->delete();
        return back();
    }

    public function storeProperty(Request $request,$id)
    {
        $this->validate($request, [
            'vacancyProperty' => 'required|unique:vacancyProperties,description'
        ]);
        $addNewProperties = new VacancyProperties;
        $addNewProperties->description=$request->vacancyProperty;
        $addNewProperties->titleId=$id;
        $addNewProperties->save();
        return redirect('/admin/vacancyProperty/'.$id);
    }

    public function editProperty($id,$editId)
    {
        if (! \Auth::guard('admin')->user()->can('vacancy_edit')) {
            return redirect()->route('home');
        }
        $OneProperty = VacancyProperties::where('propertiesId',$editId)->first();
        return view('Admin.vacancyProperty.editProperty',compact('OneProperty','id'));
    }

    public function updateProperty(Request $request, $id,$editId)
    {
        $this->validate($request, [
            'description' => 'required',
        ]);
        $OneProperty = VacancyProperties::where('propertiesId',$editId)->update(['description'=>$request['description']]);
        return redirect()->route('vacancyProperty.show',$id);
    }


}


	//
    