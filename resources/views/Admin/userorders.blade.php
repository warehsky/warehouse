@extends('layouts.app')

@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/datatables.min.js') }}"></script> 
<link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/orders.css') }}" rel="stylesheet">

<div class="container">
@if(isset($user))
    <div class="btn-back"><a class="btn primary" href="/admin/users" >&#8679;Пользователи</a></div>
@endif
        <div class="oder-title">
            @if(isset($user))
                <span class="oder-label">Заказы пользователя:</span>
                <span class="oder-user-id">[#{{$user->id}}]</span>
                <span class="oder-user-fio">{{$user->fio}}</span>
            @else
                <span class="oder-label">Заказы:</span>
            @endif
        </div>
    <table id="orders-tbl" class="table table-striped table-hover" style="width:100%">
        <thead>
            <tr>
                <th>ID</br><form id="frm_order_id" method='POST'><input id="order_id" name="order_id" value="@if(isset($order_id)){{$order_id}}@endif"><input type="hidden" name="id" value="@if(isset($user)){{$user->id}}@endif"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form></th>
                <th>№ 1С</br><form id="frm_order_number" method='POST'><input id="order_number" name="order_number" value="@if(isset($order_number)){{$order_number}}@endif"><input type="hidden" name="id" value="@if(isset($user)) {{$user->id}}@endif"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form></th>
                <th>Дата</th>
                <th>Тип кон.</th>
                <th>Клиент</br><form id="frm_order_client" method='POST'><input id="order_client" name="order_client" value="@if(isset($order_client)){{$order_client}}@endif"><input type="hidden" name="id" value="@if(isset($user)) {{$user->id}}@endif"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form></th>
                <th>Торговая Точка</br><form id="frm_order_point" method='POST'><input id="order_point" name="order_point" value="@if(isset($order_point)){{$order_point}}@endif"><input type="hidden" name="id" value="@if(isset($user)) {{$user->id}}@endif"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form></th>
                <th>LAT/LNG</th>
                <th>Сумма</br><form id="frm_order_summa" method='POST'><input id="order_summa" name="order_summa" value="@if(isset($order_summa)){{$order_summa}}@endif"><input type="hidden" name="id" value="@if(isset($user)) {{$user->id}}@endif"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form></th>
                <th>Коментарий</th>
                <th>Атрибуты</th>
                <th>Пользователь</br><form id="frm_order_user" method='POST'><input id="order_user" name="order_user" value="@if(isset($order_user)){{$order_user}}@endif"><input type="hidden" name="id" value="@if(isset($user)) {{$user->id}}@endif"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form></th>
                <th>Дополнительно</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr class="order-head">
                <td>{{$order->id}}</td>
                <td>{{$order->number}}</td>
                <td class="orderDates" data-dates="<p>Дата создания:{{$order->date_time_created}}</p><p>Дата доставки:{{$order->date_time_shipment}}</p><p>Дата обновления:{{$order->updateTm}}</p>">{{$order->updateTm}}</td>
                <td>{{$order->contractType}}</td>
                <td>[#{{$order->clientId}}]{{$order->client}}</td>
                <td>[#{{$order->trade_point_id}}]{{$order->trade_point}}</td>
                <td>{{$order->coord_created_lat}}<br>{{$order->coord_created_lng}}</td>
                <td>{{$order->sum_total}}</td>
                <td>{{$order->note}}</td>
                <td class="text-center"><?=str_replace(", ", "<br/>", $order->attributes)?></td>
                <td>[#{{$order->user_id}}]{{$order->fio}}</td>
                <td>
                    <button type="button" class="btn btn-info btnTovar" style="padding: 3px;font-size: 12px;">Товары</button>
                    @if($order->invoices)
                        <a class="btn btn-primary " style="padding: 3px;font-size: 12px;" href="#" data-invoice="{{$order->id}}" 
                        onclick="frmInvoices.dialog({title: 'Накладные для заказа №{{$order->id}}  ( {{$order->number}} )'}).dialog( 'option', 'id', {{$order->id}} ).dialog( 'open' )">Накладные</a>
                    @else
                        <a class="btn btn-primary disabled" href="#" style="padding: 3px;font-size: 12px;">Накладные</a>
                    @endif
                </td>
            </tr>
            <tr >
                <td class="order-items" colspan="12">
                    <table width="100%" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Товар</th>
                                <th>Кол-во</th>
                                <th>Цена</th>
                                <th>Тип</th>
                                <th>%</th>
                                <th>Guid</th>
                            </tr>
                        </thead>
                        <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>[#{{$item->item_id}}]{{$item->items->item}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{$item->price}}</td>
                        <td>{{$item->priceTypes->title}}</td>
                        <td>{{$item->percent}}</td>
                        <td>{{$item->guid}}</td>
                    </tr>
                @endforeach
                        </tbody>
                    </table>
                </td>
</tr>
            @endforeach
        </tbody>
        
    </table>
      <div class="orders-links"><?=(is_array($orders) ? "" : str_replace("page=", (isset($user)?"id=$user->id&":"")."page=", $orders->links()))?></div>  
</div>
<div id="orderDates"></div>
<div id="frmInvoices" title="Накладные"><div id="frmInvoicesContent"></div></div>
<script>
    var $ = jQuery;
    jQuery(document).ready(function($) {
        $('#orderDates').hide();
        $('.btnTovar').click(function(e){
            var o = $(this).parent().parent().next('tr').find('.order-items');
            if(o.css('display')=='table-cell')
                o.css('display', 'none');
            else
                o.css('display', 'table-cell');
        }); 
        $(".orderDates").mouseover(function(e){
            inf = $('#orderDates');
            d = $(this).attr('data-dates');
            inf.html( d );
            inf.offset ( {
                left: e.pageX,
                top: e.pageY
            });
            inf.show();
        });
        $(".orderDates").mouseout(function(e){
            inf = $('#orderDates');
            inf.html( '' );
            inf.offset ( { left: 0, top: 0 } );
            inf.hide();
        });
        $("#order_number").focusout(function(e){ $("#frm_order_number").submit(); });
        $("#order_id").focusout(function(e){ $("#frm_order_id").submit(); });

        // окно для показа накладных
        frmInvoices = $( "#frmInvoices" ).dialog({
            autoOpen: false,
            height: 500,
            width: $('body').width()*0.8,
            modal: true,
            buttons: {
                
                закрыть: function() {
                    frmInvoices.dialog( "close" );
                }
            },
            close: function() {
                frmInvoices.dialog( "close" );
            },
            open: function(){
                getInvoices($(this).dialog('option', 'id'));
            }
        });

    } );
    function getInvoices(id){
        data = {
            orderId: id,
        }
        $.ajax({
	        url: '/admin/invoicesAjax',         /* Куда пойдет запрос */
	        method: 'get',             /* Метод передачи (post или get) */
	        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
	        data: data,     /* Параметры передаваемые в запросе. */
	        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
                b = $('#frmInvoicesContent');
                b.empty();
                td = "";
                if(data.length)
                data.forEach(function(item, index, ar){
                    td += "<table>";
                    td += "<tr>";
                    td += "<th>" + item.id + "</th>";
                    td += "<th>" + item.number + "</th>";
                    td += "<th>" + item.status + "</th>";
                    td += "<th>" + item.note + "</th>";
                    td += "</tr>";
                    td += "</table>";
                    td += "<div class='invoiceitems-tbl'><table>";
                    td += "<th>ID</th>";
                    td += "<th>Наименование</th>";
                    td += "<th>Кол-во</th>";
                    td += "<th>Цена</th>";
                    td += "<tbody>";
                    item.invoice_items.forEach(function(item, index, ar){
                        td += "<tr>";
                        td += "<td>" + item.id + "</td>";
                        td += "<td>" + item.items.item + "</td>";
                        td += "<td>" + item.quantity + "</td>";
                        td += "<td>" + item.price + "</td>";
                        td += "</tr>";
                        
                        if(index==(ar.length-1)){
                            td += "</tbody></table></div>";
                        }
                    });
                    if(index==(ar.length-1)){
                        b.append(td);
                    }
                });
                else{
                    b.html('нет накладных');
                }
	        }
        });
    }
</script>
@endsection
