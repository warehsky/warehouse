@extends('layouts.admin')
@section('content')
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Редактирование лимита по заказам

        <button style='margin-left:5px;' class='btn btn-xs btn-info' id='modalBtn'>Установить лимит на заказы</button>
    </div>

    <div class="card-body">
        <table class=" table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>
                        ID
                    </th>
                    <th>
                        Название
                    </th>
                    <th>
                        Доставка 
                        <input type="date" class='search' id="dateTable" value = {{$nowTime}}>
                        от
                        <input type="time" class='search' id="timeStart">
                        до 
                        <input type="time" class='search' id="timeEnd">
                    </th>
                    <th>
                        Базовое ограничение
                    </th>
                    <th>
                        Текущее ограничение
                    </th>
                   
                </tr>
            </thead>
            <tbody id="searchTbody">
            
            </tbody>
        </table>

            


    </div>
</div>


<div id="modalWindow" class="modal">

  <!-- Модальное содержание -->
  <div class="modal-content">
      <div>
  <span>Установка ограничения по заказам на волну</span> 
  <span class="close">&times;</span>
  </div>
    <hr style="width:100%">
    <div style='margin-top:10px;'>
        <label>Дата</label>
        <input type="date" class="setOrderLimit" id="dateDisable" value = {{$nowTime}}>
        <span>&emsp;<input type="checkbox" id="baseLimit" class='setOrderLimit'> Базовое ограничение</span>
        <br>
        <label>Волна </label>
        <select class='setOrderLimit' id="timeLimit">
                <option>Не выбрано</option>
            @foreach($waves as $v) 
                <option value="{{ $v->time }}">{{$v->time}}</option>
            @endforeach                            
        </select>
        <span id='editLimitInWaves'></span>
        <br>
        <label>Лимит заказов</label>
        <input type="number" id="CountOrder">
        <br>
        <button id='disableButton' class='btn-xs btn-success'>Применить</button>
    </div>
    
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
  padding-top: 10px; /* Расположение коробки */
  left: 0;
  top: 0;
  width: 100%; /* Полная ширина */
  height: 33%; /* Полная высота */
  overflow: auto; /* Включите прокрутку, если это необходимо */
  background-color: rgb(0,0,0); /* Цвет запасной вариант  */
  background-color: rgba(0,0,0,0.1); /*Черный с непрозрачностью */
}

/* Модальное содержание */
.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 90%;
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
</style>
@endsection
@section('scripts')
@parent
<script>

function fetch_data()
    {
        var timeStart = $('#timeStart').val();
        var timeEnd = $('#timeEnd').val();
        var date = $('#dateTable').val();
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/timeWaves/getOrderLimit",
        data: 
            {
                date: date,
                timeStart: timeStart,
                timeEnd: timeEnd
            },
        dataType: 'json',
        success:function(data)
        {
            console.log(data);
            $('#searchTbody').html('');
            let tbl=getWebItemsTbl(data);
            $('#searchTbody').html(tbl);
        }
        })
    }

function getWebItemsTbl(data){
    let tbl = "";
    let status= [];
    
    data.forEach(function(item, index, ar){
        tbl += "<tr itemId="+item.id+">";
        tbl += "<td class='tdSelect' style='cursor:pointer'>" + item.id + "</td>";
        tbl += "<td>" + item.description + "</td>";
        tbl += "<td>" + item.timeFrom.slice(0,5) +" - "+ item.timeTo.slice(0,5) + "</td>";
        tbl += "<td><span>" + (item.orderLimit==0? "Не ограничено":item.orderLimit ) + "</span>";
        var countOrders; 
        if (item.countOrder!=null)
            countOrders = item.countOrder;
        else 
            countOrders = "Используется базовое ("+(item.orderLimit==0 ? "Не ограничено" : item.orderLimit)+")";

        if (countOrders == 0)
        countOrders = "Не ограничено"; 

        tbl += "<td>" + countOrders + "</td>";
        
        tbl += "<tr>";
    });
    return tbl;
}



$(document).ready(function(){
    fetch_data();
});

$(document).on('change','.search',function(){
    fetch_data();
});

$(document).on('change','.setOrderLimit',function(){
    var baseLimit =  $('#baseLimit').is(":checked") ? true : false
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                date: $('#dateDisable').val(), 
                time: $('#timeLimit').val(),
                baseLimit : baseLimit
            },
        url:"/admin/timeWaves/getEditLimitOrder",
        dataType: 'json',
        type: "get",
        success:function(data)
        {
            console.log(200);
            var temp = `Выбрано ${data.count} волн (<b>${(baseLimit ? 'Базовое' : 'Текущее' )}</b> ограничение: ${(data.limitOrder !=0 ? data.limitOrder : "Не ограничено")})`;
            $('#editLimitInWaves').html(temp);
        }
    });
});


$(document).on('click', '#disableButton',function(){
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                date: $('#dateDisable').val(), 
                time: $('#timeLimit').val(),
                countOrder:$('#CountOrder').val(),
                baseLimit : $('#baseLimit').is(":checked") ? true : false
            },
        url:"/admin/timeWaves/saveOrderLimit",
        dataType: 'json',
        type: "get",
        success:function(data)
        {
            if (data.code==200)
                fetch_data();
                alert(data.msg);
        }
    });
});







$(document).on('click','.tdSelect',function(){
    $('#waveId').val($('#waveId').val()+$(this).html()+',');
});

//Открытие модального окна установки лимита по заказам
$(document).on('click','#modalBtn',function(){
    document.getElementById('modalWindow').style.display='block';
});
//Закрытие модального окна установки лимита по заказам нажатием кнопки 
$(document).on('click','.close',function(){
    document.getElementById('modalWindow').style.display='none';
});
</script>
@endsection


