@extends('layouts.admin')
@section('content')
<a href="{{ route('userAdmins.index')}}"><button class="btn btn-outline-primary" style="margin-bottom:10px">Вернутся к списку</button></a>
<div class="card">
    <div class="card-header">
        Редактировать admin пользователя
    </div>

    <div class="card-body">
        <form action="{{ route("userAdmins.update", [$user->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('login') ? 'has-error' : '' }}">
                <label for="login">Login*</label>
                <input type="text" id="login" name="login" class="form-control" value="{{ old('login', isset($user) ? $user->login : '') }}" required>
                @if($errors->has('login'))
                    <em class="invalid-feedback">
                        {{ $errors->first('login') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" class="form-control">
                @if($errors->has('password'))
                    <em class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">Почта*</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', isset($user) ? $user->email : '') }}" required>
                @if($errors->has('email'))
                    <em class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">Имя</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($user) ? $user->name : '') }}" required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>
            <div>
            <label for="name">Роль</label>
            <select name="role[]" id="role" class="form-control select2" multiple="multiple" required>
            @foreach($roles as $id => $el)
                <option value="{{ $el }}" 
                @if (isset($role))
                    @if (is_int(array_search($el,$role)))
                        selected
                    @endif
                @endif>{{$el}}</option>
            @endforeach
            </select>
            </div>
            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                <label for="note">Заметка</label>
                <input type="text" id="note" name="note" class="form-control" value="{{ old('note', isset($user) ? $user->note : '') }}" required>
                @if($errors->has('note'))
                    <em class="invalid-feedback">
                        {{ $errors->first('note') }}
                    </em>
                @endif
            </div>
            <div class="form-group {{ $errors->has('chatName') ? 'has-error' : '' }}">
                <label for="chatName">Имя в чате</label>
                <input type="text" id="chatName" name="chatName" class="form-control" value="{{ old('chatName', isset($user) ? $user->chatName : '') }}" required>
                @if($errors->has('chatName'))
                    <em class="invalid-feedback">
                        {{ $errors->first('chatName') }}
                    </em>
                @endif
            </div>
            <div>
                <label>Обновить токен: </label>
                <input type="checkbox" name="api_token" /><br />
            </div>
            <div>
                <input class="btn btn-success" type="submit" value="Сохранить">
            </div>
        </form>


    </div>
</div>
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
	<script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script>
		jQuery(document).ready(function($) {
			$('.select2').select2();
		});
	</script>
@endsection