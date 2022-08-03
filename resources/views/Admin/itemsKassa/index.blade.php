@extends('layouts.admin')
@section('content')
@if(auth()->guard('admin')->user()->can('itemGroups_edit'))
    <div style="margin-bottom: 10px;" class="row">
        <input type="hidden" value="{{$page ?? 1}}" id="page">
        <input type="hidden"  id="childid" value="{{$_GET['parentId'] ?? ""}}">
        <input type="hidden"  id="title" value="{{$_GET['title'] ?? ""}}">
        <input type="hidden"  id="select_choice">
        <input type="hidden"  id="parentid" value="{{$_GET['childId'] ?? ""}}">
        <input type="hidden" id="api_token" value="{{$api_token}}">
    </div>
@endif
<div class="card">
    <div class="card-header">
        Товары при кассе
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col">
                <div id="web-groups"></div>
            </div>

            <div class="col">
                <div id="item-add" class="col-lg-12"></div>    
                <table class="table table-striped table-bordered table-hover" style="margin-top:11px">
                    <thead>
                        <tr>
                            <th>ID<br><input type='text' id='searchId' class='search' value="{{$_GET['id'] ?? ""}}" placeholder='ID' size='2'></input></th>
                            <th style='max-width:300px;'>Название<br><input type='text' style='max-width:270px;' id='searchName' class='search' value="{{$_GET['name'] ?? ""}}" placeholder='Название' size='50'></input></th>
                            <th>Цена</th>
                            <th>Цена диск.</th>
                            <th>Цена акц.</th>
                            <th>Кол-во</th>
                            @if(auth()->guard('admin')->user()->can('itemKassa_edit'))
                                <th>Действие</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="web-items"></tbody>
                </table>   
                <div id="pagLink"> </div>
            </div>
            <div class="col">
                <div>
                <p>Временной интервал</p>
                <select id="timeSelect">
                </select>
                <a href="{{ route('itemsKassaDate')}}"><button  class="btn btn-xs btn-primary" >Редактировать</button></a>
                </div>
                <div style="overflow: scroll; max-height: 900px; margin-top:10px;">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Код</th>
                                <th>Название</th>
                                <th>Порядок</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="body_kassa_items"></tbody>
                    </table>
                </div>
            </div>
           

        </div>
    </div>
</div>
<style>
.tree span:hover {
   font-weight: bold;
 }

 .tree span {
   cursor: pointer;
 }
</style>
@endsection
@section('scripts')
@parent
<script>
$(function () {
 
    getDateActiveItemKassa();
    getWebGroups();

    //getItemKassa();
    

})
/*
* Получение списка предлогаемых товаров с последовательностью их вывода
*/


function getDateActiveItemKassa()
{
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/get_Date_items_kassa',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            let tbl = "";
            if (data.length)
            {
                $('#select_choice').val(data[0].id);
                getItemKassa(data[0].id);
            }
            data.forEach(function(data){
                tbl += "<option value="+data.id+">"+data.dateStart+" | "+data.dateEnd+"</option>";
            });
            $("#timeSelect").empty().append(tbl);
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }

    });
}

$(document).on('click', 'option', function(){
        var dateId = $(this).val();
        $('#select_choice').val(dateId);
        getItemKassa(dateId);
    });

$(document).on('click', '.findParam', function(){
    $('#parentid').val($(this).find('#findItemParent').val());
    $('#childid').val($(this).find('#findItemChild').val());
    $('#title').val($(this).find('#findItemTitle').val());
    $('#page').val(1);
    getWebGroups();
});

