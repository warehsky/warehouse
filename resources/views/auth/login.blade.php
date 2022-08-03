@extends('layouts.app')

<link href="{{ asset('css/login.css') }}" rel="stylesheet">

@section('content')

<div class="container">
<div class="form__auth">
        <form class="form form-login" method="POST" action="{{ route('login') }}">
        @csrf
            <h3 class="form__title">{{ __('Вход') }}</h3>

            <div class="form__group">
                <label for="email" class="form__label">{{ __('Электронный адрес') }}</label>
                <input type="email" id="email" name="email" class="form__input" required autocomplete placeholder=" ">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form__group">
                <label for="password" class="form__label">{{ __('Пароль') }}</label>
                <input type="password" id="password" name="password" class="form__input" required placeholder=" ">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form__checkout">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="" for="remember">{{ __('Запомнить') }}</label>
            </div>

            <button type="submit" class="form__btn">{{ __('Вход') }}</button>
        </form>
    </div>
    </div>


@endsection
