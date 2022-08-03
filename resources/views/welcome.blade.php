@extends('layouts.app')
<link href="{{ asset('css/css/reset.css') }}" rel="stylesheet">
@if($agent->isMobile())
<link href="{{ asset('css/media.css') }}" rel="stylesheet">
@endif
@section('content')

    <div class="container">
     <div class="main__content">
        <h2 class="main__content-title"> МТ Доставка</h2>
        <div class="main__content-subtitle">Делайте покупки вместе с нами</div>
     </div>
    </div>

@endsection
