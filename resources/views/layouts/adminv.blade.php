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
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" /> 
    <link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    
    <!-- <link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" /> -->
    <link href="{{ asset('css/css/Admin/global.css') }}" rel="stylesheet" />
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
    <script>
          $(document).ready(function(){
            
            
          });
          function menu_state(){
            if ( document.getElementById("elem_main").classList.contains('active-main') ){
                document.getElementById('elem_main').classList.remove('active-main');
                document.getElementById('elem_navbar').classList.remove('active-navbar');
                document.getElementById('elem_sidebar').classList.remove('active-sidebar');
                document.getElementById('elem_hamburger').classList.remove('active-hamburger');
                let item = document.getElementsByClassName('menu-h');
                for(i=0;i<item.length;i++)
                    item[i].classList.add('menu-pass');
            }else{
                document.getElementById('elem_main').classList.add('active-main');
                document.getElementById('elem_navbar').classList.add('active-navbar');
                document.getElementById('elem_sidebar').classList.add('active-sidebar');
                document.getElementById('elem_hamburger').classList.add('active-hamburger');
                let item = document.getElementsByClassName('menu-h');
                for(i=0;i<item.length;i++)
                    item[i].classList.remove('menu-pass');
            }
          }
          /** */
          function sub_menu(id){

            let itm = document.getElementById('sub-'+id);
            let arrow = document.getElementById('down-'+id);
            if ( itm.classList.contains('passiv-submenu') ){
                itm.classList.remove('passiv-submenu');
                arrow.classList.add('down-up');
            }else{
                itm.classList.add('passiv-submenu');
                arrow.classList.remove('down-up');
            }
          }
        </script>
</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed pace-done sidebar-lg-show">
    <header class="app-header navbar active-navbar" id="elem_navbar">
       
        
            
        <svg onclick="menu_state()" data-v-64757d20="" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="hamburger active-hamburger" id="elem_hamburger"><path data-v-64757d20="" d="M408 442h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8zm-8 204c0 4.4 3.6 8 8 8h480c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8H408c-4.4 0-8 3.6-8 8v56zm504-486H120c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zm0 632H120c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zM142.4 642.1L298.7 519a8.84 8.84 0 0 0 0-13.9L142.4 381.9c-5.8-4.6-14.4-.5-14.4 6.9v246.3a8.9 8.9 0 0 0 14.4 7z"></path></svg>
           
       
        
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
          let api_token = "<?= Auth::guard('admin')->user()->getToken() ?>";
          setupIndicators(orders, api_token);//From /js/admin/createIndicators.js
        </script>
        @if(auth()->guard('admin')->user()->can('report_view'))
        
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
        <!-- <div class="navbar-collapse" id="navbarSupportedContent">
                    
          
        </div>   -->
        <form id="logoutform" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>      
    </header>

    <div class="app-body">
        <!-- @include('partials.menu') -->
        <main class="main active-main" id="elem_main">
          <div class="sidebar-container active-sidebar" id="elem_sidebar">
          <ul class="mr-auto mt-menu">
          <li>
             <a class="nav-link" href="/admin/"><div class="menudiv menudiv-r"><img class="sky-menu-icon" src="/img/icons/menu/home1.png" /></div><div class="menudiv menu-h">Главная</div></a>
            </li>
          @if(auth()->guard('admin')->user()->can('userAdmin_all'))
            <li>
              <a class="nav-link" href="{{ route('userAdmins.index') }}"><div class="menudiv menudiv-r"><img class="sky-menu-icon" src="/img/icons/menu/contr1.png" /></div><div class="menudiv menu-h">Admin пользователи</div></a>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('options_all'))
            <li>
              <a class="nav-link" href="{{ route('optionsIndex') }}"><div class="menudiv menudiv-r"><img class="sky-menu-icon" src="/img/icons/menu/ctrl1.png" /></div><div class="menudiv menu-h">Настройки</div></a>
            </li>
            @endif
            
            @if(auth()->guard('admin')->user()->can('userAdmin_all'))
            <li>
              <a class="nav-link" onclick="sub_menu('1')" href="#"><div class="menudiv menudiv-r"><img class="sky-menu-icon" src="/img/icons/menu/store1.png" /></div><div class="menudiv menu-h">Хранение</div>
              <div class="menudiv menudiv-l menu-h"><img class="sky-menu-icon" src="/img/icons/menu/down1.png" id="down-1"/></div>
              </a>
              <div class="submenu passiv-submenu menu-h" id="sub-1">
                <ul class="">
                    <a class="nav-link" href="/orders">Приход</a>
                    <a class="nav-link" href="/expenses">Расход</a>
                </ul>
              </div>
            </li>
            @endif

            @if(auth()->guard('admin')->user()->can('userAdmin_all'))
            <li>
              <a class="nav-link" onclick="sub_menu('2')" href="#"><div class="menudiv menudiv-r"><img class="sky-menu-icon" src="/img/icons/menu/spr1.png" /></div><div class="menudiv menu-h">Справочники</div>
              <div class="menudiv menudiv-l menu-h"><img class="sky-menu-icon" src="/img/icons/menu/down1.png" id="down-2"/></div>
              </a>
              <div class="submenu passiv-submenu menu-h" id="sub-2">
                <ul class="">
                <a class="nav-link" href="/cargos">Типы груза</a>
                <a class="nav-link" href="/operations">Типы операций</a>
                </ul>
              </div>
            </li>
            @endif

           

            @if(auth()->guard('admin')->user()->can('report_view'))
            <li>
             <a id="ReportLi" class="nav-link" href="/admin/report"><p id='reportP'>Отчеты</p></a>
            </li>
            @endif

            
          </ul>
          </div>


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
    .sidebar-container {
    background-color: #304156;
    bottom: 0;
    font-size: 1.0rem;
    height: 100%;
    left: 0;
    overflow: hidden;
    position: fixed;
    top: 0;
    -webkit-transition: width .58s;
    transition: width .58s;
    width: 44px!important;
    z-index: 1001;
    }
.sidebar-container a{
    color: #fafafa;
}
.sidebar-container a:hover{
    color: #dddddd;
    text-decoration-line: none;
}
.active-sidebar{
    transition: width .58s;
    width: 210px!important;
}
.active-main, .active-navbar{
    margin-left: 210px!important;
    transition: margin-left .58s;
}
.active-hamburger, .down-up{
    -webkit-transform: rotate(180deg);
    transform: rotate(180deg);
    transition: rotate .58s;
}

.main, .navbar{
    margin-left: 44px;
    transition: margin-left .58s;
}
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
.menudiv {
    display:inline-block;
    max-width: 100px;
    max-height: 50px;
    overflow: hidden;
}
.menu-pass{
    width: 0px!important;
    transition: width .58s;
    
}
.menudiv-l {
    float: right;
    width: 35px;
    text-align: right;
}
.menudiv-r {
    float: left;
    margin-right: 10px;
}
.sky-menu-icon{
    width: 20px;
    height: 20px;
}
.sky-menu-icon-r{
    margin: 0px 5px 0px 5px;
}
.sky-menu-icon-l{
    margin: 0px 5px 0px 0px;
}

ul.mt-menu {
    margin: 0;
    padding: 0px;
}
ul.mt-menu li {
    /* display: inline-block !important; */
    /* padding: 0px 5px 0px 5px; */
    color: honeydew;
    list-style-type: none;
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

.passiv-submenu{
    height: 0;
    transition: height 1.58s;
    overflow: hidden;
}
</style>
</html>
