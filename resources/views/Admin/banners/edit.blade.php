@extends('layouts.admin')
@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<div class="card">
    <div class="card-header">
        Редактировать группу баннеров
    </div>

    <div class="card-body">
        <form action="{{ (isset($banner) && is_object($banner))?route("banners.update", [$banner->id]):route("banners.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($banner) && is_object($banner))
                @method('PUT')
            @else
                @method('POST')
            @endif
            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="title">Тип*</label>
                <select name="type" id="type" class="form-control"  required>
                    @foreach($types as $key=>$type)
                        <option value="{{ $key }}" {{ (  (isset($banner) && $banner->type==$key)) ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('autoplay') ? 'has-error' : '' }}">
                <label for="title">Смена баннера*</label>
                <select name="autoplay" id="autoplay" class="form-control"  required>
                    @foreach($autoplays as $key=>$a)
                        <option value="{{ $key }}" {{ (  (isset($banner) && $banner->autoplay==$key)) ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
                @if($errors->has('autoplay'))
                    <em class="invalid-feedback">
                        {{ $errors->first('autoplay') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('public') ? 'has-error' : '' }}">
                <label for="title">Публикация*</label>
                <select name="public" id="public" class="form-control"  required>
                    @foreach($publics as $key=>$p)
                        <option value="{{ $key }}" {{ (  (isset($banner) && $banner->public==$key)) ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                @if($errors->has('public'))
                    <em class="invalid-feedback">
                        {{ $errors->first('public') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            
            <div>
                <input class="btn btn-danger" type="submit" value="Сохранить">
                <a  class="btn btn-default" href="{{ url()->previous() }}">
                к списку
                </a>
            </div>
        </form>


    </div>
</div>
<div id="frmWeb" title="Web группы">
    <div id="frmWebContent" class="addweb-content"></div>
    <div class="addweb-footer">
        <div><span>выбор:</span><span class="addweb-choosen"></span>
                <input id="addweb-choosen-id" type="hidden" value="">
                <input id="addweb-choosen-itemId" type="hidden" value="">
        </div>
    </div>
</div>

<style>
.tree span:hover {
   font-weight: bold;
 }

 .tree span {
   cursor: pointer;
 }
 .minus{
   background-image: url(/img/minus.png) no-repeat;
   font-size: 26px;
   color: blue;
   cursor: pointer;
}
.plus{ 
   background-image: url(/img/plus.png) no-repeat;
   font-size: 26px;
   color: green;
   cursor: pointer;
}
.gr-name{
   cursor: pointer;
}
.gr-name-active{
   color: blue;
}
</style>

@endsection