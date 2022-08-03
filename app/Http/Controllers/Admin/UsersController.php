<?php

namespace App\Http\Controllers\Admin;

use App\Model\Admin;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\StoreUsersRequest;
use App\Http\Requests\Admin\UpdateUsersRequest;

class UsersController extends Controller
{
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $users = Admin::select('id','name','login','api_token','email','note','chatName')->get();
        return view('Admin.userAdmins.index', compact('users'));
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $role = Role::get()->pluck('name','name');
        return view('Admin.userAdmins.create', compact('role'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUsersRequest $request)
    {
        $user = new Admin;
        $user->name=$request->name;
        $user->login=$request->login;
        $user->email=$request->email;
        $user->note=$request->note;
        $user->password=Hash::make($request->password);
        $user->save();
        $user->assignRole($request->role);
        $user->generateToken();
        return redirect()->route('userAdmins.index');
    }


    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $user = Admin::find($id);
        $role = $user->getRoleNames()->toArray();
        $roles = Role::get()->pluck('name');
        return view('Admin.userAdmins.edit', compact('user','role','roles'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUsersRequest $request, $id)
    {
        $user=Admin::find($id);
        $user->update([
            'name'=>$request['name'], 
            'email'=>$request['email'], 
            'note'=>$request['note'],
            'login'=>$request['login'],
            'chatName'=>$request['chatName'],
        ]);

        if ($request['password']!=null)
                $user->update(['password'=>Hash::make($request->password),]);
        $user->syncRoles($request->role);

        if (isset($request->api_token))
            $user->generateToken();
        return redirect()->route('userAdmins.index');
    }

    public function show(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $user = Admin::find($id);
        $role = $user->getRoleNames()->toArray();
        return view('Admin.userAdmins.show', compact('user','role'));
    }

    /**
     * Remove User from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if (! \Auth::guard('admin')->user()->can('userAdmin_all')) {
            return redirect()->route('home');
        }
        $user = Admin::find($id);
        $user->delete();
        return redirect()->route('userAdmins.index');
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
        User::whereIn('id', request('ids'))->delete();

        return response()->noContent();
    }

}
