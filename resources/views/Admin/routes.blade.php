@extends('layouts.app')

@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script> 
<link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/routes.css') }}" rel="stylesheet">

<div class="container">
    <div class="btn-back"><a class="btn primary" href="/admin/users" >&#8679;Пользователи</a></div>
    <h2>Маршрут</h2>
    <h2>[#{{$user->id}}] {{$user->fio}} </h2>
    <div>
        Сегодня:&nbsp;
        <span class="route-top-data">{{date("d-m-Y")}}</span>
        <span class="route-top-day">@if($routes and count($routes)){{$routes[0]->dayOfWeek(date("N")-1)}}@endif
        </span><span class="route-top-d-of-week">({{(int)(getDayForRoute(date("j"))/7)+1}})</span>
    </div>
    <ul class="list-group">
            @foreach($routes as $route)
            <?php $day = getDayForRoute($route->dayOfWeek); 
                  $tps = explode(",", $route->points);
                  $tpout = "";
                  $errtp = 0;
                  $locked = 0;
                  foreach($tps as $tp){
                    if(array_key_exists($tp, $trade_points)){
                        if($trade_points[$tp]->t_is_locked || $trade_points[$tp]->c_is_locked)
                            $locked++;
                        
                        $tpout .= "<tr>" . 
                            "<td ".($trade_points[$tp]->c_is_locked ? "class='item-block'":"").">[#".$trade_points[$tp]->client_id."]" . $trade_points[$tp]->client . ($trade_points[$tp]->c_is_locked? " (блок)":"")."</td>" .
                            "<td ".($trade_points[$tp]->t_is_locked ? "class='item-block'":"").">[#".$trade_points[$tp]->id."]" . $trade_points[$tp]->trade_point . ($trade_points[$tp]->t_is_locked? " (блок)":"")."</td>" .
                        "</tr>";
                    }else{
                        $errtp++;
                        $tpout .= "<tr>" .
                            "<td>$tp</td>" .
                            "<td>торговая точка не найдена</td>" .
                            "<td>торговая точка не найдена</td>" .
                        "</tr>";
                    }
                    
                  }
            ?>
            <li class="list-group-item">
                <div class="r-week">{{$route->dayOfWeek($route->dayOfWeek)}} ({{(int)($route->dayOfWeek/7)+1}})&nbsp;<span class="badge badge-primary badge-pill">{{(strlen($route->points) ? substr_count($route->points,",")+1 : 0) }} точек</span>
                  @if($errtp > 0)<span class="badge badge-danger badge-pill">{{$errtp}} точек не найдено</span>@endif
                  @if($locked > 0)<span class="badge badge-danger badge-pill">{{$locked}} точек(клиентов) заблокировано</span>@endif
                  <span>{{$route->updateTm}}</span>
                  &nbsp;[dayOfWeek={{$route->dayOfWeek}}]
                  @if($route->dayOfWeek($route->dayOfWeek)=="Вс")<hr/>@endif
                </div>
                <div class="r-route">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Клиент</th>
                                <th>Наименование</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?=$tpout?>
                        </tbody>
                    </table>
                </div>
            </li>
            @endforeach
    </ul>
</div>

<script>
    var $ = jQuery;
    jQuery(document).ready(function($) {
       $('.r-week').click(function(e){
           var c = $(this).next("div");
           if( c.css("display") == "none" )
            c.css("display", "block");
           else
            c.css("display", "none");
       });
        
    } );
    
</script>

@endsection
<?php

function getDayForRoute($dayOfWeek){
    $start_of_week = 0;
    $week_of_year = date("W");
    $day_of_week = $dayOfWeek;
    return (($day_of_week-$start_of_week+6)%7)+(($week_of_year+3)%4)*7;
}
?>