@extends('layouts.admin')
@section('content')
<input type="hidden"  id="ReportBack" value={{$_GET['ReportBack'] ?? 0}} />
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Список отчетов
    </div>

    <div class="card-body">
    
        <div class="row">
            <div class="col-sm-3">
                <ul>
                    <li id='stockItems' style="cursor: pointer;" @if($_GET["rep0"] ?? "") class='IsError' >Отчет по акционным товарам ({{$_GET["rep0"]}}) @else >Отчет по акционным товарам @endif  </li>
                        
                    <div id='divStockItems' class='unShow divStockItems'>
                            
                        <input type="radio" style="cursor: pointer;"  id="noStockTag" name="drone" value="noStockTag" >
                                <label id="noStockTagLabel" style="cursor: pointer; margin-bottom: 0;" for="noStockTag">Товар с акционной ценой, но без тега</label><br>
                            
                        <input type="radio" style="cursor: pointer;" id="noStockPrice" name="drone" value="noStockPrice" >
                                <label id="noStockPriceLabel" style="cursor: pointer; margin-bottom: 0;" for="noStockPrice">Товар с тегом, но без акционной цены</label>
                        
                            </div>
                    <li id='noPrice' style="cursor: pointer;" @if($_GET["rep1"] ?? "") class='IsError'  >Отчет по товарам без цены курьера ({{$_GET["rep1"]}}) @else >Отчет по товарам без цены курьера @endif </li>
                       
                        <div id='divPriceItems' class='unShow divStockItems'>
                            
                            <input type="radio" class='priceReport' style="cursor: pointer;"  id="allItems" name="price" value="2" >
                                <label id="allItemsLabel" style="cursor: pointer; margin-bottom: 0;" for="allItems">Все товары</label><br>
                            
                            <input type="radio" class='priceReport' style="cursor: pointer;" id="deletedItems" name="price" value="1" >
                                <label id="deletedItemsLabel" style="cursor: pointer; margin-bottom: 0;" for="deletedItems">Удаленные товары</label><br>
                            
                            <input type="radio" class='priceReport' style="cursor: pointer;" id="notDeletedItems" name="price" value="0">
                                <label id="notDeletedItemsLabel" style="cursor: pointer; margin-bottom: 0;" for="notDeletedItems" @if($_GET["rep1"] ?? "") class='IsError'  >Активные товары ({{$_GET["rep1"]}}) @else >Активные товары @endif</label>
                        
                        </div>
                    <li id='noDiscountPrice' style="cursor: pointer;" @if($_GET["rep2"] ?? "") class='IsError' >Отчет по дисконтным товарам ({{$_GET["rep2"]}}) @else >Отчет по дисконтным товарам @endif </li>
                    <li id='noDescrip' style="cursor: pointer;" @if($_GET["rep3"] ?? "") class='IsError'  >Отчет по товарам без описания ({{$_GET["rep3"]}}) @else >Отчет по товарам без описания @endif </li> 
                    <li id='noImage' title='Загрузка отчета может длится до 3 минут' style="cursor: pointer;" @if($_GET["rep4"] ?? "") class='IsError'    @endif>Отчет по товарам без изображения </li> 
                    <li id='diffPrice' style="cursor: pointer;" @if($_GET["rep5"] ?? "") class='IsError'  >Отчет по товарам с некорректной ценой ({{$_GET["rep5"]}}) @else >Отчет по товарам с некорректной ценой @endif </li> 
                    <li id='countDeviceTypeLi' style="cursor: pointer;" >Отчет по кол-ву заказов с различных устройств</li> 
                        <div id='countDeviceTypeDiv' class='unShow' style='outline: 1px solid #A9A9A9; margin-left: -20px; padding: 5px; border-radius: 5px; margin-top:5px'>
                            От
                            <input style='margin-right:7px' type="date" id='dateStart'>
                            До
                            <input type="date" id='dateEnd'>
                            <button id='countDeviceType' class='btn btn-xs btn-success'>Применить</button>
                        </div>

                    <li id='infoOrderLi' style="cursor: pointer;" >Отчет по заказам</li> 
                        <div id='infoOrderDiv' class='unShow' style='outline: 1px solid #A9A9A9; margin-left: -20px; padding: 5px; border-radius: 5px; margin-top:5px'>
                            От
                            <input style='margin-right:7px' type="date" id='dateStartInfoOrder'>
                            До
                            <input type="date" id='dateEndInfoOrder'>
                            <button id='infoOrder' class='btn btn-xs btn-success'>Применить</button>
                        </div>



                    <li id='webUsersSomeOrdersLi' title='Показывает пользователей которые сделали определенное количество заказов в указанный период времени (И у них нет заказов раньше или позже указанного периода)' style="cursor: pointer;" >Пользователи у которых меньше чем N заказов за период</li> 
                        <div id='webUsersSomeOrdersDiv' style='outline: 1px solid #A9A9A9; margin-left: -20px; padding: 5px; border-radius: 5px; margin-top:5px' class='unShow'>
                            Кол-во заказов
                            <input type="number" id='maxCountOrder' ><br>
                            От
                            <input style='margin-right:7px; margin-top:5px'  type="date" id='dateStartOrder'>
                            До
                            <input type="date" id='dateEndOrder'>
                            
                            <button id='webUsersSomeOrders' class='btn btn-xs btn-success'>Применить</button>
                        </div>


                    <li id='phoneNumberStatistic' style="cursor: pointer;" >Статистика звонков</li> 
                    <li id='phoneNumberBackLink' style="cursor: pointer;" >Обратная связь 377</li> 



                    <li id='infoSiteLi' style="cursor: pointer;" >Статистика компонентов сайта</li> 
                        <div id='infoSiteDiv' class='unShow' style='outline: 1px solid #A9A9A9; margin-left: -20px; padding: 5px; border-radius: 5px; margin-top:5px'>
                            От
                            <input style='margin-right:7px' type="date" id='dateStartInfoSite'>
                            До
                            <input type="date" id='dateEndInfoSite'><br>
                            Круг. диаграмма
                            <input type="checkbox" id="pieDiagram">
                            <button id='infoSite' class='btn btn-xs btn-success'>Применить</button>
                                <div style='max-height:400px;overflow: auto;'>
                                    <table class="table table-bordered table-striped table-hover" style="margin:0">
                                        <thead>
                                            <th>
                                                <input type="checkbox" id="AllCheckBox" checked>
                                            </th>
                                            <th>
                                                Название
                                            </th>
                                            <th>
                                                Описание
                                            </th>
                                        </thead>
                                        <tbody id='TBodySiteInfo'>

                                        </tbody>
                                    </table>
                            </div>
                        </div>

                    <li id='1CLoseItems' style="cursor: pointer;" >Ошибки загрузки товаров</li> 
                    <div id='loseItems' class='unShow' style='outline: 1px solid #A9A9A9;  padding: 5px; border-radius: 5px; margin-top:5px'>
                        <input type="file" name="file" id="file" style='margin-bottom:10px'><br>
                        <button id='viewLoseItems' class='btn-xs btn-success'>Проверка</button>
                    </div>
                    
                </ul>
            </div>

            
            <div id="content" align="center" class="unShow col-sm-8">
                <p id='infoAllOrder' class='unShow'></p>
                <canvas id="myChart" style='width:100%; height:100%'></canvas>
            </div>
           
            <h4 id='loadingImage' class='unShow'>Загрузка...</h4>
            
            <div class="card unShow col-sm-9 mainTableScroll" id='FreeTable' style="margin-top:10px">
                @include('Admin.phoneNumber.statisticMain')
            </div>

            <div class="card unShow col-sm-9 mainTableScroll" id='FreeTableBackLink' style="margin-top:10px">
                @include('Admin.phoneNumber.backLinkPhoneMain')
            </div>
            
            <div class="card unShow col-sm-8 mainTableScroll" id='mainTable' style="margin-top:10px">
         
                <div class="card-header">
                    <p id='nameReport'></p>
                </div>
                
                
                <div class="card-body">

                    <div  class="table-responsive">
                        <table id='outPutTable' class='table table-bordered table-striped table-hover datatable'>
                        </table>
                    </div>
                </div>
            </div>

        </div>
       
    </div>
