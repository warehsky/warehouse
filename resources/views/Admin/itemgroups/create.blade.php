@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Создать группу товаров
    </div>

    <div class="card-body">
        <form action="{{ route("itemgroups.store") }}" method="POST" enctype="multipart/form-data">
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
                <select id="parentId" name="parentId" class="form-control" >
                    <option value="0" @if($parent && $parent->id==0) selected @endif>корневой каталог</option>
                    @foreach($itemgroups as $g)
                        <option value="{{$g->id}}" @if($g->id==old('parentId') || ($parent && (int)$g->id==(int)$parent->id) ) selected @endif>[#{{$g->id}}]{{$g->title}}</option>
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
                <p>
                <input class="btn btn-danger" type="submit" value="Сохранить">
                </p>
            </div>
        </form>


    </div>
</div>
@endsection