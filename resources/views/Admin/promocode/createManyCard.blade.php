@extends('layouts.admin')
@section('content')

<div class="popup" id="myPopup">
    <span class="popuptext" >Добавлено</span>
</div>

@if(session('error'))
    <div class="alert alert-danger">
        {{session('error')}}
    </div>
@endif
@if(session('success'))
    <div style='margin-top:10px' class="alert alert-success">
        {{session('success')}}
    </div>
@endif

<div class="popup" id="myPopup">
    <span id='popupText' class="popuptext">Сохранено</span>
</div>

<div class="card" style="margin-top:10px">

    <div class="card-header">
        Дисконтная карта
             
    </div>
    
    <div class="card-body">
        <div class="row">

            <div class="col-sm-3" style="overflow: auto; height:600px;">

            <div style='border: 1px solid black; border-radius: 8px; padding:2px; max-width: 210px; margin-bottom:20px'>
                <span>Загрузка из файла Excel <input type="checkbox" id="typeInput"> </span>
            </div> 
            <button class='btn-xs btn-info' id='monthWebUsers' title='Клиенты которые получили заказ за последний месяц'>Постоянные клиенты</button>
            <div class="card unShow" id='loadExcel' style="margin-top:10px">
                <div class="card-header">
                    Загрузка из Excel
                </div>

                <div class="card-body">
                    <input type="file" name="file" id="file" style='margin-bottom:10px'>
                    <p style='margin-bottom:10px'>Столбец <input type="number" id="column" size="4"></p>
                    <p style='margin-bottom:10px'>Строка от <input type="number" id="lineFirst" size="4"> до <input type="number" id="lineLast" size="4"></p>
                    <button class='btn btn-xs btn-success' id='ExcelFile'>Добавить</button>
                </div>
            </div>

            <div id='manualInput'>
                <p>Все покупатели</p>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <th>Телефон<br>
                        <input type="text" id="searchID" class="search" value="{{$_GET['id'] ?? ""}}" placeholder="Телефон" size="20"></input>
                        </th>
                    </thead>
                    <tbody id="tableWebUsers">

                    </tbody>
                </table>
                <div id="pagLink"> </div>
            </div>
            </div>

            <div class="col-sm-5">
                <p style="margin-left:10px;">Свойства дисконтной карты</p>
                <form action="/admin/addManyDiscountCart" method="post" style=" margin-left:10px; margin-bottom:10px">
                    {{ csrf_field() }}
                    
                    <table  class="table table-striped table-bordered table-hover">
                        <tr>
                            <td>Код</td>
                            <td><input type="text" name='title' id='inputUpper'  maxlength="10"></td>
                        </tr>
                        <tr>
                            <td>Тип</td>
                            <td><select name="type" id='discountType'></select></td>
                        </tr>
                        <tr>
                            <td id='tdDiscount'>Размер скидки в %</td>
                            <td><input type="number" name='discount' size=4></td>
                        </tr>

                        <tr>
                            <td id='tdStart'>Дата начала действия промокода</td>
                            <td><input type="date" name='startValidity' id='startValidity'></td>
                        </tr>
                        <tr>
                            <td id='tdEnd'>Дата окончания действия промокода</td>
                            <td><input type="date" name='expiration' id='expiration'></td>
                        </tr>
                        <tr>
                            <td>Акция "Приведи друга"</td>
                            <td><input type="checkbox" name="friendPromocode" id="friendPromocode"></td>
                        </tr>
                        <tr>
                            <td>Включить рассылку SMS-сообщений</td>
                            <td><input type="checkbox" name="sendSms" id="sms"></td>
                        </tr>
                        <tr>
                        <!-- #promocode - цифро-буквенный код промокода -->
                        <td title='При вводе определенного кодового слова подставляется:
    #user - имя покупателя
    #promostart - дата начала действия промокода
    #promoend - дата окончания действия промокода'><span style='cursor: pointer' id='openHelp'>Сообщение для рассылки (?)</span>

                            <div id='helpDiv' class='unShow div_block'>               
                                <p  id='addText' style='cursor:pointer;' class='pUser'><param value='#user'><b>#user</b> - имя покупателя</p>
                                <p  id='addText' style='cursor:pointer;' class='unShow'><param value='#promocode'><b>#promocode</b> - цифро-буквенный код промокода</p>
                                <p  id='addText' style='cursor:pointer;'><param value='#promostart'><b>#promostart</b> - дата начала действия промокода</p>
                                <p  id='addText' style='cursor:pointer;'><param value='#promoend'><b>#promoend</b> - дата окончания действия промокода</p>
                            </div>
                        </td>
                            <td><textarea name="smsMsg" id="smsMsg" cols="40" rows="3"></textarea></td>
                        </tr>
                            <input type="hidden" name="id" id="InputIds">
                            <input type="hidden" name="idNotInBase" id="InputIdsNotInBase">
                    </table>
                    <div class="btn-box">
                        <button class='btn-sm btn-success'>Создать</button>
                        <p id='inputText'></p>
                    </div>
                </form>


            
            </div>

            <div class="col-sm-2" style="overflow: auto; height:600px;">



                <p>Выбранные покупатели</p>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <th>Телефон</th>
                        <th></th>
                    </thead>
                    <tbody id="selectedWebUsers">

                    </tbody>
                </table>
            </div>

            <div class="col-sm-2" style="overflow: auto; height:600px;">



                <p>Покупатели которых нет в базе</p>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <th>Телефон</th>
                        <th></th>
                    </thead>
                    <tbody id="selectedWebUsersNotInBase">

                    </tbody>
                </table>
            </div>

        </div>





    </div>
