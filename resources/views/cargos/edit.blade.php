@extends('layouts.admin')
@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<script src="{{ asset('js/items_mob.js') }}"></script>
<div class="card">
    <div class="card-header">
        Редактировать товар
    </div>
    <div class="card-body">
        <form 
        @if(isset($_GET['ReportBack']))
            action="/admin/items/{{$item->id}}?ReportBack={{$_GET['ReportBack'] ?? ''}}"
        @endif
        @if(isset($_GET['groupRoute']))
            action="/admin/items/{{$item->id}}?ids={{$_GET['id'] ?? ''}}&name={{$_GET['name'] ?? ''}}&parentId={{$_GET['parentId'] ?? ''}}&page={{$_GET['page'] ?? ''}}&groupRoute={{$_GET['groupRoute'] ?? ''}}&popularSort={{$_GET['popularSort'] ?? ''}}&id1c={{$_GET['id1c'] ?? ''}}&weightId={{$_GET['weightId'] ?? ''}}"
        @else
            action="/admin/items/{{$item->id}}?ids={{$_GET['id'] ?? ''}}&name={{$_GET['name'] ?? ''}}&longName={{$_GET['longName'] ?? ''}}&page={{$_GET['page'] ?? ''}}&date={{$_GET['date'] ?? ''}}&sorting={{$_GET['sorting'] ?? ''}}&id1c={{$_GET['id1c'] ?? ''}}&weightId={{$_GET['weightId'] ?? ''}}" 
        @endif
        
        
        method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">Короткое название*</label>
                <input type="text" id="title" name="title" maxlength="100" class="form-control" value="{{ old('title', isset($item) ? $item->title : '') }}" required onchange="let o=$('#longTitle'); if(o.val()=='') o.val(this.value)">
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
                <input type="text" id="longTitle" name="longTitle" maxlength="255" class="form-control" value="{{ old('longTitle', isset($item) ? $item->longTitle : '') }}" required>
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
                <textarea type="text" id="descr" name="descr" class="form-control" >{{ old('descr', isset($item) ? $item->descr : '') }}</textarea>
                @if($errors->has('descr'))
                    <em class="invalid-feedback">
                        {{ $errors->first('descr') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('mult') ? 'has-error' : '' }}">
                <label for="mult">Кратность (минимальное кол-во для заказа. 0-нет кратности)</label>
                <input type="text" id="mult" name="mult" class="form-control" value="{{old('mult', isset($item) ? $item->mult : 0) }}">
                @if($errors->has('mult'))
                    <em class="invalid-feedback">
                        {{ $errors->first('mult') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('prepayment') ? 'has-error' : '' }}">
                <p>Товар по предоплате (например торт, указать в часах)</p>
                <!-- <label class="switch">
                    <input type="checkbox" id="prepayment" name="prepayment" @if($item->prepayment??0) checked @endif >
                    <span class="slider"></span> 
                </label>-->
                <input type="number" id="prepayment" name="prepayment" value="{{old('mult', isset($item) ? $item->prepayment : 0) }}" >
                @if($errors->has('prepayment'))
                    <em class="invalid-feedback">
                        {{ $errors->first('prepayment') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>

            <div class="form-group {{ $errors->has('popular') ? 'has-error' : '' }}">
                <label for="popular" title="Чем больше рейтинг, тем выше товар будет отображаться">Рейтинг товара</label> 
                <label for="checkBoxPopular" title="Чем больше рейтинг, тем выше товар будет отображаться">(Автоматическое обновление</label>
                <input type="checkbox" name='autoPopular' id="checkBoxPopular" @if ($item->autoPopular) checked @endif>)
                <input type="number" id="popular" name="popular"  class="form-control" value="{{old('popular', isset($item) ? $item->popular : 0) }}" @if ($item->autoPopular) disabled @endif>
                @if($errors->has('popular'))
                    <em class="invalid-feedback">
                        {{ $errors->first('popular') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>

            <div class="form-group {{ $errors->has('imgSmall') ? 'has-error' : '' }}">
                <label for="imgSmall">Маленькая картинка</label>
                @if( file_exists(storage_path('app/img/items/small/'.$item->id.'.png')) )
                    <div>
                        <img src="/img/img/items/small/{{$item->id}}.png" width="124">
                    </div>
                @endif
                <input type="file" id="imgSmall" name="imgSmall" class="form-control" value="{{ old('imgSmall', isset($item) ? $item->imgSmall : '') }}" >
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
                @if( file_exists(storage_path('app/img/items/big/'.$item->id.'.png')) )
                    <div>
                        <img src="/img/img/items/big/{{$item->id}}.png" width="124">
                    </div>
                @endif
                <input type="file" id="imgBig" name="imgBig" class="form-control" value="{{ old('imgBig', isset($item) ? $item->imgBig : '') }}" >
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
                <select id="parentId" name="parentId" class="form-control" >
                    <option value="0">корневой каталог</option>
                    @foreach($itemgroups as $g)
                        <option value="{{$g->id}}" @if($g->id==$item->parentId) selected @endif>[#{{$g->id}}]{{$g->title}}</option>
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
                <div>Товар в мобильном агенте</div>
                <span>{{$item->item}}</span>
                <p id="item-mob" class="helper-block">
                    {{$item_mob->item}}
                </p>
                <label for="descr">Группа товара в мобильном агенте</label>
                <p id="group-mob" class="helper-block">
                    {{$group_mob}}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="Сохранить">
                <a style="margin-left:10px;" class="btn btn-default" 
                
                @if(isset($_GET['ReportBack']))
                    href="/admin/report?ReportBack={{$_GET['ReportBack']}}"
                @endif
                @if(isset($_GET['groupRoute']))
                    href="/admin/itemgroups?id={{$_GET['id']}}&name={{$_GET['name']}}&page={{$_GET['page']}}&parentId={{$_GET['parentId']}}&popularSort={{$_GET['popularSort'] ?? ''}}&id1c={{$_GET['id1c'] ?? ''}}&weightId={{$_GET['weightId'] ?? ''}}"
                @else
                    href="/admin/items?id={{$_GET['id'] ?? ''}}&name={{$_GET['name'] ?? ''}}&longName={{$_GET['longName'] ?? ''}}&page={{$_GET['page'] ?? ''}}&date={{$_GET['date'] ?? ''}}&sorting={{$_GET['sorting'] ?? ''}}&id1c={{$_GET['id1c'] ?? ''}}&weightId={{$_GET['weightId'] ?? ''}}"
                @endif>
                
                
                
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
<div id="frmItems" title="Товары мобильного агента">
    <div id="frmItemsContent" class="items-content">
    @include('Admin.items.items')
    </div>
    <div class="items-footer">
        
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
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
</style>
<script>
    
    $(document).on('change','#checkBoxPopular',function(){
        if ($(this).is(':checked'))
            $("#popular").attr('disabled','disabled');
        else
            $('#popular').removeAttr('disabled');

    });

</script>
@endsection