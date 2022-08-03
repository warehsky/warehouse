@extends('layouts.admin')

@section('content')


<div class="card" style="margin-top:10px">
    <div class="card-header">
        Создание зоны доставки
    </div>

<form action="/admin/deliveryZone" method="post" style="margin-top:20px; margin-left:10px; margin-bottom:10px" enctype="multipart/form-data">
{{ csrf_field() }}
    <label>Стоимость доставки: </label><br />
	<input type="number" name="cost" size="50" /><br />

    <label>Минимальная сумма заказа от: </label><br />
	<input type="number" name="limit_min" size="50" /><br />

    <label>Бесплатная доставка от: </label><br />
	<input type="number" name="limit" size="50" /><br />

    <label>Бесплатная доставка для льготников от: </label><br />
	<input type="number" name="limit_lgot" size="50" /><br />

    <label>Цвет заливки: </label><br />
	<input type="text" name="fill" size="50" /><br />

    <label>Цвет обводки: </label><br />
	<input type="text" name="stroke" size="50" /><br />

    <label>Прозрачность: </label><br />
	<input type="number" step="any" name="fillOpacity" size="50" /><br />

    <label>Описание: </label><br />
	<input type="text" name="description" size="100" /><br />
    
    <label>Подпись для страницы описания: </label><br />
	<input type="text" name="balloon" size="100" /><br />
    
    
    <input type="submit" class="btn btn-success" value="Сохранить" style="margin-top:10px">
</form> 

</div>
<a style="margin-top:10px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку зон доставки</a>
@endsection