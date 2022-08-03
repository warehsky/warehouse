@extends('layouts.app');

@section('content')

	<div class="columns">
		
		<div class="column is-10 is-offset-1">
			<form action="/users" method="post">
				
				{{ csrf_field() }}

					<div class="input-field">
						<label class="field">
							<input type="text" class="input" name="name" placeholder="name">
						</label>						
					</div>
	
					<div class="input-field">
						<label class="field">
							<input type="password" class="input" name="password" placeholder="password">
						</label>						
					</div>
			
					<div class="input-field">
						<label class="field">
							<input type="text" class="input" name="email" placeholder="email">
						</label>						
					</div>

					<div class="input-field">
						<label class="field">Роль:
							<input type="text" class="input" name="role" placeholder="role" value="0">
						</label>						
					</div>

					<div class="input-field">
						<label class="field">Примечания:
							<input type="text" class="input" name="note" placeholder="note">
						</label>						
					</div>

					<label class="field">
						<input type="submit" name="submit" class="button is-primary">			
					</label>

			</form>	
			<a href=" {{ url('/users')}} ">Все пользователи</a>		
		</div>
	</div>
	
@endsection
	