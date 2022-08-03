@extends('layouts.adminv')

@section('content')
<script src="{{ mix('js/app.js') }}" defer></script>
<!-- <div class="container"> -->
<h1>Чат</h1>
    <div id="goods">
        <chat username="{{$moderatorname}}" :canwrite="{{(int)$canWriting}}" api_token="{{$api_token}}"></chat>
    </div>    
        
<!-- </div> -->

@endsection
