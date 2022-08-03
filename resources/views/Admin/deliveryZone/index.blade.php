@extends('layouts.admin')

@section('content')
<h3>Окно редактирования зон доставки</h3>
@if(auth()->guard('admin')->user()->can('deliveryZone_edit'))
<a href="{{ route('deliveryZone.create')}}"><button  class="btn btn-success" >Добавить зону доставки</button></a>
@endif
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Список зон доставки
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            ID
                        </th>
                        <th>
                            Цена доставки
                        </th>
                        <th>
                            Минимальная сумма заказа 
                        </th>
                        <th>
                            Сумма для бесп. доставки
                        </th>
                        <th>
                            Сумма для бесп. доставки для льготников
                        </th>
                        <th>
                            Описание
                        </th>
                        <th>
                            Подпись
                        </th>
                        <th>
                            Дата создания
                        </th>
                        <th>
                            Дата последнего редактирования
                        </th>
                        <th>
                            Статус
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($Zones as $el)                
                <tr
                @if($el->deleted)
                 style="background:#FFF3DC;"
                @endif
                >
                    <td>{{$el->id}}</td> 
                    <td>{{$el->cost}}</td>
                    <td>{{$el->limit_min}}</td>
                    <td>{{$el->limit}}</td>
                    <td>{{$el->limit_lgot}}</td>
                    <td>{{$el->description}}</td>
                    <td>{{$el->balloon}}</td>
                    <td>{{$el->created_at}}</td>
                    <td>{{$el->updated_at}}</td>
                    <td>
                        @if($el->deleted)
                            Деактивировано
                        @else
                            Активно
                        @endif
                    </td>
                    <td>
                    <a href = "{{route('deliveryZone.show', $el->id)}}"><button  class="btn btn-xs btn-primary" >Открыть</button></a>
                    @if(auth()->guard('admin')->user()->can('deliveryZone_edit'))
                        <a href = "{{route('deliveryZone.edit', $el->id)}}"><button  class="btn btn-xs btn-info" >Редактировать</button></a>
                        <form action="{{ route('deliveryZone.destroy', $el->id) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
	                        <input type="hidden" name="_method" value="DELETE">
	                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
	                        <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                        </form>
                    @endif
                    </td>

                </tr>
                @endforeach
                </tbody>
            </table>
        </div>


    </div>
</div>
@endsection