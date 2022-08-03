@extends('layouts.admin')

@section('content')
<h3>Окно редактирования страницы акций</h3>
@if(auth()->guard('admin')->user()->can('pageStock_edit'))
<a href="{{ route('pageStock.create')}}"><button class="btn btn-success">Создать акцию</button></a>
@endif


<h5 style="margin-top:20px">Опубликованные акции</h5>
<table class="table table-striped table-bordered table-sm" style="width:95%; margin-top:10px">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Название</th>
                <th scope="col">Описание</th>
                <th scope="col">Изображение</th>
                <th scope="col">Дата начала</th>
                <th scope="col">Дата окончания</th>
                <th scope="col">Дата создания</th>
                <th scope="col">Дата публикации</th>
            </tr>
            </thead>
            @foreach($ActiveStock as $el)                
                <tr> 
                    <td>{{$el->id}}</td> 
                    <td>{{$el->title}}</td>
                    <td>{{$el->description}}</td>
                    <td><img src="{{$el->image}}" height="30"></td>
                    <td>{{$el->timeStart}}</td>
                    <td>{{$el->timeEnd}}</td>
                    <td>{{$el->created_at}}</td>
                    <td>{{$el->updated_at}}</td>
                    <td>
                    @if(auth()->guard('admin')->user()->can('pageStock_publication'))
                        <a href="{{ route('changeStatus' , $el->id)}}"><button  class="btn btn-info btn-xs" >Убрать с публикации</button></a>
                    @endif
                        @if(auth()->guard('admin')->user()->can('pageStock_edit'))
                        <a href="{{ route('pageStock.edit' , $el->id)}}"><button class="btn btn-primary btn-xs">Редактировать</button></a>    
                        <form action="{{ route('pageStock.destroy', $el->id) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
	                        <input type="hidden" name="_method" value="DELETE">
	                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
	                        <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                        </form>
                        @endif
                    </td>

                </tr>
            @endforeach
</table>



<h5 style="margin-top:30px">Не опубликованные акции</h5>
<table class="table table-striped table-bordered table-sm" style="width:95%; margin-top:10px">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Название</th>
                <th scope="col">Описание</th>
                <th scope="col">Изображение</th>
                <th scope="col">Дата начала</th>
                <th scope="col">Дата окончания</th>
                <th scope="col">Дата создания</th>
                <th scope="col">Дата снятия с публикации</th>
            </tr>
            </thead>
            @foreach($DeactiveStock as $el)                
                <tr> 
                    <td>{{$el->id}}</td> 
                    <td>{{$el->title}}</td>
                    <td>{{$el->description}}</td>
                    <td><img src="{{$el->image}}" height="30"></td>
                    <td>{{$el->timeStart}}</td>
                    <td>{{$el->timeEnd}}</td>
                    <td>{{$el->created_at}}</td>
                    <td>{{$el->updated_at}}</td>
                    <td>
                    @if(auth()->guard('admin')->user()->can('pageStock_publication'))
                        <a href="{{ route('changeStatus' , $el->id)}}"><button  class="btn btn-info btn-xs" >Опубликовать</button></a>
                    @endif
                        @if(auth()->guard('admin')->user()->can('pageStock_edit'))
                        <a href="{{ route('pageStock.edit' , $el->id)}}"><button class="btn btn-primary btn-xs">Редактировать</button></a>    
                        <form action="{{ route('pageStock.destroy', $el->id) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
	                        <input type="hidden" name="_method" value="DELETE">
	                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
	                        <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                        </form>
                        @endif
                    </td>

                </tr>
            @endforeach
</table>






@endsection