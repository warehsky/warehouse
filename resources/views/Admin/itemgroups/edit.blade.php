@extends('layouts.admin')
@section('content')

<param id='percentQuantityParam' value={{$countPercent}}>
<param id='countItemsBestSeller' value={{$countItemsBestSeller}}>

@if(session('error'))
    <div class="alert alert-danger">
        {{session('error')}}
    </div>
@endif

<div class="card">
    <div class="card-header">
        Редактировать группу товара
    </div>

    <div class="card-body">
        <form action="{{ route("itemgroups.update", [$group->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label for="title">Краткое название*</label>
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
            <div class="form-group {{ $errors->has('warehouses') ? 'has-error' : '' }}">
                <label for="warehouses">Склады*
                    <span class="btn btn-info btn-xs select-all">выбрать все</span>
                    <span class="btn btn-info btn-xs deselect-all">отменить все</span></label>
                <select name="warehouses[]" id="warehouses" class="form-control select2" multiple="multiple" required>
                    <option value="0" {{ in_array(0, $lwarehouses) ? 'selected' : '' }}>Склад партнеров</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ ( in_array($warehouse->id, old('warehouses', [])) || (isset($group) && in_array($warehouse->id, $lwarehouses))) ? 'selected' : '' }}>{{ $warehouse->warehouse }}</option>
                    @endforeach
                </select>
                @if($errors->has('warehouses'))
                    <em class="invalid-feedback">
                        {{ $errors->first('warehouses') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.role.fields.permissions_helper') }}
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
            <div class="form-group {{ $errors->has('sort') ? 'has-error' : '' }}">
                <label for="sort">Порядковый номер в подгруппе</label>
                <input type="text" id="sort" name="sort" class="form-control" value="{{ old('sort', isset($group) ? $group->sort : '') }}" >
                @if($errors->has('sort'))
                    <em class="invalid-feedback">
                        {{ $errors->first('sort') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('waitshow') ? 'has-error' : '' }}">
                <label for="waitshow">Допустимое кол-во товаров в ожидании в подгруппе</label>
                <input type="text" id="waitshow" name="waitshow" class="form-control" value="{{ old('waitshow', isset($group) ? $group->waitshow : '') }}" >
                @if($errors->has('waitshow'))
                    <em class="invalid-feedback">
                        {{ $errors->first('waitshow') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            @if($group->parentId == 0)
            <div class="form-group {{ $errors->has('percentQuantity') ? 'has-error' : '' }}">
                <label id='labelBestSeller' for="percentQuantity">Процент товара в хитах продаж (Свободно: {{$countPercent-$group->percentQuantity}}%; Будет показано: {{$countItemsBestSeller*$group->percentQuantity/100}} товаров)</label>
                <input type="number" id="percentQuantity" name="percentQuantity" class="inputpercent" value="{{ old('percentQuantity', isset($group) ? $group->percentQuantity : '') }}" >
                @if($errors->has('percentQuantity'))
                    <em class="invalid-feedback">
                        {{ $errors->first('percentQuantity') }}
                    </em>
                @endif
                <p class="helper-block">
                
                </p>
            </div>
            @endif
            <div class="form-group {{ $errors->has('imgIcon') ? 'has-error' : '' }}">
                <label for="imgIcon">Иконка</label>

                @if( file_exists(storage_path('app/img/catalog/icons/'.$group->id.'.svg')) )
                    <div>
                        <img src="/img/img/catalog/icons/{{$group->id}}.svg" width="24">
                    </div>
                @endif
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
                <label for="imgSmall">Маленькая картинка</label>
                @if( file_exists(storage_path('app/img/catalog/small/'.$group->id.'.png')) )
                    <div>
                        <img src="/img/img/catalog/small/{{$group->id}}.png" width="124">
                    </div>
                @endif
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
                @if( file_exists(storage_path('app/img/catalog/big/'.$group->id.'.png')) )
                    <div>
                        <img src="/img/img/catalog/big/{{$group->id}}.png" width="124">
                    </div>
                @endif
                <input type="file" id="imgBig" name="imgBig" class="form-control" value="{{ old('imgBig', isset($group) ? $group->imgBig : '') }}" >
                @if($errors->has('imgBig'))
                    <em class="invalid-feedback">
                        {{ $errors->first('imgBig') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('imgShadow') ? 'has-error' : '' }}">
                <label for="imgShadow">Картинка по умолчанию (для товаров подгруппы)</label>
                @if( file_exists(storage_path('app/img/catalog/shadow/'.$group->id.'.png')) )
                    <div>
                        <img src="/img/img/catalog/shadow/{{$group->id}}.png" width="124">
                    </div>
                @endif
                <input type="file" id="imgShadow" name="imgShadow" class="form-control" value="{{ old('imgShadow', isset($group) ? $group->imgShadow : '') }}" >
                @if($errors->has('imgShadow'))
                    <em class="invalid-feedback">
                        {{ $errors->first('imgShadow') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('parentId') ? 'has-error' : '' }}">
                <label for="parentId">Родитель</label>
                <select id="parentId" name="parentId" class="form-control" >
                    <option value="0" @if($group->parentId==0) selected @endif>корневой каталог</option>
                    @foreach($itemgroups as $g)
                        <option value="{{$g->id}}" @if((int)$g->id==(int)$group->parentId) selected @endif>[#{{$g->id}}]{{$g->title}}</option>
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
.inputpercent{
    display: block;
    width: 100%;
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.inputpercent:focus {
    color:#495057;
    background-color:#fff;
    outline:0;
}
.error {
    border: 2px solid red;
}
</style>
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
	<script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script>
		jQuery(document).ready(function($) {
			$('.select-all').click(function () {
				let $select2 = $(this).parent().siblings('.select2')
				$select2.find('option').prop('selected', 'selected')
				$select2.trigger('change');
			})
			$('.deselect-all').click(function () {
				let $select2 = $(this).parent().siblings('.select2')
				$select2.find('option').prop('selected', '')
				$select2.trigger('change')
			})
			$('.select2').select2();
		});

        $(document).on('keyup','#percentQuantity',function(){
            $('#labelBestSeller').html();
            var countPer = $('#percentQuantityParam').val()-$(this).val();
            var countItem = Math.round($('#countItemsBestSeller').val() * $(this).val() / 100);
            $('#labelBestSeller').html('Процент товара в хитах продаж (Свободно: '+countPer+'%; Будет показано: '+countItem+' товара(-ов))');
            if (Number($(this).val())>Number($('#percentQuantityParam').val()))
                $(this).addClass('error');
            else 
                $(this).removeClass('error');
        });
	</script>
@endsection