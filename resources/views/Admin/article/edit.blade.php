@extends('layouts.adminv')
@section('content')
<script src="{{ mix('js/app.js') }}" defer></script>
  <form method="POST" name="articleEditForm" style="height:100%;" action="@if($article) /admin/articleupdate/{{$article->id}} @else /admin/articlestore @endif" enctype="multipart/form-data">
  @csrf
  <label>ID</label> <span>{{$article?$article->id:''}}</span>
  <label>Дата</label> <span>{{$article?$article->created_at:''}}</span>
  <div>
    <label>Название</label><input name="title" value="{{$article?$article->title:''}}" require>
  </div>
  <div>
    <label>Анотация</label><textarea name="text" require>{{$article?$article->text:''}}</textarea>
  </div>
  <div class="form-group {{ $errors->has('preview') ? 'has-error' : '' }}">
    <label for="preview">Картинка (для анонса на главной)</label>
    @if( $article && file_exists(public_path('img/img/Articles/'.$article->id.'/preview.png')) )
        <div>
            <img src="/img/img/Articles/{{$article->id}}/preview.png" width="124">
        </div>
    @endif
    <input type="file" id="preview" name="preview" class="form-control" value="{{ old('preview') }}" >
    @if($errors->has('preview'))
        <em class="invalid-feedback">
            {{ $errors->first('preview') }}
        </em>
    @endif
    <p class="helper-block">
        
    </p>
  </div>
  <div>
    <label>Публикация</label>
    <select name="public" require>
      <option value="0" {{$article?($article->public==0?'selected':''):''}}>не опубликован</option>
      <option value="1" {{$article?($article->public==1?'selected':''):''}}>опубликован</option>
    </select>
  </div>
  @if($article)
  <div id="goods">
    <article-editor api_token="{{$api_token}}" 
      :articleid="{{$article?$article->id:0}}"
      :design="{{($article&&$article->json)?$article->json:'null'}}"
      form="articleEditForm"
      jsoninput="json"
      htmlinput="html">
    </article-editor>
  </div>
  @else
  <div><input type="submit" value="Подтвердить сохранение"></div>
  @endif
  
  </form>
  
  
  



@endsection
@section('scripts')
@parent


@endsection
