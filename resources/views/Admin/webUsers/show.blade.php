@extends('layouts.admin')
@section('content')
<input type="hidden" id="api_token" value="{{$api_token}}">
<div class="popup" id="myPopup">
        <span id='popupText' class="popuptext">Сохранено</span>
    </div>
@if(session('success'))
    <div class="alert alert-success">
        {{session('success')}}
    </div>
@endif
@if(session('danger'))
    <div class="alert alert-danger">
        {{session('danger')}}
    </div>
@endif

<div id="AllTabs" class="tab">

</div>
<div id="mainTable">

</div>

<a style="margin-top:10px;" class="btn btn-default" href="{{ route('WebUsers') }}">Назад</a>
<style>
.vis{
    display:none;}

.layer {
    overflow: scroll;
    height: 570px;
    padding: 5px;
} 

body {font-family: Arial;}

/* Style the tab */
.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
    font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}

.remove-all-styles {
    all: revert;
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
</style>
@endsection
@section('scripts')
@parent
<script>

//Меню
function getOptionsGroups()
{
    var permission = '{{auth()->guard("admin")->user()->can("WebUsers_show")}}';
    if(permission) 
        data = ['Информация','Настройки','Дисконтные карты','SMS'];
    else 
        data = ['Информация','Настройки','Дисконтные карты'];

    var buttons = '';
    var int = 0;
    data.forEach(function(item, index, ar){
        var str = new String(item);
        buttons +='<button class="tablinks" value='+index+' id="group_'+index+'">'+str+'</button>';
    });

    $('#AllTabs').html(buttons);
    document.getElementById("group_0").click();
    
}

$(document).ready(function(){
    getOptionsGroups();
});

//Нажатие на кнопку меню
$(document).on('click','.tablinks',function(evt) {
  var i, tablinks;
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    $('#mainTable').html("");
    var code = Number($(this).val());

    switch (code)
    {
        case 0: pageUserInformation();  break;
        case 1: pageSettings();         break;
        case 2: pagePromocode();        break;
        case 3: pageSMS();        break;
    }
    evt.currentTarget.className += " active";
});

//Страница с информацией и заметками
function pageUserInformation()
{
    table = `<div class="card" style="margin-top:5px">

    <div class="card-header">
       Просмотр покупателя:<b> {{$user->userName}} ({{$user->phone}})</b> <button id="historyNote" class="btn btn-xs btn-info">Временные заметки</button>
    </div><div class="card-body" >    
        <div class="row">
        <div id = "LeftDiv" class=" col-sm-12 layer">      
        <table class=" table table-bordered table-striped table-hover">
            <tr><td>Телефон</td><td>{{$user->phone}}</td></tr>
            <tr><td>Код</td><td>{{$user->code}}</td></tr>
            <tr><td>Имя</td><td>{{$user->userName}}</td></tr>
            <tr><td>Email</td><td>{{$user->email}}</td></tr>
            <tr><td>День рождения</td><td>{{$user->birthday}}</td></tr>
            <tr><td>Адресс</td><td>{{$user->adress}}</td></tr>
            <tr><td>Заметка</td>
            <td id="TdNote">
            <p id="PNote">{{$user->note}}</p> 
            @if(auth()->guard('admin')->user()->can('WebUsers_note_edit'))
            <input class='vis' type="text" id='NoteInput'>
            <button id="SaveNote" class="btn btn-xs btn-success vis">Сохранить</button>
            <button id="NoteButton" class='btn btn-xs btn-info'>Изменить</button>
            <form  action="{{ route('WebUserNoteDelete', $user->id) }}" method="POST" onsubmit="return confirm('Вы уверены?');" style="display: inline-block;">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="submit" id="deleteNoteForm" class="btn btn-danger btn-xs vis" value="Удалить">
            </form>@endif</td></tr>
            <tr><td>Кол-во бонусов</td><td>{{$user->bonus}}</td></tr>
            <tr><td>Последний заказ</td>
                <td>@if ($lastOrderUser!=null)
                        {{$lastOrderUser->updated_at}}
                    @else 
                        -
                    @endif</td></tr>
            <tr><td>Сумма посл. заказа</td>
                <td>@if ($lastOrderUser!=null)
                        {{$lastOrderUser->sum_total}}
                    @else 
                        -
                    @endif</td></tr>
            <tr><td>Последнее изменение</td><td>{{$user->createTm}}</td></tr>
            <tr><td>История за месяц</td><td><a href = '/admin/orders?phone={{$maskPhone}}'>{{$historyOrder}}</a></td></tr>
            </table></div>
            <div id='divNewNote' class='col-sm-7 layer vis'>
                <div>
                @if(auth()->guard('admin')->user()->can('WebUsers_note_edit'))
                    <h5>Временные заметки</h5>
                    <label for="inputNewNote">Новая заметка</label>
                    <input id="inputNewNote" size="35" type="text">
                    <button class='btn btn-xs btn-success' id='buttonNewNote'>Сохранить</button>
                @endif
                </div>
                <table id="historyTableNote" class="table table-bordered table-striped table-hover" style="overflow: scroll;">
                    <thead id="THeadNote">
                    </thead>
                    <tbody id="TBodyNote">
                    </tbody>
                </table>
            </div></div></div></div>
            `;
    $('#mainTable').html(table);
}

//Глобальная заметка: открытие режима редактирования
$(document).on('click', '#NoteButton', function(event){
   
   $('#NoteInput').toggleClass('vis');
   $('#SaveNote').toggleClass('vis');
   $('#deleteNoteForm').toggleClass('vis');
   if ($('#NoteButton').html()=='Изменить')
       $('#NoteButton').html('Отмена');
   else 
       $('#NoteButton').html('Изменить');
});

//Сохранение постоянной заметки
$(document).on('click', '#SaveNote', function(event){
    var id='{{$user->id}}';
    $('#NoteButton').html('Изменить');

    var note=$('#NoteInput').val();
    $('#NoteInput').toggleClass('vis');
    $('#SaveNote').toggleClass('vis');
    $('#deleteNoteForm').toggleClass('vis');
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/WebUserNote?id="+id+"&note="+note,
        dataType: 'json',
        success:function(data)
        {
            if (data==200)
              $('#PNote').html(note);
            else 
                alert("Ошибка сохранения");
        }
        })
});



