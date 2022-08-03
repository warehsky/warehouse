@extends('layouts.admin')
@section('content')
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Редактирование волн

        <button style='margin-left:5px;' class='btn btn-xs btn-info' id='modalBtn'>Отключение волн</button>
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
                        <select id='searchName'></select>
                    </th>
                    <th>
                        Доставка от
                    </th>
                    <th>
                        Доставка до
                    </th>
                    <th>
                        Статус
                    </th>
                    <th>

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
  <span>Отключение волн</span> 
  <span class="close">&times;</span>
  </div>
    <hr style="width:100%">
    

    <div >
        <label>Дата</label>
        <input type="date" id="dateDisable">
        <label style='cursor:help; margin-left:20px' title='Код волны нужно вводить через зяпятую и без пробелов (159,34,35,11)'> Код волны </label>
        <input type="text" id="waveId" value=''>
    </div>
    <label style='margin-bottom:0; margin-top:10px'>Зона</label>
    <select name="selectedZone[]" id='timeWaveDisable' class="select2" multiple="multiple">
    </select>

    <div style='margin-top:10px;'>
        <label>Время с </label>
        <input type="time" id="timeStart">
        <label> по </label>
        <input type="time" id="timeEnd">
        &nbsp;&nbsp;&nbsp;
        <label>Дата начала блокировки</label>
        <input type="date" id="dateDisableStart">
        &nbsp;
        <label>Время начала блокировки</label>
        <input type="time" id="timeDisableStart">
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
var idSeach='-1';

function fetch_data()
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getTimeWaves?id="+idSeach,
        dataType: 'json',
        success:function(data)
        {
            $('#searchTbody').html('');
            let tbl=getWebItemsTbl(data);
            $('#searchTbody').html(tbl);
        }
        })
    }

function getWebItemsTbl(data){
    let tbl = "";
    let status= [];
    
    data[0].forEach(function(item, index, ar){
        disable='';
        if (Object.getOwnPropertyNames(data[1]).indexOf(String(item.id))!=-1)
        {
            data[1][item.id].forEach(function(value, vIndex, vAr){
                disable+='<br><span style="font-size: 12px;line-height: 12px; color: red;">Выключено '+value+'</span>'+
                '<span id="disButton" style="cursor:pointer; font-size: 12px;line-height: 12px; color: red;">'+
                '<param id="date" value='+value+'>'+
                '<param id="wave" value='+item.id+'> (Отмена)</span>';
            });
        } 
        tbl += "<tr>";
        tbl += "<td class='tdSelect' style='cursor:pointer'>" + item.id + "</td>";
        tbl += "<td>" + item.description + "</td>";
        tbl += "<td>" + item.timeFrom.slice(0,5) + "</td>";
        tbl += "<td>" + item.timeTo.slice(0,5) + "</td>";
        if (item.deleted)
            status=['Выключено','Включить'];
        else 
            status=['Включено','Выключить'];
            tbl += "<td>" + status[0] + disable+"</p></td>";
        tbl += "<td><button class='btn btn-xs btn-info' id='changeStatus' value="+item.id+">"+status[1]+"</td>";
        tbl += "<tr>";
    });
    return tbl;
}

$(document).on('click','#changeStatus',function(event){
    var id = $(this).val();
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/timeWaves/changeStatus?id="+id,
        dataType: 'json',
        success:function(data)
        {
            if (data.code==200)
            {
                fetch_data(idSeach);
            }
        }
        })
});



$(document).on('click', 'option', function(){
    idSeach = $(this).val();
    fetch_data(idSeach);
});

$(document).ready(function(){
    fetch_data(idSeach);
    $('.select2').select2();
    getSelectDeliveryZones();
});

$(document).on('click', '#disableButton',function(){
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                selectedZone: $('#timeWaveDisable').val(),
                dateDisable: $('#dateDisable').val(), 
                timeStart: $('#timeStart').val(), 
                timeEnd: $('#timeEnd').val(),
                waveId:$('#waveId').val(),
                dateDisableStart:$('#dateDisableStart').val(),
                timeDisableStart:$('#timeDisableStart').val()
            },
        url:"/admin/timeWaves/timeWavesDisable",
        dataType: 'json',
        type: "get",
        success:function(data)
        {
            if (data.code==200)
                fetch_data(idSeach);
                alert(data.msg);
        }
    });
});

$(document).on('click', '#disButton',function(){
    var date=$(this).find('#date').val();
    var wave=$(this).find('#wave').val();
    if (confirm('Включить волну?'))
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                date: date,
                wave: wave
            },
        url:"/admin/timeWaves/deleteTimeWaveDisable",
        dataType: 'json',
        type: "get",
        success:function(data)
        {
            fetch_data(idSeach);
        }
    });
});

function getSelectDeliveryZones()
{
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getDeliveryZones",
        dataType: 'json',
        success:function(data)
        {
            var op="";
            op+='<option value="-1" selected>Все зоны</option>';
            for(var i=0;i<data.length;i++)
            {
                op+='<option value='+data[i].id+'>'+data[i].description+'</option>';
            }

        $('#searchName').html(" ");
        $('#searchName').append(op);
        $('#timeWaveDisable').html();
        $('#timeWaveDisable').append(op);
        }
        })
}

$(document).on('select2:select','#timeWaveDisable',function(e){

        if ($(this).select2('data').length>=2)
        {
            $('#timeWaveDisable').find("option[value='-1']").prop("selected", false);
            $('#timeWaveDisable').find("option[value='-1']").trigger('change');
           
        }
        else 
            $('#timeWaveDisable').find("option[value='-1']").trigger('change');
});


$(document).on('click','.tdSelect',function(){
    $('#waveId').val($('#waveId').val()+$(this).html()+',');
});

//Открытие модального окна отключение зон
$(document).on('click','#modalBtn',function(){
    document.getElementById('modalWindow').style.display='block';
});
//Закрытие модального окна нажатием кнопки 
$(document).on('click','.close',function(){
    document.getElementById('modalWindow').style.display='none';
});

//Закрытие модального окна убиранием фокуса 
/*
$(document).on('click',window,function(event){
    if (event.target == document.getElementById('modalWindow')){
    document.getElementById('modalWindow').style.display='none';}
});
*/
</script>
@endsection


