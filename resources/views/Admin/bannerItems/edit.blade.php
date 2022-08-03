@extends('layouts.admin')
@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<div class="card">
    <div class="card-header">
        Редактировать баннер
    </div>
    @if(isset($_GET['fileError']))
  <div class="bg-danger">{{$_GET['fileError']}}</div>
@endif
    <div class="card-body">
        <form action="{{ (isset($bannerItem) && is_object($bannerItem))?route("bannerItems.update", [$bannerItem->id]):route("bannerItems.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($bannerItem) && is_object($bannerItem))
                @method('PUT')
            @else
                @method('POST')
                <input type="hidden" name="bannerId" value="{{$bannerId}}">
            @endif
            <div class="form-group {{ $errors->has('link') ? 'has-error' : '' }}">
                <label for="link">Ссылка*</label>
                <input id="link" name="link" value="{{isset($bannerItem) ? $bannerItem->link : ''}}" required>
                @if($errors->has('link'))
                    <em class="invalid-feedback">
                        {{ $errors->first('link') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('link_mobile') ? 'has-error' : '' }}">
                <label for="link_mobile">Ссылка мобильная*</label>
                <input id="link_mobile" name="link_mobile" value="{{isset($bannerItem) ? $bannerItem->link_mobile : ''}}" required>
                @if($errors->has('link_mobile'))
                    <em class="invalid-feedback">
                        {{ $errors->first('link_mobile') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            
            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">Баннер*</label>

                
                    <div>
                        <img src="{{isset($bannerItem) ? $bannerItem->image:''}}" height="44">
                    </div>
                
                <input type="file" id="image" name="image" class="form-control" value="{{ old('image', isset($bannerItem) ? $bannerItem->image : '') }}" >
                @if($errors->has('image'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label for="image">Баннер мобильный *</label>

                
                    <div>
                        <img src="{{isset($bannerItem) ? $bannerItem->image_mobile : ''}}" height="44">
                    </div>
                
                <input type="file" id="image_mobile" name="image_mobile" class="form-control" value="{{ old('image_mobile', isset($bannerItem) ? $bannerItem->image_mobile : '') }}" >
                @if($errors->has('image_mobile'))
                    <em class="invalid-feedback">
                        {{ $errors->first('image_mobile') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>

            <div class="form-group {{ $errors->has('alt') ? 'has-error' : '' }}">
                <label for="alt">ALT*</label>
                <input id="alt" name="alt" value="{{isset($bannerItem) ? $bannerItem->alt : ''}}" required>
                @if($errors->has('alt'))
                    <em class="invalid-feedback">
                        {{ $errors->first('alt') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('alt_mobile') ? 'has-error' : '' }}">
                <label for="alt_mobile">ALT мобильный*</label>
                <input id="alt_mobile" name="alt_mobile" value="{{isset($bannerItem) ? $bannerItem->alt_mobile : ''}}" required>
                @if($errors->has('alt_mobile'))
                    <em class="invalid-feedback">
                        {{ $errors->first('alt_mobile') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('sort') ? 'has-error' : '' }}">
                <label for="sort">Порядок*</label>
                <input id="sort" name="sort" value="{{isset($bannerItem) ? $bannerItem->sort : ''}}" required>
                @if($errors->has('sort'))
                    <em class="invalid-feedback">
                        {{ $errors->first('sort') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('public') ? 'has-error' : '' }}">
                <label for="title">Публикация*</label>
                <select name="public" id="public" class="form-control"  required>
                    @foreach($publics as $key=>$p)
                        <option value="{{ $key }}" {{ (  (isset($bannerItem) && $bannerItem->public==$key)) ? 'selected' : '' }}>{{ $p }}</option>
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