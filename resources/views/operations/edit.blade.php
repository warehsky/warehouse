@extends('layouts.admin')
@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<div class="card">
    <div class="card-header">
        Редактировать тип операции
    </div>
    <div class="card-body">
        <form 
        action="/operations/{{ (isset($operation) ? $operation->id : 0) }}"
        
        method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('operation') ? 'has-error' : '' }}">
                <label for="operation">ID</label>
                <span>{{ old('operation', isset($operation) ? $operation->id : '') }}</span>
            </div>
            <div class="form-group {{ $errors->has('operation') ? 'has-error' : '' }}">
                <label for="operation">Название*</label>
                <input type="text" id="operation" name="operation" maxlength="100" class="form-control" value="{{ old('operation', isset($operation) ? $operation->operation : '') }}" required >
                @if($errors->has('operation'))
                    <em class="invalid-feedback">
                        {{ $errors->first('operation') }}
                    </em>
                @endif
                <p class="helper-block">
                    
                </p>
            </div>
            
            <div>
                <input class="btn btn-danger" type="submit" value="Сохранить">
                <a style="margin-left:10px;" class="btn btn-default" 
                    href="/operations">
                к списку
                </a>
            </div>
        </form>


    </div>
</div>

<style>
.tree span:hover {
   font-weight: bold;
 }

 .tree span {
   cursor: pointer;
 }
 .minus{
   background-image: url(/img/minus.png) no-repeat;
   font-size: 26px;
   color: blue;
   cursor: pointer;
}
.plus{ 
   background-image: url(/img/plus.png) no-repeat;
   font-size: 26px;
   color: green;
   cursor: pointer;
}
.gr-name{
   cursor: pointer;
}
.gr-name-active{
   color: blue;
}
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
</style>
<script>
    
    $(document).on('change','#checkBoxPopular',function(){
        if ($(this).is(':checked'))
            $("#popular").attr('disabled','disabled');
        else
            $('#popular').removeAttr('disabled');

    });

</script>
@endsection