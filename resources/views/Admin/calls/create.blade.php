@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Создать фирму
    </div>

    <div class="card-body">
        <form action="{{ route("admin.firms.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">Название*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($firm) ? $firm->name : '') }}" required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('cruds.permission.fields.title_helper') }}
                </p>
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="сохранить">
            </div>
        </form>


    </div>
</div>
@endsection