@extends('layouts.admin')
@section('content')
    
<input type="hidden" value="{{$page ?? 1}}" id="page">
<div class="card">
    <div class="card-header">
        Сопутствующие товары по группам
        <input type="hidden" id="api_token" value="{{$api_token}}">
        <input type="hidden" id="edit_group" value="0">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <H1>Выбор группы</H1>
                <div id="web-groups"></div>
            </div>

            <div class="col">
                <H1>Назначенные группы</H1>
                <div id="item-add" class="col-lg-12">
                </div>
                <div id="web-items"></div>
            </div>
            <div class="col">
                <H1>Выбор группы для назначения</H1>
                <div class="col-lg-12">
                    
                </div>
                
                <div id="groups-to-add"></div>
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
  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });


    getWebGroups(0);
    getSuggestGroups(0);
    

})

/*
*  Получение Web групп
*/
function getWebGroups(add){
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
                if(event.target.getAttribute('data-parentid')>0)
                    itemsFill(event.target.innerHTML, event.target.getAttribute('data-id'));
                else{
                    $("#item-add").empty();
                    $("#edit_group").val(0);
                    $("#web-items").empty();
                }
                let childrenContainer = event.target.parentNode.querySelector('ul');
                
                if (!childrenContainer) return; // нет детей

                childrenContainer.hidden = !childrenContainer.hidden;
            }
            if('<?=$groups?>' != ''){
                itemsFill('<?=($group->title??'')?>', <?=($group->id??0)?>);
            }
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}

//
function getSubGroup(items, str, h, add){
    openGroups=[<?=$groups?>];
    let ul = "<ul "+str+h+">";
    items.forEach(function(item, index, ar){
        ul += "<li>"
        +"<span data-id='" + item.id + "' data-parentid='" + item.parentId + "'>" + item.title + "</span>";
        if(openGroups.includes(item.id))
            hh = "";
        else
            hh =  " hidden=''";
        if( typeof item.children != "undefined" && item.children.length>0)
           ul += getSubGroup(item.children, "", hh, add);
        ul += "</li>";
    });
    ul += "</ul>";
    return ul;
}
// запрос и заполнение групп
function itemsFill(html, id){
    let btn = "<div>группа: <span class='group-choosen'>"+html+"</span></div>";
    $("#edit_group").val(id);
    // btn += "<a class='btn btn-success' href='/admin/items/create?parentId="+id+"'>Добавить товар</a>";

    $("#item-add").empty().append( btn );
    getSuggests(id);
}
/*
*  Получение сопутсвующих подгрупп
*/
function getSuggests(groupId){
    data = {
        groupId: groupId
    }
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/get_suggests',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            let itemscont = $("#web-items");
            let ul = "<ul>";
            for(let i=0; i < data.length; i++){
                ul += "<li>@if(auth()->guard('admin')->user()->can('suggests_edit'))<a class='btn btn-xs btn-danger' href='#' onclick='deleteGroup(" + data[i].id + ")'>удалить</a>@endif&nbsp;" 
                + data[i].title;
            }
            ul += "</ul>";
            itemscont.empty().append(ul);
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
/**  Добавляет группу */
function addGroup(id){
    if(!id || id<=0) return;
    data = {
        groupId: $("#edit_group").val(),
        suggestId: id
    }
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/add_suggest',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            if($("#edit_group").val()>0)
                getSuggests($("#edit_group").val());
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
/**  Удаляет группу из списка предложений*/
function deleteGroup(id){
    if(!id || id<=0) return;
    data = {
        groupId: $("#edit_group").val(),
        suggestId: id
    }
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/del_suggest',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            if($("#edit_group").val()>0)
                getSuggests($("#edit_group").val());
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
/*
*  Получение Web групп для добавления к сопутствующим товарам
*/
function getSuggestGroups(add){
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
            let con = $("#groups-to-add");
            con.empty();
            con.append(getSuggestSubGroup(data, "class='tree' id='stree'", ""));
            //  ловим клики на всём дереве
            tree = document.getElementById('stree');
            tree.onclick = function(event) {

                if (event.target.tagName != 'SPAN') {
                    return;
                }
                
                let childrenContainer = event.target.parentNode.querySelector('ul');
                
                if (!childrenContainer) return; // нет детей

                childrenContainer.hidden = !childrenContainer.hidden;
            }
            
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
/** */
function getSuggestSubGroup(items, str, h, add){
    openGroups=[<?=$groups?>];
    let ul = "<ul "+str+h+">";
    items.forEach(function(item, index, ar){
        
        ul += "<li>"+(item.parentId?"@if(auth()->guard('admin')->user()->can('suggests_edit')) <a class='btn btn-xs btn-info' href='#' onclick='addGroup(" + item.id + ")'>добавить</a>@endif&nbsp;":"")
        +"<span data-id='" + item.id + "'>" + item.title + "</span>";
        
        if(openGroups.includes(item.id))
            hh = "";
        else
            hh =  " hidden=''";
        if( typeof item.children != "undefined" && item.children.length>0)
           ul += getSuggestSubGroup(item.children, "", hh, add);
        ul += "</li>";
    });
    ul += "</ul>";
    return ul;
}
</script>
@endsection