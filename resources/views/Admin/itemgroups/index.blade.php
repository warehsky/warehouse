@extends('layouts.admin')
@section('content')
@if(auth()->guard('admin')->user()->can('itemGroups_edit'))
<div class="popup" id="myPopup">
    <span class="popuptext" >Сохранено</span>
</div>
    <div style="margin-bottom: 10px;" class="row">
    <input type="hidden" value="{{$page ?? 1}}" id="page">
    <input type="hidden" value="{{$_GET['parentId'] ?? ""}}" id="parentid">
    <input type="hidden" value="{{$_GET['popularSort'] ?? 'asc'}}" id="popularSort">

        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("itemgroups.create") }}">
                Добавить группу товара
            </a>
        </div>
    </div>
@endif
<div class="card">
    <div class="card-header">
        Группы товаров
        <input type="hidden" id="api_token" value="{{$api_token}}">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <div id="web-groups"></div>
            </div>

            <div class="col">
                <div id="item-add" class="col-lg-12">
            </div>


     
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID<br><input type='text' id='searchId' class='search' value="{{$_GET['id'] ?? ""}}" placeholder='ID' size='2'></input></th>
                        <th>Код 1С<br><input type='text' id='searchId1c' class='search' value="{{$_GET['id1c'] ?? ""}}" placeholder='Код 1С' size='7'></input></th>
                        <th>Код на весах<br><input type='text' id='searchWeightid' class='search' value="{{$_GET['weightId'] ?? ""}}" placeholder='Код на весах' size='9'></input></th>
                        
                        <th>Название<br><input type='text' id='searchName' class='search' value="{{$_GET['name'] ?? ""}}" placeholder='Название' size='30'></input></th>
                        <th>Остаток</th>
                        <th>Цена</th>
                        <th id="sort"  style="cursor: pointer;">
                            <div id="arrow"  style="min-width:150px;">Популярные &uarr;</div>                     
                        </th>

                        @if(auth()->guard('admin')->user()->can('itemGroups_edit'))
                            <th>Действие</th>
                        @endif
                    </tr>
                </thead>
                
                <tbody id="web-items">
                    
                </tbody>
            <table>   
                <div id="pagLink"> </div>
            </div>
        </div>
    </div>
</div>
<style>
.tree span:hover {
   font-weight: bold;
 }
 .vis{
    display:none;
}
 .tree span {
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
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)

  let deleteButtonTrans = 'удалить'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('itemgroups.mass_destroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('Удалить?')

        return
      }

      if (confirm('Уверен')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)


  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-Group:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });

    getWebGroups();
    

})