</div>

<a style="margin-top:20px;" class="btn btn-default" href="{{ route('promocode.index') }}">Назад</a>


<meta name="csrf-token" content="{{ csrf_token() }}" />
<input type="hidden" name="hidden_page" id="hidden_page" value={{$_GET['page'] ?? 1}} />
<style>
.div_block{
    border: 1px solid black;
    padding: 7px;
    width: 100%;
    border-radius: 10px;
}

.unShow{
    display:none;
}
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
.pagination{
    line-height: none!important;
}
.page-link{
    padding: 2px 2px;
}
p{
    margin: 0;
}
.btn-box{
    display: flex;
    align-items: center;
    justify-content: flex-start;
}
.btn-box p{
    margin-left: 20px;
}
</style>
@endsection
@section('scripts')
@parent
<script>
//При вводе промокода переводит все буквы в верхний регистр
$(document).on('keyup','#inputUpper',function() {
    $(this).val($(this).val().toUpperCase());
});

//При вводе промокода переводит все буквы в верхний регистр
$(document).on('input','#inputUpper', function() {
    $(this).val($(this).val().replace(/[^A-Za-z0-9]/, ''));
});

//загрузка страницы
$(document).ready(function(){
    $('#hidden_page').val(1);
    document.getElementById('typeInput').checked = false;
    reloadSelectDiscountType();
    reloadWebUsers();
});

//Получение типов для промокодов
function reloadSelectDiscountType()
{
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/showDiscountType",
        dataType: 'json',
        success:function(data)
        {
            var op="";
            for(var i=0;i<data.length;i++)
            {
                op+='<option value='+data[i].id+'>'+data[i].title+'</option>';
            }

        $('#discountType').html(" ");
        $('#discountType').append(op);
        }
        })
}

//Изменение описания поля размер скидки или сертификата
$(document).on('click','#discountType',function(){
    if ($(this).val()==3)
    {
        $('#tdDiscount').html('Размер сертификата');
        $('#tdStart').html('Дата начала действия сертификата');
        $('#tdEnd').html('Дата окончания действия сертификата');
        $('#friendPromocode').prop('disabled', true);
    }
    else 
    {
        $('#tdDiscount').html('Размер скидки в %');
        $('#tdStart').html('Дата начала действия промокода');
        $('#tdEnd').html('Дата окончания действия промокода');
        $('#friendPromocode').prop('disabled', false);
    }
});

