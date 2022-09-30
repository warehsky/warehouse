@extends('layouts.adminv')
@section('content')


        <script src="{{ mix('js/app.js') }}" defer></script>
<!-- <div class="container"> -->
    <div id="goods">
        <items-view 
                mode="spr">
        </items-view>
    </div>    
        
<!-- </div> -->

@endsection
@section('scripts')
@parent
@endsection