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
            <a style="margin-top:20px;" class="btn btn-default" 
            
                @if(isset($_GET['groupRoute']))
                    href="/admin/itemgroups?id={{$_GET['id']}}&name={{$_GET['name']}}&page={{$_GET['page']}}&parentId={{$_GET['parentId']}}&popularSort={{$_GET['popularSort'] ?? ''}}&id1c={{$_GET['id1c'] ?? ''}}&weightId={{$_GET['weightId'] ?? ''}}"
                @else
                    href="/admin/items?id={{$_GET['id'] ?? ''}}&name={{$_GET['name'] ?? ''}}&longName={{$_GET['longName'] ?? ''}}&page={{$_GET['page'] ?? ''}}&date={{$_GET['date'] ?? ''}}&sorting={{$_GET['sorting'] ?? ''}}&id1c={{$_GET['id1c'] ?? ''}}&weightId={{$_GET['weightId'] ?? ''}}"
                @endif
        
            
            
            >
                к списку
            </a>
        </div>


    </div>
</div>
@endsection


