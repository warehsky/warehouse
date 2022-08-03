<?php

namespace App\Http\Controllers\Admin;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRolesRequest;
use App\Http\Requests\Admin\UpdateRolesRequest;

class RolesController extends Controller
{
    /**
     * Display a listing of Role.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //\Auth::guard('admin')->user()->assignRole('administrator');
        //dd(\Auth::guard('admin')->user()->can('users_manage'));
        //if (! \Auth::guard('admin')->user()->can('users_manage')) {
        //    return abort(401);
        //}
        
       if (! \Auth::guard('admin')->user()->can('userAdmin_all'))
       {
        return redirect()->route('home');
       }

        $roles = Role::all();

        return view('Admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating new Role.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $permissions = Permission::get()->pluck('name', 'name');

        return view('Admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created Role in storage.
     *
     * @param  \App\Http\Requests\StoreRolesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRolesRequest $request)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $role = Role::create($request->except('permission'));
        $permissions = $request->input('permission') ? $request->input('permission') : [];
        $role->givePermissionTo($permissions);

        return redirect()->route('roles.index');
    }


    /**
     * Show the form for editing Role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $permissions = Permission::get()->pluck('name', 'name');

        return view('Admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update Role in storage.
     *
     * @param  \App\Http\Requests\UpdateRolesRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRolesRequest $request, Role $role)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }

        $role->update($request->except('permission'));
        $permissions = $request->input('permission') ? $request->input('permission') : [];
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index');
    }

    public function show(Role $role)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }

        $role->load('permissions');

        return view('Admin.roles.show', compact('role'));
    }


    /**
     * Remove Role from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }

        $role->delete();

        return redirect()->route('roles.index');
    }

    /**
     * Delete all selected Role at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        Role::whereIn('id', request('ids'))->delete();

        return response()->noContent();
    }

}