</div>





<style>
img {
    width: 75px;
}
.unShow{
    display:none;
}
.divStockItems{
    padding-left: 15px;
}
.mainTableScroll{
    overflow: scroll;
    height: 650px;
}
.IsError{
    color: #FF0000;
}
</style>
@endsection
@section('scripts')
@parent

<script>
var idClickObject =[];
//Скрыть все компоненты на странице
function displayNoneAll()
{
    $('#mainTable').addClass("unShow");
    $('#loadingImage').addClass("unShow");
    $('#divStockItems').addClass("unShow");
    $('#content'). addClass("unShow");
    $('#FreeTable').addClass('unShow');
    $('#infoOrderDiv').addClass("unShow");
    $('#divPriceItems').addClass("unShow");
    $('#countDeviceTypeDiv').addClass("unShow");
    $('#webUsersSomeOrdersDiv').addClass("unShow");
    $('#FreeTableBackLink').addClass('unShow');
    $('#infoSiteDiv').addClass("unShow");
    $('#infoAllOrder').addClass("unShow");
    $('#loseItems').addClass('unShow');

    $('#allItems').prop('checked', false);
    $('#noStockTag').prop('checked', false);
    $('#deletedItems').prop('checked', false);
    $('#noStockPrice').prop('checked', false);
    $('#notDeletedItems').prop('checked', false);
}


