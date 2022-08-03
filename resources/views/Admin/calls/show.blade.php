@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Просмотр фирмы
    </div>

    <div class="card-body">
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            ID
                        </th>
                        <td>
                            {{ $firm->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Название
                        </th>
                        <td>
                            {{ $firm->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">
                назад к списку
            </a>
        </div>

        <nav class="mb-3">
            <div class="nav nav-tabs">

            </div>
        </nav>
        <div class="tab-content">

        </div>
    </div>
</div>
@endsection