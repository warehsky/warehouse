@extends('layouts.admin')
@section('content')

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
        Создание промокодов
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <form action="/admin/createPromocode" method="post" style="margin-top:20px; margin-left:10px; margin-bottom:10px">
                    {{ csrf_field() }}
                    <table style="width:550px" class="table table-striped table-bordered table-hover">
                        <tr>
                            <td>Количество промокодов</td>
                            <td><input type="number" name='count' value=1 size=4></td>
                        </tr>
                        <tr>
                            <td>Код</td>
                            <td><input type="text" name='title' id='inputUpper'  maxlength="10"></td>
                        </tr>
                        <tr>
                            <td>Тип</td>
                            <td><select name="type" id='discountType'></select> <button type='button' class ='btn-xs btn-info' id='changeDiscountType'>Изменить</button></td>
                        </tr>
                        <tr>
                            <td>Размер скидки в %</td>
                            <td><input type="number" name='discount' size=4></td>
                        </tr>

                        <tr>
                            <td>Дата начала действия промокода</td>
                            <td><input type="date" name='startValidity'></td>
                        </tr>
                        <tr>
                            <td>Дата окончания действия промокода</td>
                            <td><input type="date" name='expiration'></td>
                        </tr>

                    </table>

                    <button style='margin-top:10px' class='btn-sm btn-success'>Создать</button>
                </form>
            </div>
            
            <div id='tableDiscountType' class="col-sm-5 unShow">
            <input id='inputNewDiscountType' type="text">
            <button style='margin-left:5px; margin-bottom:5px' id='buttonNewDiscountType' class='btn-sm btn-success'>Создать</button>
                <table class=" table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Тип</th>
                            <th></th>
                    </thead>
                    <tbody id='tableBodyDiscountType'>

                    </tbody>
                </table>
            </div>
        </div>





    </div>
</div>

<a style="margin-top:20px;" class="btn btn-default" href="{{ route('promocode.index') }}">Назад</a>




<style>
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

</style>
@endsection
@section('scripts')
@parent
<script>

$(document).on('keyup','#inputUpper',function() {
    $(this).val($(this).val().toUpperCase());
});

$(document).on('input','#inputUpper', function() {
    $(this).val($(this).val().replace(/[^A-Za-z0-9]/, ''));
});

$(document).ready(function(){

    reloadSelectDiscountType();
});

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

$(document).on('click','#changeDiscountType',function()
{
    $('#tableDiscountType').toggleClass('unShow');
    reloadDiscountType();
});

function reloadDiscountType()
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
        let tbl = "";
        data.forEach(function(item, index, ar){
            tbl += "<tr>";
            tbl += "<td>" + item.id + "</td>";
            tbl += "<td id=nameItems><input type='text' id='i_"+item.id+"' class='unShow' value='"+item.title+"'><button class='unShow btn-xs btn-success saveType'  value="+item.id+" type='button' id='b_"+item.id+"'>Сохранить</button><p id='p_"+item.id+"' >" + item.title + "</p></td>";
            tbl += "<td><button class='btn-xs btn-info discountTypeMenuEdit' value="+item.id+">Редактировать</button><button style='margin-left:5px' class='discountTypeMenuDelete btn-xs btn-danger' value="+item.id +">Удалить</button></td>";
            tbl += "<tr>";
        });
        $('#tableBodyDiscountType').html(" ");
        $('#tableBodyDiscountType').html(tbl);
    }
    })
}


$(document).on('click','.discountTypeMenuEdit',function(){
    var id = $(this).val();
    console.log(id);
    $('#i_'+id).toggleClass('unShow');
    $('#p_'+id).toggleClass('unShow');
    $('#b_'+id).toggleClass('unShow');
});

$(document).on('click','.discountTypeMenuDelete',function(){
    var id = $(this).val();

    $.ajax({
    headers: {'X-Access-Token': $('#api_token').val()},
    beforeSend: function(xhr) {
        xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
    },
    url:"/admin/deleteDiscountType?id="+id,
    dataType: 'json',
    success:function(data)
    {
        if (data.code==200)
        {
            reloadDiscountType();
            reloadSelectDiscountType();
        }
        showReturn(data.mesage);
        unShowReturn();
    }
    })

});

$(document).on('click','.saveType',function(){
    var id = $(this).val();
    var title = $("#i_"+id).val();
    $.ajax({
    headers: {'X-Access-Token': $('#api_token').val()},
    beforeSend: function(xhr) {
        xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
    },
    url:"/admin/editDiscountType?id="+id+"&title="+title,
    dataType: 'json',
    success:function(data)
    {
        if (data.code==200)
        {
            reloadDiscountType();
            reloadSelectDiscountType();
        }
        showReturn(data.mesage);
        unShowReturn();
    }
    })

});

$(document).on('click','#buttonNewDiscountType',function(){
    var title = $("#inputNewDiscountType").val();
    $.ajax({
    headers: {'X-Access-Token': $('#api_token').val()},
    beforeSend: function(xhr) {
        xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
    },
    url:"/admin/addDiscountType?title="+title,
    dataType: 'json',
    success:function(data)
    {
        if (data.code==200)
        {
            reloadDiscountType();
            reloadSelectDiscountType();
        }
        showReturn(data.mesage);
        unShowReturn();
        

    }
    })

});


function showReturn(text) {
        var popup = document.getElementById("myPopup");
        $('#popupText').html(text);
        popup.classList.toggle("show");
    }

    function unShowReturn(){setTimeout(showReturn, 3000);}


</script>
@endsection