//Вывод подменю для отчета по акционным товарам
$(document).on('click','#stockItems',function(){
    displayNoneAll();
    $('#divStockItems').toggleClass("unShow");

    var NoStockTag=[];
    var NoStockPrice=[];

    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/getNoStockTagOrPrice",
        dataType: 'json',
        success:function(data)
        {
            NoStockTag=data.noStockTag;
            NoStockPrice=data.noStockPrice;
            
            if (NoStockTag.length)
            {
                $('#noStockTagLabel').addClass('IsError');
                $('#noStockTagLabel').html('Товар с акционной ценой, но без тега ('+NoStockTag.length+')');
            }

            if (NoStockPrice.length)
            {
                $('#noStockPriceLabel').addClass('IsError');
                $('#noStockPriceLabel').html('Товар с тегом, но без акционной цены ('+NoStockPrice.length+')');
            }

        }
});

//Отчет по товарам у которых есть акционная цена но нет акционного тега (330)
$(document).on('click','#noStockTag',function(event){
    $('#mainTable').removeClass("unShow");
    $('#loadingImage').addClass("unShow");
    $('#divPriceItems').addClass("unShow");
    $('#nameReport').html('Товар с акционной ценой, но без тега');
    if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
    $.extend( $.fn.dataTable.defaults, {
            searching: true,
            select:false,
            destroy: true,
        } );
        $('.datatable').DataTable( {
        data: NoStockTag,
        columns: [
            { title: "ID" },
            { title: "ID 1C" },
            { title: "Название" },
            { title: "Цена" },
            { title: "Акционная цена" },
            { title: "Изображение" },
            
        ],
        columnDefs: [{ 
            className: 'select-checkbox'},
            { targets:   0
        } ],
        
    } );
});

//Отчет по товарам у которых есть акционный тег (330) но нет акционной цены
$(document).on('click','#noStockPrice',function(event){
    $('#mainTable').removeClass("unShow");
    $('#loadingImage').addClass("unShow");
    
    $('#divPriceItems').addClass("unShow");
    $('#nameReport').html('Товар с тегом, но без акционной цены');
    if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
    $.extend( $.fn.dataTable.defaults, {
            searching: true,
            select:false,
            destroy: true,
        } );
        $('.datatable').DataTable( {
        data: NoStockPrice,
        columns: [
            { title: "ID" },
            { title: "ID 1C" },
            { title: "Название" },
            { title: "Цена" },
            { title: "Акционная цена" },
            { title: "Изображение" },
            
        ],
        columnDefs: [{ 
            className: 'select-checkbox'},
            { targets:   0
        } ],
        
    } );
    });
});    

