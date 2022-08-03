@extends('layouts.admin')

@section('content')
<h3>Окно редактирования страницы вакансий</h3>
@if(auth()->guard('admin')->user()->can('vacancy_edit'))
<a href="{{ route('vacancy.create')}}"><button  class="btn btn-success" >Добавить вакансию</button></a>
@endif
<a href="{{ route('vacancyProperty.index')}}"><button  class="btn btn-info" >Характеристики</button></a>
<a href="{{ route('vacancySpecialty.index')}}"><button  class="btn btn-info" >Особенности</button></a>
@if(auth()->guard('admin')->user()->can('vacancy_edit'))
<a href="{{ route('vacancy.helpPage')}}"><button  class="btn btn-info" >Помощь при заполнении</button></a>
@endif
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Список вакансий
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-item">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            ID
                        </th>
                        <th>
                            Название
                        </th>
                        <th>
                            Описание
                        </th>
                        <th>
                            Иконка
                        </th>
                        <th>
                            Необходимость
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($AllVacancy as $el)                
                <tr>
                    <td>

                    </td>
                    <td>{{$el->id}}</td> 
                    <td>{{$el->vacancyTitle}}</a></td>
                    <td>{{@str_limit($el->vacancyDescription, $limit = 60, $end = '...')}}</td>
                    <td><img src="{{$el->vacancyImage}}" height="30" style="filter: invert(85%) sepia(50%) saturate(5446%) hue-rotate(350deg) brightness(94%) contrast(94%);"></td>
                    <td>@if ($el->vacancyRequired==0)
                            Не требуется
                        @else
                            Требуется
                        @endif </td>
                    <td>
                        
                    @if(auth()->guard('admin')->user()->can('vacancy_edit'))
                        <a href = "{{route('vacancy.show', $el->id)}}"><button  class="btn btn-xs btn-info" >Открыть</button></a>
                        <a href = "{{route('vacancy.edit', $el->id)}}"><button  class="btn btn-xs btn-info" >Редактировать</button></a>
                        <a href="{{ route('changeRequired',$el->id)}}"><button  class="btn btn-xs btn-info" >Изменить состояние</button></a>
                        <form action="{{ route('vacancy.destroy', $el->id) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
	                        <input type="hidden" name="_method" value="DELETE">
	                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
	                        <input type="submit" class="btn btn-danger btn-xs" value="Удалить">
                        </form>
                    @endif
                    </td>

                </tr>
                @endforeach
                </tbody>
            </table>
        </div>


    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)

  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 2, 'asc' ]],
    pageLength: 100,
  });
  $('.datatable-item:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection







