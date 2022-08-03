@extends('layouts.admin')
@section('content')
  <div>
    <a class="btn" href="/admin/article/0">Добавить статью</a>
  </div>
  <table>
  <th>ID</th>
  <th>Дата</th>
  <th>Название</th>
  <th>Действие</th>
  <tbody>
  @foreach($articles as $article)
    <tr>
     <td>{{$article->id}}</td>
     <td>{{$article->created_at}}</td>
     <td>{{$article->title}}</td>
     <td><a href="/admin/article/{{$article->id}}">изменить</a></td>
    </tr>
  @endforeach
  </tbody>
  </table>



@endsection
@section('scripts')
@parent


@endsection
