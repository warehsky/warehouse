@extends('layouts.admin')
@section('content')

<div class="card" style="margin-top:10px">
    <div class="card-header">
        Новый номер
    </div>


<form action="/admin/phoneNumber" method="post" style="margin-left:10px;">
{{ csrf_field() }}
    <label>Номер телефона: </label><br />
        <input type="text" name="phone" size="50" /><br />
    <label>Имя: </label><br />
        <input type="text" name="name" size="50" /><br />
    <label>Рассылка: </label>
        <input type="checkbox" name="unsubscribe" /><br />
    <input type="submit" class="btn btn-success" value="Далее" style="margin-bottom:10px;">
</form>
</div>

<a style="margin-top:10px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>
@endsection