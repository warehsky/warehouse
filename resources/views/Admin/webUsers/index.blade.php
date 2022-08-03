@extends('layouts.admin')

@section('content')
<h3>Окно просмотра списка покупателей</h3>
<div class="card" style="margin-top:10px">
<div class="popup" id="myPopup">
        <span id='popupText' class="popuptext"></span>
    </div>
    <div class="card-header">
        Список покупателей
    </div>
    <input type="hidden" id="api_token" value="{{$api_token}}">
    <input type="hidden" name="hidden_page" id="hidden_page" value={{$_GET['page'] ?? 1}} />
    <input type="hidden"  id="sorting" value={{$_GET['sorting'] ?? 'desc'}} />
    <input type="hidden"  id="sortingField" value={{$_GET['sortingField'] ?? 'createTm'}} />
    <param id="selectedId" value="">
    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-item">
                <thead>
                    <tr>
                        
                        <th>
                            Телефон<br>
                            <input type="text" id="searchID" class="search" value="{{$_GET['id'] ?? ""}}" placeholder="Телефон" size="20"></input>
                        </th>
                        <th>
                            Имя<br>
                            <input type="text" id="searchName" class="search" value="{{{$_GET['name'] ?? ""}}}" placeholder="Имя" size="20"></input>
                        </th>
                        <th>
                            Код
                        </th>
                        <th>
                            Заметка
                        </th>
                        <th>
                            Кол-во бонусов
                        </th>
                        <th>
                            Последний заказ
                        </th>
                        <th>
                            Сумма посл. заказа
                        </th>
                        <th id="sort" class="search" style="cursor: pointer;min-width:260px;">
                            <param value="createTm">
                            Последнее изменение
                        </th>
                        <th>
                            
                            <div id="sort" class="search" style="cursor: pointer;min-width:260px;">
                                    <param value="countOrder">
                                    История<br>
                                </div>
                            <input type="date" class="search" id="dateStart">
                            <input type="date" class="search" id="dateEnd">
                        </th>
                        <th style="min-width:170px">
                            <input type="checkbox" id="allDeliveryAdd"> Только довозы
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


<div id="modalWindow" class="modal">

  <!-- Модальное содержание -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <div class="modalP">
    <p >Временные заметки для покупателя: </p><b>
    <p id='p_Name'></p>
    <p id='p_Phone'></p></b>
    </div>
    <hr style="width:100%">
    <div style="display: block ruby;">
        <p>Новая заметка</p>
        <input id='inputNewNote' type="text" size="45">
        <button id='buttonNewNote' class='btn btn-xs btn-success'>Сохранить</button>
    </div>
    				
    <table class="table table-bordered table-striped table-hover" style="margin-top:5px">
        <thead>
            <th>Заметка</th>
            <th>Последние изменения</th>
            <th>Дата создания</th>
            <th>Дата редактирования</th>
            <th>Статус</th>
        </thead>
        <tbody id='TBodyNote'>

        </tbody>
    </table>
  </div>

</div>

<div id="modalWindowDelivery" class="modal">

  <!-- Модальное содержание -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <div class="modalP">
    <p>Довозы покупателя: </p><b>
    <p id='webUser_name'></p>
    <p id='webUser_phone'></p></b>
    </div>
    <hr style="width:100%">
    <table class="table table-bordered table-striped table-hover" style="margin-top:5px">
        <thead>
            <th>ID</th>
            <th>Номер заказа</th>
            <th>Товар</th>
            <th>Кол-во</th>
            <th>Подтв. кол-во</th>
            <th>Раб. Склада</th>
            <th>Дата создания</th>
            <th>Создал</th>
            <th>Закрыл</th>
            <th>Дата доставки</th>
            <th>Волна</th>
            <th>Заказ довоза</th>
            <th>Статус
                </br>
                <select id="statusLostDelivery">
                    <option value="0" selected>Все</option>
                    <option value="1">Не подтвержден</option>
                    <option value="2">Подтвержден</option>
                    <option value="3">Закрыт</option>
                </select>
            </th>
            <th>
                
            </th>
        </thead>
        <tbody id='TBodyDelivery'>

        </tbody>
    </table>
  </div>

</div>


<style>
body {font-family: Arial, Helvetica, sans-serif;}