function getItemKassa(dateId)
    {
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/get_items_kassa',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: {dateId:dateId},     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            let tbl = "";
            data.forEach(function(data){
                tbl += "<tr>";
                tbl += "<td>" + data.itemId + "</td>";
                tbl += "<td class='findParam' style='cursor:pointer'><param id='findItemParent' value="+data.childId+">" + data.title + "<param id='findItemChild' value="+data.parentId+"><param id='findItemTitle' value=\'"+data.TagTitle+"\'></td>";

                tbl += "<td>"+
                    "<input style='display:none; max-width:60px;' type='number' value="+data.itemsorder+" id="+data.itemId+">"+
                    "<button style='display:none;' class='btn btn-xs btn-success button_change_itemsorder_itemsKassa' value="+ data.itemId +" id=b_"+data.itemId+">Save</button>" + 
                    "<p id=p_"+data.itemId+">"+data.itemsorder + "</p></td>";
                @if(auth()->guard('admin')->user()->can('itemKassa_edit'))
                tbl += "<td><button class='btn btn-xs btn-danger' id='delete_in_itemsKassa' value="+data.itemId +">Удалить</button>"+
                "<button  class='btn btn-xs btn-info' id='change_itemsorder_itemsKassa' value="+data.itemId +">Изменить</button></td>";
                @endif
                tbl += "</tr>";
            });
            $("#body_kassa_items").empty().append(tbl);

        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }

    });
}
/*
*  Получение Web групп
*/
function getWebGroups(){
    data = {
        groupId: 0
        
    }
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/getItemGroups',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            let con = $("#web-groups");
            con.empty();
            con.append(getSubGroup(data, "class='tree' id='tree'", ""));
            //  ловим клики на всём дереве
            tree = document.getElementById('tree');
            tree.onclick = function(event) {

                if (event.target.tagName != 'SPAN') {
                    return;
                }
                $('#page').val(1);
                $('#childid').val(event.target.getAttribute('data-id'));
                $('#title').val(event.target.innerHTML);
                itemsFill(event.target.innerHTML, event.target.getAttribute('data-id'));
                let childrenContainer = event.target.parentNode.querySelector('ul');
                
                if (!childrenContainer) return; // нет детей

                childrenContainer.hidden = !childrenContainer.hidden;
            }
            if($('#childid').val() != ''){
                itemsFill($('#title').val(),Number($('#childid').val()));
            }
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
//
function getSubGroup(items, str, h){
    openGroups = [Number($('#childid').val()),Number($('#parentid').val())];
    let ul = "<ul "+str+h+">";
    items.forEach(function(item, index, ar){
        ul += "<li><span data-id='" + item.id + "'>" + item.title + "</span>";
        
        if(openGroups.includes(item.id))
            hh = "";
        else
            hh =  " hidden=''";
        if( typeof item.children != "undefined" && item.children.length>0)
           ul += getSubGroup(item.children, "", hh);
        
        ul += "</li>";
    });
    ul += "</ul>";
    return ul;
}
// запрос и заполнение товаров группы
function itemsFill(html, id){
    let btn = "<div>группа: <span class='group-choosen'>"+html+"</span></div>";
    $("#item-add").empty().append( btn );
    var IDVal = $('#searchId').val();
    var NameVal = $('#searchName').val();
    getWebItems(id, IDVal, NameVal);
}
/*
*  Получение Web товаров
*/
function getWebItems(parentId,id,name){
    datal = {
        parentId: parentId,
        id: id,
        name: name,
        childId: $('#parentid').val(),
        page: $('#page').val(),
        title: $('#title').val(),
        path:'/admin/itemsKassa',
    }
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/get_all_items_kassa',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: datal,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            $('#web-items').html('');
            let itemscont = $("#web-items");
            let tbl = getWebItemsTbl(data.items);
            itemscont.empty().append(tbl);
            //console.log(data);
            $("#pagLink").empty().append(data.links);
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
/**
 * строит таблицу из товаров, возвращает в виде строки
 */
function getWebItemsTbl(data){
    let tbl = "";
    var IDVal = $('#searchId').val();
    var NameVal = $('#searchName').val();
    var parId = $('#childid').val();
    var page= $('#page').val()
    data.forEach(function(item, index, ar){
        tbl += "<tr>";
        tbl += "<td>" + item.id + "</td>";
        
        tbl += "<td >" + item.title + "</td>";
        tbl += "<td><p id=price_"+item.id+">" + item.price + "</p></td>";
        if (item.discountPrice!=null)
            tbl += "<td><p id=priceDiscount_"+item.id+">" + item.discountPrice + "</p></td>";
        else 
            tbl += "<td><p id=priceDiscount_"+item.id+">-</p></td>";
        if (item.stockPrice!=null)
            tbl += "<td><p id=priceStock_"+item.id+">" + item.stockPrice + "</p></td>";
        else 
            tbl += "<td><p id=priceStock_"+item.id+">-</p></td>";
        tbl += "<td><p id=quantity_"+item.id+">" + item.quantity + "</p></td>";
        tbl += "<td>";
        tbl += "<div>";
        @if(auth()->guard('admin')->user()->can('itemKassa_edit')) 
        tbl += "<button class='btn btn-xs btn-success' id='add_in_itemsKassa' value="+item.id +">Добавить</button>&nbsp;";
        @endif
        tbl += "</div></td>";
        tbl += "</tr>";
    });
    
    return tbl;
}
/**
 * Добавляет новый товар в itemsKassa
 */
function add_new_itemsKassa(itemId,dateId)
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/add_in_items_kassa',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: {id:itemId,dateId:dateId},     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            getItemKassa($('#select_choice').val());
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}        
/**
 * Удаляет товар из itemsKassa
 */
function delete_in_itemsKassa(itemId,dateId)
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/delete_in_items_kassa',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: {id:itemId,dateId:dateId},     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            
            getItemKassa($('#select_choice').val());
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
} 

function change_itemsorder_itemsKassa(itemId,itemsort,dateId)
    {
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/change_itemsorder_items_kassa',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: {id:itemId,itemsort,dateId:dateId},     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            getItemKassa($('#select_choice').val());
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
    $(document).on('keyup', '.search', function(){
        var IDVal = $('#searchId').val();
        var NameVal = $('#searchName').val();
        var parId = $('#childid').val();
        $('#page').val(1);
        getWebItems(parId,IDVal,NameVal);
    });

    $(document).on('click', '#delete_in_itemsKassa', function(){
        var id = $(this).val();
        delete_in_itemsKassa(id,$('#select_choice').val());
    });
 
    $(document).on('click', '#add_in_itemsKassa', function(){
        var id = $(this).val();
        var price = $("#price_"+id).html();
        var quantity = $("#quantity_"+id).html();

                    if ($('#select_choice').val()!='')
                        if (price==0 || quantity==0)
                        {
                            if (confirm("Цена или количество товара равно 0. Все равно добавить?"))
                            {
                                add_new_itemsKassa(id,$('#select_choice').val());
                            }
                        }
                        else 
                            add_new_itemsKassa(id,$('#select_choice').val());
                    else 
                        alert('Не выбран временной интервал');
    });

    $(document).on('click', '#change_itemsorder_itemsKassa', function(){
        id=$(this).val();
        $("#p_"+id).html('');
        $("#"+id).toggle();
        $("#b_"+id).toggle();
    });

    $(document).on('click', '.button_change_itemsorder_itemsKassa', function(){
        var id = $(this).val();
        var value=$("#"+id).val();
        $("#"+id).toggle();
        $("#b_"+id).toggle();
        change_itemsorder_itemsKassa(id,value,$('#select_choice').val());
    });



</script>
@endsection