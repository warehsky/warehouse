@extends('layouts.adminv')

@section('content')
<script src="{{ mix('js/app.js') }}" defer></script>
<!-- <div class="container"> -->
    <div id="goods">
        <order 
            api_token="{{$api_token}}"
            :order_id="{{$orderId}}"
            d_from="{{$dFrom}}"
            d_to="{{$dTo}}"
            status="{{$status}}"
            :editable="{{$editable}}"
            wareh_url="{{ env('WAREH_URL',false) }}">
        </order>
    </div>    
        
<!-- </div> -->

@endsection
