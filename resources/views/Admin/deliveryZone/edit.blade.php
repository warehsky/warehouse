@extends('layouts.admin')

@section('content')

<div class="card" style="margin-top:10px">
    <div class="card-header">
        Редактирование зоны доставки
    </div>

<form action="{{ route('deliveryZone.update', $Zone['id']) }}" method="post" style="margin-top:10px; margin-left:10px;">
{{ csrf_field() }}
{{ method_field('PATCH') }}

    <label>Стоимость доставки:</label></br>
    <input type="text" name="cost" size="50" value="{{$Zone['cost']}}"/><br />   

    <label>Минимальная сумма заказа от: </label><br />
	<input type="number" name="limit_min" size="50" value="{{$Zone['limit_min']}}"/><br />

    <label>Бесплатная доставка от:</label></br>
    <input type="text" name="limit" size="50" value="{{$Zone['limit']}}"/><br />  

    <label>Бесплатная доставка для льготников от:</label></br>
    <input type="text" name="limit_lgot" size="50" value="{{$Zone['limit_lgot']}}"/><br />  

    <label>Цвет заливки: </label><br />
	<input type="text" name="fill" size="50" value="{{$Zone['fill']}}" /><br />

    <label>Цвет обводки: </label><br />
	<input type="text" name="stroke" size="50" value="{{$Zone['stroke']}}"/><br />

    <label>Прозрачность: </label><br />
	<input type="number" step="any" name="fillOpacity" size="50" value="{{$Zone['fillOpacity']}}"/><br />

    <label>Описание: </label><br />
	<input type="text" name="description" size="100" value="{{$Zone['description']}}"/><br />

    <label>Подпись для страницы описания: </label><br />
	<input type="text" name="balloon" size="100" value="{{$Zone['balloon']}}"/><br />
    <p>Деактивация зоны</p>
    <label class="switch">
        <input type="checkbox" id="deleted" name="deleted" @if($Zone['deleted']??0) checked @endif >
        <span class="slider"></span> 
    </label><br />


<input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px; margin-bottom:10px">
</form>

</div>
<a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку зон доставки</a>
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
</style>
@endsection