@extends('layouts.adminv')

@section('content')
<script src="{{ mix('js/app.js') }}" defer></script>
<h2>Сканирование пакетов</h2>
<!-- <div class="container"> -->
    <div id="goods">
        <warehousepacks 
        api_token="{{$api_token}}"
        d_from="{{$dFrom}}"
        d_to="{{$dTo}}"
        phone="{{$phone}}"
        shop_url="{{ env('SHOP_URL') }}"
        :status="{{$status}}"
        :error-rate="{{$errorRate}}"
        :packs="{{$packs}}"
        :ismobile="{{(int)$agent->isMobile()}}">
        </warehousepacks>
    </div>    
        
<!-- </div> -->

@endsection
