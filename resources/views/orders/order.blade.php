@extends('layouts.adminv')

@section('content')
<script src="{{ mix('js/app.js') }}" defer></script>
<!-- <div class="container"> -->
    <div id="goods">
        <orders 
            :error-rate="{{$errorRate}}"
            api_token="{{$api_token}}"
            phone="{{$phone}}"
            d_from="{{$dFrom}}"
            d_to="{{$dTo}}"
            status="{{$status}}"
            shop_url="{{ env('SHOP_URL',false) }}"
            :show-pickup="Boolean({{ env('SHOW_PICKUP') }})">
        </orders>
    </div>    
        
<!-- </div> -->

@endsection