$(document).on('click', '#sort', function(event){
        var sr=$('#popularSort').val();
        $('#page').val(1);
        var dawn= 'Популярные &darr;';
        var up='Популярные &uarr;' ;
        if(sr=='asc')
        {
            $('#popularSort').val('desc');
            $('#arrow').html(up);
        }
        else 
        {
            $('#popularSort').val('asc');
            $('#arrow').html(dawn);
        }
        
        var IDVal = $('#searchId').val();
        var NameVal = $('#searchName').val();
        var parId = $('#parentid').val();
        getWebItems(parId,IDVal,NameVal);
    });

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
                $('#parentid').val(event.target.getAttribute('data-id'));
                itemsFill(event.target.innerHTML, event.target.getAttribute('data-id'));
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
function getSubGroup(items, str, h){
    openGroups=[<?=$groups?>];
    let ul = "<ul "+str+h+">";
    items.forEach(function(item, index, ar){
        ul += "<li><span data-id='" + item.id + "'>" + item.title + "</span>";
        
        if(openGroups.includes(item.id))
            hh = "";
        else
            hh =  " hidden=''";
        if( typeof item.children != "undefined" && item.children.length>0)
           ul += getSubGroup(item.children, "", hh);
        ul += "<div>";
        @if(auth()->guard('admin')->user()->can('itemGroups_edit'))
        ul += "<a class='btn btn-xs btn-primary' href='/admin/itemgroups/" + item.id + "'>просмотр</a>&nbsp;";
        ul += "<a class='btn btn-xs btn-info' href='/admin/itemgroups/" + item.id + "/edit'>изменить</a>&nbsp;";
        ul += "<form action='/admin/itemgroups/" + item.id + "' method='POST' onsubmit='return confirm(\"Уверен\");' style='display: inline-block;'>";
        ul += "    <input type='hidden' name='_method' value='DELETE'>";
        ul += "    <input type='hidden' name='_token' value='{{ csrf_token() }}'>";
        ul += "    <input type='submit' class='btn btn-xs btn-danger' value='удалить'>";
        ul += "</form>&nbsp;";
        ul += "<a class='btn btn-xs btn-success' href='/admin/itemgroups/create?parentId="+item.id+"' >Добавить подгруппу товара</a>";
        @endif
        ul += "</div></li>";
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
        popularSort:$('#popularSort').val(),
        parentId: parentId,
        id: id,
        name: name,
        page: $('#page').val(),
        id1c: $('#searchId1c').val(),
        weightId: $('#searchWeightid').val()
    }
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/get_search_items',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: datal,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            $('#web-items').html('');
            let itemscont = $("#web-items");
            let tbl = getWebItemsTbl(data.items);
            itemscont.empty().append(tbl);
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
    var parId = $('#parentid').val();
    var popularSort = $('#popularSort').val();
    var page= $('#page').val();
    var id1c = $('#searchId1c').val();
    var weightId = $('#searchWeightid').val();
    data.forEach(function(item, index, ar){
        tbl += "<tr>";
        tbl += "<td>" + item.id + "</td>";
        tbl += "<td>" + item.id1c + "</td>";
        tbl += "<td>" + item.weightId + "</td>";
        tbl += "<td>" + item.title + "</td>";
        tbl += "<td>" + item.quantity + "</td>";
        tbl += "<td>" + item.price + "</td>";
        tbl += "<td id='popular'><param id='parPopular' value='"+item.id+"'><input type='number' id='popularInput"+item.id+"' class='vis' value="+item.popular+"><p id='pTd'>" + item.popular + "</p></td>";
        tbl += "<td>";
        tbl += "<div>";
        @if(auth()->guard('admin')->user()->can('itemGroups_edit'))
        tbl += "<a class='btn btn-xs btn-primary' href='/admin/items/"+ item.id +"?id="+IDVal+"&name="+NameVal+"&parentId="+parId+"&page="+page+"&groupRoute=1&popularSort="+popularSort+"&id1c="+id1c+"&weightId="+weightId+"'>просмотр</a>&nbsp;";
        tbl += "<a class='btn btn-xs btn-info' href='/admin/items/"+ item.id +"/edit?id="+IDVal+"&name="+NameVal+"&parentId="+parId+"&page="+page+"&groupRoute=1&popularSort="+popularSort+"&id1c="+id1c+"&weightId="+weightId+"'>изменить</a>&nbsp;";
        tbl += "<form action='/admin/items/"+ item.id +"?id="+IDVal+"&name="+NameVal+"&parentId="+parId+"&page="+page+"&groupRoute=1&popularSort="+popularSort+"&id1c="+id1c+"&weightId="+weightId+"' method='POST' onsubmit='return confirm(\"Уверен\");' style='display: inline-block;'>";
        tbl += "    <input type='hidden' name='_method' value='DELETE'>";
        tbl += "    <input type='hidden' name='_token' value='{{ csrf_token() }}'>";
        tbl += "    <input type='submit' class='btn btn-xs btn-danger' value='удалить'>";
        tbl += "</form>&nbsp;";
        @endif
        "</div></td>";
        tbl += "</tr>";
    });
    
    return tbl;
}
    $(document).on('dblclick', '#popular', function(){
        let id=$(this).find('#parPopular').val();
        $(this).find('#pTd').html('');
        $('#popularInput'+id).removeClass('vis');
        $('#popularInput'+id).focus();
        $('#popularInput'+id).blur(function(){
            if ($(this).val()!='')
            {
            $.ajax({
                headers: {'X-Access-Token': $('#api_token').val()},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                },
                url:"/Api/changePopularValue?id="+id+"&value="+$(this).val(),
                dataType: 'json',
                success:function(data)
                {
                    $('#popularInput'+id).addClass('vis');
                    var IDVal = $('#searchId').val();
                    var NameVal = $('#searchName').val();
                    var parId = $('#parentid').val();
                    getWebItems(parId,IDVal,NameVal);
                    showReturn();
                    unShowReturn();


                }
            })
            }
            else 
            alert('Пустое поле');
        });

        function showReturn() {
            var popup = document.getElementById("myPopup");
            popup.classList.toggle("show");}

        function unShowReturn(){setTimeout(showReturn, 1500);}
    });



    $(document).on('keyup', '.search', function(){
        $('#page').val(1);
        var IDVal = $('#searchId').val();
        var NameVal = $('#searchName').val();
        var parId = $('#parentid').val();
        getWebItems(parId,IDVal,NameVal);
    });

   

</script>
@endsection