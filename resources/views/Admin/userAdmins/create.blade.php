@extends('layouts.admin')
@section('content')
<a href="{{ route('userAdmins.index')}}"><button class="btn btn-outline-primary" style="margin-bottom:10px">Вернутся к списку</button></a>
<div class="card" >
    <div class="card-header">
        Создать пользователя
    </div>

    <div class="card-body">
        <form action="{{ route("userAdmins.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Логин -->
            <div class="form-group {{ $errors->has('login') ? 'has-error' : '' }}">
                <label for="login">Логин*</label>
                <input type="text" id="login" name="login" class="form-control" value="{{ old('login', isset($user) ? $user->login : '') }}" required>
                @if($errors->has('login'))
                    <em class="invalid-feedback">
                        {{ $errors->first('login') }}
                    </em>
                @endif
            </div>
            <!-- Пароль -->
            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">Пароль*</label>
                <input type="password" id="password" name="password" class="form-control" required>
                @if($errors->has('password'))
                    <em class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </em>
                @endif
            </div>
            <!-- Почта -->
            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">Почта*</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', isset($user) ? $user->email : '') }}" required>
                @if($errors->has('email'))
                    <em class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </em>
                @endif
            </div>
            <!-- Имя -->
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">Имя</label>
                <input type="name" id="name" name="name" class="form-control" required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
            </div>
            <!-- Заметка -->
            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                <label for="note">Заметка</label>
                <input type="note" id="note" name="note" class="form-control" required>
                @if($errors->has('note'))
                    <em class="invalid-feedback">
                        {{ $errors->first('note') }}
                    </em>
                @endif
            </div>
            <div>
            <label for="note">Role</label>
            <select name="role[]" id="role" class="form-control select2" multiple="multiple" required>
                    @foreach($role as $id => $role)
                        <option value="{{ $id }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <p>
                <input class="btn btn-success" type="submit" value="Сохранить">
                </p>
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