@extends('layouts.admin')

@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        {{session('error')}}
    </div>
@endif
<a style="margin-top:20px;" class="btn btn-default" href="{{ route('itemsKassa') }}">Назад</a>



<div class="card" style="margin-top:10px;">
    <div class="card-header">
        <p>Создать новый интервал:</p>
        <form action="{{ route('itemsKassaDateStore')}}" method="get">
            <input type="date" name="dateStart">
            <input type="date" name="dateEnd">
            <input type="submit" class="btn btn-xs btn-success" value="Сохранить" style="margin-top: 10px; margin-bottom:10px">
        </form>
    </div>
   
    <div class="card-body">
            <div class="row">
               
                
                <div class="col">
                    <p>Активные интервалы</p>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата начала</th>
                                <th>Дата окончания</th>    
                                <th></th>   
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeInterval as $el)
                                <tr>
                                    <td>{{$el->id}}</td>
                                    <form action="/admin/itemsKassaDate/update/{{$el->id}}" method="POST">
                                    {{ csrf_field() }}
                                        <td>
                                            <input style="display:none;" type="date" name='dateStart' id="Start_{{$el->id}}" value='{{$el->dateStart}}'>    
                                            <p id="p_{{$el->id}}">{{$el->dateStart}}</p>
                                        </td>
                                        <td>
                                            <input style="display:none;" type="date" name='dateEnd' id="End_{{$el->id}}" value='{{$el->dateEnd}}'>
                                            <p id="p2_{{$el->id}}">{{$el->dateEnd}}</p>
                                        </td>
                                        <td>
                                            <input type="submit" id="save_{{$el->id}}" style="display:none;" class="btn-xs btn btn-success" value="Сохранить">
                                    </form>
                                        <button id="change_{{$el->id}}" value="{{$el->id}}" class="change btn btn-xs btn-info" >Изменить</button>
                                        <form action="{{ route('itemsKassaDateDelete', $el->id) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="col">
                <p>Прошедшие интервалы</p>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата начала</th>
                                <th>Дата окончания</th>    
                                <th></th>   
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deactiveInterval as $el)
                                <tr>
                                    <td>{{$el->id}}</td>
                                    <form action="/admin/itemsKassaDate/update/{{$el->id}}" method="POST">
                                    {{ csrf_field() }}
                                        <td>
                                            <input style="display:none;" type="date" name='dateStart' id="Start_{{$el->id}}" value='{{$el->dateStart}}'>    
                                            <p id="p_{{$el->id}}">{{$el->dateStart}}</p>
                                        </td>
                                        <td>
                                            <input style="display:none;" type="date" name='dateEnd' id="End_{{$el->id}}" value='{{$el->dateEnd}}'>
                                            <p id="p2_{{$el->id}}">{{$el->dateEnd}}</p>
                                        </td>
                                        <td>
                                            <input type="submit" id="save_{{$el->id}}" style="display:none;" class="btn-xs btn btn-success" value="Сохранить">
                                    </form>
                                        <button id="change_{{$el->id}}" value="{{$el->id}}" class="change btn btn-xs btn-info" >Изменить</button>
                                        <form action="{{ route('itemsKassaDateDelete', $el->id) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>



@endsection
@section('scripts')
@parent
<script>

$(document).on('click', '.change', function(){
        var id = $(this).val();
        $("#Start_"+id).toggle();
        $("#End_"+id).toggle();
        $("#save_"+id).toggle();
        $("#change_"+id).toggle();
        $("#p_"+id).html('');
        $("#p2_"+id).html('');
    });

</script>
@endsection
