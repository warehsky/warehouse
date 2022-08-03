@extends('layouts.admin')

@section('content')

<div class="card" style="margin-top:10px">
    <div class="card-header">
        Создание вакансии
    </div>


<form action="/admin/vacancy" method="post" style="margin-top:10px; margin-left:10px; margin-bottom:10px" enctype="multipart/form-data">
{{ csrf_field() }}
        <label>Название вакансии: </label><br />
		<input type="text" name="vacancyTitle" size="50" /><br />
        <label>Описание вакансии: </label><br />
        <textarea name="vacancyDescription" cols="50" rows="5"></textarea><br />
        @if(auth()->guard('admin')->user()->can('vacancy_add_image'))
        <label for="vacancyImage">Иконка</label>
        <input type="file" id="vacancyImage" name="vacancyImage" class="form-control">
        @endif
        <label>Требуется в данный момент: </label>
        <input type="checkbox" name="vacancyRequired" /><br />
        <input type="submit" class="btn btn-success" value="Далее">
</form>
</div>
<a style="margin-top:10px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>

@endsection