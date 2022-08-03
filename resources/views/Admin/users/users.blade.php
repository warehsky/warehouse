@extends('layouts.app')

@section('content')

	<div class="columns">
		<div class="column is-10 is-offset-1">
			<table class="table is-striped">
				<thead>
					<tr>
						<th>Id</th>
						<th>Пользователь</th>
						<th>Email</th>
						<th>Роль</th>
						<th>Роли</th>
						<th>Примечание</th>
						<th>Действие</th>
					</tr>
				</thead>

				<tbody>
					@foreach($users as $user)
						<tr>

								<td>
									{{ $user->id }}
								</td>

								<td>
									{{ $user->name }}
								</td>

								<td>
									{{ $user->email }}
								</td>

								<td>
									{{ $user->role }}
								</td>

								<td>
									@foreach($user->roles()->pluck('name') as $role)
										<span class="badge badge-info">{{ $role }}</span>
									@endforeach
								</td>
								
								<td>
									{{ $user->note }}
								</td>

								<td>
									@if(\Auth::guard('admin')->user()->can('users_manage'))
										<a class="btn btn-xs btn-info" href="{{ route('users.edit', $user->id) }}" style="padding: 1px 5px;font-size: 12px;">
											изменить
										</a>
										<form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Вы уверены');" style="display: inline-block;">
											<input type="hidden" name="_method" value="DELETE">
											<input type="hidden" name="_token" value="{{ csrf_token() }}">
											<input type="submit" class="btn btn-xs btn-danger" value="удалить" style="padding: 1px 5px;font-size: 12px;">
										</form>
									@endif
								</td>

						</tr>
					@endforeach					
				</tbody>
			</table>

			<a href="{{ url('users/create') }}">Создать пользователя</a>

		</div>
	</div>
	

@endsection