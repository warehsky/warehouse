@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Просмотр группы товара
    </div>

    <div class="card-body">
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.id') }}
                        </th>
                        <td>
                            {{ $group->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Короткое название
                        </th>
                        <td>
                            {{ $group->title }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Полное название
                        </th>
                        <td>
                            {{ $group->longTitle }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Описание
                        </th>
                        <td>
                        {{ $group->discr }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Иконка
                        </th>
                        <td>
                        @if( file_exists(storage_path('app/img/catalog/icons/'.$group->id.'.svg')) )
                            <div>
                                <img src="/img/img/catalog/icons/{{$group->id}}.svg" width="24">
                            </div>
                        @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Маленькая картинка
                        </th>
                        <td>
                        @if( file_exists(storage_path('app/img/catalog/small/'.$group->id.'.png')) )
                            <div>
                                <img src="/img/img/catalog/small/{{$group->id}}.png" width="124">
                            </div>
                        @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Большая картинка
                        </th>
                        <td>
                        @if( file_exists(storage_path('app/img/catalog/big/'.$group->id.'.png')) )
                            <div>
                                <img src="/img/img/catalog/big/{{$group->id}}.png" width="124">
                            </div>
                        @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Родитель
                        </th>
                        <td>
                        [#{{ $parent->id }}] {{ $parent->title }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">
                к списку
            </a>
        </div>


    </div>
</div>
@endsection