//Отчет по товарам у которых нет изображения
$(document).on('click','#noImage',function(event){
    displayNoneAll();
    $('#nameReport').html('Товары без изображения');   
    $('#loadingImage').removeClass("unShow");
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/noImg",
        dataType: 'json',
        success:function(data)
        {
            $('#loadingImage').addClass("unShow");
            $('#mainTable').removeClass("unShow");
            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "ID" },
                    { title: "ID 1C" },
                    { title: "Название" },
                    { title: "Дата создания" },
                    { title: "" },

                ];
            createTable(data.noImg,col);
            
        }
        });
});

//Отчет по товарам у которых нет описания
$(document).on('click','#noDescrip',function(event){
    $('#nameReport').html('Товары без описания');
    displayNoneAll();
    $('#mainTable').removeClass("unShow");
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/getNoDescr",
        dataType: 'json',
        success:function(data)
        {
            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "ID" },
                    { title: "ID 1C" },
                    { title: "Название" },
                    { title: "Дата создания" },
                    { title: "" },
                    

                ];
            createTable(data.noDescr,col);
        }
        });
});

//Вывод подменю для отчета без курьерской цены
$(document).on('click','#noPrice',function(event){
    displayNoneAll();
    $('#nameReport').html('Товары без цены курьера');
    $('#divPriceItems').toggleClass("unShow");

});

//Отчет по товарам у которых нет курьерской цены (32)
$(document).on('click','.priceReport',function(){
        $('#mainTable').removeClass("unShow");
            $.ajax({
                headers: {'X-Access-Token': $('#api_token').val()},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                },
                url:"/Api/getNoPrice/"+$(this).val(),
                dataType: 'json',
                success:function(data)
                {
                    if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                        $('#outPutTable').DataTable().destroy();
                        $('#outPutTable').empty();
                    }
                    var col=[
                            { title: "ID" },
                            { title: "ID 1C" },
                            { title: "Название" },
                            { title: "Дата создания" },
                            { title: "" },
                        ];
                    createTable(data.noPrice,col);
                }
            });
});

//Изменяет статус удален ли товар на обратный себе
$(document).on('click','#changeDeletedStatus',function(event){
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/changeDelStatus?id="+$(this).val(),
        dataType: 'json',
        success:function(data)
        {
            $('#noPrice').trigger('click');
        }
        });
});

//Отчет по товарам у которых указано дисконтное кол-во не нет дисконтной цены (64)
$(document).on('click','#noDiscountPrice',function(event){
    $('#nameReport').html('Товары с дисконтным количеством но без дисконтной цены');
    displayNoneAll();
    $('#mainTable').removeClass("unShow");
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/getNoDiscountPrice",
        dataType: 'json',
        success:function(data)
        {
            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "ID" },
                    { title: "ID 1C" },
                    { title: "Название" },
                    { title: "Цена" },
                    { title: "Диск. кол-во" },
                    { title: "Цена диск." },
                    { title: "Дата создания" },
                ];
            createTable(data.noDiscountPrice,col);
        }
        });
});

//Отчет по товарам у которых дисконтная или акционная цена выше или равна курьерской
$(document).on('click','#diffPrice',function(event){
    $('#nameReport').html('Товары с некорректной ценой');
    displayNoneAll();
    $('#mainTable').removeClass("unShow");
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/getDiffPrice",
        dataType: 'json',
        success:function(data)
        {
            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "ID" },
                    { title: "ID 1C" },
                    { title: "Название" },
                    { title: "Цена" },
                    { title: "Цена диск." },
                    { title: "Цена акц." },
                    { title: "Диск. кол-во" },
                    { title: "Дата создания" },
                    

                ];
            createTable(data.diffPrice,col);
        }
        });
});

//Выводит подменю для отчета по количеству заказов с различных устройств
$(document).on('click','#countDeviceTypeLi',function(event){
    displayNoneAll();
    $('#countDeviceTypeDiv').toggleClass("unShow");
});

//вывод подменю для ввода периода за который нужно расчитать количество заказов
$(document).on('click','#infoOrderLi',function(event){
    displayNoneAll();
    $('#infoOrderDiv').toggleClass("unShow");
});

