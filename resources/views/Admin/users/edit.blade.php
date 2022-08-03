@extends('layouts.app')


@section('content')

<div class="columns">


		<div class="column is-10 is-offset-1">
			<h1>Редактирование {{$users->name}} </h1>

			<form action="{{"/users/" . $users->id}}" method="post">
				
				{{ csrf_field() }}
		    {{ method_field('PATCH') }}

					<div class="input-field">
						<label class="field">
							<input type="text" class="input" name="name" placeholder="name" value="{{$users->name}}">
						</label>						
					</div>
	
					<div class="input-field">
						<label class="field">
							<input type="password" class="input" name="password" placeholder="password">
						</label>						
					</div>
			
					<div class="input-field">
						<label class="field">
							<input type="text" class="input" name="email" placeholder="email" value="{{$users->email}}">
						</label>						
					</div>

					<div class="input-field">
						<label class="field">Роль:
							<input type="text" class="input" name="role" placeholder="role" value="{{$users->role}}">
						</label>						
					</div>

					<div class="input-field">
						<label class="field">Примечание:
							<input type="text" class="input" name="note" placeholder="Примечание" value="{{$users->note}}">
						</label>						
					</div>
			
					
					<div class="form-group {{ $errors->has('roles') ? 'has-error' : '' }}">
						<label for="roles">Роли*
							<span class="btn btn-info btn-xx select-all" style="padding: 1px 5px;font-size: 12px;">выделить все</span>
							<span class="btn btn-info btn-xx deselect-all" style="padding: 1px 5px;font-size: 12px;">отменить все</span></label>
						<select name="roles[]" id="roles" class="form-control select2" multiple="multiple" required>
							@foreach($roles as $id => $roles)
								<option value="{{ $id }}" {{ (in_array($id, old('roles', [])) || isset($users) && $users->roles()->pluck('name', 'id')->contains($id)) ? 'selected' : '' }}>{{ $roles }}</option>
							@endforeach
						</select>
						@if($errors->has('roles'))
							<em class="invalid-feedback">
								{{ $errors->first('roles') }}
							</em>
						@endif
						<p class="helper-block">
							
						</p>

						<label class="field">
							<input type="submit" name="submit" class="button is-primary" value="Сохранить">			
						</label>
					</div>
			</form>	
			<a href=" {{ url('/users')}} ">Все пользователи</a>		
		</div>
	</div>

	<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
	<script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
	<script src="{{ asset('js/select2.full.min.js') }}"></script>
	<script>
		jQuery(document).ready(function($) {
			$('.select-all').click(function () {
				let $select2 = $(this).parent().siblings('.select2')
				$select2.find('option').prop('selected', 'selected')
				$select2.trigger('change');
			})
			$('.deselect-all').click(function () {
				let $select2 = $(this).parent().siblings('.select2')
				$select2.find('option').prop('selected', '')
				$select2.trigger('change')
			})
			$('.select2').select2();
		});
	</script>
@endsection