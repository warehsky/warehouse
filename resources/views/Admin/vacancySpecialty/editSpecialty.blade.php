@extends('layouts.admin')

@section('content')


<form action="{{ route('updateSpecialty',['id' => $id, 'editId' => $OneSpecialty->specialtyId])}}" method="post">
{{ csrf_field() }}
    <label>Введите новое описание особенности</label></br>
    <textarea name="specialtyDescription" cols="50" rows="5">{{$OneSpecialty->specialtyDescription}}</textarea><br />
    <input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px">
</form>

<a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>

@endsection