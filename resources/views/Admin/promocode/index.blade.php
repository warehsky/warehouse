@extends('layouts.admin')
@section('content')

@if(auth()->guard('admin')->user()->can('Promocode_edit'))
    <a href="{{ route('promocode.pageCreate') }}" style='margin-bottom:10px' class='btn btn-success btn-xs'>Создать промокод</a>
    <a href="{{ route('promocode.createManyCard') }}" style='margin-bottom:10px' class='btn btn-success btn-xs'>Дисконтная карта</a>
@endif

<p style='margin-bottom:0px'>Фильтры:</p>
<div style="margin-bottom:5px">
Тип: 
    <select class='selectCard' id="selectType">
        <option value="0" selected>Дисконтные карты</option>
        <option value="1">Промокоды</option>
        <option value="2">Акция "Приведи друга"</option>
        <option value="3">Постоянные карты</option>
        <option value="5">Сертификаты</option>
        <option value="4">Все</option>
    </select>
Статус: 
    <select class='selectCard' id="selectStatus">
        <option value="0" selected>Активные</option>
        <option value="1">Ожидающие</option>
        <option value="2">Использованные</option>
        <option value="3">Деактивированные</option>
        <option value="4">Просроченные</option>
        <option value="5">Все</option>
    </select>
Дата с:  
    <input type="date" id="dateStart">    
По:  
    <input type="date" id="dateEnd">    
    <button id='asseptFilters' class='btn-xs btn-info'>Применить</button>
    <button id='removeFilters' class='btn-xs btn-info'>Очистить фильтры</button>
</div>

<div class="card">
    <div class="card-header"> 
        <span>Промокоды</span> 
        <span style="position: absolute; top:0; right:0; margin-right:10px; margin-top:10px" id='totalSum'></span> 
         
    </div>

    <div class="card-body" style = 'font-size: 11pt;'>

    <div class="banner-box" style="text-align: center;">
        <span id='loader'><img src="/img/loader.gif"  width="50" height="50"></span>
    </div>

        <table id='dataTable' style='margin:0px; width:100%' class="ui celled table stripe">
           
        </table>
    </div>
</div>

<style>
p
{
    margin:0;   
}
.banner-box {
    position: relative;
}
.unShow
{
    display:none;
}
</style>
@endsection
@section('scripts')
@parent
<script>


function fetch_data()
{
    //loader
    // $('#loader').html();
    // $('#loader').html(' <img src="/img/loader2.gif"  width="30" height="30">');

    $.ajax({

        beforeSend: function() {
            $('#loader').show();
        },
        // complete: function() {
            
        // },
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                selectType : $('#selectType').val(),
                selectStatus : $('#selectStatus').val(),
                dateStart : $('#dateStart').val(),
                dateEnd : $('#dateEnd').val()
            },
        url:"/admin/getAllPromocode",
        dataType: 'json',
        type: "get",
        success:function(data)
        {
            $('#totalSum').html('Общая сумма: '+data.sum);
            var permission = false;
            @if(auth()->guard('admin')->user()->can('Promocode_edit'))
                permission = true;
            @endif
            createTable(data,permission);
            // $('#loader').html(' <img src="/img/loader-success.png"  width="30" height="30">');
            // setTimeout(function(){$('#loader').html('');}, 5000);
            $('#loader').hide();
        }
    });

    
}


