
<div class="popup" id="myPopup">
    <span class="popuptext" >Добавлено</span>
</div>

<button class="btn btn-xs btn-info" style='max-width:150px' id='excelExport'>Выгрузить в Excel</button>
<div class="card" style="margin-top:10px">
    <div class="card-header">
        <div style="padding:5px;">
            Дата
                <input type="date" id="newDate" >
            Время
                <input type="time" id="newTime" >
            Имя
                <input type="text" id="newName" >
            Номер
                <input type="text" id="newPhone" size="13">
            Примечание
                <input type="text" id="newDescr" size="30" >
            <button class='btn-xs btn-success' id="newRecord">Создать</button>
        </div>
      
    </div>
    
    <input type="hidden" name="hidden_page" id="hidden_page" value={{$_GET['page'] ?? 1}} />
    <input type="hidden"  id="sorting" value={{$_GET['sorting'] ?? 'desc'}} />
    <param id="selectedId" value="">
    <div class="card-body">
        
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            <div id="sort" class="search" style="cursor: pointer;min-width:260px;">
                                    <param value="countOrder">
                                    Дата<br>
                                </div>
                            <input type="date" class="search" id="dateStartTime" value=
                            
                            @if (isset($nowTime)) 
                                {{$nowTime}}
                            @endif   >
                            <input type="date" class="search" id="dateEndTime">
                        </th>
                        <th>
                            Время
                        </th>
                        <th>
                            Имя
                        </th>
                        <th>
                            Телефон<br>
                            <input type="text" id="phone" class="search" value="{{$_GET['phone'] ?? ""}}" placeholder="Телефон" size="12"></input>
                        </th>
                        
                        <th>
                            Способ заказа
                        </th>
                        <th>
                            Итог
                        </th>
                        <th>
                            Дата доставки
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

.vis{
    display:none;}
    .promo-block{
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 25px;
}
.promo-block_item{
    width: 60%;
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
    height: 75px;
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

<script>


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
    var page = $('#hidden_page').val();
    var dateStart = $('#dateStartTime').val();
    var dateEnd = $('#dateEndTime').val();
    var sortingMethod = $('#sorting').val();
    var phone = $('#phone').val();
    $.ajax({
    headers: {'X-Access-Token': $('#api_token').val()},
    beforeSend: function(xhr) {
        xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
    },
    url:"/admin/getPhoneNumberStatistic?page="+page+"&dateStart="+dateStart+"&dateEnd="+dateEnd+"&sortingMethod="+sortingMethod+'&phone='+phone+'&path=/admin/phoneNumberStatistic',
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
// item.created_at.slice(11, 13)
function getWebItemsTbl(data){
    let tbl = "";
    data[0].forEach(function(item, index, ar){
        let time = item.created_at.slice(11,13) +':'+item.created_at.slice(14,16);
        let date = item.created_at.slice(8,10)+'.' +item.created_at.slice(5,7)+'.'+item.created_at.slice(0,4);
        tbl += "<tr>";
        tbl += "<td><p class=P"+item.id+">" + date + "</p><input class='vis I"+item.id+"' type='date' id=iDate"+item.id+" value='"+item.created_at.slice(0, 10)+"'></td>";
        tbl +="<td><p class=P"+item.id+">" + time +"</p><input class='vis I"+item.id+"' type='time' id=iTime"+item.id+" value='"+time+"'></td>";
        let name=item.name==undefined?" ":item.name;
        tbl += "<td><p class=P"+item.id+">" + $("<td>").text(name).html() + "</p><input class='vis I"+item.id+"' type='text' id=iName"+item.id+"  value='"+$("<td>").text(name).html()+"'></td>";
        tbl += "<td><p class=P"+item.id+">" + item.phone + "</p><input class='vis I"+item.id+"' type='text' id=iPhone"+item.id+"  value='"+item.phone+"'></td>";
        tbl += "<td>"+
                "<p class=P"+item.id+">" + item.deviceType + "</p>"+
                "<input class='vis I"+item.id+"' type='text' id=iDesc"+item.id+" value='"+item.deviceType+"'>"+
                "<button class='btn-xs btn-success vis saveBTN' id=B"+item.id+" value="+item.id+">Сохранить</button>"+
                "</td>";
        tbl += "<td>" + item.status + "</td>";
        tbl += "<td>" + item.deliveryDate + "</td>";
        
        if (item.id)
            tbl += '<td><button style="margin-left:0px;" class="btn btn-xs btn-info " id=changeBut value='+item.id+'>Редактировать</button><br>'+
                    '<button class="btn btn-xs btn-danger " id=deleteRecord value='+item.id+'>Удалить</button></td>';
        else 
            tbl += "<td></td>";

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



//Вхождение в режим редактирования
$(document).on('click','#changeBut',function(event){
    var id=$(this).val();
    if ($(this).html()=='Редактировать')
        $(this).html('Отмена');
    else 
        $(this).html('Редактировать');
    $('.I'+id).toggleClass('vis');
    $('#B'+id).toggleClass('vis');
    $('.P'+id).toggleClass('vis');
});






//Кнопка создания новой заметки
$(document).on('click','.saveBTN',function(event){
    var id=$(this).val();
    var descr=$('#iDesc'+id).val();
    var phone=$('#iPhone'+id).val();
    var name=$('#iName'+id).val();
    var date=$('#iDate'+id).val()+' '+$('#iTime'+id).val()+':00';
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                descr: descr,
                id: id,
                phone:phone,
                name:name,
                date:date
            },
        url:"/admin/updatePhoneNumberStatistic",
        dataType: 'json',
        success:function(data)
        {
            if (data.code!=200)
                alert("Ошибка сохранения");
            else
            {
                fetch_data();
            }
        }
        }) 
});


$(document).on('click','#newRecord',function(event){
    var str = '';
    var descr=$('#newDescr').val();
    var phone=$('#newPhone').val();
    var name=$('#newName').val();

    if (!descr)
        str +='"Примечание"';
    if (!phone)
        str +=' "Телефон"';
    if (!name)
        str +=' "Имя"';
    if (str)
        alert('Поле(я): '+str+' не заполнено');

    var date=$('#newDate').val()+' '+$('#newTime').val();
    if (!str)
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                descr: descr,
                phone:phone,
                name:name,
                date:date
            },
        url:"/admin/createPhoneNumberStatistic",
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
                $('#newDescr').val('');
                $('#newPhone').val('');
                $('#newName').val('');
                $('#newDate').val('');
                $('#newTime').val('');
            }
        }
        }) 
});

$(document).on('click','#deleteRecord',function(event){
    var id=$(this).val();
    if (confirm("Вы уверены?"))
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                id: id
            },
        url:"/admin/deletePhoneNumberStatistic",
        dataType: 'json',
        success:function(data)
        {
            if (data.code!=200)
                alert("Ошибка сохранения");
            else
                fetch_data();
        }
    }) 
});

$(document).on('click','#excelExport',function(){
   
    var dateStart=$('#dateStartTime').val();
    var dateEnd=$('#dateEndTime').val();
    window.location.href='/admin/exportPhoneNumberStatistic?dateStart='+dateStart+'&dateEnd='+dateEnd;

});


function showReturn() 
{
    var popup = document.getElementById("myPopup");
    popup.classList.toggle("show");
}
function unShowReturn(){setTimeout(showReturn, 1500);}
</script>
