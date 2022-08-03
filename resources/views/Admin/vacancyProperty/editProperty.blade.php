@extends('layouts.admin')

@section('content')


<form action="{{ route('updateProperty', ['id' => $id, 'editId' => $OneProperty->propertiesId]) }}" method="post">
{{ csrf_field() }}
    <label>Введите новое описание характеристики</label></br>
    <textarea name="description" cols="50" rows="5">{{$OneProperty->description}}</textarea><br />
    <input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px">
</form>

<a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>

@endsection