function createTable(data,perm)
{
    var table = $('#dataTable').DataTable( {
        createdRow: function (row, dataCR, index) {
            if (dataCR.orderId > 0) {
                var strLink = '<a href="/admin/orders?orderId='+dataCR.orderId+'">'+dataCR.orderId+'</a>'
                $('td', row).eq(7).html(strLink);
            }
            if (dataCR.sumOrder!=null)
            $('td', row).eq(8).html(dataCR.sumOrder.toFixed(2));

            if (perm && dataCR.orderId == 0)
            {
                var buttons = `<button id='deactivateButton' class='btn btn-xs btn-info' value=${dataCR.id}>${(dataCR.status ? 'Деактивировать' : 'Активировать')}</button>`;
                    buttons += `<button id='showLongingPromocode' class='btn btn-xs btn-info' style='margin-left:5px;'>Продлить</button>
                                        <input type="date" class='unShow' id="longingPromocodeInput">
                                        <button id='saveLongingPromocode' class='unShow btn-xs btn-success' value=${dataCR.id}>Сохранить</button>`;

                $('td', row).eq(12).html(buttons);
            }
            var str = '';
            if (dataCR.orderId)
                str = 'Использован';
            else 
                if (!dataCR.status)
                    str = 'Деактивирован';
                else 
                    if (dataCR.startValidity>=data.end)
                        str = 'Ожидает';
                    else 
                        if (data.now<=dataCR.expiration)
                            str = 'Активный';
                        else 
                            str = 'Просроченный';

            $('td', row).eq(9).html(str);
        },
        data:data.promocode,
        columns: [
        { title: 'ID',  data: 'id'},
        { title: 'Код', data: 'title'},
        { title: 'Тип', data: 'type'},
        { title: 'Скидка', data: 'discount'},
        { title: 'Время действия, c:', data: 'startValidity'},
        { title: 'Время действия, до:', data: 'expiration'},
        { title: 'Пользователь', data: 'phone'},
        { title: 'Номер заказа', data: 'orderId'},
        { title: 'Сумма', data: 'sumOrder'},
        { title: 'Статус', data: 'status'},
        { title: 'Дата создания', data: 'created_at'},
        { title: 'Дата изменения', data: 'updated_at'},
        { title: 'Действие', data: null}
        ],
        columnDefs: 
        [
            {
                targets: -1,
                data: null,
                defaultContent: "",
            }
        ],
        
    } );

    
}

//Деактивация промокода
$(document).on('click','#deactivateButton',function(){
    var id = $(this);
    var status = $(this).parents('tr').children()[9];
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/changeStatusDiscount?id="+id.val(),
        dataType: 'json',
        success:function(data)
        {
            if (data.code==200)
                status.textContent=='Активный' ? (status.textContent='Деактивирован', id.html('Активировать')) : (status.textContent='Активный', id.html('Деактивировать'))
            else 
                alert("Ошибка");
        }
        })

});


//Продление промокода
$(document).on('click','#saveLongingPromocode',function(){
    $(this).parents('td').find('#longingPromocodeInput').toggleClass('unShow');
    $(this).parents('td').find('#saveLongingPromocode').toggleClass('unShow');

    var id = $(this);
    var date = $(this).parents('td').find('#longingPromocodeInput').val();
    var oldDate = $(this).parents('tr').children()[5];
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/extendPromocode?id="+id.val()+'&newDate='+date,
        dataType: 'json',
        success:function(data)
        {
            if (data.code==200)
                oldDate.textContent = date;
            else 
                alert("Ошибка");
        }
        })

});


//Вход в режим лонгирования промокода
$(document).on('click','#showLongingPromocode',function(){
    $(this).parents('td').find('#longingPromocodeInput').toggleClass('unShow');
    $(this).parents('td').find('#saveLongingPromocode').toggleClass('unShow');
    $(this).html()=='Продлить' ? $(this).html('Отмена') : $(this).html('Продлить');
});



$(document).ready(function(){
    fetch_data();
});


//Применение фильтров
$(document).on('click','#asseptFilters',function(){
    if ( $.fn.dataTable.isDataTable('#dataTable') ) {
        $('#dataTable').empty();
        $('#dataTable').DataTable().destroy();
    }
    fetch_data();
});


//Сброс фильров
$(document).on('click','#removeFilters',function(){
    $('#selectType').val(4);
    $('#selectStatus').val(5);
    $('#dateStart').val(0);
    $('#dateEnd').val(0);
});


</script>
@endsection


