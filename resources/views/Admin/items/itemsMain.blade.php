@extends('layouts.admin')
@section('content')
<div class="card" style="margin-top:10px">
    <div class="popup" id="myPopup">
        <span id='popupText' class="popuptext">Сохранено</span>
    </div>
    <div class="card-header">
        <b>Группировка товаров:</b> 

            <input type="checkbox" style="cursor: pointer; margin-left:15px;" class='search'  id="mainItem">
                <label style="cursor: pointer;" for="mainItem">Только главные товары</label>
                
            <input type="checkbox" style="cursor: pointer; margin-left:15px;" class='search' id="notMainItem">
                <label style="cursor: pointer;" for="notMainItem">Убрать главные товары</label>

            <input id='unShowUsedItem' type="checkbox" style="cursor: pointer; margin-left:15px;" class='search'>
                <label style="cursor: pointer;" for="unShowUsedItem">Убрать использованные товары</label>

            <input id='removeFilter' type="checkbox" style="cursor: pointer; margin-left:15px;" class='search'>
                <label style="cursor: pointer;" for="removeFilter">Отключить фильтр по тегам</label>

    </div>
    
    <input type="hidden" id="api_token" value="{{$api_token}}">
    <input type="hidden" name="hidden_page" id="hidden_page" value={{$_GET['page'] ?? 1}} />
    <input type="hidden"  id="sorting" value={{$_GET['sorting'] ?? 'desc'}} />
    <input type="hidden"  id="sortingField" value={{$_GET['sortingField'] ?? 'created_at'}} />
    <param id="selectedItemsParam" value=''>
    <div class="card-body">
    
        <div class="row">
            <div class="col-sm-8 layer">
                <div class="table-responsive">
                    <table class=" table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <div id="sort" class="search" style="cursor: pointer;">
                                        <param value="id">
                                        ID<br>
                                    </div>
                                    <input type="text" id="searchID" class="search" placeholder="ID" size="5"></input>
                                </th>
                                <th>
                                    <div id="sort" class="search" style="cursor: pointer;">
                                        <param value="title">
                                        Название<br>
                                    </div>
                                    <input type="text" id="searchName" class="search" placeholder="Название" size="20"></input>
                                </th>
                                <th>
                                    <div id="sort" class="search" style="cursor: pointer;">
                                        <param value="parentId">
                                        Группа (id или название)<br>
                                    </div>
                                    <input type="text" id="searchParent" class="search" placeholder="Группа" size="20"></input>
                                </th>
                                <th>
                                    <div id="sort" class="search" style="cursor: pointer; min-width: 250px;">
                                        <param value="created_at">
                                        Дата добавления<br>
                                    </div>
                                    <input type="date" id="searchDateStart" class="search" size="10"></input>
                                    <input type="date" id="searchDateEnd" class="search" size="10"></input>
                                </th>
                                <th id="sort" class="search" style="cursor: pointer; min-width:120px;">
                                    <param value="mainItem">Главный
                                    
                                </th>
                                @if(auth()->guard('admin')->user()->can('ItemsMain_edit')) 
                                <th>Действие</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="searchTbody">
                            
                        </tbody>
                    </table>
                    <div id="pagLink"> </div>
                </div>
            </div>

            <div class="col-sm-4" style="overflow:scroll; height: 660px;">
                <h5 id='selectItems'></h5>
                <table id="childTable" class=" vis table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            @if(auth()->guard('admin')->user()->can('ItemsMain_edit')) 
                            <th>Действие</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="childTbody">
                    </tbody>
                </table>
            </div>
           
        </div>

    </div>
</div>
<style>

