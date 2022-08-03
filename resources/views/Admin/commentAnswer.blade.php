@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Редактировать/добавить ответ на отзыв о товаре
    </div>
    <div class="card-header">
        [{{$comment->id}}]{{$comment->comment}}
    </div>
    <div class="card-body">
        <form action="{{ is_object($answer)?route("commentAnswer.update", [$answer->id]):route("commentAnswer.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(is_object($answer))
                @method('PUT')
            @else
                @method('POST')
            @endif
            <input type="hidden" name="commentId" value="{{$comment->id}}">
            <div class="form-group {{ $errors->has('answer') ? 'has-error' : '' }}">
                <label for="title">Ответ*</label>
                <input type="text" id="answer" name="answer" class="form-control" value="{{ old('answer', is_object($answer) ? $answer->answer : '') }}" required >
                @if($errors->has('answer'))
                    <em class="invalid-feedback">
                        {{ $errors->first('answer') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div class="form-group {{ $errors->has('answer') ? 'has-error' : '' }}">
                <label for="status">Статус*</label>
                <select type="text" id="status" name="status" class="form-control" value="{{ old('status', is_object($answer) ? $answer->status : 0) }}" required >
                    <option value="0" @if(is_object($answer) && $answer->status==0) selected @endif>не опубликован</option>
                    <option value="1" @if(is_object($answer) && $answer->status==1) selected @endif>опубликован</option>
                </select>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="Сохранить">
                <a  class="btn btn-default" href="{{ url()->previous() }}">
                    назад
                </a>
            </div>
        </form>


    </div>
</div>

@endsection