////////////////////////////////////////Временные заметки 

//Сохранение новой заметки
$(document).on('click','#buttonNewNote',function(event){
    var id='{{$user->id}}';
    var note=$('#inputNewNote').val();
    $('#inputNewNote').val('');
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
            else
            updateHistoryNote();
        }
        }) 
    }
    else
    alert('Пустое поле');
});

//Нажатие на кнопку "Временные заметки"
$(document).on('click', '#historyNote', function(event){
    $('#divNewNote').toggleClass('vis');
    $('#LeftDiv').toggleClass('col-sm-5');
    $('#LeftDiv').toggleClass('col-sm-12');
    updateHistoryNote();
});

//Функция заполнение таблицы временных заметок
function updateHistoryNote()
{
    var id='{{$user->id}}';
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getHistoryNoteForPerson?id="+id,
        dataType: 'json',
        success:function(data)
        {
            let tbh="<tr><th>Заметка</th><th>Последние изменения</th><th>Дата создания</th><th>Дата редактирования</th><th style='min-width:140px'>Статус</th>/tr>"
                $('#THeadNote').html(tbh);
            let tbl = "";
            data.forEach(function(item, index, ar){
                tbl += "<tr>";
                tbl += "<td><p id='P"+item.id+"'>" + item.note + "</p><input type='text' value=\'"+item.note+"\' id='I"+item.id+"' class='vis'><button class='vis btn btn-xs btn-success updateNote' value="+item.id+" id='B"+item.id+"' >Сохранить</button></td>";
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

//Вход в режим редактирования временной заметки
$(document).on('click','#changeNoteBut',function(event){
    var id=$(this).val();
    console.log(id);
    $('#I'+id).toggleClass('vis');
    $('#B'+id).toggleClass('vis');

});

//Обновление заметки
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Страница промокодов
function pagePromocode()
{
    table = `
        <div class="card" style="margin-top:5px">
        <div class="card-header">
        Дисконтные карты покупателя:<b> {{$user->userName}} ({{$user->phone}})</b>
        </div><div class="card-body" >    
        <div class="row">
        @if(auth()->guard('admin')->user()->can('Promocode_edit')) 
        <div id = "LeftDivPromocode" class=" col-sm-5 layer"> 
        <h5>Создание дисконтных карт</h5>
        <td><form action="{{ route('WebUsers_addDiscount') }}" method="post">
                {{ csrf_field() }}
                <table class="table table-bordered table-striped table-hover">
                <tr>
                    <td>Размер скидки в %</td>
                    <td><input type="number" name='discount' size="5"></td>
                </tr>
                <tr>
                    <td>Тип</td>
                    <td><select name="type">
                            @foreach($promocodeType as $el)  
                                <option value={{$el->id}}>{{$el->title}}</option>              
                            @endforeach
                        </select></td>
                </tr>
                <tr>
                    <td>Дата начала</td>
                    <td><input type="date" name='dateStart'></td>
                </tr>
                <tr>
                    <td>Дата окончания</td>
                    <td><input type="date" name='dateEnd'></td>
                </tr>
                </table>
                <input type="hidden" name='id' value="{{$user->id}}">
                <input type="hidden" name='phone' value="{{$maskPhone}}">
                <input type="submit" class="btn btn-success btn-xs" value="Сохранить"> 
            </form></td></tr>
        @endif
        </div>

        <div  class='col-sm-7 layer'>
            <h5 id='titleNote' class=''>История дисконтных карт</h5>
            <table id="historyTableStock" class="table table-bordered table-striped table-hover" style="overflow: scroll;">
                <thead id="THead">
                </thead>
                <tbody id="TBody">
                </tbody>
            </table>
        </div></div></div></div>
        
        `;
        refreshDiscountHistory();
        $('#mainTable').html(table);
}

//Обновление таблицы истории промокодов
function refreshDiscountHistory()
{
    var id='{{$user->id}}';
    var nowTime='{{$now}}';
    var endTime='{{$end}}';
    var phone='{{$user->phone}}';
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getStockForPerson?id="+id,
        dataType: 'json',
        success:function(data)
        {
            let tbh="<tr><th>ID</th><th>Тип</th><th>Скидка</th><th>Дата начала</th><th>Дата окончания</th><th>Заказ</th><th>Дата создания</th><th style='min-width:150px'>Статус</th></tr>"
            $('#THead').html(tbh);
            let tbl = "";
            data.forEach(function(item, index, ar){
                tbl += "<tr>";
                tbl += "<td>" + item.id + "</td>";
                tbl += "<td>" + item.type + "</td>";
                tbl += "<td>" + item.discount + "</td>";
                tbl += "<td>" + item.startValidity + "</td>";
                tbl += "<td>" + item.expiration + "</td>";
                if (item.orderId==0)
                    tbl += "<td>-</td>";
                else 
                    tbl += "<td>" +  item.orderId  + "</td>";
                tbl += "<td>" + item.created_at + "</td>";

                var str="";
                var btn="";
                var cls="";
                if (item.orderId && item.typeId!=1)
                    tbl += "<td>Использованный</td>";
                else 
                {
                    if (item.status)
                    {
                        if (item.startValidity>=endTime)
                            str="Ожидает";
                        else 
                            if (item.expiration>=nowTime)
                                str="Активный";
                            else 
                                str="Просроченный";
                        btn=" btn-danger'>Деактивировать"
                    }
                    else
                    { 
                        str="Деактивирован"
                        btn=" btn-success'>Активировать";
                    }
                        tbl += " <td>"+str+"@if(auth()->guard('admin')->user()->can('Promocode_edit'))<br><button id='deactivateDiscount' value="+item.id+" class='btn btn-xs "+btn+"</button>"+
                        "<button style='margin-left:5px' class='btn btn-xs btn-info' id='longTime' value="+item.id+">Продлить</button>"+
                        "<input style='margin-top:10px' type='date' class='vis' id='inpTime_"+item.id+"'><button class='btn btn-xs btn-success vis butTime' value="+item.id+" id='butTime_"+item.id+"'>Сохранить</button>@endif</td> ";
                }
                    tbl += "<tr>";
            });
            $('#TBody').html(tbl);
         
            
        }
        })
}

//Вход в режим лонгирование промокода
$(document).on('click', '#longTime', function(event){
    var id = $(this).val();
    $('#inpTime_'+id).toggleClass('vis');
    $('#butTime_'+id).toggleClass('vis');
    if ($(this).html()=='Продлить')
        $(this).html('Отмена');
    else 
        $(this).html('Продлить');
});


//Лонгирование промокода
$(document).on('click', '.butTime', function(event){
    var id = $(this).val();
    var time = $('#inpTime_'+id).val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/extendPromocode?id="+id+"&newTime="+time,
        dataType: 'json',
        success:function(data)
        {
            if (data.code==200)
                refreshDiscountHistory();
            else 
                alert(data.msg);
        }
        })

});


