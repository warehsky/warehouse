@extends('layouts.adminv')
@section('content')
  
<h4>Настройки</h4>
<div id="AllTabs" class="tab">

</div>
<form method="get" action="/admin/updateoptions" enctype="multipart/form-data" style='margin-top:10px'>
    @csrf
      <div id="tableBody">
      </div>
<button style='margin-left:66%; margin-top:10px; margin-bottom:20px;font-size: 12pt;border-radius: 3px;'>Сохранить</button>
<input type='hidden' name="optionTitle" id='optionTitle' value="{{$optionLink ?? 0}}">
</form>
    

<style>
.vis{
    display:none;
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
</style>


@endsection
@section('scripts')
@parent
<script>
//запрос для получения данных настроек
function fetch_data(groupId='')
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getOptions",
        data: { 
                groupId: groupId,
            },
        dataType: 'json',
        success:function(data)
        {
            $('#tableBody').html('');
            let tbl=getWebItemsTbl(data);
            $('#tableBody').html(tbl);
        }
        })
    }

//построение таблицы настроек
function getWebItemsTbl(data){
    let tbl = "";
    
    data[1].forEach(function(item, index, ar){
      var result = data[0].filter(el => el.subgroup == item);
        if (item==null)
        item = "Без группы";
        tbl += "<fieldset style='width:70%;'><legend>"+item+"</legend>";
        result.forEach(function(value,ind,a){
            tbl += "<div style='margin-top:7px;'><lable>"+value.description+"</lable>";
            //Отключение автоматического обновление "Топ продаж" 
            if (value.field == 'bestSellerStatus')
            {
                var select1,select0 = "";
                if (value.value == 1)
                    select1 = "selected";
                else 
                    select0 = "selected";
                tbl += "<select style='float: inline-end;' name='bestSellerStatus'><option value='0' "+select0+">Выключено</option><option value='1' "+select1+">Включено</option></select>";
            }
            else //Отключение акции "Приведи друга"
                if (value.field == 'bringFriendStatus')
                {
                    var select1,select0 = "";
                    if (value.value == 1)
                        select1 = "selected";
                    else 
                        select0 = "selected";
                    tbl += "<select style='float: inline-end;' name='bringFriendStatus'><option value='0' "+select0+">Выключено</option><option value='1' "+select1+">Включено</option></select>";
                }
                else
                {                   
                    let newItem = "";
                    //Обновление тега "Новинка"
                    if (value.field == 'NewItemsDay')
                    {
                        newItem = 'id="newItemDays"';
                        tbl += "<button type='button' id='updateNewItems' style='font-size: 12pt;border-radius: 3px; margin-left:50px;'>Обновить</button>"
                        tbl += "<span id='loaderNewItem'></span>";
                    }
                    //Обновление тега "Хиты продаж"
                    if (value.field == 'bestSellerDay')
                    {
                        newItem = 'id="bestSellerDay"';
                        tbl += "<button type='button' id='updateBestSellerDay' style='font-size: 12pt;border-radius: 3px; margin-left:50px;'>Обновить</button>"
                        tbl += "<span id='loaderBestSeller'></span>";
                    }
                    let type = '';
                    //Выбор типа поля input
                        if (value.type == "int" || value.type == "integer")
                            type+='number';
                        else 
                            type=='text';                  
                    
                    tbl += "<input style='float: inline-end;' name="+value.field+" type='"+type+"' size='40' "+newItem+" value= '" + value.value + "' >";
                }
        });
        tbl +="</div></fieldset>"
    });
    return tbl;
}

function getOptionsGroups()
{
  $.ajax({
    headers: {'X-Access-Token': $('#api_token').val()},
    beforeSend: function(xhr) {
        xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
    },
    url:"/admin/getOptionGroups",
    dataType: 'json',
    success:function(data)
    {
      var buttons = '';
      var int = 0;
      data.forEach(function(item, index, ar){
        if (item == null)
            var str = 'Общие';
        else 
            var str = new String(item);
        
        buttons +='<button class="tablinks" id="group_'+index+'">'+str+'</button>';
        if (str==$('#optionTitle').val())
          int=index;
      });

      $('#AllTabs').html(buttons);
      document.getElementById("group_"+int).click();
    }
    })
}

$(document).on('click','#updateNewItems',function(){
    var day = $('#newItemDays').val();
    
    $('#updateNewItems').attr('disabled','disabled');
    $('#loaderNewItem').html();
    $('#loaderNewItem').html('<img src="/img/loader.gif"  width="20" height="20">');

    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/newItems?day="+day,
        dataType: 'json',
        success:function(data)
        {
            $('#updateNewItems').removeAttr('disabled');
            if (data.code == 200)
            {
                $('#loaderNewItem').html();
                $('#loaderNewItem').html('<img src="/img/loader-success.png"  width="20" height="20">');
            }
        }
        })
});

$(document).on('click','#updateBestSellerDay',function(){
    var day = $('#bestSellerDay').val();
    $('#updateBestSellerDay').attr('disabled','disabled');
    $('#loaderBestSeller').html();
    $('#loaderBestSeller').html('<img src="/img/loader.gif"  width="20" height="20">');

    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/bestSeller?day="+day,
        dataType: 'json',
        success:function(data)
        {
            $('#updateBestSellerDay').removeAttr('disabled');
            if (data.code == 200)
            {
                $('#loaderBestSeller').html();
                $('#loaderBestSeller').html('<img src="/img/loader-success.png"  width="20" height="20">');
            }
        }
        })
});

$(document).ready(function(){
    getOptionsGroups();
    //fetch_data();
});


$(document).on('click','.tablinks',function(evt) {
  var i, tablinks;
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    fetch_data($(this).html());
    
    $('#optionTitle').val($(this).html());
    evt.currentTarget.className += " active";
});


</script>
@endsection      

<!-- `pokp${99}oiuhui` -->