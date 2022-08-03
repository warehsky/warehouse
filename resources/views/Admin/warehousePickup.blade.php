@extends('layouts.admin')

@section('content')
<div class="popup" id="myPopup">
        <span class="popuptext" >Изменено</span>
    </div>

<input type="hidden" name="hidden_page" id="hidden_page" value={{$_GET['page'] ?? 1}} />
<h4>Склад(назначение)</h4>
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Список заказов
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            ID заказа
                        </th>
                        <th>
                            ID 1C
                        </th>
                        <th>
                            Дата доставки
                        </th>
                        <th>
                            Время доставки
                        </th>
                        <th style="width:300px">
                            Способ оплаты
                        </th>
                        <th>
                            Всего товаров
                        </th>
                        <th>
                            Дата создания<br>
                            <input type="date" class="search" id="dateStart" value="{{$def}}">
                            <input type="date" class="search" id="dateEnd">
                        </th>
                        <th>
                            Статус
                        </th>
                        <th>
                            Рабочий
                        </th>
                        <th style="min-width:160px">

                        </th>
                    </tr>
                </thead>
                <tbody id="searchTbody">
                    
                </tbody>
            </table>
            <div id="pagLink"> </div>
        </div>


    </div>
</div>
<style>
.vis
{
    display:none;
}

.popup {
    position: fixed;
    right: calc(50% - 75px);
    top: calc(50% - 50px);
    background: #555;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 150px;
    height: 100px;
    border-radius: 6px;
    z-index: 20;
    opacity: .9;
    cursor: pointer;
    visibility: hidden;
}

.popup .popuptext {
    color: #FFFFFF;
    text-align: center;
    font-size: 16px;
    line-height: 24px;
}
.popup.show {
  visibility: visible;
}

.itemTH{
    padding: 3px;
}
</style>
@endsection
@section('scripts')
@parent
<script>



//запрос для получения данных по заказам
function fetch_data()
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/warehouseGetOrders",
        data: { 
                dateStart: $('#dateStart').val(),
                dateEnd: $('#dateEnd').val(),
                path: '/admin/warehousePickup', 
                page: $('#hidden_page').val(),
            },
        dataType: 'json',
        success:function(data)
        {
            $('#searchTbody').html('');
            let tbl=getWebItemsTbl(data);
            $("#pagLink").empty().append(data.links);
            $('#searchTbody').html(tbl);
        }
        })
    }
//построение таблицы заказов
function getWebItemsTbl(data){
    let tbl = "";
    data[0].forEach(function(item, index, ar){
        tbl += "<tr>";
        tbl += "<td>" + item.id + "</td>";
        tbl += "<td>" + item.number + "</td>";
        tbl += "<td>" + item.deliveryDate + "</td>";
        tbl += "<td>с " + item.timeFrom + " по "  + item.timeTo +  "</td>";
        tbl += "<td>" + item.payment + "</td>";
        tbl += "<td>" + item.count + "</td>";
        tbl += "<td>" + item.created_at + "</td>";            
        tbl += "<td>" + item.status + "</td>";            
        tbl += "<td><select id='WareHouseWorker"+item.id+"' disabled>"+
                "<option value=''>Не выбрано</option>";

            data[1].forEach(function(value, key, a){
                tbl +="<option value="+value.id; 
                if (value.id==item.pickupId)
                    tbl +=" selected ";
                tbl +=">"+value.name+"</option>"
            });

        tbl += '<td><button style="margin-left:0px;" class="btn btn-xs btn-info " id=change value='+item.id+'>Редактировать</button>'+
                "<button class='btn-xs btn-success vis saveChange' style='margin-left:5px' id=SaveBut"+item.id+" value="+item.id+">Сохранить</button>"+
                '<br><button style="margin-left:0px;" class="btn btn-xs btn-info " id=items value='+item.id+'>Товары</button></td>';
        tbl += "</tr>";
        tbl += "<tr class='vis'  id='tr"+item.id+"'></tr>"

    });
    return tbl;
}

    function showReturn() {
        var popup = document.getElementById("myPopup");
        popup.classList.toggle("show");
    }

    function unShowReturn(){setTimeout(showReturn, 1500);}


//поиск
$(document).on('keyup change', '.search', function(){
    $('#hidden_page').val(1);
    fetch_data();
});
//начальная загрузка страницы
$(document).ready(function(){
    $('#hidden_page').val(1);
    fetch_data();
});
//пагинация
$(document).on('click', '.pagination a', function(event){
    event.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    $('#hidden_page').val(page);
    $('li').removeClass('active');
    $(this).parent().addClass('active');
    fetch_data();
});

//поиск
$(document).on('keyup change', '.search', function(){
    $('#hidden_page').val(1);
    fetch_data();
});

//Вхождение в режим редактирования
$(document).on('click','#change',function(event){
    var id=$(this).val();
    if ($(this).html()=='Редактировать')
        $(this).html('Отмена');
    else 
        $(this).html('Редактировать');

    $('#SaveBut'+id).toggleClass('vis');

    if ($('#WareHouseWorker'+id)[0].disabled)
        $('#WareHouseWorker'+id).removeAttr('disabled');
    else  
        $('#WareHouseWorker'+id).attr('disabled', 'disabled');
});


//Кнопка создания нового комментария
$(document).on('click','.saveChange',function(event){
    var orderId=$(this).val();
    var pickupId=$('#WareHouseWorker'+orderId).val();

        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                orderId: orderId,
                pickupId: pickupId,
            },
        url:"/admin/warehousePickupCreate",
        dataType: 'json',
        success:function(data)
        {
            if (data.code!=200)
                alert("Ошибка сохранения");
            else
            {
                fetch_data();
                showReturn();
                unShowReturn();
            }
        }
        }) 
});

//Кнопка создания нового комментария
$(document).on('click','#items',function(event){
    var orderId=$(this).val();

    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                orderId: orderId
            },
        url:"/admin/warehouseGetItemFromOrder",
        dataType: 'json',
        success:function(data)
        {
            $('#tr'+orderId).toggleClass('vis');
            $('#tr'+orderId).html('');
            let tbl=getItems(data);
            $('#tr'+orderId).html(tbl);
        }
        }) 
});

//построение таблицы товаров
function getItems(data){
    let tbl = "<td colspan='9'><div><table class='table-bordered table-striped table-hover' >"+
                "<head><tr><th>№</th><th>ID</th><th>Наименование</th><th>Базовое кол-во</th><th>Кол-во</th><th>Остаток</th></tr></head><body>";
    
    data.forEach(function(item, index, ar){
        tbl += "<tr>";
        tbl += "<td style='padding:3px'>" + (index+1) + "</td>";
        tbl += "<td style='padding:3px'>" + item.itemId + "</td>";
        tbl += "<td style='padding:3px'>" + item.title + "</td>";
        tbl += "<td style='padding:3px'>" + item.quantity_base + "</td>";
        tbl += "<td style='padding:3px'>" + item.quantity + "</td>";
        tbl += "<td style='padding:3px'>" + item.quantityAll + "</td>";
        tbl += "</tr>";
    });
    tbl +="</body></table></div></td>"; 
    
    return tbl;
}

</script>
@endsection
