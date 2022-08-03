@extends('layouts.app')

@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/datatables.min.js') }}"></script> 
<link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet">
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/users.css') }}" rel="stylesheet">

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css"> 
 -->


<div class="container">
    <form>
    <div class="users-field-title">
        <span class="users-label">Редактирование тестового пользователя <b>{{$user->fio}}</b>:</span>
    </div>
    <div class="users-edit-field">
        <label for="login" class="users-label">Login:</label>
        <input id="login" name="login"   value="{{$user->login}}"/>
    </div>
    <div class="users-edit-field">
        <label for="Password" class="users-label">Password:</label>
        <input id="login" name="Password"   value="{{$user->password}}"/>
    </div>
    <div class="users-edit-field">
        <label for="areaId" class="users-label">areaId:</label>
        <input id="areaId" name="areaId"   value="{{$user->areaId}}"/>
    </div>
    <div class="users-edit-field">
        <label for="PriceTypes" class="users-label">PriceTypes:</label>
        <input id="PriceTypes" name="PriceTypes"   value="{{$user->priceTypes}}"/>
    </div>
    <div class="users-edit-field">
        <label for="tradeDirection" class="users-label">tradeDirection:</label>
        <input id="tradeDirection" name="tradeDirection"   value="{{$user->tradeDirection}}"/>
    </div>
    <div class="users-edit-field">
        <label for="tradePerm" class="users-label">tradePerm:</label>
        <input id="tradePerm" name="tradePerm"   value="{{$user->tradePerm}}"/>
    </div>
    <div class="users-edit-field">
        <label for="storeCheckPerm" class="users-label">storeCheckPerm:</label>
        <input id="storeCheckPerm" name="storeCheckPerm"   value="{{$user->storeCheckPerm}}"/>
    </div>
    <div class="users-edit-field">
        <label for="gen" class="users-label">gen:</label>
        <input id="gen" name="gen"   value="{{$user->gen}}"/>
    </div>
    <div class="users-edit-field">
        <label for="f2percent" class="users-label">f2percent:</label>
        <input id="f2percent" name="f2percent"   value="{{$user->f2percent}}"/>
    </div>
    <div class="users-edit-field">
        <label for="f2time" class="users-label">f2time:</label>
        <input id="f2time" name="f2time"   value="{{$user->f2time}}"/>
    </div>
    <input id="userId" name="userId"   value="{{$user->id}}" type="hidden"/>
    <div class="users-edit-field">
        <input id="userUpdate" name="userUpdate" type="submit"   value="Сохранить"/>
    </div>
    </form>
    
</div>

<script>
    jQuery(document).ready(function($) {

    } );
    
</script>

@endsection
