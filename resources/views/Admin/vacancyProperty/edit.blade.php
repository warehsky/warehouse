@extends('layouts.admin')

@section('content')



<form action="{{ route('vacancyProperty.update', $OneProperty->titleId) }}" method="post">
{{ csrf_field() }}
{{ method_field('PATCH') }}
    <label>Введите новое название характеристики</label></br>
    <input type="text" name="title" size="50" value="{{$OneProperty->title}}"/><br />
    <input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px">
</form>

<a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>

@endsection