.modalP{
    margin:0;
    display: block ruby;
}
/* Модальный (фон) */
.modal {
  display: none; /* Скрыто по умолчанию */
  position: fixed; /* Оставаться на месте */
  z-index: 1; /* Сидеть на вершине */
  padding-top: 100px; /* Расположение коробки */
  left: 0;
  top: 0;
  width: 100%; /* Полная ширина */
  height: 100%; /* Полная высота */
  overflow: auto; /* Включите прокрутку, если это необходимо */
  background-color: rgb(0,0,0); /* Цвет запасной вариант  */
  background-color: rgba(0,0,0,0.4); /*Черный с непрозрачностью */
}

/* Модальное содержание */
.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 95%;
}

/* Кнопка закрытия */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  align-self: flex-end;
}
.vis{
    display:none;}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
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
    width: 200px;
    height: 150px;
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
</style>
@endsection
@section('scripts')
@parent
<script>

    var allDeliveryAdd = 0;





//сортировка по дате
$(document).on('click', '#sort', function(event){
    $('#hidden_page').val(1);
    var sr=$('#sorting').val();
    $('#hidden_page').val(1);
    $('#sortingField').val($(this).find('param').val());
    if(sr=='asc')
        $('#sorting').val('desc');
    else 
        $('#sorting').val('asc');
        fetch_data();
});

//построение таблицы покупателей
function fetch_data()
    {
        var id = $('#searchID').val();
        var name = $('#searchName').val();
        var page = $('#hidden_page').val();
        var dateStart = $('#dateStart').val();
        var dateEnd = $('#dateEnd').val();
        var sortingMethod = $('#sorting').val();
        var sortingField = $('#sortingField').val();
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/AllWebUsers?page="+page+"&id="+id+"&name="+name+"&dateStart="+dateStart+"&dateEnd="+dateEnd+"&sortingMethod="+sortingMethod+"&delivAdd="+allDeliveryAdd+"&sortingField="+sortingField+"&path=/admin/WebUsers",
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

function getWebItemsTbl(data){
    console.log(data);
    let tbl = "";
    data[0].forEach(function(item, index, ar){
        tbl += "<tr>";
        tbl += "<td>" + item.phone + "</td>";
        let name=item.userName==undefined?" ":item.userName;
        tbl += "<td>" + $("<td>").text(name).html()  + "</td>";
        tbl += "<td>" + item.code + "</td>";
        tbl += "<td>" + item.note + "</td>";
        tbl += "<td>" + item.bonus + "</td>";
        if(data[1][item.id]!=undefined)
        {
            tbl += "<td>" + data[1][item.id].updated_at + "</td>";
            tbl += "<td>" + data[1][item.id].sum_last.toFixed (2) + "</td>";
        }
        else 
        {
            tbl += "<td>-</td>";
            tbl += "<td>-</td>";
        }
        tbl += "<td>" + item.createTm + "</td>";

        let l=item.phone.length;
        let phone=item.phone.slice(0,l-10)+"("+item.phone.slice(l-10,l-7)+") "+item.phone.slice(l-7,l-4)+"-"+item.phone.slice(l-4,l-2)+"-"+item.phone.slice(l-2,l);
        tbl += "<td><a href = \'/admin/orders?phone="+phone+"\'>" + item.countOrder + "</a></td><td>";

        tbl+= "<a href = \'/admin/WebUsers/"+item.phone+"\' class='btn btn-xs btn-info' >Открыть</a>"+
              "<button style='margin-left:5px;' class='btn btn-xs btn-info' id='modalBtn' value="+item.id+">Заметки"+
              "<param id='selectedName' value="+$("<td>").text(name).html()+"><param id='selectedPhone' value="+item.phone+"></button>"+
              "<br><button class='btn btn-xs btn-"+(item.countDeliveryAdd ? "warning" : "info")+"' id='modalBtnDelivery' value="+item.id+">Довоз "+(item.countDeliveryAdd ? ("("+item.countDeliveryAdd+")") : " ")+"<param id='selectedName' value="+$("<td>").text(name).html()+"><param id='selectedPhone' value="+item.phone+"></button>"+
              "</div></td></tr>";
              
        
    });
    return tbl;
}

//поиск
$(document).on('keyup change', '.search', function(){
    $('#hidden_page').val(1);
    fetch_data();
});
//начальная загрузка страницы
$(document).ready(function(){
    $("#allDeliveryAdd").prop("checked", false);
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


//Открытие модального окна заметок
$(document).on('click','#modalBtn',function(){
    document.getElementById('modalWindow').style.display='block';
    $('#selectedId').val($(this).val());
    $('#p_Name').html($(this).find('#selectedName').val());
    $('#p_Phone').html('('+$(this).find('#selectedPhone').val()+')');
    updateHistoryNote();
});

//Закрытие модального окна заметок нажатием кнопки 
$(document).on('click','.close',function(){
    document.getElementById('modalWindow').style.display='none';
});
//Закрытие модального окна заметок убиранием фокуса 
$(document).on('click',window,function(event){
    if (event.target == document.getElementById('modalWindow')){
    document.getElementById('modalWindow').style.display='none';}
});

//Загрузка таблицы заметок в модальном окне 
function updateHistoryNote()
{
    var id = $('#selectedId').val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getHistoryNoteForPerson?id="+id,
        dataType: 'json',
        success:function(data)
        {
            let tbl = "";
            data.forEach(function(item, index, ar){
                tbl += "<tr>";
                tbl += "<td><input type='text' value=\'"+item.note+"\' id='I"+item.id+"' class='vis'><button class='vis btn btn-xs btn-success updateNote' value="+item.id+" id='B"+item.id+"' >Сохранить</button><p id='P"+item.id+"'>" + item.note + "</p></td>";
                tbl += "<td>" + item.name + "</td>";
                tbl += "<td>" + item.created_at + "</td>";
                tbl += "<td>" + item.updated_at + "</td>";
                if (item.status)
                    tbl += "<td>Актуально</td>";
                else 
                    tbl += "<td>Не актуально</td>";
                @if(auth()->guard('admin')->user()->can('WebUsers_note_edit'))
                tbl += "<td><button id='changeNoteBut' class=' btn-xs btn-info' value="+item.id+">Редактировать</button><br><button id='changeStatusNote' class='btn btn-xs btn-info' value="+item.id+">Изменить статус</button></td>"
                @endif
                tbl += "<tr>";
            });
            $('#TBodyNote').html(tbl); 
        }
        })     
}

//Обновление записи в таблице заметок
$(document).on('click','.updateNote',function(event){
    id=$(this).val();
    note=$('#I'+id).val();
    $('#I'+id).toggleClass('vis');
    $('#B'+id).toggleClass('vis');
    if (note)
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/updateNoteForWebUser?id="+id+"&note="+note,
        dataType: 'json',
        success:function(data)
        {
            if (data.code!=200)
                alert("Ошибка сохранения");
            else
            updateHistoryNote();
        }
        }) 
    }
    else
    alert('Пустое поле');
});

