<?php

use Illuminate\Support\Facades\Auth;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME', 'Permissions Manager') }}</title>    
    
    <!-- <link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" /> -->
    <link href="{{ asset('css/css/Admin/global.css') }}" rel="stylesheet" />
    @yield('styles')
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
    <script defer>window.course = Number('{{ $course }}');</script>
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
          let orders = JSON.parse("{{ auth()->guard('admin')->user()->can('orders_all') }}");
          let messages = JSON.parse("{{ auth()->guard('admin')->user()->can('chat_view') }}");
          let notification = JSON.parse("{{ auth()->guard('admin')->user()->can('chat_notification') }}");
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
                  if (item){
                    urlReport+='rep'+index+"="+item+"&";
                    count+=item;
                  }
                });
                $('#reportP').html("Отчеты ("+count+")");
                $('#ReportLi').attr("href", urlReport);
              }
            });
          });
        </script>
        @endif
        <!-- indicators -->

        <span class="nav-item" style="display: flex;">
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
              <a class="nav-link" href="{{ route('userAdmins.index') }}">Admin пользователи</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('itemGroups_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('itemgroups.index') }}">Группы товаров</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('items_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('items.index') }}">Товары</a>
            </li>        
            @endif
            @if(auth()->guard('admin')->user()->can('articles_all'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('articles') }}">Рецепты</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('suggests_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('suggests') }}">Сопутств. товары</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('options_all'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('optionsIndex') }}">Настройки</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('comments_all'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('comments') }}">Отзывы</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('commentsFirm_all'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('commentsFirm') }}">Сервис отзывы</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('banners_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('banners.index') }}">Банеры</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('email_view'))
              <?php $tmp = \App\Model\Backlink::getCountNewMessage();?>
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" @if($tmp>0) style="color:#ff0000" @endif href="{{ route('email') }}">Обратная связь: {{$tmp}}</a>
            </li> 
            @endif
            @if(auth()->guard('admin')->user()->can('pageStock_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('pageStock.index') }}">Акции</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('vacancy_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('vacancy.index') }}"> Вакансии</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('Promocode_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('promocode.index') }}">Промокоды</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('phoneNumber_all'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('phoneNumber.index') }}">Номера телефонов</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('changeSequenceStock_all'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('changeSequence.index') }}">Акции товаров</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('webUsers_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('WebUsers') }}">Покупатели</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('deliveryZone_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
              <div style="display:flex; border:1px solid; border-radius:5px">
                <a class="nav-link" style="border-right:1px solid black" href="{{ route('deliveryZone.index') }}">Зоны доставки</a>
                <a class="nav-link" href="{{ route('zonesEditor') }}">Редактирование зон</a>
              </div>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('TimeWaves_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="{{ route('timeWaves') }}">Волны</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('itemKassa_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
              <p title="Товары в слайдере 'Возможно вы забыли заказать?'"><a class="nav-link" href="{{ route('itemsKassa') }}">Товары при кассе</a></p>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('imageLoad_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('imageload') }}">Загрузка изображений</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('imageForGallery_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
              <a class="nav-link" href="{{ route('loadImageForGallery') }}">Галерея</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('ItemsMain_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="{{ route('itemsMain') }}">Группировка товаров</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('report_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a id="ReportLi" class="nav-link" href="/admin/report"><p id='reportP'>Отчеты</p></a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('warehouse_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="/admin/warehouse?dFrom={{$_REQUEST['dFrom']??''}}&dTo={{$_REQUEST['dTo']??''}}">Склад(весовой товар)</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('warehouse_view'))
            <li class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="/admin/warehousepacks?dFrom={{$_REQUEST['dFrom']??''}}&dTo={{$_REQUEST['dTo']??''}}">Склад(пакеты)</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('warehouse_admin'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="{{ route('warehousePickup') }}">Склад(назначение)</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('phoneNumberStatistic_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="{{ route('phoneNumberStatistic') }}">Статистика звонков</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('phoneNumberBackLink_view'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="{{ route('backLinkPhoneIndex') }}">Обратная связь 377</a>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('TimeWaves_OrderLimit'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="{{ route('timeWaves/OrderLimit') }}">Заказы на волну</a>
            </li>
            @endif
            @if(auth()->guard('admin')->user()->can('advertising_Img'))
            <li  class="nav-item" style="padding: 0px 5px 0px 5px;">
             <a class="nav-link" href="{{ route('loadOneImageIndex') }}">Рекламные изображения</a>
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
    @yield('scripts')
</body>
<style>
    .navbar {
    position: relative;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
    padding: .5rem 1rem;
}
    .navbar-brand {
    display: inline-block;
    padding-top: .3125rem;
    padding-bottom: .3125rem;
    margin-right: 1rem;
    font-size: 1.25rem;
    line-height: inherit;
    white-space: nowrap;
}
.navbar-collapse {
    -ms-flex-preferred-size: 100%;
    flex-basis: 100%;
    -webkit-box-flex: 1;
    -ms-flex-positive: 1;
    flex-grow: 1;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
}
.nav-link {
    display: block;
    padding: .5rem 1rem;
}
.nav-link:hover{
    color: #0056b3;
}
ul.mt-menu {
    margin: 0;
        margin-right: 0px;
}
ul.mt-menu li {
    display: inline-block !important;
    padding: 0px 5px 0px 5px;
}
.mr-auto, .mx-auto {
    margin-right: auto !important;
}
a {
    color: #007bff;
    text-decoration: none;
    background-color: transparent;
    -webkit-text-decoration-skip: objects;
}
a:hover{
  text-decoration: underline;
}
.btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    border: 1px solid transparent;
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: transparent;
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: .25rem;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
body{
  font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
  line-height: 1.5;
}
.navbar-toggler:not(:disabled):not(.disabled) {
    cursor: pointer;
}
[type=reset], [type=submit], button, html [type=button] {
    -webkit-appearance: button;
}
.navbar-toggler {
    display: none!important;
    padding: .25rem .75rem;
    font-size: 1.25rem;
    line-height: 1;
    background-color: transparent;
    border: 1px solid transparent;
    border-radius: .25rem;
}
</style>
</html>
