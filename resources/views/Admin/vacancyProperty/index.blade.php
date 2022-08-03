@extends('layouts.admin')

@section('content')

<h3>Меню редактирования названий характеристик</h3>

<div class="container-sm">
        <div class="row">
            @if(auth()->guard('admin')->user()->can('vacancy_edit'))
            <div class="col-lg-4">
                <form action="/admin/vacancyProperty" method="post" style="margin-bottom: 10px;">
                    {{ csrf_field() }}
	                <input type="submit" class="btn btn-success" value="Создать"/>
                    <input type="text" name="vacancyPropertyTitle" size="50"/>
                </form> 
            </div>
            @endif
        </div>
</div>


<table class="table table-striped table-bordered table-sm ">
                
            <thead>
                <th scope="col">Название характеристики</th>
            </thead>
                @foreach($AllVacancyProperty as $key=>$value)
                    <tr>
                        <td>{{$value->title}}</td>
                        <td>
                        @if(auth()->guard('admin')->user()->can('vacancy_edit'))
                            <a href="{{ route('vacancyProperty.show',$value->titleId)}}"><button  class="btn btn-xs btn-primary" >Открыть</button></a>
                            <a href="{{ route('vacancyProperty.edit',$value->titleId)}}"><button  class="btn btn-xs btn-info" >Редактировать название</button></a>
                            <form action="{{ route('vacancyProperty.destroy', $value->titleId) }}" method="POST" onsubmit="return confirm('Вы уверены');" style="display: inline-block;">
	                            <input type="hidden" name="_method" value="DELETE">    
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
	                            <input type="submit" class="btn btn-xs btn-danger" value="Удалить">
                            </form>
                        @endif
                        </td>
                    </tr>
                @endforeach
</table>
<a href="{{ route('vacancy.index')}}"><button  class="btn btn-default" >Назад к списку вакансий</button></a>
@endsection