//Вхождение в режим редактирования
$(document).on('click','#changeNoteBut',function(event){
    var id=$(this).val();
    $('#I'+id).toggleClass('vis');
    $('#B'+id).toggleClass('vis');
    $('#P'+id).toggleClass('vis');
});

//Изменение статуса заметки
$(document).on('click','#changeStatusNote',function(event){
    id=$(this).val();
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/changeStatusWebUsersNote?id="+id,
        dataType: 'json',
        success:function(data)
        {
            if (data.code!=200)
                alert("Ошибка изменение статуса");
            else
            updateHistoryNote();
        }
        }) 
});

//Кнопка создания новой заметки
$(document).on('click','#buttonNewNote',function(event){
    var id=$('#selectedId').val();
    var note=$('#inputNewNote').val();
    if (note)
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/addNewNoteForWebUser?note="+note+"&id="+id,
        dataType: 'json',
        success:function(data)
        {
            if (data.code!=200)
                alert("Ошибка сохранения");
            else{
            updateHistoryNote();
            $('#inputNewNote').val('');    
            }
        }
        }) 
    }
    else
    alert('Пустое поле');
});

//Открытие модального окна Довозов
$(document).on('click','#modalBtnDelivery',function(){
    document.getElementById('modalWindowDelivery').style.display='block';
    $('#selectedId').val($(this).val());
    $('#webUser_name').html($(this).find('#selectedName').val());
    $('#webUser_phone').html('('+$(this).find('#selectedPhone').val()+')');
    var id = $(this).val();
    console.log(id);
    updateDelivery(id);
});

//Закрытие модального окна довозов нажатием кнопки 
$(document).on('click','.close',function(){
    document.getElementById('modalWindowDelivery').style.display='none';
});
//Закрытие модального окна довозов убиранием фокуса 
$(document).on('click',window,function(event){
    if (event.target == document.getElementById('modalWindowDelivery')){
    document.getElementById('modalWindowDelivery').style.display='none';}
});

