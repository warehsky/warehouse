@extends('layouts.admin')

@section('content')

<h3>Меню редактирования значений характеристики</h3>

<div class="container-sm">
        <div class="row">
        @if(auth()->guard('admin')->user()->can('vacancy_edit'))
            <div class="col-lg-4">
                <form action="{{route('storeProperty', $id)}}" method="post" style="margin-bottom: 10px">
                    {{ csrf_field() }}
	                <input type="submit" class="btn btn-success" value="Создать"/>
                    <input type="text" name="vacancyProperty" size="50"/>
                </form> 
            </div>
        @endif
        </div>
</div>



<table class="table table-striped table-bordered table-sm">
    <thead>
        <th scope="col">Код</th>
        <th scope="col">Название</th>
    </thead>
    
        @foreach($OneProperty as $key)
        <tr>
        <td>{{$key->propertiesId}}</td>
        <td>{{$key->description}}</td>
        <td> 
        @if(auth()->guard('admin')->user()->can('vacancy_edit'))
            <a href="{{ route('editProperty',['id' => $id, 'editId' => $key->propertiesId])}}"><button  class="btn btn-xs btn-info" >Редактировать название</button></a>
            <form action="{{ route('destroyProperty', $key->propertiesId) }}" method="POST" onsubmit="return confirm('Вы уверены');" style="display: inline-block;">
	            <input type="hidden" name="_method" value="DELETE">
	            <input type="hidden" name="_token" value="{{ csrf_token() }}">
	            <input type="submit" class="btn btn-xs btn-danger" value="Удалить">
            </form>
            @endif
        </td>
        </tr> 
        @endforeach
    

</table>
<a href="{{ route('vacancyProperty.index')}}"><button  class="btn btn-default" >Назад к списку характеристик</button></a>



@endsection