//Деактивация промокода
$(document).on('click', '#deactivateDiscount', function(event){
    var id = $(this).val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/changeStatusDiscount?id="+id,
        dataType: 'json',
        success:function(data)
        {
            if (data.code==200)
                refreshDiscountHistory();
            else 
                alert(data.msg);
        }
        })

});


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function pageSettings()
{   
    var id = "{{$user->id}}";
    $.ajax({
        method: "GET",
        url: "/admin/getSettings?id="+id,
        dataType: 'json',
        success: function(data) {
            if (data.status==200)
            {
                var table = ` <div class="card" style="margin-top:5px">
                                <div class="card-header">
                                Настройки покупателя:<b> {{$user->userName}} ({{$user->phone}})</b>
                                </div><div class="card-body" >
                            <fieldset class='remove-all-styles' style='width:30%;'><legend class='remove-all-styles'>SMS Подтверждения:</legend>`;
                    table += '<lable>Отправка заказа без кода:</lable>';
                
                if (data.settings.historyMonth>0)
                    table += '<span> Постоянный покупатель</span>';
                else 
                {
                    var checked = ''; 
                    if (data.settings.autologin>0)
                        checked = 'checked';
                    table += '<input type="checkbox" id="autologin" '+checked+'>';
                }
                table += '<br><lable>Вход в личной кабинет без кода: </lable>';
                if (data.settings.autoownperm>0)
                    table += '<span style="margin-top:5px;"> Код отключен покупателем</span>';
                else 
                {
                    var checked = ''; 
                    if (data.settings.autoown>0)
                        checked = 'checked';
                    table += '<input type="checkbox" id="autoown" '+checked+'>';
                }               
                table +=  `</fieldset></div></div>`;
                $('#mainTable').html(table);
            }
            
        },
        error: function(data){
            console.log("Error: " + data);
        },
    });


   
}

