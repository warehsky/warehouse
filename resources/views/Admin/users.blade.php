@extends('layouts.app')

@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/datatables.min.js') }}"></script> 
<link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet">
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/users.css') }}" rel="stylesheet">

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css"> 
 -->


<div class="container">
    <div class="users-title">
        <span class="users-label">Пользователи:</span>
    </div>
    <div class="users-search">
        <label for="id-search" class="users-label">Поиск по ID:</label>
        <input id="id-search"  value="{{$id}}"/>
    </div>
    <div class="users-search">
        <label for="users-search" class="users-label">Поиск по ФИО:</label>
        <input id="users-search"   value="{{$f}}"/>
        <input id="users-order" type="hidden"  value="{{$order}}"/>
        <input id="users-order-dir" type="hidden"  value="{{$dir}}"/>
    </div>
    <div class="users-search">
        <label for="td-search" class="users-label">Фильтр по TD:</label>
            <select id="td-search">
            <option value='0'>Выбор не сделан</option>
            @forEach($trades as $trade)
                <option value='{{$trade->id}}' @if($trade->id == $tradeDirection) selected @endif>[#{{$trade->id}}]{{$trade->title}} ({{$trade->parent}})</option>
            @endforeach
            </select>
    </div>
    <div class="users-search">
        <label for="ta-search" class="users-label">ТА:</label>
            <select id="ta-search">
            <option value='-1'>все</option>
            <option value='0' @if($tradePerm==0) selected @endif>запрещено</option>
            <option value='1' @if($tradePerm==1) selected @endif>разрешено</option>
            </select>
    </div>
    <div class="users-search">
        <label for="tm-search" class="users-label">МР:</label>
            <select id="tm-search">
            <option value='-1'>все</option>
            <option value='0' @if($storeCheckPerm==0) selected @endif>запрещено</option>
            <option value='1' @if($storeCheckPerm==1) selected @endif>разрешено</option>
            </select>
    </div>
    <div style="width: 100%;max-width: 99%;">
        @if(\Auth::guard('admin')->user()->hasRole('administrator'))
            <a class="btn btn-info" style="padding: 1px 5px;font-size: 12px;" onclick="setGen(0)" href="#">Перегрузить номенклатуру</a>
        @endif
    </div>
    <table id="users-tbl" class="table table-striped table-hover" style="width:100%">
        <thead>
            <tr>
                <th class="sortable @if($order=='id') @if($dir=='asc') sorting_asc @else sorting_desc  @endif @endif" data-sorting='id'>ID</th>
                <th class="sortable @if($order=='login') @if($dir=='asc') sorting_asc @else sorting_desc  @endif @endif" data-sorting='login'>Логин</th>
                <th>Пароль</th>
                <th class="sortable @if($order=='fio') @if($dir=='asc') sorting_asc @else sorting_desc  @endif @endif" data-sorting='fio'>Ф И О</th>
                <th>Место</th>
                <th>Тип цены</th>
                <th>Клиент</th>
                <th>Торговое направление</th>
                <th class="sortable @if($order=='is_locked') @if($dir=='asc') sorting_asc @else sorting_desc  @endif @endif" data-sorting='is_locked'>Блок</th>
                <th>Уст.</th>
                <th>Guid</th>
                <th>Время Логина</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>@if($user->id==666)<a  target="_blank" href="/admin/testuseredit" title="Редактировать">{{$user->id}}</a>@else{{$user->id}}@endif</td>
                <td>{{$user->Login}}</td>
                <td>{{$user->Password}}</td>
                <td id="fio{{$user->id}}">{{$user->fio}}</td>
                <td>{{$user->Area}}</td>
                <td>{{$PriceTypes::getPriceTypesByType($user->PriceTypes)}}</td>
                <td>{{$user->client}}</td>
                <td><a href="/admin/remains?td={{$user->tradeDirection}}">[#{{$user->tradeDirection}}]{{$user->tdTitle}}</a></td>
                @if(\Auth::guard('admin')->user()->hasRole('administrator'))
                    <td><a href='#' class='btn primary' onclick="setLocked({{$user->id}}, '{{$user->fio}}', this)">{{$user->is_locked}}</a></td>
                @else
                    <td>{{$user->is_locked}}</td>
                @endif
                <td><div class="tdguid tdguid-slim r-guid">{{$user->guid}}</div></td>
                <td class="show-hide">{{\Carbon\Carbon::parse($user->login_time)->timezone('Europe/Moscow')->format('d-m-Y H:i:s')}}</td>
                <td>
                    <select class="custom-select" title="Выбор действия" id="user_act" onchange="if(this.value==0) return; if(this.value=='f') getOrderAttr({{$user->id}}, {{$user->tradeDirection}}); else window.open(this.value,'_blank');">
                        <option value="0">Действие</option>
                        <option value="/admin/orders?id={{$user->id}}"><a class="btn primary" target="_blank" href="/admin/orders?id={{$user->id}}" >Заказы</a></option>
                        <option value="/admin/routes?u={{$user->id}}"><a class="btn primary" target="_blank" href="/admin/routes?u={{$user->id}}" >Маршруты</a></option>
                        <option value="f"><a class="btn primary" href="#" onclick="getOrderAttr({{$user->id}}, {{$user->tradeDirection}})">Атрибуты</a></option>
                        <option value="/admin/userevents?id={{$user->id}}"><a class="btn primary" target="_blank" href="/admin/userevents?id={{$user->id}}">События</a></option>
                        <option value="/admin/usertracks?id={{$user->id}}"><a class="btn primary" target="_blank" href="/admin/usertracks?id={{$user->id}}">gps трекинг</a></option>
                        <option value="/admin/userdebts?id={{$user->id}}"><a class="btn primary" target="_blank" href="/admin/userdebts?id={{$user->id}}">Долги ТТ</a></option>
                        <option value="/admin/userorderdebts?id={{$user->id}}"><a class="btn primary" target="_blank" href="/admin/userorderdebts?id={{$user->id}}">Долги заказов</a></option>
                        <option value="/admin/pko?id={{$user->id}}"><a class="btn primary" target="_blank" href="/admin/pko?id={{$user->id}}">ПКО</a></option>
                        <option value="f"><a class="btn primary" target="_blank" onclick="setGen({{$user->id}})" href="#">Перегрузить номенклатуру</a></option>
                    </select>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="users-links" id="users-links"><?=$users->links?></div>
    {{config('loadapi.PGINATE_USERS')}} записей на странице
</div>
<div id="frmAttr"><div id="frmAttrContent"></div></div>
<div id="FrmScheduleReq" title="Запросы режима работы"><div id="frmScheduleReqContent"></div></div>
<div id="FrmSessionsReq" title="Таблица запросов"><div id="frmSessionsReqContent"></div></div>
<div id="frmUserProp" title="свойства пользователя"><div id="frmUserPropContent"></div></div>
<div id="info" class="hide-panel">Последнее обновление Users:<br/><span id="info-lastUpd"></span></div>
<style>
    .users-links::after{
        content: "{{config('loadapi.PGINATE_USERS')}} записей на странице";
    }
</style>
<script>
    var $ = jQuery;
    var tds = [];
    var page = <?=$page?>;
    <?php echo "var isAdmin=" . (\Auth::guard('admin')->user()->hasRole('administrator')?"1":"0") . ";";
          echo "var isModerator=" . (\Auth::guard('admin')->user()->can('calls_manage')?"1":"0") . ";";
          echo "var url_call_center='" . env('URL_CALL_CENTER', 'http://mtagentcalls.intertorg.org/') . "';";
    ?> 
    $(document).ready(function($) {
        // окно для показа атрибутов
        frmAttr = $( "#frmAttr" ).dialog({
            autoOpen: false,
            height: 600,
            width: 500,
            modal: true,
            buttons: {
                
                Close: function() {
                    frmAttr.dialog( "close" );
                }
            },
            close: function() {
                frmAttr.dialog( "close" );
            }
        });
        // окно для показа свойств пользователя
        frmUserProp = $( "#frmUserProp" ).dialog({
            autoOpen: false,
            height: 300,
            width: 300,
            modal: true,
            buttons: {
                
                закрыть: function() {
                    frmUserProp.dialog( "close" );
                }
            },
            close: function() {
                frmUserProp.dialog( "close" );
            }
        });
        
        // окно для показа запросов режима работы
        FrmScheduleReq = $( "#FrmScheduleReq" ).dialog({
            autoOpen: false,
            height: 600,
            width: document.documentElement.clientWidth/2,
            modal: true,
            buttons: {
                
                Close: function() {
                    FrmScheduleReq.dialog( "close" );
                }
            },
            close: function() {
                FrmScheduleReq.dialog( "close" );
            }
        });
        // окно для показа сессий
        FrmSessionsReq = $( "#FrmSessionsReq" ).dialog({
            autoOpen: false,
            height: 300,
            width: document.documentElement.clientWidth*0.9,
            modal: true,
            buttons: {
                
                Close: function() {
                    FrmSessionsReq.dialog( "close" );
                }
            },
            close: function() {
                FrmSessionsReq.dialog( "close" );
            }
        });
        show_full_guid();
        $("#users-search").keyup(function(e){
            getItems(1);
        });
        $("#id-search").keyup(function(e){
            getItems(1);
        });
        $("#td-search").change(function(e){
            getItems(1);
        });
        $("#ta-search").change(function(e){
            getItems(1);
        });
        $("#tm-search").change(function(e){
            getItems(1);
        });
        $("#users-tbl thead tr .sortable").click(function(){
            c = $(this);
            $("#users-tbl thead tr th").removeAttr("data-sort");
            if(c.hasClass("sorting_asc"))
                c.attr("data-sort", "desc");
            else
                c.attr("data-sort", "asc");
            $("#users-order").val(c.attr("data-sorting"));
            $("#users-order-dir").val(c.attr("data-sort"));
            getItems(1);
        });
        
        getItems(page);
    } );
    // Расширить/сузить колонку guid
    function show_full_guid(){
        $(".tdguid").dblclick(function(e){
            if($(this).hasClass("tdguid-slim")){
                $(".tdguid").removeClass("tdguid-slim");
                $(".tdguid").addClass("tdguid-wide");
            }else{
                $(".tdguid").removeClass("tdguid-wide");
                $(".tdguid").addClass("tdguid-slim");
            }
        });
    }
    // Получает аттрибуты для торгового направления привязанного к пользователю
    function getOrderAttr(id, td_id){
        frmAttr.dialog( "option", "title", $("#fio"+id).html() );
        data = {
            td: td_id,
        }
        $.ajax({
	        url: '/admin/orderattributesajax',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                td = "<ul>";
                b = $('#frmAttrContent');
                b.empty();
                if(data.length)
                data.forEach(function(item, index, ar){
                    td += "<li><span class='td-id'>[#"+item.id+"]</span>&nbsp;<span class='td-title'>"+item.title+"</span>&nbsp;&laquo;<span class='td-value'>"+item.value+"</span>&raquo;</li>";
                    
                    if(index==(ar.length-1)){
                        b.append(td);
                    }
                });
                else{
                    b.html('нет атрибутов');
                }
                frmAttr.dialog( "open" );
	        }
        });
    }
    /**
    Получает строки для таблицы пользователей
     */
    function getItems(page){
        data = {
            page: page, 
            f: $("#users-search").val(),
            id: $("#id-search").val(),
            td: $("#td-search").val(),
            tradePerm: $("#ta-search").val(),
            storeCheckPerm: $("#tm-search").val(),
            order: $("#users-order").val(),
            dir: $("#users-order-dir").val(),
        }
        $.ajax({
	        url: '/admin/usersajax',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                tr = "";
                b = $('#users-tbl tbody');
                if(data.users.data.length)
                        data.users.data.forEach(function(item, index, ar){
                        client="";
                        clients = "";
                        login_time = "";
                        if(item.sessions.length>0){
                            client = item.sessions[0].client;
                            d = (new Date(item.sessions[0].login_time*1000));
                            login_time = (d.getDate()>9 ? d.getDate() : "0"+d.getDate())+"-"+
                            ((1+d.getMonth())>9 ? (1+d.getMonth()) : "0"+(1+d.getMonth()))+"-"+d.getFullYear()+" "+
                            (d.getHours()>9 ? d.getHours() : "0"+d.getHours())+":"+
                            (d.getMinutes()>9 ? d.getMinutes() : "0"+d.getMinutes())+":"+
                            (d.getSeconds()>9 ? d.getSeconds() : "0"+d.getSeconds());
                        }
                        prop = "<table>";
                        prop += "<tr><td>tradePerm</td><td>" + item.tradePerm + "</td></tr>";
                        prop += "<tr><td>storeCheckPerm</td><td>" + item.storeCheckPerm + "</td></tr>";
                        prop += "<tr><td>f2percent</td><td>" + item.f2percent + "</td></tr>";
                        prop += "<tr><td>f2time</td><td>" + item.f2time + "</td></tr>";
                        prop += "</table>";
                        tr += "<tr><td>" + (item.test==1?"<a  target='_blank' href='/admin/testuseredit?userId="+item.id+"' title='Редактировать'>"+item.id+"</a>":item.id) + 
                        "</td><td>" + item.Login + 
                        "</td><td>" + item.Password + 
                        "</td><td id='fio" + item.id + "'>" + item.fio + 
                        "</td><td>" + item.Area + 
                        "</td><td>" + item.PriceType +
                        "</td><td>" + client + 
                        "</td><td>" +
                        ( "<a href='/admin/remains?td=" + item.tradeDirection + "'>[#" + item.tradeDirection + "]" + item.tdTitle + "</a>" ) +
                        "</td><td>" + 
                        ( isAdmin===1 ? "<a href='#' class='btn primary' onclick=\"setLocked(" + item.id + ", '" + item.fio + "', this)\">" + item.is_locked + "</a>" : item.is_locked ) +
                        "</td>"+
                        "<td>"+(isAdmin===1 ?"<a href='#' class='btn primary' onclick=\"setDeviceCheck(" + item.id + ", '" + item.fio + "', this)\">" + item.checkDevice + "</a>": item.checkDevice )+"</td>"+
                        "<td><div class='tdguid tdguid-slim r-guid'>" + item.guid + "</div>" +
                        "</td><td data-updatetm='" + item.updateTm + "' class='show-hide'>" + login_time + "</td><td>"+
                        "<select class='custom-select' title='Выбор действия' id='user_act' onchange=\"if(this.value==0) return; if(this.value=='f') getOrderAttr(" + item.id + ", " + item.tradeDirection + "); else if(this.value=='g') setGen(" + item.id + ", '" + item.fio + "'); else if(this.value=='gr') getScheduleReq(" + item.id + "); else if(this.value=='sess') getSessionsReq(" + item.id + "); else if(this.value=='props') frmUserProp.dialog( 'open' ).html('"+prop+"'); else window.open(this.value,'_blank'); this.value='0';\">" +
                        "<option value='0'>Действие</option>" + 
                        (item.tradePerm > 0 ? "<option value='/admin/orders?id=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/orders?id=" + item.id + "' >Заказы</a></option>" :"") +
                        "<option value='/admin/routes?u=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/routes?u=" + item.id + "' >Маршруты</a></option>" +
                        "<option value='f'><a class='btn primary' href='#' onclick='getOrderAttr(" + item.id + ", " + item.tradeDirection + ")'>Атрибуты</a></option>" +
                        "<option value='/admin/userevents?id=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/userevents?id=" + item.id + "'>События маршрута</a></option>" +
                        "<option value='/admin/usertracks?id=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/usertracks?id=" + item.id + "'>Таблица gps трекинга</a></option>" +
                        "<option value='/admin/userdebts?id=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/userdebts?id=" + item.id + "'>Долги ТТ</a></option>" +
                        "<option value='/admin/userorderdebts?id=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/userorderdebts?id=" + item.id + "'>Долги заказов</a></option>" +
                        "<option value='/admin/pko?id=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/pko?id=" + item.id + "'>ПКО</a></option>" +
                        ( isAdmin===1 ? "<option value='g'><a class='btn primary' onclick=\"setGen(" + item.id + ", '" + item.fio + "')\" href='#'>Перегрузить номенклатуру</a></option>" : "" ) +
                        "<option value='gr'><a class='btn primary' href='#' onclick='getScheduleReqs(" + item.id + ")'>Графики работы ТТ</a></option>" +
                        (item.storeCheckPerm > 0 ? "<option value='/admin/storechecks?id=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/storechecks?id=" + item.id + "' >Заказы мерчендайзера</a></option>" :"") +
                        "<option value='/admin/logs?userId=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/logs?userId=" + item.id + "'>Логи вызова функций</a></option>" +
                        "<option value='/admin/newroutereq?userId=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/newroutereq?userId=" + item.id + "'>Заявки на маршрут</a></option>" +
                        "<option value='/admin/usermap?userId=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/usermap?userId=" + item.id + "'><b>КАРТА GPS-ТРЕКИНГА</b></a></option>" +
                        ( true ? "<option value='sess'><a class='btn primary' href='#' onclick='getSessionsReqs(" + item.id + ")'>Сессии</a></option>" : "") +
                        "<option value='/admin/reportevents?userId=" + item.id + "'><a class='btn primary' target='_blank' href='/admin/reportevents?userId=" + item.id + "'><b>Отчет GPS-ТРЕКИНГА</b></a></option>" +
                        ( true ? "<option value='props'><a class='btn primary' href='#' >Свойства</a></option>" : "") +
                        ( isModerator ? "<option value='" + url_call_center + "calls/create?userId=" + item.id + "'><a target='_blank' class='btn primary' href='http://callcenter.loc/calls/create?userId=" + item.id + "' >Создать звонок</a></option>" : "") +
                        "</select>" +
                        "</td></tr>";
                        
                        if(index==(ar.length-1)){
                            b.empty();
                            b.append(tr);
                            $("#users-links").html(data.links);
                            
                            $("#users-tbl thead tr th").removeClass("sorting_asc");
                            $("#users-tbl thead tr th").removeClass("sorting_desc");
                            $("#users-tbl thead tr th[data-sort]").addClass("sorting_"+$("#users-tbl thead tr th[data-sort]").attr("data-sort"));
                            show_full_guid();
                            show_hide_info();
                        }
                });
                else{
                    b.empty();
                    $("#users-links").html('');
                }
	        }
        });
    }
    /**
    * Устанавливает/снимает блокировку пользователя
    */
    function setLocked(id, fio, o){
        if(o.innerHTML=="0"){
            msg = "Установить блокировку на торгового агента [#" + id + "]" + fio + " ?";
            block = 1;
        }
        else{
            msg = "Снять блокировку на торгового агента [#" + id + "]" + fio + " ?";
            block = 0;
        }
        if( !confirm(msg) )
            return;
        data = {
            id: id,
            block: block,
        }
        $.ajax({
	        url: '/admin/userlock',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                if(data.res > 0)
                    o.innerHTML = block;
                else
                    alert("Изменить блокировку на торгового агента [#" + id + "]" + fio + " неудалось!");
	        },
            error: function(request, status, error){
                console.error(request.responseText);
                console.error(error);
            }
        });
    }
    /**
    * Устанавливает/снимает ограничение используемых устройств у пользователя
    */
    function setDeviceCheck(id, fio, o){
        if(o.innerHTML=="0"){
            msg = "Установить ограничение используемых устройств на торгового агента [#" + id + "]" + fio + " ?";
            block = 1;
        }
        else{
            msg = "Снять ограничение используемых устройств на торгового агента [#" + id + "]" + fio + " ?";
            block = 0;
        }
        if( !confirm(msg) )
            return;
        data = {
            id: id,
            block: block,
        }
        $.ajax({
	        url: '/admin/userDeviceCheck',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                if(data.res > 0)
                    o.innerHTML = block;
                else
                    alert("Изменить ограничение используемых устройств на торгового агента [#" + id + "]" + fio + " неудалось!");
	        },
            error: function(request, status, error){
                console.error(request.responseText);
                console.error(error);
            }
        });
    }
    /**
     * Устанавливает(увеличивает на 1) поле gen для выбранного пользователя, если id=0 то для всех
     */
    function setGen(id, fio){
        if(id>0)
            msg = "Установить поле gen на торгового агента [#" + id + "]" + fio + " ?";
        else
            msg = "Установить поле gen на всех торговых агентов ?";
        if( !confirm(msg) )
            return;
        data = {
            id: id,
        }
        $.ajax({
	        url: '/admin/usergeneretion',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                if(data.res <= 0)
                    alert("Изменить gen торгового агента [#" + id + "]" + fio + " неудалось!");
	        },
            error: function(request, status, error){
                console.error(request.responseText);
                console.error(error);
            }
        });
    }
    
    /**Получить запросы графиков для точек продажи сделанные выбраным пользователем */
    function getScheduleReq(userId){
        theah = "<table width='100%' class='table table-bordered table-hover tbl-reg'>" +
                "<thead>" +
                "<tr>" +
                    "<th>ID</th>" +
                    "<th>Тип</th>" +
                    "<th>День</th>" +
                    "<th>График</th>" +
                    "<th>Обновление</th>" +
                "</tr>" +
                "</thead>" +
                "<tbody>";
        tend = "</tbody></table>";
        data = {
            userId: userId,
        }
        $.ajax({
	        url: '/admin/scheduleRequestsUserAjax',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
            success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                tbl = $("#frmScheduleReqContent");
                t = "";
                tp = 0;
                clientId = 0;
                if(data.reqs.length)
                    data.reqs.forEach(function(item, index, ar){
                        if( index==0 || tp!=item.tpId ){
                            if(index)
                                t += tend;
                            if(clientId!=item.clientId){
                                t += "<div class='client-title'>[#" + item.clientId + "]" + item.client + "</div>";
                                clientId=item.clientId;
                            }
                            t += "<div class='tpoint-title' show='0'><span>+</span>&nbsp;[#" + item.tpId + "]" + item.trade_point + "</div>" + theah;
                            tp = item.tpId;
                        }
                        schedule = String(item.schedule);
                        if(schedule.length<8)
                        schedule = "0"+schedule;
                        schedule = schedule[0]+schedule[1]+":"+schedule[2]+schedule[3]+"-"+schedule[4]+schedule[5]+":"+schedule[6]+schedule[7];
                        t += "<tr><td>" + item.id + 
                        "</td><td>" + item.title +
                        "</td><td>" + getDayOfWeek(item.dayOfWeek) +
                        "</td><td>" + schedule + 
                        "</td><td>" + item.updateTm + "</td></tr>";
                        if(index==(ar.length-1)){
                            t += tend + "</div>";
                            tbl.empty();
                            tbl.append(t);
                            setEventPoint();
                            FrmScheduleReq.dialog( "open" );
                        }
                    });
	        },
            error: function(request, status, error){
                console.error(request.responseText);
                console.error(error);
            }
        });
    }
    /** дни недели */
    function getDayOfWeek(d){
        days = ["Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"];
        if(d>=0 && d<=6)
            return days[d];
        else
            return "Ош";
    }
    /**Установить событие открыть/закрыть  */
    function setEventPoint(){
        $(".tpoint-title").click(function(e){
            if(this.attributes.show.value==0){
                this.attributes.show.value=1;
                $(this).next().css("display", "table");
                $(this).find("span").html("-");
            }else{
                this.attributes.show.value=0;
                $(this).next().css("display", "none");
                $(this).find("span").html("+");
            }
        });
    }
    /** получить выпадающий список для торговых направлений */
    function getIds(id, userId, fio){
        str_tds = "<select id='tds-" + userId + "' onchange='setTd(" + id + "," + userId + ",\"" + fio + "\")'><option value='0'>Выбор не сделан</option>";
        tds.forEach(function(item, index, ar){
            str_tds += "<option value='" + item.id + "'" + 
            (item.id===id ? "selected" : "" ) + ">" +
            "[#" + item.id + "]" + item.title +
            "</option>"; 
            if(index==(ar.length-1)){
                str_tds += "</select>";
            }
        });
        return str_tds;
    }
    /**
     * Устанавливает торговое напрвление для выбранного пользователя 
     */
    function setTd(tdIdold, userId, fio){
        tdId = $("#tds-"+userId).val();
        msg = "Установить торговое направление на торгового агента [#" + userId + "]" + fio + " ?";
        if( !confirm(msg) ){
            $("#tds-"+userId).val(tdIdold);
            return;
        }
        data = {
            userId: userId,
            tdId: tdId
        }
        $.ajax({
	        url: '/admin/usertdset',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                if(data.res <= 0)
                    alert("Изменить торговое направление торгового агента [#" + userId + "]" + fio + " неудалось!");
	        },
            error: function(request, status, error){
                console.error(request.responseText);
                console.error(error);
            }
        });
    }
    <?php 
        $json = json_encode($trades);
    ?>
    tds = <?=$json?>;
    /**Получить сессии для выбранного пользователя */
    function getSessionsReq(userId){
        t = "<table width='100%' class='table table-bordered table-hover '>" +
                "<thead>" +
                "<tr>" +
                "<th>ID</th>" +
                "<th>Устройство</th>" +
                "<th>ID Устройства</th>" +
                "<th>Версия</th>" +
                "<th>Время логина</th>" +
                "<th>Обновление</th>" +
                "</tr>" +
                "</thead>" +
                "<tbody>";
        tend = "</tbody></table>";
        data = {
            userId: userId,
        }
        $.ajax({
	        url: '/admin/UserSessionsAjax',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
            success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                tbl = $("#frmSessionsReqContent");
                if(data.sessions.length)
                    data.sessions.forEach(function(item, index, ar){
                        t += "<tr><td>" + item.id + 
                        "</td><td>" + item.deviceName +
                        "</td><td>" + item.deviceId +
                        "</td><td>" + item.client +
                        "</td><td>" + item.login_time + 
                        "</td><td>" + item.updateTm + "</td></tr>";
                        if(index==(ar.length-1)){
                            t += tend ;
                            tbl.empty();
                            tbl.append(t);
                            FrmSessionsReq.dialog( "open" );
                        }
                    });
	        },
            error: function(request, status, error){
                console.error(request.responseText);
                console.error(error);
            }
        });
    }
    /** */
    function show_hide_info(){
        $(".show-hide").on("mouseover", function(e){
            inf = $('#info');
            var s = $(this).attr('data-updatetm');
            $('#info-lastUpd').html( s );
            y = e.pageY+10;
            if(y - $(document).scrollTop() + inf.height() > $(window).height())
                y = y - inf.height();
            inf.offset ( {
                left: e.pageX+10,
                top: y
            });
            inf.show();
        }).on("mouseout", function(e){
            inf = $('#info');
            inf.offset ( { left: 0, top: 0 } );
            inf.hide();
        });
    }
</script>

@endsection