//вывод подменю для ввода периода за который нужно просмотреть статистику переходов по сайту
$(document).on('click','#infoSiteLi',function(event){
    displayNoneAll();
    $('#infoSiteDiv').toggleClass("unShow");

    let ids = localStorage.idClickObject;
    if (ids)
        idClickObject = JSON.parse(ids);
    else 
        idClickObject = [];
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/getClickObject",
        dataType: 'json',
        success:function(data)
        {
            let tbl = "";
            data.clickObject.forEach(function(item, index, ar){
                tbl += "<tr>";
                tbl += "<td><input type='checkbox' class='checkt' id='' value='"+item.id+"'";
                if (idClickObject.indexOf(String(item.id))==-1)
                    tbl+=" checked=checked";
                tbl += "></td>";    
                tbl += "<td>" + item.title + "</td>";
                tbl += "<td>" + item.description + "</td>";
                tbl += "<tr>";
            });
            $('#TBodySiteInfo').html(tbl); 
        }
        });
});

//Отключение некоторых полей для вывода статискики по сайту
$(document).on('click','.checkt',function(){
    let id = $(this).val();
    let findId = idClickObject.indexOf(id);
    if (findId==-1)
        idClickObject.push(id);
    else    
        idClickObject.splice(findId, 1);
}); 

//Отчет по количеству заказов за период
$(document).on('click','#infoOrder',function(event){
    $('#nameReport').html('Отчет по заказам');
    $('#content').removeClass("unShow");
    $('#infoAllOrder').removeClass("unShow");
    
    var dateStart = $('#dateStartInfoOrder').val();
    var dateEnd = $('#dateEndInfoOrder').val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/infoOrders/?dateStart="+dateStart+"&dateEnd="+dateEnd,
        dataType: 'json',
        success:function(data)
        {
            $('#infoAllOrder').html('Всего заказов: '+data.resultForPeriod.countOrders+' на сумму: '+data.resultForPeriod.sumOrders.toFixed(2));
            var type =
            {
                type: 'bar',
                data: 
                {
                    labels: [], //Подписи оси x
                    datasets:
                    [{
                        label: 'Сумма за день', //Метка
                        borderColor: 'blue', //Цвет
                        borderWidth: 1, //Толщина линии
                        fill: false //Не заполнять под графиком
                    }] //Можно добавить другие графики до ]
                },
                options: 
                {
                    responsive: false, //Вписывать в размер canvas
                    scales: 
                    {
                        xAxes: 
                        [{
                            display: true
                        }],
                        yAxes: 
                        [{
                            ticks: {
                                beginAtZero: true
                            },
                            display: true
                        }]
                    }
                }
            };
            Diagram(data,0,type);

        }
        });
});

//Отчет по количеству заказов с определенных устройств
$(document).on('click','#countDeviceType',function(event){
    $('#nameReport').html('Отчет по кол-ву заказов с различных устройств');
    $('#mainTable').removeClass("unShow");
    var dateStart = $('#dateStart').val();
    var dateEnd = $('#dateEnd').val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/getCountTypeDevice/?dateStart="+dateStart+"&dateEnd="+dateEnd,
        dataType: 'json',
        success:function(data)
        {
            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "Название" },
                    { title: "Количество заказов" },
                    { title: "Количество заказов в %" },
                    
                    

                ];
            createTable(data.countDeviceType,col);
        }
        });
});

//Выводит подменю для отчета по новым пользователям в указанный период времени
$(document).on('click','#webUsersSomeOrdersLi',function(event){
    displayNoneAll();
    $('#webUsersSomeOrdersDiv').toggleClass("unShow");
});

//Отчет по количеству новых пользователей в указанный период времени
$(document).on('click','#webUsersSomeOrders',function(event){
    $('#nameReport').html('Пользователи у которых меньше чем N заказов за период');
    $('#mainTable').removeClass("unShow");
    var maxCountOrder = $('#maxCountOrder').val();
    var dateStart = $('#dateStartOrder').val();
    var dateEnd = $('#dateEndOrder').val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/getWebUsersSomeOrders/?dateStart="+dateStart+"&dateEnd="+dateEnd+"&maxCountOrder="+maxCountOrder,
        dataType: 'json',
        success:function(data)
        {
            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "ID" },
                    { title: "Номер" },
                    { title: "Имя" },
                    { title: "Кол-во заказов" },
                    
                    

                ];
            createTable(data.resultfirstOrder,col);
        }
        });
});

