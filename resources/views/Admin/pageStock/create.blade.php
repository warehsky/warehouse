@extends('layouts.admin')

@section('content')

@if(session('error'))
    <div class="alert alert-danger">
        {{session('error')}}
    </div>
@endif

<div class="card" style="margin-top:10px">
    <div class="card-header">
        Создание акции
    </div>

<form action="/admin/pageStock" method="post" style="margin-top:20px; margin-left:10px; margin-bottom:10px" enctype="multipart/form-data">
{{ csrf_field() }}
    <label>Название акции: </label><br />
    <textarea name="title" cols="50" rows="5"></textarea><br />
    <label>Описание акции: </label><br />
    <textarea name="description" cols="50" rows="5"></textarea><br />
    <label for="image" style="margin-top:20px;">Изображение</label>
    <input type="file" id="image" name="image"  class="form-control" style="width:20%">
    <label style='margin-top:5px;margin-bottom:0px;'>Дата начала акции: </label><br>
    <input type="date" name="timeStart"><br />
    <label style='margin-top:5px;margin-bottom:0px;'>Дата окончания акции: </label><br>
    <input type="date" name="timeEnd"><br />
    <label style="margin-top:20px; ">Опубликовать: </label>
    <input type="checkbox" name="status" /><br />
    <input type="submit" class="btn btn-success" value="Далее">
</form> 

</div>
<a style="margin-top:10px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>
@endsection
