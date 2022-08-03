


@extends('layouts.admin')
@section('content')


<div class="card" style="margin-top:10px">
    <div class="card-header">
        Меню редактирования
    </div>


    <form action="{{ route('phoneNumber.update', $EditPhone['phone']) }}" method="post" style="margin-top:10px; margin-left:10px;">
{{ csrf_field() }}
{{ method_field('PATCH') }}

<label>Номер телефона:</label></br>
    <input type="text" name="phone" size="50" value="{{$EditPhone['phone']}}"/><br />
<label>Имя клиента:</label></br>
    <input type="text" name="name" size="50" value="{{$EditPhone['name']}}"/><br />

<label>Рассылка: </label>
    <input type="checkbox" name="unsubscribe" @if($EditPhone['unsubscribe']) checked @endif /><br />

<input type="submit" class="btn btn-success" value="Сохранить" style="margin-top: 10px; margin-bottom: 10px">


</form>

</div>



<a style="margin-top:10px;" class="btn btn-default" href="{{ url()->previous() }}">Назад к списку</a>


@endsection