/*
    Постороение таблицы отчета
    Входящие параметры: 
        data - массив с данными 
        col - структура для заголовка таблицы (Пример: [{ title: "ID" },{ title: "Название" },{ title: "Цена" },] )
*/
function createTable(data,col)
{
    
        $.extend( $.fn.dataTable.defaults, {
            searching: true,
            select:false,
            destroy: true,
            });

            $('.datatable').DataTable( {
                data: data,
                columns: col,
        columnDefs: [{ 
            className: 'select-checkbox'},
            { targets:   0
        } ],
        
    } );
}

$(document).ready(function(){
    var ReportBack=$('#ReportBack').val();
    if (ReportBack!=0)
    {
        var li=['stockItems','noPrice','noDiscountPrice','noDescrip','noImage'];
        $.ajax({
            headers: {'X-Access-Token': $('#api_token').val()},
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
            },
            url:"/Api/allReport",
            dataType: 'json',
            success:function(data)
            {
                data.ReportErrors.forEach(function(item, index, ar){
                    if (item>0)
                    {
                        if (index!=4)
                            $('#'+li[index]).html($('#'+li[index]).html()+" ("+item+")").addClass('IsError');
                        else 
                            $('#'+li[index]).addClass('IsError');
                    }
                });
            if (ReportBack==3)
                $('#noDescrip').trigger('click');
            if (ReportBack==4)
                $('#noImage').trigger('click');
            }
        });
    }
});


//Построение диаграммы для отчета по заказам за период времени
function Diagram (data,point,type) 
{
    $('#myChart').remove();
    $('#content').append('<canvas id="myChart" style="width:100%; height:100%; min-height:800px; max-height:800px"><canvas>'); //
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart (ctx, type);

    if (point==0)
    {
        var days = [
                'Воскресенье',
                'Понедельник',
                'Вторник',
                'Среда',
                'Четверг',
                'Пятница',
                'Суббота',
                ];
    
    //Заполняем данными
        data.sumOrderForDay.forEach(function(item, index, ar){
            var parts =item.date.split('.');
            var date = new Date(parts[2], parts[1] - 1, parts[0]); 
            var day = days[date.getDay()];
            myChart.data.labels.push( day+' '+item.date+' (Заказов: '+item.countOrder+')');
            myChart.data.datasets[0].data.push(item.sumForDay.toFixed(2));
        });
    }

    if (point==1)
    {
        data.sumCountClickSite.forEach(function(item, index, ar){
            myChart.data.labels.push(item.title + ' (Переходов: '+item.count+')');
            myChart.data.datasets[0].data.push(item.count);
        });
    }
   
    
  //Обновляем
    myChart.update();
}


$(document).on('click','#phoneNumberStatistic',function(){
    displayNoneAll();
    $('#FreeTable').toggleClass('unShow');
});


$(document).on('click','#phoneNumberBackLink',function(){
    displayNoneAll();
    $('#FreeTableBackLink').toggleClass('unShow');
});


