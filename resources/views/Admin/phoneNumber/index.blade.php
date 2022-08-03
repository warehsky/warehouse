

@extends('layouts.admin')
@section('content')



    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-4">
            <a class="btn btn-success" href="{{ route("phoneNumber.create") }}">
                Добавить номер
            </a>
            <a class="btn btn-info" href="{{ route("updateDataNumbers") }}">
                Обновить
            </a>
               
        </div>
        <div class="col-lg-8"> 
        
            <form action="{{ route('PhoneFilterIndex') }}" method="post">
            @csrf
                    <select size="1" name=code>
                        <option selected value=0>Все операторы</option>
                        <option value=1>Номера РФ(+7)</option>
                        <option value=2>Номера Феникс(071)</option>
                        <option value=3>Номера МТС(066,050,095,099)</option>
                        <option value=4>Городские номера(062)</option>
                    </select>

                    <select size="1" name=subscribe>
                        <option selected value=2>Рассылка</option>
                        <option value=1>Включена</option>
                        <option value=0>Выключена</option>
                    </select>

                    <input type="submit" class="btn btn-success btn-xs" value="Применить фильтры">
                    <a href="{{ route('phoneNumber.index')}}"><button class="btn btn-primary btn-xs">Сбросить фильтры</button></a> 
            </form>
        
        </div>

    </div>

<div class="card">
    <div class="card-header">
        Список номеров
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-item">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            Телефон
                        </th>
                        <th>
                            Имя
                        </th>
                        <th>
                            Источник
                        </th>
                        <th>
                            Рассылка
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allPhone as $el)
                        <tr data-entry-id="{{ $el->phone }}">
                            <td>
                          
                            </td> 
                            <td>{{$el->phone}}</td> 
                            <td>{{$el->name}}</td>
                            <td>{{$el->source}}</td>
                            <td>@if ($el->unsubscribe)
                                    Включена
                                @else 
                                    Выключена        
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('phoneNumber.edit', $el->phone)}}"><button class="btn btn-primary btn-xs">Редактировать</button></a>    
                                <a href="{{ route('ChangePhoneUnsubscribe', $el->phone)}}"><button  class="btn btn-info btn-xs">Изменить статус рассылки</button></a>

                                <form action="{{ route('phoneNumber.destroy', $el->phone) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="submit" class="btn btn-xs btn-danger" value="Удалить">
                                </form>

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

  let deleteButtonTrans = 'Удалить'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('PhoneMassDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('Удалить?')

        return
      }

      if (confirm('Вы уверены?')) {
        $.ajax({
          method: 'POST',
          url: config.url,
          data: {"_token":"{{ csrf_token() }}", ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)


  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 2, 'desc' ]],
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
