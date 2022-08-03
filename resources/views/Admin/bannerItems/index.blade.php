@extends('layouts.admin')
@section('content')
@if(auth()->guard('admin')->user()->can('banners_edit'))
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("bannerItems.create", ['bannerId' => $bannerId]) }}">
                Создать баннер
            </a>
        </div>
    </div>
@endif
<div class="card">
    <div class="card-header">
        баннеры группы
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
                            ID группы
                        </th>
                        <th>
                            Ссылка
                        </th>
                        <th>
                            Банер
                        </th>
                        <th>
                            Банер мобильный
                        </th>
                        <th>
                            ALT
                        </th>
                        <th>
                            Сортировка
                        </th>
                        <th>
                            Публикация
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bannerItems as $key => $item)
                        <tr data-entry-id="{{ $item->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $item->id ?? '' }}
                            </td>
                            <td>
                                {{$item->bannerId ?? ''}}
                            </td>
                            <td>
                                {{ $item->link ?? '' }}
                            </td>
                            <td>
                                <img src="{{$item->image}}" height="30">
                            </td>
                            <td>
                            <img src="{{$item->image_mobile}}" height="30">
                            </td>
                            <td>
                                {{ $item->alt ?? '' }}
                            </td>
                            <td>
                                {{ $item->sort ?? '' }}
                            </td>
                            <td>
                            {{ $item->public ? 'опубликован' : 'не опубликован' }}
                            </td>
                            <td>
                                <a class="btn btn-xs btn-primary" href="{{ route('bannerItems.show', $item->id) }}">
                                    просмотр
                                </a>
                                @if(auth()->guard('admin')->user()->can('banners_edit'))
                                <a class="btn btn-xs btn-info" href="{{ route('bannerItems.edit', $item->id) }}">
                                    изменить
                                </a>

                                <form action="{{ route('bannerItems.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Уверен');" style="display: inline-block;">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="submit" class="btn btn-xs btn-danger" value="удалить">
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
@if(auth()->guard('admin')->user()->can('banners_edit'))
  let deleteButtonTrans = 'удалить'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('bannerItems.MassDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('Удалить?')

        return
      }

      if (confirm('Уверен')) {
        $.ajax({
          method: 'POST',
          url: config.url,
          data: {"_token":"{{ csrf_token() }}", ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endif

  $.extend(true, $.fn.dataTable.defaults, {
    
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