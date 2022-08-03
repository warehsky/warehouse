@extends('layouts.adminv')

@section('content')
<script src="{{ mix('js/app.js') }}" defer></script>
<div id="goods">
    <zones-editor-page api_token="{{$api_token}}"></zones-editor-page>
</div>
@endsection