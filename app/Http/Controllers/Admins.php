<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Admin;
use Spatie\Permission\Models\Role;
use DB;
use Hash;

class Admins extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            
            if(!\Auth::guard('admin')->check()){
                return redirect(route('admin.login'));
            }
            if(!\Auth::guard('admin')->user()->hasRole('administrator')){
                //return redirect(route('home'));
            }
            return $next($request);
        });
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = Admin::all();

        return view('Admin/users/users', ['users' => $users]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin/users/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $request->input('name');
        $password = $request->input('password');
        $email = $request->input('email');
        $role = $request->input('role');
        $note = $request->input('note');

        $hash_password = Hash::make($password);

        Admin::create([
            'name' => $name,
            'password' => $hash_password,
            'email' => $email,
            'role' => $role,
            'note' => $note
        ]);

        return redirect('/users');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('Admin/users/profile', ['users' => Admin::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $roles = Role::get()->pluck('name', 'name');
        return view('Admin/users/edit', ['users' => Admin::findOrFail($id), 'roles' => $roles]);
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
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');
        $note = $request->input('note');
        $data = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'note' => $note
        ];
        if($password && strlen($password)){
            $hash_password = Hash::make($password);
            $data['password'] = $hash_password;
        }
        $roles = $request->input('roles') ? $request->input('roles') : [];
        $user = Admin::find($id);
        $user->syncRoles($roles)->update($data);

        return redirect('/users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $users = Admin::findOrFail($id);

        $users->delete();

        return redirect('/users/');


    }
}
