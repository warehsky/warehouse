@extends('layouts.admin')

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{session('success')}}
    </div>
@endif
<h3>Сообщения формы обратной связи окна "Контакты"</h3>
{{$AllEmail->links()}}
<table class="table table-striped table-bordered table-sm">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Имя</th>
                <th scope="col">Email</th>
                <th scope="col">Телефон</th>
                <th scope="col">Сообщение</th> 
                <th scope="col">Дата получения</th>
                <th scope="col">Статус</th>
            </tr>
            </thead>
            @foreach($AllEmail as $el)                
                <tr>
                    <td>{{$el->id}}</td> 
                    <td>{{$el->name}}</td>
                    <td>{{$el->email}}</td>
                    <td>{{$el->phone}}</td>
                    <td>{{@str_limit($el->comment, $limit = 70, $end = '...')}}</td>
                    <td>{{$el->created_at}}</td>
                    <td @if($el->status==0) style="color:red;"  @endif  >@if ($el->status==0)
                            (Новое)
                        @else
                            (Отвечено)
                        @endif </td>
                    <td>
                        <a href="{{ route('emailshow' , $el->id)}}"><button class="btn btn-primary">Открыть</button></a>    
                        <a href="{{ route('lookemail' , $el->id)}}"><button  class="btn btn-info" >Изменить статус</button></a>
                    </td>

                </tr>
            @endforeach
</table>


@endsection
