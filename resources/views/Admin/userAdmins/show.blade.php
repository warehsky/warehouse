@extends('layouts.admin')
@section('content')
<a href="{{ route('userAdmins.index')}}"><button class="btn btn-outline-primary" style="margin-bottom:10px" >Вернутся к списку</button></a>
<div class="card">
    <div class="card-header">
        Просмотр WEB пользователя
    </div>

    <div class="card-body">
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.id') }}
                        </th>
                        <td>
                            {{ $user->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Login
                        </th>
                        <td>
                            {{ $user->login }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Почта
                        </th>
                        <td>
                            {{ $user->email }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Имя
                        </th>
                        <td>
                            {{ $user->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Api token
                        </th>
                        <td>
                            {{ $user->api_token }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Заметка
                        </th>
                        <td>
                            {{ $user->note }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Роль
                        </th>
                        <td>
                            @foreach($role as $key=>$value)
                            {{ $value }}
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>
            
        </div>


    </div>
</div>
@endsection