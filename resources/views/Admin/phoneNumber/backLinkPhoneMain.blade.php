
<div class="popup" id="myPopup">
    <span class="popuptext" >Изменено</span>
</div>

<button class="btn btn-xs btn-info" style='max-width:150px' id='excelExport_backLink'>Выгрузить в Excel</button>
<div class="card" style="margin-top:10px">
    <div class="card-header">
        Обратная связь 377
    </div>
    
    <input type="hidden" name="hidden_page_backLink" id="hidden_page_backLink" value={{$_GET['page'] ?? 1}} />
    <input type="hidden"  id="sorting_BackLink" value={{$_GET['sorting'] ?? 'desc'}} />
    <param id="selectedId" value="">
    <div class="card-body">
        
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            ID Заказа
                        </th>
                        <th>
                            <div id="sort" class="search" style="cursor: pointer;min-width:260px;">
                                    <param value="countOrder">
                                    Дата доставки<br>
                                </div>
                            <input type="date" class="search" id="dateStartTimeBack" value=
                            
                            @if (isset($nowTime)) 
                                {{$nowTime}}
                            @endif   >
                            <input type="date" class="search" id="dateEndTimeBack" value=
                            @if (isset($nowTime)) 
                                {{$nowTime}}
                            @endif   >
                        </th>
                        <th>
                            Имя
                        </th>
                        <th>
                            Телефон<br>
                            <input type="text" id="phone_BackLink" class="search" value="{{$_GET['phone'] ?? ""}}" placeholder="Телефон" size="12"></input>
                        </th>
                        <th>
                            История
                        </th>
                        <th>
                            Комментарий
                        </th>
                        <th>
                            Статус отзыва
                        </th>
                        <th>
                            Источник
                        </th>
                        <th>
                            Виновный
                        </th>
                    
                       
                        <th style="min-width:160px">

                        </th>
                    </tr>
                </thead>
                <tbody id="searchTbodyBackLink">
                    
                </tbody>
            </table>
            <div id="pagLinkBackLink"> </div>
        </div>


    </div>
</div>




<div id="modalWindow_backLink" class="modal">

  <!-- Модальное содержание -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <div class="modalP">
    
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


.modalP{
    margin:0;
}
/* Модальный (фон) */
.modal {
  display: none; /* Скрыто по умолчанию */
  /* position: fixed; Оставаться на месте */
  z-index: 1; /* Сидеть на вершине */
  padding-top: 50px; /* Расположение коробки */
  left: 0;
  top: 0;
  width: 100%; /* Полная ширина */
  /* height: 100%; Полная высота */
  overflow: auto; /* Включите прокрутку, если это необходимо */
  background-color: rgb(0,0,0); /* Цвет запасной вариант  */
  
  background-color: rgba(0,0,0,0); /*Черный с непрозрачностью */
}

/* Модальное содержание */
.modal-content {
  background-color: #fefefe;
  margin: 40px;
  padding: 5px;
  border: 1px solid #888;
  box-shadow: 0 0 5px rgba(0,0,0,0.5);
  width: 400px;
}

/* Кнопка закрытия */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 14px;
  font-weight: bold;
  align-self: flex-end;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}
</style>

<script>


//сортировка по дате
$(document).on('click', '#sort', function(event){
    $('#hidden_page_backLink').val(1);
    var sr=$('#sorting_BackLink').val();
    $('#hidden_page_backLink').val(1);
    $('#sortingField').val($(this).find('param').val());
    if(sr=='asc')
        $('#sorting_BackLink').val('desc');
    else 
        $('#sorting_BackLink').val('asc');
        fetch_data_backLink();
});

//построение таблицы покупателей
function fetch_data_backLink()
{
    var page = $('#hidden_page_backLink').val();
    var dateStart = $('#dateStartTimeBack').val();
    var dateEnd = $('#dateEndTimeBack').val();
    var sortingMethod = $('#sorting_BackLink').val();
    var phone = $('#phone_BackLink').val();
    $.ajax({
    headers: {'X-Access-Token': $('#api_token').val()},
    beforeSend: function(xhr) {
        xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
    },
    url:"/admin/getPhoneNumberBackLink?page="+page+"&dateStart="+dateStart+"&dateEnd="+dateEnd+"&sortingMethod="+sortingMethod+'&phone='+phone+'&path=/admin/backLinkPhoneIndex',
    dataType: 'json',
    success:function(data)
    {
        $('#searchTbodyBackLink').html('');
        let tbl=getWebItemsTbl_BackLink(data);
        $("#pagLinkBackLink").empty().append(data.links);
        $('#searchTbodyBackLink').html(tbl);
    }
    })
}

