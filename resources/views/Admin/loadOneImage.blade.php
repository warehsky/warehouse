@extends('layouts.admin')
@section('content')
<input type="hidden" id="api_token" value="{{$api_token}}">
<meta name="csrf-token" content="{{ csrf_token() }}" />
<div class="card" style="margin-top:10px">
    <div class="popup" id="myPopup">
        <span class="popuptext">Изображение удалено</span>
    </div>
    <div class="card-header">
        Рекламные изображения
    </div>
        <div class="card-body">
            <div style='border: 1px solid grey; padding: 5px; width:600px; border-radius: 3px; margin-bottom:10px' >
                Добавить изображение:
                <input type="file" id="addImages">
                <p id='newURL' style='margin:0'>Ссылка:</p>
            </div>
        <table class=" table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    
                    <th style='width:200px'>
                        Изображение
                    </th>
                    <th>
                        Ссылка
                    </th>
                    <th>
                        
                    </th>
                    
                </tr>
            </thead>
            <tbody id="imageBody">
                
            </tbody>
        </table>
    </div>
</div>
<style>
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
    width: 150px;
    height: 100px;
    border-radius: 6px;
    z-index: 20;
    /*transition: 2s ease;*/
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
.selectedImage {
    border: 3px solid #FF0000;
}
.changeCursor{
    cursor:crosshair; 
}
.layer {
    overflow: scroll;
    width: 300px;
    height: 690px;
    padding: 5px;
} 
.btn-delete{
    display:none;
}
</style>
@endsection
@section('scripts')
@parent
<script>
    var nameImage  = new FormData();
    $(document).on('change','#addImages',function(){
        nameImage.append('image', $(this)[0].files[0]);
        addNewImage();
    });

   
    function addNewImage()
    {
        $.ajax({
            headers: {'X-Access-Token': $('#api_token').val(),'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                },
            url: "/admin/loadOneImage",
            type: 'POST',
            data: nameImage,
            dataType: 'json',
            async: true,
            success: function (data) {
                $('#newURL').html('Ссылка: <b>'+data);
                fetch_data();
            },
            cache: false,
            contentType: false,
            processData: false
        });

    };


    function showReturn() {
        var popup = document.getElementById("myPopup");
        popup.classList.toggle("show");
    }

    function unShowReturn(){setTimeout(showReturn, 1500);}


/////////////////////////////////////////////////////////////////////////////////////////////////////////


function fetch_data()
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/admin/getAllImage",
        dataType: 'json',
        success:function(data)
        {
            console.log(data);
            let tbl=getWebItemsTbl(data);
            $('#imageBody').html(tbl);
        }
        })
    }

function getWebItemsTbl(data){
    let tbl = "";
    
    data.forEach(function(item, index, ar){
        tbl += "<tr>";
        tbl += `<td><img src='/${item.img}' width='100%' height='100%' ></td>`;
        tbl += "<td><a href='"+item.url+"' target='_blank'>"+item.url+"</a></td>";
        tbl += "<td><button class='btn-xs btn-danger' value="+item.name+" id='deleteImageBTN'>Удалить</button></td>";
        tbl += "<tr>";
    });
    return tbl;
}

$(document).on('click','#deleteImageBTN',function(){
    var name = $(this).val();
    if(confirm('Удалить изображение?'))
    {
        $.ajax({
            headers: {'X-Access-Token': $('#api_token').val()},
            beforeSend: function(xhr) {
                xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
            },
            url:"/admin/deleteImage",
            data: 
                {
                    id: name,
                },
            dataType: 'json',
            success:function(data)
            {
                if(data.code==200)
                {
                    fetch_data();
                    showReturn();
                    unShowReturn();
                }
                else
                    console.log(data);
            }
            })
    }
});

$(document).ready(function(){
    fetch_data();
});






</script>
@endsection