//Получение таблицы пользователей
function reloadWebUsers()
{
    var id = $('#searchID').val();
    var name = $('#searchName').val();
    var page = $('#hidden_page').val();

    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/AllWebUsers?page="+page+"&id="+id+"&path=/admin/WebUsers&selectedIds="+ids,
        dataType: 'json',
        success:function(data)
        {
            let tbl = "";
        data[0].forEach(function(item, index, ar){
            tbl += "<tr>";
            tbl += "<td style='cursor: pointer' id='WebUserTD' userId="+item.id+" userName="+item.userName+" ><p>" + item.phone + "</p></td>";
            tbl += "<tr>";
        });

        var issetPhoneInBase = false;
        var issetPhone = false;

        idsWebUsers.forEach((value, key) => {
            if (value==$('#searchID').val())
            {
                issetPhoneInBase=true;
                return issetPhoneInBase;
            }
        })

        idsWebUsersNotInBase.forEach((value, key) => {
            if (value==$('#searchID').val())
            {
                issetPhone=true;
                return issetPhone;
            }
        })

        tbl += "<tr>";
        if (tbl=="<tr>" && !issetPhone && !issetPhoneInBase)
            tbl += "<td>Номера нет в базе данных <button class='btn-xs btn-success' id=addFromLeftBar >Добавить</button></td>";

        if (tbl=="<tr>" && issetPhoneInBase)
            tbl += "<td>Номер уже используется</td>";

        tbl += "</tr>";

        $('#tableWebUsers').html(" ");
        $('#tableWebUsers').html(tbl);
        $("#pagLink").empty().append(data.links);
        }
        })
}

//Пагинация
$(document).on('click', '.pagination a', function(event){
    event.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    $('#hidden_page').val(page);
    $('li').removeClass('active');
    $(this).parent().addClass('active');
    reloadWebUsers();
});

//поиск
$(document).on('keyup change', '.search', function(){
    $('#hidden_page').val(1);
    reloadWebUsers();
});

//открыть окно помощи при вводе сообщения для рассылке смс
$(document).on('click','#openHelp',function(){
    $('#helpDiv').toggleClass('unShow');
});

//Окно помощи при вводе сообщения для рассылке смс
$(document).on('click','#addText', function(){
    var cursorPos = $('#smsMsg').prop('selectionStart');
    var v = $('#smsMsg').val();
    var textBefore = v.substring(0,  cursorPos );
    var textAfter  = v.substring( cursorPos, v.length );
    $('#smsMsg').val( textBefore+ $(this).find('param').val() +textAfter );
    $('#smsMsg').focus();
});