function getWebItemsTbl_BackLink(data){
    let tbl = "";
    data[0].forEach(function(item, index, ar){
        let func = noteModalButton(data[1],item);
        let modalBTN='';
        if (func)
                modalBTN = '<button class="btn" style="background: url(/img/icons/nitification.svg) no-repeat; min-width:30px;min-height:30px; margin-left:10px;" id="modalBtn" value="'+func+'"></button>';
                

        let date = item.created_at.slice(8,10)+'.' +item.created_at.slice(5,7)+'.'+item.created_at.slice(0,4);
        tbl += "<tr itemId="+item.id+" >";
        tbl += "<td>" + item.id + "</td>";
        tbl += "<td>" + date + "</td>";
        let name=item.name==undefined?" ":item.name;
        tbl += "<td>" + $("<td>").text(name).html()  + "</td>";
        tbl += "<td>" + item.phone + modalBTN + "</td>"; //Заметку добавить
        
        tbl += "<td>Заказов: " + item.count + "</td>";
        var comment = '';
        if (item.comment!=null)
            comment=item.comment;
        tbl += "<td>"+
                "<p class=P_B"+item.id+">" + comment + "</p>"+
                "<input class='vis I_B"+item.id+"' type='text' id=iComment"+item.id+" value='"+comment+"'>"+
                "</td>";

        tbl += "<td><select class='recSel' id='recallSelect"+item.id+"' disabled>"+
            "<option value=''>Не выбрано</option>";
            if (item.recall==1)
                tbl +="<option value='1' selected>Положительный</option><option value='0'>Негативный</option>";
            else 
                if (item.recall==0)
                    tbl +="<option value='1'>Положительный</option><option value='0' selected>Негативный</option>";
                else
                    tbl +="<option value='1'>Положительный</option><option value='0'>Негативный</option>";

                tbl +="<option value='2' ";
                if (item.recall==2)
                tbl +="selected";
                tbl +=">Не отвечает</option></select></td>";

        if (item.count>1 && !item.source)
            tbl += "<td>Повторный заказ</td>";
        else 
        {
            var source = item.source ? item.source : ''
            tbl += "<td><p style='margin:0;' id='infoP_"+item.id+"'>" + source + "</p>"+
            "<input size=15 type='text' class='vis' id='infoInput_"+item.id+"' value="+source+" ><br></td>";
        }

        
        tbl += "<td><select id='guiltySelect"+item.id+"' disabled>";

        data[2].forEach(function(i, ind, a){
            var sel = "";
            if (i.id == item.guilty)
            sel = "selected";

            tbl += "<option value='"+i.id+"' "+sel+">"+i.guilty+"</option>";
        });

        
        tbl += "</select></td>";



        tbl += '<td><button style="margin-left:0px;" class="btn btn-xs btn-info " id=changeBut_backLink value='+item.id+'>Редактировать</button>'+
        "<button class='btn-xs btn-success vis saveBTN_BackLink' style='margin-left:5px' id=B_B"+item.id+" value="+item.id+">Сохранить</button>"+
        '</td>';
    });
    return tbl;
}

//Проверяет есть ли заметки для определенного пользователя, если находит возвращает строку html для модального окна
function noteModalButton(data,itemMain)
{
    let notes = data.filter(item => item.webUserId == itemMain.webUserId);
    let dataModal='';
    if (itemMain.note)
        dataModal+='<p style=margin:0;><b>'+itemMain.name+'</b> ('+itemMain.phone+')'+'</p><p style=margin:0;><b>Основная заметка:</b></p><p style=margin:0;>&bull; '+itemMain.note+'</p>';
    if (notes.length != 0)
    {
        dataModal+='<p style=margin:0;><b>Временные заметки:</b></p>';
        notes.forEach(function(item, index, ar){
            dataModal+='<p style=margin:0;>&bull; '+item.note+'</p>';
        });
    }
    return dataModal;
}