//Обновление таблицы довозов для определенного пользователя
function updateDelivery(id,status=0)
{
    console.log(id,status);
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getAllLostDelivery?id="+id+"&status="+status,
        dataType: 'json',
        success:function(data)
        {
            let tbl = "";
            data.forEach(function(item, index, ar){
                tbl += "<tr>";
                tbl += "<td>"+ item.id + "</td>";
                tbl += "<td><a href='/admin/orders?orderId="+item.orderId+"'>"+item.orderId+"</a></td>";
                tbl += "<td>" + item.title + "</td>";
                tbl += "<td>" + item.quantity + "</td>";
                tbl += "<td>" + item.confirmQuantity + "</td>";
                tbl += "<td>" + (item.workhouse ? item.workhouse : " ") + "</td>";
                tbl += "<td>" + item.created_at + "</td>";
                tbl += "<td>" + item.creator + "</td>";
                tbl += "<td>" + (item.closer ? item.closer : " ") + "</td>";
                
                tbl += "<td>";
                    tbl += "<span id='span_deliveryDate"+item.id+"'>" + (item.deliveryDate ? item.deliveryDate : item.addedId || item.status!=2 ?  " " : "<input type='date' id='date_"+item.id+"'>") + "</span></td>";
                
                let select = "<select id='select_"+item.id+"'><option value='0'>Не выбрано</option>"  
                item.waveId.forEach(function(it, i, arg){
                    var wave = it.split('/');
                    select += "<option value='"+parseInt(wave[0])+"'>"+wave[1]+"</option>"
                })
                select += "</select>";

                tbl += "<td><span id='span_timeWave"+item.id+"'>" + (item.timeWave ? item.timeWave : item.addedId || item.status!=2 ?  " " : select) + "</span></td>";
                if (item.addedId!=0)
                    tbl += "<td><a href='/admin/orders?orderId="+item.addedId+"'>"+item.addedId+"</a></td>";
                else 
                    tbl += "<td><span id='span_order"+item.id+"'></span></td>";
                if (item.status==1)
                    tbl += "<td>Не подтвержден</td>";
                else 
                    if (item.status==2) 
                        tbl += "<td>Подтвержден</td>";
                    else 
                        tbl += "<td>Закрыт</td>";
                @if(auth()->guard('admin')->user()->can('order_corrects'))
                    tbl += "<td><button id='closeLostDelivery' class='btn btn-xs btn-info closeDeliveryAdd' value="+item.id;
                    if (item.status != 2)
                        tbl += " disabled ";
                    tbl += ">Закрыть</button></td>"
                @endif
                tbl += "</tr>";
            });
            $('#TBodyDelivery').html(" "); 
            $('#TBodyDelivery').html(tbl); 
        }
        })     
}
//Фильтр по статусу в довозах (select) 
$(document).on('change','#statusLostDelivery',function(){
    var status = $(this).val();
    var id = $('#selectedId').val();
    updateDelivery(id,status);
});

//Закрытие довоза
$(document).on('click','.closeDeliveryAdd',function(){
    let id = $(this).val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/closeDeliveryAdd",
        dataType: 'json',
        data: {
            id:id,
            waveId:$('#select_'+id).val(),
            deliveryDate:$('#date_'+id).val(),
            },
        success:function(data)
        {
            console.log(data);
            if (data.success)
            {
                var status = $("#statusLostDelivery").val();
                var idUser =  $('#selectedId').val();
                updateDelivery(idUser,status);
                showReturn(data.data.msg);
                unShowReturn();
            }
            else 
            {
                showReturn(data.error.msg);
                unShowReturn();
            }
        }
        }) 
});

//Показать всплывающее окно с переданным текстом
function showReturn(text) {
        var popup = document.getElementById("myPopup");
        $('#popupText').html(text);
        popup.classList.toggle("show");
}

//Скрыть всплывающее окно чеез 3 секунды
function unShowReturn(){setTimeout(showReturn, 3000);}

//Фильтр по довозам
$(document).on('click','#allDeliveryAdd',function(){
    if ($('#allDeliveryAdd').is(':checked'))
        allDeliveryAdd = 1;
    else 
        allDeliveryAdd = 0;
    fetch_data();
});

</script>
@endsection
