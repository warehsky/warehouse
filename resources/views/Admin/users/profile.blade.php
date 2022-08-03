@extends('layouts.app');

@section('content')


	<div class="columns">
		<div class="column is-10 is-offset-1">
			<table class="table is-striped">

				<thead>
					<tr>
						<th>id</th>
						<th>Имя</th>
						<th>Пароль</th>
						<th>Почта</th>
						<th>Редактирование</th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<th>{{$users->id}}</th>
						<th>{{$users->name}}</th>
						<th>{{$users->password}}</th>
						<th>{{$users->email}}</th>
						<th>
							<a href="{{ url('/users/' . $users->id . '/edit')}}">Редактировать данные</a>
						</th>
					</tr>
				</tbody>

			</table>

			<div>
				<a href=" {{ url('/users')}} ">Все пользователи</a>		
			</div>
			<a href=" {{ url('/users/create')}} ">Создать нового пользователя</a>

		</div>
	</div>

	
@endsection