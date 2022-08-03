@extends('layouts.app')

@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<link href="{{ asset('css/tdirections.css') }}" rel="stylesheet">

<div class="container">
    <div class="row justify-content-left">
        <div id="td-selected" class="col-md-6 td-name-active">
            
            
                <form action="{{route('reportevents')}}" method="GET">
                    <span class="groupId">#{{$tdId}}</span>&nbsp;<span class="td-name">{{$tdname}}</span>&nbsp;
                    <span class="report-date">{{$d}}</span>&nbsp;
                    <input type="hidden" name="d" id="d" value="{{$d}}">
                    <input type="hidden" name="tdId" id="tdId" value="{{$tdId}}">
                    <input type="hidden" name="userId" id="userId" value="{{$userId}}">
                    <input type="submit" value="Сформировать отчет" class="btn">
                    <a href="#" class="btn btn-info" id="btn_flt">изменить фильтр</a>
                </form>
            
            <div id="msg" class="text-success"></div>
        </div>
    </div>
    <div class="row justify-content-left" id='flt_panel'>
        <div class="col-md-5 td-items">
            <ul id="td-groupid_0" class="list-group">
                <?php foreach($groups as $group):?>
                <li class="list-group-item">
                    <div id="{{$group->id}}">
                    @if(true) <span class="wr-group plus">+</span> @else <span class="no-group">&nbsp;</span> @endif
                    &nbsp;&nbsp;&nbsp;<span class="groupId">[#{{$group->id}}]</span>&nbsp;&nbsp;<span class="td-name" >{{$group->title}}&nbsp;</span><span class="badge badge-primary badge-pill">{{$group->attrs}}</span>
                    </div>
                </li>
                <?php endforeach ?>
            </ul> 
        </div>
        <div class="col-md-3">
            <label for="report-date">Дата</label>
            <div ><input id="report-date" name="report-date" type="date" value="{{$d}}"></div>
            
        </div>
        
    </div>
    <div id="report">
        <table width='100%' class='table table-bordered '>
            <th>
            ФИО
            </th>
            <th>
            Маршрут нач.
            </th>
            <th>
            Маршрут кон.
            </th>
            <th>
            1-я ТТ
            </th>
            <th>
            последняя ТТ
            </th>
            <th>
            обман gps/события
            </th>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{$u->fio ?? ''}}</td>
                    <td>{{$u->RouteStart ?? ''}}</td>
                    <td>{{$u->RouteFinish ?? ''}}</td>
                    <td>{{$u->VisitStart ?? ''}}</td>
                    <td>{{$u->VisitFinish ?? ''}}</td>
                    <td>{{$u->fake_gps ?? ''}} / {{$u->fake_ev ?? ''}}</td>
                </tr>
                <tr>
                <td colspan="6">
                    <div>
                <table width='100%' class='table table-bordered table-hover'>
                    <th>
                    Клиент
                    </th>
                    <th>
                    адрес ТТ
                    </th>
                    <th>
                    начало
                    </th>
                    <th>
                    конец
                    </th>
                    <th>
                    длительность
                    </th>
                    <th>
                    батарея
                    </th>
                    <th>
                    действия
                    </th>
                    <tbody>   
                        <?php $visited = [];?>
                @foreach($u->events as $e)
                <?php $c = new \Carbon\Carbon($e->createTm); 
                    if($e->rEnd)
                        $dif = round($c->diffInSeconds(new \Carbon\Carbon($e->rEnd))/60);
                    else
                        $dif = "";
                    if(!in_array($e->tradePointId, $u->tps))
                        $al = "text-danger";
                    else{
                        $al = "";
                        $visited[] = $e->tradePointId;
                    }
                ?>
                    <tr>
                        <td class="{{$al}}">[#{{$trade_points[$e->tradePointId]->client_id}}] {{$trade_points[$e->tradePointId]->client}}</td>
                        <td >{{$trade_points[$e->tradePointId]->address}}</td>
                        <td >{{$e->createTm}}</td>
                        <td >{{$e->rEnd ?? 'нет'}}</td>
                        <td >{{$dif}}</td>
                        <td @if($e->battery<15) class="text-danger"@endif>{{$e->battery}}</td>
                        
                        <td><a href="{{route('usermap', 
                            ['userId' => $u->id, 'd' => $d, 'info-tp' => $e->tradePointId, 
                            'tstart' => (new \Carbon\Carbon($e->createTm))->subMinutes(10)->format('h'),
                            'tend' => (new \Carbon\Carbon( ($e->rEnd ?? $e->createTm) ))->addMinutes(10)->format('H')
                            ])}}" class="btn btn-info" id="btn_flt" target="_blank">карта</a></td>
                    </tr>
                @endforeach
                @foreach($u->tps as $t)
                    @if(!in_array($t, $visited))
                    <tr>
                        <td>[#{{$trade_points[$t]->client_id}}] {{$trade_points[$t]->client}}</td>
                        <td>[#{{$trade_points[$t]->id}}] {{$trade_points[$t]->trade_point}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
                </table>
                <div>посетил <?=(round(count($visited)/count($u->tps), 2)*100)?>% торговых точек на маршруте
                </div>
                </div>
                </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    var $ = jQuery;
    jQuery(document).ready(function($) {
        $("#td-groupid_0 div").on("click", itemWrap);
        $('#btn_flt').click(function() { 
            let o = $('#flt_panel');
            if(o.is(":visible"))
                o.hide();
            else
                o.show(); 
        });
        if(!{{$tdId}} && !{{$userId}}){
            $("#td-selected").hide();
            $('#flt_panel').show();
        }else{
            $('#flt_panel').hide();
        }
        
        $('#report-date').change(function(){
            $('.report-date').html($('#report-date').val());
            $('#d').val($('#report-date').val());
        });
    } );
    function itemWrap(){
        sel = $("#td-selected");
        sel.find(".groupId").html( $(this).find(".groupId").html() );
        sel.find(".td-name").html( $(this).find(".td-name").html() );
        $('#tdId').val( this.id );
        $("#btn-reload-items").attr("data-tradedirection", this.id);
        $("#btn-redirect-users").attr("href", "/admin/users?td=" + this.id);
        $("#td-selected").show();
        gr = $('#group_' + this.id);
            if( gr.length > 0 ){
                gr.remove();
                $(this).find('.wr-group').html('+');
            }
            else{
                getTdChilds(this.id);
                $(this).find('.wr-group').html('-');
                $("#td-groupid_0 div").removeClass('td-name-active');
                $(this).addClass('td-name-active');
            }
    }
    
    /**
    * Получить подгруппы и атрибуты группы
    */
    function getTdChilds(subgroup){
        data = {
            group: subgroup 
        }
        $.ajax({
	        url: '/admin/tdirectionsajax',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                li = "<ul id='group_" + subgroup + "'>";
                //вывод подгрупп
                data.td.forEach(function(item, index, ar){
                    li += "<li>";
                    li += "<div id='" + item.id + "'>";
                    if((item.childs)>0){
                        li += '<span class="wr-group plus">+</span>';
                    }else{
                        li += '<span class="no-group">&nbsp;&nbsp;&nbsp;</span>';
                    }
                    li += "&nbsp;&nbsp;&nbsp;<span class='groupId'>[#" + item.id + "]</span>&nbsp;&nbsp;<span class='td-name' >" + item.title + "&nbsp;</span><span class='badge badge-primary badge-pill'>" + item.attrs + "</span>";
                    li += "</div></li>";
                    if(index==(ar.length-1)){
                        li += "</ul>";
                        b = $('#'+subgroup).parent();
                        b.append(li);
                        $("#group_" + subgroup + " div").on("click", itemWrap);
                    }
                });
                
                
                
	        }
        });
    }
</script>

@endsection
