@extends('layouts.admin')

@section('content')



<form action="{{ route('vacancySpecialty.update', $OneSpecialty->specialtyTitleId) }}" method="post">
{{ csrf_field() }}
{{ method_field('PATCH') }}
    <label>Введите новое название особенности</label></br>
    <input type="text" name="specialtyTitle" size="50" value="{{$OneSpecialty->specialtyTitle}}"/><br />
    <input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px">
</form>

<a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>

@endsection