//поиск
$(document).on('keyup change', '.search', function(){
    $('#hidden_page_backLink').val(1);
    fetch_data_backLink();
});

//начальная загрузка страницы
$(document).ready(function(){
    $('#hidden_page_backLink').val(1);
    fetch_data_backLink();
});

//пагинация
$(document).on('click', '.pagination a', function(event){
    event.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    $('#hidden_page_backLink').val(page);
    $('li').removeClass('active');
    $(this).parent().addClass('active');
    fetch_data_backLink();
});

//Вхождение в режим редактирования
$(document).on('click','#changeBut_backLink',function(event){
    var id=$(this).val();
    if ($(this).html()=='Редактировать')
        $(this).html('Отмена');
    else 
        $(this).html('Редактировать');
    $('.I_B'+id).toggleClass('vis');
    $('#B_B'+id).toggleClass('vis');
    $('#infoP_'+id).toggleClass('vis');
    $('#infoInput_'+id).toggleClass('vis');
    $('.P_B'+id).toggleClass('vis');
    if ($('#recallSelect'+id)[0].disabled)
    {
        $('#recallSelect'+id).removeAttr('disabled');
        var selectRecall = $(this).parents()[1].getElementsByClassName('recSel')[0].value;
        if (selectRecall === '0')
            $('#guiltySelect'+id).removeAttr('disabled');
    }
    else  
    {
        $('#recallSelect'+id).attr('disabled', 'disabled');
        $('#guiltySelect'+id).attr('disabled', 'disabled');
    }
    
});

$(document).on('change','.recSel',function(){
    var id = $(this).val();
    var idSelect = $(this).parents()[1].getAttribute('itemId');

    if (id === '0')
        $('#guiltySelect'+idSelect).removeAttr('disabled');
    else 
        $('#guiltySelect'+idSelect).attr('disabled', 'disabled');
});

//Кнопка создания нового комментария
$(document).on('click','.saveBTN_BackLink',function(event){
    var orderId=$(this).val();
    var comment=$('#iComment'+orderId).val();
    var recall=$('#recallSelect'+orderId).val();
    var source=$('#infoInput_'+orderId).val();
    var guilty=$('#guiltySelect'+orderId).val();
    var str='';

    if (recall!= 0)
        guilty = 1;
    if (recall==2 && comment=='')
        comment='Не отвечает';
    
    if (recall==0 && guilty==1)
        str+='Виновный ';

    if (comment=='')
        str+='Комментарий';
    if (recall=='')
        str+=' Статус отзыва';
    if (str!='')
        alert('Поле '+str+' обязательно к заполнению');
    else
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        data: 
            {
                orderId: orderId,
                comment: comment,
                recall: recall,
                source: source,
                guilty: guilty
            },
        url:"/admin/addPhoneNumberBackLink",
        dataType: 'json',
        success:function(data)
        {
            if (data.code!=200)
                alert("Ошибка сохранения");
            else
            {
                fetch_data_backLink();
                showReturn_backLink();
                unShowReturn_backLink();
            }
        }
        }) 
});


//Экспорт в excel
$(document).on('click','#excelExport_backLink',function()
{
    var dateStart=$('#dateStartTimeBack').val();
    var dateEnd=$('#dateEndTimeBack').val();
    window.location.href='/admin/exportPhoneNumberBackLink?dateStart='+dateStart+'&dateEnd='+dateEnd;
});


function showReturn_backLink() 
{
    var popup = document.getElementById("myPopup");
    popup.classList.toggle("show");
}
function unShowReturn_backLink(){setTimeout(showReturn_backLink, 1500);}


$(document).on('click','#modalBtn',function(){
    document.getElementById('modalWindow_backLink').style.display='block';
    $('.modalP').html($(this).val());
});
//Закрытие модального окна нажатием кнопки 
$(document).on('click','.close',function(){
    document.getElementById('modalWindow_backLink').style.display='none';
});
//Закрытие модального окна убиранием фокуса 
$(document).on('click',window,function(event){
    if (event.target == document.getElementById('modalWindow_backLink')){
    document.getElementById('modalWindow_backLink').style.display='none';}
});
</script>
