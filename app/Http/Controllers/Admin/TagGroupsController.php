<?php

namespace App\Http\Controllers\Admin;

use App\Model\TagGroups;
use App\Model\Tagent;
use App\Model\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class TagGroupsController extends Controller
{
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = TagGroups::all();

        return view('Admin.taggroups.index', compact('users'));
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.taggroups.create');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store($request)
    {
        TagGroups::create($request->all());

        return redirect()->route('Admin.taggroups.index');
    }


    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $group = TagGroups::find($id);
        return view('Admin.taggroups.edit', compact('user'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $group = TagGroups::find($id);
        $group->update($request->all());
        
        return redirect()->route('Admin.taggroups.index');
    }

    public function show(Request $request, $id)
    {
        $group = TagGroups::find($id);
        return view('Admin.taggroups.show', compact('group'));
    }

    /**
     * Remove User from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $group = TagGroups::find($id);

        $group->delete();

        return redirect()->route('Admin.taggroups.index');
    }

    /**
     * Delete all selected User at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('users_manage')) {
            return abort(401);
        }
        TagGroups::whereIn('id', request('ids'))->delete();

        return response()->noContent();
    }

}