function showReturn() 
{
    var popup = document.getElementById("myPopup");
    popup.classList.toggle("show");
}
function unShowReturn(){setTimeout(showReturn, 500);}



    //Выборанные покупатели
    var idsWebUsers = new Map();
    var idsWebUsersNotInBase = new Map();
    var ids = [];
    var idNotInBase = [];
    var nameUser = '';

    //Добавление пользователя в список тех будет выдан промокод
    $(document).on('click', '#WebUserTD', function(event){

        var id = $(this)[0].getAttribute('userId');
        nameUser = $(this)[0].getAttribute('userName');
        var phone = $(this).find('p').html(); 
        idsWebUsers.set(id,phone);

        if (idsWebUsers.size>1 || idsWebUsersNotInBase.size>0)
            $('.pUser').addClass('unShow');


        tableSelectedWebUser();
        showReturn();
        unShowReturn();
        reloadWebUsers();
    });

    //Удаление из массива выбранных покупателей
    $(document).on('click', '#SelectedWebUserDelete', function(event){
        var id = $(this).val();

        if (idsWebUsers.size==1 || idsWebUsersNotInBase.size==0)
            $('.pUser').removeClass('unShow');

        idsWebUsers.delete(id);
        tableSelectedWebUser();
        reloadWebUsers();
    });

    //Удаление из массива выбранных покупателей
    $(document).on('click', '#SelectedWebUserNotInBaseDelete', function(event){
        var id = $(this).val();
        idsWebUsersNotInBase.delete(id);
        tableSelectedWebUserNotInBase();
    });

    //обновление таблицы выбранных покупателей
    function tableSelectedWebUser()
    {
        ids=[];
        let tbl = "";
        idsWebUsers.forEach(function(item, index, ar){
            tbl += "<tr>";
            tbl += "<td>" + item + "</td>";
            tbl += "<td><button class='btn btn-xs btn-danger' id=SelectedWebUserDelete value="+index+">Удалить</button></td>";
            tbl += "<tr>";
            ids.push(index);

        });
        $('#InputIds').val(ids);
        $('#selectedWebUsers').html(" ");
        $('#selectedWebUsers').html(tbl);
    }

    function tableSelectedWebUserNotInBase()
    {
        idNotInBase = [];
        let tbl = "";
        idsWebUsersNotInBase.forEach(function(item, index, ar){
            tbl += "<tr>";
            tbl += "<td>" + item + "</td>";
            tbl += "<td><button class='btn btn-xs btn-danger' id=SelectedWebUserNotInBaseDelete value="+index+">Удалить</button></td>";
            tbl += "<tr>";
            idNotInBase.push(item);
        });
        $('#InputIdsNotInBase').val(idNotInBase);
        $('#selectedWebUsersNotInBase').html(" ");
        $('#selectedWebUsersNotInBase').html(tbl);
    }

    //Обработка excel файла
    $(document).on('click', '#ExcelFile', function(event){
        var formData = new FormData();
        formData.append('file', $("#file")[0].files[0]);
        formData.append('column', $("#column").val());
        formData.append('lineFirst', $("#lineFirst").val());
        formData.append('lineLast', $("#lineLast").val());
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                },
            url: "/admin/UploadFileExcel",
            type: 'POST',
            data: formData,
            async: true,
            success: function (data) {

                data[0].forEach(function(item, index, ar){
                    idsWebUsers.set(item['id'],item['phone']);
                });

                data[1].forEach(function(item, index, ar){
                    idsWebUsersNotInBase.set(item['id'],item['phone']);
                });

                
                

                tableSelectedWebUser();
                tableSelectedWebUserNotInBase();
                reloadWebUsers();
                showReturn();
                unShowReturn();
            },
            cache: false,
            contentType: false,
            processData: false
        });
        
    });
  
    
    //Выбор типа ввода данных, с файла или же вручную
    $(document).on('change', '#typeInput', function(event){
        var chbox;
        chbox=document.getElementById('typeInput');
        if (chbox.checked) 
        {
            $('#manualInput').addClass('unShow');
            $('#loadExcel').removeClass('unShow');
        }
        else 
        {
            $('#loadExcel').addClass('unShow');
            $('#manualInput').removeClass('unShow');
        }
        
    });


    $(document).on('change keyup focus','#smsMsg', function(){
        var text = $(this).val();
        if (idsWebUsers.size==1 && idsWebUsersNotInBase.size==0)//если выбран только один покупатель 
            text = text.replaceAll('#user',nameUser);
        else
            text = text.replaceAll('#user','Клиент');
    
        text = text.replaceAll('#promocode','TEST123456');
        text = text.replaceAll('#promostart',$('#startValidity').val());
        text = text.replaceAll('#promoend',$('#expiration').val());
        //Вхождение подстроки
        $('#inputText').html(text + ' (Знаков:'+ text.length+')');       
        
    });

   
                
    // Добавить пользователя в таблицу для добавления в базу
    $(document).on('click', '#addFromLeftBar', function(event){
        var phone = $('#searchID').val();
        idsWebUsersNotInBase.set((idsWebUsersNotInBase.size+1).toString(),phone); 
        tableSelectedWebUserNotInBase();
    });



    $(document).on('click', '#monthWebUsers', function(event){
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/activeWebUsers",
        dataType: 'json',
        success:function(data)
        {
            data.forEach(function(item, index, ar){
                    idsWebUsers.set(item['id'],item['phone']);
                });

            tableSelectedWebUser();
            reloadWebUsers();
            showReturn();
            unShowReturn();
        }
        });
    });
 

    $(document).on('click', '#friendPromocode', function(event){
        if ($(this).is(':checked'))
        {
            $('#discountType option[value=2]').prop('selected', 'selected');
            $('#discountType option[value!=2]').attr('selected', false);
            $('#discountType option[value!=2]').attr('disabled', 'disabled');
        }
        else
        {
            $('#discountType option').attr('selected', false);
            $('#discountType option[value!=2]').removeAttr('disabled');
        }
    });
</script>
@endsection


           