//изменение значения нужен ли код для авторизации 
$(document).on('change', '#autologin', function(){
    var btnVal = "{{$user->phone}}";
    $.ajax({
        method: "GET",
        url: "/Api/WebUsersChangeAutologin?id="+btnVal,
        dataType: 'json',
        success: function(data) {
            showReturn('Сохранено');
            unShowReturn();
        },
        error: function(data){
            showReturn('Ошибка');
            unShowReturn();
            console.log("Error: " + data);
        },
    });
});


//изменение значения нужен ли код для авторизации в личный кабинет
$(document).on('change', '#autoown', function(){
    var id = "{{$user->id}}"
    $.ajax({
        method: "GET",
        url: "/admin/changeWebUserAutoown?id="+id,
        dataType: 'json',
        success: function(data) {
            showReturn('Сохранено');
            unShowReturn();
        },
        error: function(data){
            showReturn('Ошибка');
            unShowReturn();
            console.log("Error: " + data);
        },
    });
});


    function showReturn(text) {
        var popup = document.getElementById("myPopup");
        $('#popupText').html(text);
        popup.classList.toggle("show");
    }
    function unShowReturn(){setTimeout(showReturn, 2500);}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Страница SMS
function pageSMS()
{
    table = `<div class="card" style="margin-top:5px">
                <div class="card-header">Отправить SMS покупателю <b>{{$user->userName}} ({{$user->phone}}) </b></div>
                <div class="card-body" >    
                    <div class="row">
                        <div id = "LeftDiv" class=" col-sm-5 layer">  
                            <form action="{{ route('WebUsers_WebUsersSMS', $user->phone) }}" method="get">
                                {{ csrf_field() }}
                                <p style='margin-bottom:1px'>Введите текст SMS</p>
                                <textarea name='textSMS' cols="50" required></textarea></br>
                                <input type="submit" class="btn btn-success btn-xs" value="Отправить">
                            </form>
                        </div>
                        <div class=" col-sm-7 layer">
                            <h5 id='titleSMS'>История отпраленных SMS</h5>
                            <table id="historyTableSMS" class="table table-bordered table-striped table-hover" style="overflow: scroll;">
                                <thead id="THeadSMS">
                                </thead>
                                <tbody id="TBodySMS">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>    
            `;
    getUserSMS();
    $('#mainTable').html(table);

}

//Получение отправленых SMS покупателю и заполнение таблицы
function getUserSMS()
{
    var phone='{{$user->phone}}';
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getHistorySms",
        data: {phone:phone},
        dataType: 'json',
        success:function(data)
        {
            let tbh="<tr><th>ID</th><th>Текст</th><th>Дата отправки</th><th>Отправитель</th></tr>"
            $('#THeadSMS').html(tbh);
            let tbl = "";
            data.forEach(function(item, index, ar){
                tbl += "<tr>";
                tbl += "<td>" + item.id + "</td>";
                tbl += "<td>" + item.text + "</td>";
                tbl += "<td>" + item.created_at + "</td>";
                tbl += "<td>" + item.name + "</td>";
                tbl += "<tr>";
            });
            $('#TBodySMS').html(tbl);
         
        }
        })
}


</script>
@endsection


