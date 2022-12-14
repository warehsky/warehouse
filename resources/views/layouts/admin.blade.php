<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME', 'Permissions Manager') }}</title>    
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" /> 
    <link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/css/Admin/global.css') }}" rel="stylesheet" />
    <style type="text/css">.nav-link:hover{color: #0056b3}</style>
    @yield('styles')
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
    
    <!-- Globals and Permissions -->
    <script src="/js/globals.js"></script>
    <script>
      var permissions =  JSON.parse(`<?php echo(auth()->guard('admin')->user()->getAllPermissions()); ?>`);
      UserPermissions.init(permissions.map(item=>item.name));
      Globals.api_token = "{{$api_token}}";
    </script>
    <!-- Globals and Permissions -->
</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed pace-done sidebar-lg-show">

    <header class="app-header navbar">
        <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto" type="button" data-toggle="sidebar-show">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- indicators -->
        <link rel="stylesheet" href="/css/indicators.css"/>
        <div class="indicators">
            <div class="indicator" id="i-orders"></div>
            <div class="indicator" id="i-bad"></div>
            <div class="indicator" id="i-messages"></div>
        </div>
        <script src="/js/admin/indicators.js"></script>
        <script src="/js/GTimer.js"></script>
        <script src="/js/admin/createIndicators.js"></script>
        <script>
          let orders = JSON.parse("{{ auth()->guard('admin')->user()->can('orders_all') }}"||"false");
          let messages = JSON.parse("{{ auth()->guard('admin')->user()->can('chat_view') }}"||"false");
          let notification = JSON.parse("{{ auth()->guard('admin')->user()->can('chat_notification') }}"||"false");
          let api_token = "<?= Auth::guard('admin')->user()->getToken() ?>";
          setupIndicators(orders, messages, notification, api_token);//From /js/admin/createIndicators.js
        </script>
        @if(auth()->guard('admin')->user()->can('report_view'))
        <script>            
          $(document).ready(function(){
            $.ajax({
              headers: {'X-Access-Token': $('#api_token').val()},
              beforeSend: function(xhr) {
                xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
              },
              url:"/Api/allReport",
              dataType: 'json',
              success:function(data){
                var count=0;
                if (!data.status)
                  document.getElementById("ReportLi").style.color = '#FF0000'; 
                var urlReport='/admin/report?';
                data.ReportErrors.forEach(function(item, index, ar){
                  if (item) {
                    urlReport+='rep'+index+"="+item+"&";
                    count+=item;
                  }
                });
                $('#reportP').html("???????????? ("+count+")");
                $('#ReportLi').attr("href", urlReport);
                var title="????????????: \n ?????????????????? ????????????: "+data.ReportErrors[0]+" \n ???????????? ?????? ???????? ??????????????: "+data.ReportErrors[1]+
                "\n ???????????????????? ????????????: "+data.ReportErrors[2]+" \n ???????????? ?????? ????????????????: "+data.ReportErrors[3]+
                " \n ???????????? ?? ???????????????????????? ??????????: "+data.ReportErrors[5];
                $('#ReportLi').prop('title', title);
              }
            });
          });
        </script>
        @endif
        <!-- indicators -->

        <span class="nav-item login-indicator" style="display: flex;">
            <span style="padding:0.5rem 1rem">{{ auth()->guard('admin')->user()->name }}</span>
                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                    <i class="nav-icon fas fa-fw fa-sign-out-alt">

                    </i>
                    {{ trans('global.logout') }}
                </a>
</span>
        <div class="navbar-collapse" id="navbarSupportedContent">
                    
          <ul class="mr-auto mt-menu">
          @if(auth()->guard('admin')->user()->can('userAdmin_all'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('userAdmins.index') }}">Admin ????????????????????????</a>
            </li>
            @endif
            
            @if(auth()->guard('admin')->user()->can('options_all'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('optionsIndex') }}">??????????????????</a>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('orders_all'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a id="ReportLi" class="nav-link" href="/orders"><p id='reportP'>???????????? ???? ????????????????</p></a>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('orders_all'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a id="ReportLi" class="nav-link" href="/expenses"><p id='reportP'>???????????? ????????????</p></a>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('orders_all'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a id="ReportLi" class="nav-link" href="/cargos"><p id='reportP'>???????? ??????????</p></a>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('orders_all'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a id="ReportLi" class="nav-link" href="/operations"><p id='reportP'>???????? ????????????????</p></a>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('report_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a id="ReportLi" class="nav-link" href="/admin/report"><p id='reportP'>????????????</p></a>
            </li>
            @endif
            
          </ul>
        </div>  
        <form id="logoutform" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>      
    </header>

    <div class="app-body">
        <!-- @include('partials.menu') -->
        <main class="main">


            <div style="padding-top: 20px" class="container-fluid">
                @if(session('message'))
                    <div class="row mb-2">
                        <div class="col-lg-12">
                            <div class="alert alert-success" role="alert">{{ session('message') }}</div>
                        </div>
                    </div>
                @endif
                @if($errors->count() > 0)
                    <div class="alert alert-danger">
                        <ul class="list-unstyled">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')

            </div>


        </main>
    </div>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
    <!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://unpkg.com/@coreui/coreui@2.1.16/dist/js/coreui.min.js"></script>
    <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script src="//cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
    <script src="//cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/11.0.1/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script> -->
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ asset('js/datatables.min.js') }}"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
	  <script src="{{ asset('js/select2.full.min.js') }}"></script>
    <script src="{{ asset('js/Chart.min.js') }}"></script>

    <script>
        $(function() {
  let copyButtonTrans = '{{ trans('global.datatables.copy') }}'
  let csvButtonTrans = '{{ trans('global.datatables.csv') }}'
  let excelButtonTrans = '{{ trans('global.datatables.excel') }}'
  let pdfButtonTrans = '{{ trans('global.datatables.pdf') }}'
  let printButtonTrans = '{{ trans('global.datatables.print') }}'
  let colvisButtonTrans = '{{ trans('global.datatables.colvis') }}'

  let languages = {
    'en': 'https://cdn.datatables.net/plug-ins/1.10.19/i18n/English.json'
  };

  $.extend(true, $.fn.dataTable.Buttons.defaults.dom.button, { className: 'btn' })
  $.extend(true, $.fn.dataTable.defaults, {
    language: {
      url: languages['{{ app()->getLocale() }}']
    },
    columnDefs: [{
        orderable: false,
        className: 'select-checkbox',
        targets: 0
    }, {
        orderable: false,
        searchable: false,
        targets: -1
    }],
    select: {
      style:    'multi+shift',
      selector: 'td:first-child'
    },
    order: [],
    scrollX: true,
    pageLength: 100,
    dom: 'lBfrtip<"actions">',
    buttons: [
      {
        extend: 'copy',
        className: 'btn-default',
        text: copyButtonTrans,
        exportOptions: {
          columns: ':visible'
        }
      },
      {
        extend: 'csv',
        className: 'btn-default',
        text: csvButtonTrans,
        exportOptions: {
          columns: ':visible'
        }
      },
      {
        extend: 'excel',
        className: 'btn-default',
        text: excelButtonTrans,
        exportOptions: {
          columns: ':visible'
        }
      },
      {
        extend: 'pdf',
        className: 'btn-default',
        text: pdfButtonTrans,
        exportOptions: {
          columns: ':visible'
        }
      },
      {
        extend: 'print',
        className: 'btn-default',
        text: printButtonTrans,
        exportOptions: {
          columns: ':visible'
        }
      },
      {
        extend: 'colvis',
        className: 'btn-default',
        text: colvisButtonTrans,
        exportOptions: {
          columns: ':visible'
        }
      }
    ]
  });

  $.fn.dataTable.ext.classes.sPageButton = '';
});

    </script>
    @yield('scripts')
</body>
<style>
a:hover{
  text-decoration: underline;
}
.nav-link:hover{
    color: #0056b3;
    text-decoration: underline;
}
</style>
</html>