//Отчет по количеству кликов по сайту
$(document).on('click','#infoSite',function(event){
    $('#nameReport').html('Отчет по заказам');
    $('#content').removeClass("unShow");
    
    var dateStart = $('#dateStartInfoSite').val();
    var dateEnd = $('#dateEndInfoSite').val();

    localStorage.removeItem('idClickObject');
    let json = JSON.stringify(idClickObject);
    localStorage.setItem('idClickObject',json);

    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                dateStart: dateStart,
                dateEnd: dateEnd,
                idClickObject: idClickObject
            },
        url:"/Api/infoSite",
        dataType: 'json',
        success:function(data)
        {
            const CHART_COLORS = 
            {
                
                red: 'rgb(255, 99, 132)',
                orange: 'rgb(255, 159, 64)',
                yellow: 'rgb(255, 205, 86)',
                green: 'rgb(75, 192, 192)',
                blue: 'rgb(54, 162, 235)',
                purple: 'rgb(153, 102, 255)',
                AirForceBlue: 'rgb(93, 138, 168)',
                Almond: 'rgb(239, 222, 205)',
                Amaranth: 'rgb(229, 43, 80)',
                Amethyst: 'rgb(153, 102, 204)',
                AntiqueWhite : 'rgb(250, 235, 215)',
                AppleGreen: 'rgb(141, 182, 0)',
                Aqua: 'rgb(0, 255, 255)',
                ArmyGreen: 'rgb(75, 83, 32)',
                Azure : 'rgb(0, 127, 255)',
                Bittersweet: 'rgb(254, 111, 94)',
                Blue2: 'rgb(0, 0, 255)',
                UniversityRed: 'rgb(204, 0, 0)',
                Brightgreen : 'rgb(102, 255, 0)',
                Brightturquoise : 'rgb(8, 232, 222)',
                Brightviolet : 'rgb(205, 0, 205)',
                Brown : 'rgb(150, 75, 0)',
                Celadon: 'rgb(172, 225, 175)',
                Cobalt: 'rgb(0, 71, 171)',
                grey: 'rgb(201, 203, 207)'
            };
            if ($('#pieDiagram').is(':checked'))
                var type = 
                {
                    type: 'pie',
                    data: 
                    {
                        datasets:
                        [{backgroundColor: Object.values(CHART_COLORS)}] 
                    },
                    options: {
                        responsive: true,
                        plugins: {
                        legend: {position: 'top'},
                        title: {display: true}}
                    }
                };
            else 
                var type =
                {
                    type: 'bar',
                    data: 
                    {
                        labels: [], //Подписи оси x
                        datasets:
                        [{
                            label: 'Переходы по сайту', 
                            borderColor: 'blue', //Цвет
                            borderWidth: 1, //Толщина линии
                            fill: false //Не заполнять под графиком
                        }] //Можно добавить другие графики до ]
                    },
                    options: 
                    {
                        responsive: false, //Вписывать в размер canvas
                        scales: 
                        {
                            xAxes:[{display: true}],
                            yAxes:[{display: true,beginAtZero: true,}]
                        }
                    }
                };


            Diagram(data,1,type);
        }
        });
});

//Выбрать все или убрать все для статистики компонентов сайта
$(document).on('click','#AllCheckBox',function(){
    if (!$(this).is(':checked'))
    {
        var checkBox= Object.entries($(".checkt")).map(i=>i[1]);
        checkBox.forEach(function(item, index, ar){
            item.checked=false;
            var id = item.value;
            let findId = idClickObject.indexOf(id);
            if (findId==-1)
                idClickObject.push(id);
            else    
                idClickObject.splice(findId, 1);
        });
    }
    else
    {
        idClickObject = [];
        var checkBox= Object.entries($(".checkt")).map(i=>i[1]);
        checkBox.forEach(function(item, index, ar){
            item.checked=true;
        });
    }
});

//Обработка файла Excel
$(document).on('click', '#viewLoseItems', function(event){ 
    $('#mainTable').removeClass("unShow");
    var formData = new FormData();
    formData.append('file', $("#file")[0].files[0]);
    formData.append('column', $("#column").val());
    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
        url: "/Api/checkExcelOrder",
        type: 'POST',
        data: formData,
        async: true,
        dataType: 'json',
        success: function (data) {
            if (data.checkExcelLoseItem.length == 0)
                $('#nameReport').html('Все товары загружены успешно');
            else
                $('#nameReport').html('Товары которые не загрузились');

            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "ID" },
                    { title: "Название" },
                    { title: "Описание" },
                ];
            createTable(data.checkExcelLoseItem,col);
        },
        cache: false,
        contentType: false,
        processData: false
    });
});

$(document).on('click','#1CLoseItems',function(){
    displayNoneAll();
    $('#loseItems').toggleClass('unShow');
});    

</script>
@endsection