.btn-xs.btn-danger.btn-del{
    width: 100px;
    height: 30px;
    padding: 5px;
    margin: 5px 0;
    cursor: pointer;
}
.card{
    position: relative;
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
    width: 200px;
    height: 150px;
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



.selectedItem {
    background: #BBF1CD;
}


.layer {
    overflow: scroll;
    width: 300px;
    height: 660px;
    padding: 5px;
} 
.vis{
    display:none;
}
</style>
@endsection
@section('scripts')
@parent
<script>

var selectID = 0;
var selectIDShadow = 0;

    function showReturn(text) {
        var popup = document.getElementById("myPopup");
        $('#popupText').html(text);
        popup.classList.toggle("show");
    }

    function unShowReturn(){setTimeout(showReturn, 3000);}





/////////////////////////////////////////////////////////////////////////////////////////////////////////

function fetch_data(id = 0)
    {
        console.log(id);
        var mainItem='';
        if($('#mainItem').is(':checked'))
            mainItem=1;
        if($('#notMainItem').is(':checked'))
            mainItem=0;

        var unShowUsedItem='';
        if($('#unShowUsedItem').is(':checked'))
            unShowUsedItem=true;

        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/AllItemsForLoad",
        dataType: 'json',
        method: 'get',
        data: {
                id:$('#searchID').val(),
                name:$('#searchName').val(),
                parentTitle:$('#searchParent').val(),
                dateStart:$('#searchDateStart').val(),
                dateEnd:$('#searchDateEnd').val(),
                sortingMethod:$('#sorting').val(),
                sortField:$('#sortingField').val(),
                mainItem:mainItem,
                path:'/admin/itemsMain',
                unShowUsedItem:unShowUsedItem,
                idForTag:id,
                page:$('#hidden_page').val(),
            },
        success:function(data)
        {
            $('#searchTbody').html('');
            let tbl=getWebItemsTbl(data);
            $("#pagLink").empty().append(data.links);
            $('#searchTbody').html(tbl);
        }
        })
    }

