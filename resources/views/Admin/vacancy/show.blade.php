@extends('layouts.admin')

@section('content')

@if(auth()->guard('admin')->user()->can('vacancy_edit'))
<a href="{{ route('vacancy.edit' , $OneVacancy['id'])}}"><button  class="btn btn-info" >Редактировать</button></a>
<form action="{{ route('vacancy.destroy', $OneVacancy['id']) }}" method="POST" onsubmit="return confirm('Вы уверены');" style="display: inline-block;">
	<input type="hidden" name="_method" value="DELETE">
	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	<input type="submit" class="btn btn-danger" value="Удалить">
</form>
@endif



<table class="table table-bordered border border-primary" style="margin-top: 10px; width: 30%;">
    <tr>
        <td>Код</td>
        <td>{{$OneVacancy['id']}}</td>
    </tr>
    <tr> 
        <td>Название</td>
        <td>{{$OneVacancy['vacancyTitle']}}</td>
    </tr>
    <tr>    
        <td>Описание</td>
        <td>{{$OneVacancy['vacancyDescription']}}</td>
    </tr> 
    <tr>    
        <td>Изображение</td>
        <td><img src="{{$OneVacancy['vacancyImage']}}" height="50px" style="filter: invert(85%) sepia(50%) saturate(5446%) hue-rotate(350deg) brightness(94%) contrast(94%);"></td>
    </tr> 

</table>






<div class="container-sm">
        <div class="row">
            <div class="col-lg-6">
            <h3>Характеристики</h3>
            @if(isset($OneVacancy['Property']))
                @foreach($OneVacancy['Property'] as $key => $value)
                    <table class="table table-striped table-bordered table-sm" style="width: 95%;">
                        <thead>
                            <th scope="col">{{$key}}</th>
                        </thead>
                    @foreach($value as $el)
                        <tr>
                            <td>{{$el}}</td>
                        </tr> 
                    @endforeach
                    </table>
                @endforeach
            @endif    
            </div>
            <div class="col-lg-6">
            <h3>Особенности</h3>
            @if(isset($OneVacancy['Specialty']))
                @foreach($OneVacancy['Specialty'] as $key => $value)
                    <table class="table table-striped table-bordered table-hover table-sm" style="width: 95%;">
                        <thead>
                            <th scope="col">{{$key}}</th>
                        </thead>
                    @foreach($value as $el)
                        <tr>
                            <td>{{$el}}</td>
                        </tr> 
                    @endforeach
                    </table>
                @endforeach
            @endif 
            </div>
        </div>
</div>







<a href="{{ route('vacancy.index')}}"><button  class="btn btn-default" >Назад к списку вакансий</button></a>









@endsection