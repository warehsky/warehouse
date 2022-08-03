@extends('layouts.admin')
@section('content')

@if($errors->any())
    <div class="alert aletr-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{$error}}</li>
            @endforeach
        </ul>
    <div>
@endif


<a href="{{ route('email')}}"><button class="btn btn-outline-primary" >Вернутся к списку</button></a>
<div class="alert alert-secondary" style="margin-top:10px">
    <div class="container">
        <div class="row">
            <div class="col">
                <h4>
                    {{$SelectedEmail->name}} 
                    &lt;{{$SelectedEmail->email}}&gt;
                    ({{$SelectedEmail->phone}})
                </h4>
            </div>
            <div class="col">
                <p align="right">
                    @if ($SelectedEmail->status==0)
                        (Новое)
                    @else
                        (Отвечено)
                    @endif
                </p>
            </div>
    
        </div>
    </div>
        <hr/>
            <p>{{$SelectedEmail->comment}}</p>
        <hr/>
            <p align="right">{{$SelectedEmail->created_at}}</p>
            @if(auth()->guard('admin')->user()->can('email_send_message'))
                <a href="{{ route('lookemail' , $SelectedEmail->id)}}"><button class="btn btn-info" >Изменить статус</button></a>
            @endif
</div>

    <hr/>

<h3>Ответы</h3>
@foreach($AllReturn as $key)
    <div class="alert alert-secondary"> 
       
        <p>Тема:</p>
        <p>{{$key->title}}</p>
        <hr/>
        <p>Содержание:</p>
        <p>{{$key->text}}</p>
        <hr/>
        <p>Дата ответа: {{$key->created_at}}</p>
        
    </div>
    @endforeach
    @if(auth()->guard('admin')->user()->can('email_send_message'))
    <hr/>
<div>
    <form  method="post" >
        @csrf
        <label>Тема email: </label><br />
		<input type="text" name="subject" size="50" value='{{$SelectedEmail->subject}}'/><br />
        <label>Текст email: </label><br />
        <textarea name="text" cols="120" rows="10"></textarea><br />
		<a href="{{ route('sendemail' , $SelectedEmail->id)}}"><button class="btn btn-primary">Отправить</button></a>
	</form>
</div>
@endif
@endsection