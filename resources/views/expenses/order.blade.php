@extends('layouts.adminv')

@section('content')
<script src="{{ mix('js/app.js') }}" defer></script>
<!-- <div class="container"> -->
    <div id="goods">
        <expense 
            api_token="{{$api_token}}"
            :expense_id="{{$orderId}}"
            d_from="{{$dFrom}}"
            d_to="{{$dTo}}"
            status="{{$status}}"
            wareh_url="{{ env('WAREH_URL',false) }}">
        </expense>
    </div>    
        
<!-- </div> -->

@endsection
