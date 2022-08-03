@extends('layouts.admin')

@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        {{session('error')}}
    </div>
@endif
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Редактирование акции
    </div>
<form action="{{ route('pageStock.update', $EditStock['id']) }}" method="post" enctype="multipart/form-data" style="margin-left:10px; margin-bottom:10px">
{{ csrf_field() }}
{{ method_field('PATCH') }}

    <label style="margin-top:20px;">Название:</label></br>
    <textarea name="title" cols="50" rows="5">{{$EditStock['title']}}</textarea><br />
    <label style="margin-top:20px;">Описание:</label></br>
    <textarea name="description" cols="50" rows="5">{{$EditStock['description']}}</textarea><br />
    <label style='margin-top:5px;margin-bottom:0px;'>Дата начала акции: </label><br>
    <input type="date" name="timeStart" value={{$EditStock['timeStart']}}><br />
    <label style='margin-top:5px;margin-bottom:0px;'>Дата окончания акции: </label><br>
    <input type="date" name="timeEnd" value={{$EditStock['timeEnd']}}><br />
    
    <label style="margin-top:20px;">Изображение ({{$EditStock['image']}})</label>
    <div>
        <img src="{{$EditStock['image']}}" style="box-shadow: 0 0 10px rgba(0,0,0,0.5);">
    </div>
    <input type="file"  name="image" class="form-control" style="width: 20%; margin-top: 10px">

    <label style="margin-top:20px; ">Опубликовать: </label>
    <input type="checkbox" name="status" @if($EditStock['status']) checked="checked" @endif /><br />


<input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px">
</form>
</div>
<a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>

@endsection
