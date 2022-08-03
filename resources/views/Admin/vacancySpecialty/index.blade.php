@extends('layouts.admin')

@section('content')

<h3>Меню редактирования названий особенностей</h3>
<div class="container-sm">
        <div class="row">
        @if(auth()->guard('admin')->user()->can('vacancy_edit'))
            <div class="col-lg-4">
                <form action="/admin/vacancySpecialty" method="post" style="margin-bottom: 10px">
                    {{ csrf_field() }}
	                <input type="submit" class="btn btn-success" value="Создать"/>
                    <input type="text" name="specialtyTitle" size="50"/>
                </form>   
            </div>
            @endif
        </div>
</div>




<table class="table table-striped table-bordered table-sm">
                
            <thead>
                <th scope="col">Название характеристики</th>
            </thead>
                @foreach($AllVacancySpecialty as $key=>$value)
                    <tr>
                        <td>{{$value->specialtyTitle}}</td>
                        <td>
                        @if(auth()->guard('admin')->user()->can('vacancy_edit'))
                            <a href="{{ route('vacancySpecialty.show', $value->specialtyTitleId)}}"><button  class="btn btn-xs btn-primary" >Открыть</button></a>
                            <a href="{{ route('vacancySpecialty.edit',$value->specialtyTitleId)}}"><button  class="btn btn-xs btn-info" >Редактировать название</button></a>
                            <form action="{{ route('vacancySpecialty.destroy', $value->specialtyTitleId) }}" method="POST" onsubmit="return confirm('Вы уверены');" style="display: inline-block;">
	                            <input type="hidden" name="_method" value="DELETE">    
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
	                            <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                            </form>
                        @endif
                        </td>
                    </tr>
                @endforeach
</table>
<a href="{{ route('vacancy.index')}}"><button  class="btn btn-default" >Назад к списку вакансий</button></a>

@endsection