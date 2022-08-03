@extends('layouts.admin')

@section('content')

@if(auth()->guard('admin')->user()->can('deliveryZone_edit'))
<a href="{{ route('deliveryZone.edit' , $Zone['id'])}}"><button  class="btn btn-info" >Редактировать</button></a>
<form action="{{ route('deliveryZone.destroy', $Zone['id']) }}" method="POST" onsubmit="return confirm('Вы уверены');" style="display: inline-block;">
	<input type="hidden" name="_method" value="DELETE">
	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	<input type="submit" class="btn btn-danger" value="Удалить">
</form>
@endif



<table class="table table-bordered border border-primary" style="margin-top: 10px; width: 30%;">
    <tr>
        <td>ID</td>
        <td>{{$Zone['id']}}</td>
    </tr>
    <tr> 
        <td>Стоимость доставки</td>
        <td>{{$Zone['cost']}}</td>
    </tr>
    <tr>    
        <td>Бесплатная доставка от</td>
        <td>{{$Zone['limit']}}</td>
    </tr> 
    <tr>    
        <td>Бесплатная доставка для льготников от</td>
        <td>{{$Zone['limit_lgot']}}</td>
    </tr>
    <tr>    
        <td>Описание</td>
        <td>{{$Zone['description']}}</td>
    </tr>
    <tr>    
        <td>Подпись для страницы описания</td>
        <td>{{$Zone['balloon']}}</td>
    </tr>
    <tr>    
        <td>Цвет заливки</td>
        <td>{{$Zone['fill']}}</td>
    </tr>
    <tr>    
        <td>Цвет обводки</td>
        <td>{{$Zone['stroke']}}</td>
    </tr>
    <tr>    
        <td>Прозрачность</td>
        <td>{{$Zone['fillOpacity']}}</td>
    </tr>
    <tr>    
        <td>Дата создания</td>
        <td>{{$Zone['created_at']}}</td>
    </tr>
    <tr>    
        <td>Дата последнего редактирования</td>
        <td>{{$Zone['updated_at']}}</td>
    </tr>
   
</table>
<a href="{{ route('deliveryZone.index')}}"><button  class="btn btn-default" >Назад к списку зон доставки</button></a>

@endsection