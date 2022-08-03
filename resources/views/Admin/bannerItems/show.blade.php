@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Просмотр товара
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
                            {{ $item->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Короткое название
                        </th>
                        <td>
                            {{ $item->title }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Полное название
                        </th>
                        <td>
                            {{ $item->longTitle }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Описание
                        </th>
                        <td>
                        {{ $item->discr }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Иконка
                        </th>
                        <td>
                        @if( file_exists(storage_path('app/img/items/icons/'.$item->id.'.svg')) )
                            <div>
                                <img src="/img/img/items/icons/{{$item->id}}.svg" width="24">
                            </div>
                        @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Маленькая картинка
                        </th>
                        <td>
                        @if( file_exists(storage_path('app/img/items/small/'.$item->id.'.png')) )
                            <div>
                                <img src="/img/img/items/small/{{$item->id}}.png" width="124">
                            </div>
                        @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Большая картинка
                        </th>
                        <td>
                        @if( file_exists(storage_path('app/img/items/big/'.$item->id.'.png')) )
                            <div>
                                <img src="/img/img/items/big/{{$item->id}}.png" width="124">
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
                    <tr>
                        <th>
                            Теги
                        </th>
                        <td>
                        @foreach($tags as $tag)
                            <span class="btn btn-xs btn-info">[#{{ $tag->id }}] {{ $tag->title }}</span>&nbsp;
                        @endforeach
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