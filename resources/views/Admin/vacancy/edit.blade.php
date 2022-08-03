@extends('layouts.admin')

@section('content')



<div class="card" style="margin-top:10px">
    <div class="card-header">
        Редактирование вакансии
    </div>

<form action="{{ route('vacancy.update', $EditVacancy['id']) }}" method="post" style="margin-top:10px; margin-left:10px;" enctype="multipart/form-data">
{{ csrf_field() }}
{{ method_field('PATCH') }}

    <label>Название</label></br>
    <input type="text" name="vacancyTitle" size="50" value="{{$EditVacancy['vacancyTitle']}}"/><br />   
    <label>Описание</label></br>
    <textarea name="vacancyDescription" cols="50" rows="5">{{$EditVacancy['vacancyDescription']}}</textarea><br />
    @if(auth()->guard('admin')->user()->can('vacancy_add_image'))
    <label style="margin-top:20px;">Изображение ({{$EditVacancy['vacancyImage']}})</label>
    <div>
        <img src="{{$EditVacancy['vacancyImage']}}" height="50px"  style="filter: invert(85%) sepia(50%) saturate(5446%) hue-rotate(350deg) brightness(94%) contrast(94%);">
    </div>
    <input type="file"  name="vacancyImage" class="form-control" style="width: 20%; margin-top:10px">
    @endif


    

<div class="container-sm" style="margin-top: 20px">
        <div class="row">
            <div class="col-lg-6">
            <h3>Характеристики</h3>
            @foreach($AllProperty as $key=>$value)
                <label>{{$key}}</label>
                <select name="property[]"  class="form-control select2" multiple="multiple">
                            @foreach($value as $k=>$v) 
                                <option value="{{ $k }}" 
                                @if (isset($EditVacancy['Property']))
                                @if (array_key_exists($key,$EditVacancy['Property']))
                                    @if(array_key_exists($k,$EditVacancy['Property'][$key]))    
                                        selected
                                    @endif
                                @endif
                                @endif>
                                {{$v}}</option>
                            @endforeach                            
                </select>
            @endforeach
            </div>
            <div class="col-lg-6">
            <h3>Особенности</h3>
            @foreach($AllSpecialty as $key=>$value)
                <label>{{$key}}</label>
                <select name="specialty[]"  class="form-control select2" multiple="multiple">
                            @foreach($value as $k=>$v) 
                                <option value="{{ $k }}" 
                                @if (isset($EditVacancy['Specialty']))
                                @if (array_key_exists($key,$EditVacancy['Specialty']))
                                    @if(array_key_exists($k,$EditVacancy['Specialty'][$key]))    
                                        selected
                                    @endif
                                @endif
                                @endif>
                                {{$v}}</option>
                            @endforeach                            
                </select>
            @endforeach
            </div>
        </div>
</div>


<input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px; margin-bottom:10px">
</form>

</div>
<a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">Назад</a>




<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
	<script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script>
		jQuery(document).ready(function($) {
			$('.select2').select2();
		});
	</script>
@endsection