@extends('layouts.admin')
@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<script src="{{ asset('js/items_mob.js') }}"></script>

<div class="card">
    <div class="card-header">
        Создать товар
    </div>

    <div class="card-body">
        <form action="/admin/items?ids={{$_GET['ids']}}&name={{$_GET['name']}}&longName={{$_GET['longName']}}&page={{$_GET['page']}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">Короткое название*</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', isset($group) ? $group->title : '') }}" required onchange="let o=$('#longTitle'); if(o.val()=='') o.val(this.value)">
                @if($errors->has('title'))
                    <em class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('longTitle') ? 'has-error' : '' }}">
                <label for="longTitle">Полное название*</label>
                <input type="text" id="longTitle" name="longTitle" class="form-control" value="{{ old('longTitle', isset($group) ? $group->longTitle : '') }}" required>
                @if($errors->has('longTitle'))
                    <em class="invalid-feedback">
                        {{ $errors->first('longTitle') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('descr') ? 'has-error' : '' }}">
                <label for="descr">Описание</label>
                <textarea type="text" id="descr" name="descr" class="form-control" value="{{ old('descr', isset($group) ? $group->descer : '') }}" ></textarea>
                @if($errors->has('descr'))
                    <em class="invalid-feedback">
                        {{ $errors->first('descr') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('imgIcon') ? 'has-error' : '' }}">
                <label for="imgIcon">Иконка</label>
                <input type="file" id="imgIcon" name="imgIcon" class="form-control" value="{{ old('imgIcon', isset($group) ? $group->imgIcon : '') }}" >
                @if($errors->has('imgIcon'))
                    <em class="invalid-feedback">
                        {{ $errors->first('imgIcon') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('imgSmall') ? 'has-error' : '' }}">
                <label for="imgSmall">Малая картинка</label>
                <input type="file" id="imgSmall" name="imgSmall" class="form-control" value="{{ old('imgSmall', isset($group) ? $group->imgSmall : '') }}" >
                @if($errors->has('imgSmall'))
                    <em class="invalid-feedback">
                        {{ $errors->first('imgSmall') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('imgBig') ? 'has-error' : '' }}">
                <label for="imgBig">Большая картинка</label>
                <input type="file" id="imgBig" name="imgBig" class="form-control" value="{{ old('imgBig', isset($group) ? $group->imgBig : '') }}" >
                @if($errors->has('imgBig'))
                    <em class="invalid-feedback">
                        {{ $errors->first('imgBig') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('parentId') ? 'has-error' : '' }}">
                <label for="parentId">Родитель</label>
                <select id="parentId" name="parentId" class="form-control" require>
                    <option value="">корневой каталог</option>
                    @foreach($itemgroups as $g)
                        <option value="{{$g->id}}" @if($g->id==old('parentId') || (int)$g->id==(int)$parent) selected @endif>[#{{$g->id}}]{{$g->title}}</option>
                    @endforeach
                </select>
                @if($errors->has('parentId'))
                    <em class="invalid-feedback">
                        {{ $errors->first('parentId') }}
                    </em>
                @endif
                <p class="helper-block">
                    родительская подгруппа
                </p>
            </div>
            <div class="form-group ">
                <a href="#" onclick="frmItems.dialog( 'open' );">Товар в мобильном агенте</a>
                <input type="hidden" name="id" id="id" value="">
                <p id="item-mob" class="helper-block">
                    
                </p>
                <label for="descr">Группа товара в мобильном агенте</label>
                <p id="group-mob" class="helper-block">
                    
                </p>
            </div>
            <div>
                <p>
                <input class="btn btn-danger" type="submit" value="Сохранить">
                </p>
            </div>
        </form>


    </div>
</div>

<div id="frmItems" title="Товары мобильного агента">
    <div id="frmItemsContent" class="items-content">
    @include('Admin.items.items')
    </div>
    <div class="items-footer">
        
    </div>
</div>
<style>
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