function getWebItemsTbl(data){
    let tbl = "";
    data.items.forEach(function(item, index, ar){
        if (item.mainItem)
            tbl+="<tr style='background: #F1F1FF;'>";
        else
            tbl += "<tr>";

        tbl += "<td>" + item.id + "</td>";
        s='';
        if (item.id==selectID)
            s = 'Class=selectedItem'
        tbl += "<td id=nameItems "+ s +" style='cursor:pointer'><param id='mainItem' value='"+item.mainItem+"'><param id='selectItem' value="+item.id+">" + item.title + "</td>";
        tbl += "<td>[#"+item.parentId+'] '+ item.parentTitle + "</td>";
        tbl += "<td>" + item.created_at + "</td>";
        if (item.mainItem)
        {
            tbl += "<td>Да</td>";
            @if(auth()->guard('admin')->user()->can('ItemsMain_edit')) 
                tbl += "<td><button class='btn btn-xs btn-info changeMain' value="+item.id+">Убрать из главных</button></td>"
            @endif
        }
        else 
        {
            tbl += "<td>Нет</td>";
            @if(auth()->guard('admin')->user()->can('ItemsMain_edit')) 
                tbl += "<td><button class='btn btn-xs btn-info changeMain' value="+item.id+">Добавить как главный</button><button id='addChildItem' value="+item.id+" class='btn btn-xs btn-success'>Добавить</button></td>"
            @endif
        }
        tbl += "<tr>";
    });
    return tbl;
}

    $(document).on('click','#addChildItem', function(){
            var itemId=$(this).val();
            var parentId=$('#selectedItemsParam').val();
            if (parentId!="")
            {
                $.ajax({
                    headers: {'X-Access-Token': $('#api_token').val()},
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                    },
                    url:"/admin/addNewItemInGroup?itemId="+itemId+"&parentId="+parentId,
                    dataType: 'json',
                    success:function(data)
                    {
                        
                        if (data.code==200)
                        {
                            fetch_data(selectID);
                            getChildItems(parentId); 
                        }   
                        showReturn(data.mesage);
                        unShowReturn();
                    }
                });
            }
            else 
            alert('Не выбран главный товар.');
    });


    $(document).on('click','#mainItem', function(){
           if ($('#notMainItem').is(':checked'))
            $("#notMainItem").prop("checked", false);
            
    });

    $(document).on('click','#notMainItem', function(){
           if ($('#mainItem').is(':checked'))
            $("#mainItem").prop("checked", false);
            
    });

    $(document).on('click','.changeMain', function(){
        if(confirm("Изменить статус товара?"))
        {
            var id=$(this).val();
            $.ajax({
                headers: {'X-Access-Token': $('#api_token').val()},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                },
                url:"/admin/changeMainStatusItem?id="+id,
                dataType: 'json',
                success:function(data)
                {
                    if (data.code==200)
                    {
                        if (id == selectID)
                        selectID = 0;
                        fetch_data(selectID); 
                        $('#selectItems').html('');
                        $('#childTable').addClass('vis');
                    }
                    showReturn(data.mesage);
                    unShowReturn();
                }
            });
        }
    });


    $(document).on('click', '#sort', function(){
        $('#hidden_page').val(1);
        var sr=$('#sorting').val();
        $('#hidden_page').val(1);
        $('#sortingField').val($(this).find('param').val());
        if(sr=='asc')
            $('#sorting').val('desc');
        else 
            $('#sorting').val('asc');
            fetch_data(selectID);
    });




    $(document).on('keyup change', '.search', function(){
        $('#hidden_page').val(1);
        if ($('#removeFilter').is(':checked'))
        {
            if (selectID != 0)
            {
                selectIDShadow = selectID;
                selectID = 0;
            }
        }
        else 
        if (selectIDShadow!=0)
        {
            selectID = selectIDShadow;
            selectIDShadow = 0;
        }
        fetch_data(selectID);
    });

    $(document).ready(function(){
        $('#hidden_page').val(1);
        fetch_data(selectID);
    });

    $(document).on('click', '.pagination a', function(event){
        event.preventDefault();
        
        var page = $(this).attr('href').split('page=')[1];
        $('#hidden_page').val(page);
        $('li').removeClass('active');
        $(this).parent().addClass('active');
        fetch_data(selectID);
    });

    $(document).on('click','#nameItems', function(){
        let id = $(this).find('#selectItem').val();
        let mainItem = $(this).find('#mainItem').val();
        //console.log(mainItem,id);
        if ($('#selectedItemsParam').val()==id)
        {
            $('#selectedItemsParam').val('');
            $('#selectItems').html('');
            selectID = 0;
            selectIDShadow = 0;
            $('#hidden_page').val(1);
            fetch_data(selectID);
        }
        else 
        {
            if (mainItem!=0)
            {
                $('#selectedItemsParam').val(id);
                console.log($(this).html());
                $('#selectItems').html($(this).html()+" <button id='cancelMainItem' class='btn btn-danger btn-xs'>Отмена</button>");
                $('td').removeClass('selectedItem');
                $('#childTable').addClass('vis');
                getChildItems(id);
                $('#hidden_page').val(1);
                selectID = id;
                fetch_data(id)
            }
        }
        if (mainItem!=0)
        {
            $('#childTable').toggleClass('vis');
            $(this).toggleClass('selectedItem');
        }
    });

    $(document).on('click','#deleteChild', function(){
        if(confirm("Удалить товар?"))
        {
            var itemId=$(this).val();
            var parentId =$('#selectedItemsParam').val();
            $.ajax({
                headers: {'X-Access-Token': $('#api_token').val()},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                },
                url:"/admin/deleteItemInGroup?itemId="+itemId+"&parentId="+parentId,
                dataType: 'json',
                success:function(data)
                {
                    if (data.code==200)
                    {
                        fetch_data(selectID);
                        getChildItems(parentId); 
                    }
                    showReturn(data.mesage);
                    unShowReturn();
                }
            });
        }
    });

    $(document).on('click','#cancelMainItem',function(){
        $('#selectedItemsParam').val('');
        $('#selectItems').html('');
        selectID = 0;
        selectIDShadow = 0;
        $('#hidden_page').val(1);
        fetch_data(selectID);
        $('#childTable').toggleClass('vis');
    });


    function getChildItems(id)
    {

        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getChildItems?id="+id,
        dataType: 'json',
        success:function(data)
        {
            tbl="";
            data.forEach(function(item, index, ar){
                    
                tbl += "<tr>";
                tbl += "<td>" + item.id + "</td>";
                tbl += "<td>"+ item.title + "</td>";
                @if(auth()->guard('admin')->user()->can('ItemsMain_edit')) 
                tbl += "<td><button id='deleteChild' class='btn btn-xs btn-danger' value="+item.id+">Удалить</button></td>";
                @endif
                tbl += "<tr>";
            });
            $('#childTbody').html('');
            $('#childTbody').html(tbl);
        }
        });
        
    }
    
</script>
@endsection
