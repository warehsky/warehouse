@extends('layouts.admin')
@section('content')
<div class="card" style="margin-top:10px">
    <div class="popup" id="myPopup">
        <span class="popuptext" >Изображение изменено</span>
    </div>
    <div class="card-header">
        Загрузка изображений
    </div>

   
    <input type="hidden" id="api_token" value="{{$api_token}}">
    <input type="hidden" name="hidden_page" id="hidden_page" value={{$_GET['page'] ?? 1}} />
    <input type="hidden"  id="sorting" value={{$_GET['sorting'] ?? 'desc'}} />
    <input type="hidden"  id="sortingField" value={{$_GET['sortingField'] ?? 'created_at'}} />
    <param id="selectedItemsParam" value=''>
    <param id="selectedImageParam" value=''>
    <div class="card-body">
    
        <div class="row">
            <div class="col-sm-8 layer">
                <div class="table-responsive">
                    <table class=" table table-bordered table-striped table-hover datatable datatable-item">
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
                                    Изображение
                                <th>
                                <div id="sort" class="search" style="cursor: pointer; min-width:260px;">
                                    <param value="created_at">
                                    Дата добавления<br>
                                </div>
                                    <input type="date" id="searchDateStart" class="search" size="10"></input>
                                    <input type="date" id="searchDateEnd" class="search" size="10"></input>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="searchTbody">
                            
                        </tbody>
                    </table>
                    <div id="pagLink"> </div>
                </div>
            </div>

            
            <div class="col-sm-4 layer">
            <form  method="post" enctype="multipart/form-data" id="uploadImages">
                    <input type="file" id="addImages" style="margin-bottom:10px" multiple="" class="addImages">
                <ul id="uploadImagesList">
                    <div>
                    <li class="item template">
                        <span class="img-wrap">
                            <img src="" alt="">
                        </span>
                        <span class="desc">
                            <p></p>
                            <button type='button' class="delete-link btn btn-xs btn-info" title="Удалить">Удалить</button>
                        </span>
                    </li>
                    </div>
                </ul>
            </form>
            
<a class='btn btn-xs btn-success' style="color: #ffffff" id='sendImage' title='Сохранить все изображения отображаемые в превью с соответствующими названиями'>Сохранить все изображения</a>

                <div id="images_preview"></div>
            </div>
        </div>

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

/*input start*/
/*
.addImages{
    display: none;
}
.wrap-input_btn{
    border: none;
    background: #931515;
}
*/
/*input end*/ 

.selectedItem {
    background: #BBF1CD;
}
.selectedImage {
    border: 3px solid #FF0000;
}
.changeCursor{
    cursor:crosshair; 
}
#uploadImagesList {
    list-style: none;
    padding: 0;
    display: flex;
    flex-direction: column;
    height: 100%;
}
#uploadImagesList .item {
    float: left;
    margin-right: 20px;
    margin-bottom: 20px;
    display: flex;
}
#uploadImagesList .item .img-wrap {
    width: inherit;
    display: block;
    height: 150px;
}
#uploadImagesList .item .img-wrap img{
    width: auto;
    max-height: 300px;
    cursor: pointer;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
    height: inherit;
}
#uploadImagesList .item .img-wrap p{
    width: auto;
    margin-top:20px;
}
#uploadImagesList .item .delete-link {
    cursor: pointer;
    display: block;
}
#uploadImagesList .item .desc{
    padding-left: 15px;
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




 jQuery(document).ready(function ($) {
 
     var queue = {};
     
     var imagesList = $('#uploadImagesList');

     var imageExists = false;
     var itemsExists  = false;
     var nameImage  = new FormData();
		
 
     var itemPreviewTemplate = imagesList.find('.item.template').clone();
     itemPreviewTemplate.removeClass('template');
     imagesList.find('.item.template').remove();
 

    $(document).on('click','#nameItems', function(){
        if ($('#selectedItemsParam').val()==$(this).find('param').val())
        {
            itemsExists=false;
            $('#selectedItemsParam').val('');
            $(this).find('#deleteImage').addClass('btn-delete');
        }
        else 
        {
            itemsExists=true;
            $('button').addClass('btn-delete');
            $(this).find('#deleteImage').removeClass('btn-delete');
            $('#selectedItemsParam').val($(this).find('param').val());
            $('td').removeClass('selectedItem');
            nameImage.append("id", $(this).find('param').val());
        }
        $(this).toggleClass('selectedItem');
        $('body').toggleClass('changeCursor');
        if(imageExists && itemsExists)
            addNewImage();
    });  
/*
    $('.wrap-input_btn').on('click', function(){
    $('#addImages').trigger('click'); 
});
 */
     $('#addImages').on('change', function () {
         var files = this.files;
         
         for (var i = 0; i < files.length; i++) {
             var file = files[i];
 
             if ( !file.type.match(/image\/(png)/) ) {
                 alert( 'Фотография должна быть в формате png' );
                 continue;
             }
           
             
             preview(files[i]);
         }
 
         this.value = '';
     });

       

     $(document).on('click', '#sendImage', function(event){
        nameImage = new FormData();
        for (var id in queue) {
            nameImage.append('images[]', queue[id]);
        }
        
        $.ajax({
            headers: {'X-Access-Token': $('#api_token').val()},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                },
            url: '/Api/saveAllImage',
            type: 'POST',
            data: nameImage,
            async: true,
            success: function (res) {

                if (res==200)
                {
                    imageExists = false;
                    itemsExists = false;
                    $('#selectedItemsParam').val('');
                    $('#selectedImageParam').val('');
                    get_data();
                    showReturn();
                    unShowReturn();
                }
                else 
                if (res==404)
                    alert('Файлы не выбраны');
                else
                    alert('Ошибка сохранения');
            },
            error: function (error) {
                imageExists = false;
                itemsExists = false;
                $('#selectedItemsParam').val('');
                $('#selectedImageParam').val('');
                alert('Максимальное разрешение изображения 400х400 пикселей');
            },
            cache: false,
            contentType: false,
            processData: false
        });

        return false;
        });
 


     // Создание превью
     function preview(file) {
         var reader = new FileReader();
         reader.addEventListener('load', function(event) {
             var img = document.createElement('img');
             var itemPreview = itemPreviewTemplate.clone();
             itemPreview.find('.img-wrap img').attr({src:event.target.result,id:file.name});
             itemPreview.find('.desc p').html(file.name);
             itemPreview.data('id', file.name);
             imagesList.append(itemPreview);
             queue[file.name] = file;
         });
         reader.readAsDataURL(file);
     }

 
     // Удаление фотографий
    imagesList.on('click', '.delete-link', function () {
        if(confirm("Удалить изображение?"))
        {
         var item = $(this).closest('.item'),
             id = item.data('id');
         delete queue[id];
         
         item.remove();
        }
    });

    //Выбор 
        imagesList.on('click', 'img', function () {
            var item = $(this).closest('.item'),
                id = item.data('id');
                if ($('#selectedImageParam').val()==id)
                {
                    imageExists=false;
                    $(this).toggleClass('selectedImage');
                    $('#selectedImageParam').val('');
                }
                else 
                {
                    imageExists=true;
                    $('#selectedImageParam').val(id);
                    $('img').removeClass('selectedImage');
                    $(this).toggleClass('selectedImage');
                    nameImage.delete('file');
                    nameImage.append('file', queue[id]);
                }
                    $('body').toggleClass('changeCursor');
                    if(imageExists && itemsExists)
                        addNewImage();
    });
 

    function addNewImage()
    {
        $('td').removeClass('selectedItem');
        $('img').removeClass('selectedImage');
        $('body').removeClass('changeCursor');
        $.ajax({
            headers: {'X-Access-Token': $('#api_token').val()},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
                },
            url: "/Api/addImageItem",
            type: 'POST',
            data: nameImage,
            async: true,
            success: function (res) {
                if (res==200)
                {
                    queue[$('#selectedImageParam').val()] = new File([queue[$('#selectedImageParam').val()]], $('#selectedItemsParam').val()+".png", {
                    type: queue[$('#selectedImageParam').val()].type});
                    $('#uploadImagesList').html('');
                    Object.entries(queue).forEach(el => {preview(el[1]);})
                    delete queue[$('#selectedImageParam').val()]; 
                    imageExists = false;
                    itemsExists = false;
                    $('#selectedItemsParam').val('');
                    $('#selectedImageParam').val('');
                    get_data();
                    showReturn();
                    unShowReturn();
                }
                else
                {
                    alert("Ошибка при сохранении изображения");
                }
            },
            error: function (error) {
                imageExists = false;
                itemsExists = false;
                $('#selectedItemsParam').val('');
                $('#selectedImageParam').val('');
                alert('Максимальное разрешение изображения 400х400 пикселей');
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




 });


/////////////////////////////////////////////////////////////////////////////////////////////////////////

function fetch_data(page, id, name, parentTitle,dateStart, dateEnd, sortingMethod, sortingField)
    {
        $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/AllItemsForLoad?page="+page+"&id="+id+"&name="+name+"&parentTitle="+parentTitle+"&dateStart="+dateStart+"&dateEnd="+dateEnd+"&sortingMethod="+sortingMethod+"&sortField="+sortingField+"&path=/admin/imageload",
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

function getWebItemsTbl(data){
    let tbl = "";
    data.items.forEach(function(item, index, ar){
        tbl += "<tr>";
        tbl += "<td>" + item.id + "</td>";
        tbl += "<td>" + item.title + "</td>";
        tbl += "<td>[#"+item.parentId+'] '+ item.parentTitle + "</td>";
        tbl += "<td id=nameItems style='cursor:pointer'><param value="+item.id+"><img alt='Нет изображения' src='/img/img/items/small/"+item.id+".png?" + new Date().getTime()+"' width='150'><button   class='btn-xs btn-danger btn-delete' value="+item.id+" id='deleteImage'>Удалить</button></td>";
        tbl += "<td>" + item.created_at + "</td>";
        tbl += "<tr>";
    });
    return tbl;
}

$(document).on('click','#deleteImage',function(event){
    var id = 'small\\'+$(this).val()+".png";
    if(confirm("Удалить изображение?"))
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"/Api/deleteItemImage?id="+id,
        dataType: 'json',
        success:function(data)
        {
            if (data==200)
            {
                get_data();
            }
            else 
                if (data==404)
                    alert('Изображение не найдено');
        }
        })
});


$(document).on('click', '#sort', function(event){

        var sr=$('#sorting').val();
        $('#hidden_page').val(1);
        $('#sortingField').val($(this).find('param').val());
        if(sr=='asc')
            $('#sorting').val('desc');
        else 
            $('#sorting').val('asc');
        get_data();
    });

function get_data()
{
        var IDVal = $('#searchID').val();
        var NameVal = $('#searchName').val();
        var ParentVal = $('#searchParent').val();
        var page = $('#hidden_page').val();
        var dateStart = $('#searchDateStart').val();
        var dateEnd = $('#searchDateEnd').val();
        var sortingMethod = $('#sorting').val();
        var sortingField = $('#sortingField').val();
        fetch_data(page, IDVal, NameVal, ParentVal,dateStart,dateEnd,sortingMethod,sortingField);
}

   


    $(document).on('keyup change', '.search', function(){
        $('#hidden_page').val(1);
        get_data();
    });

    $(document).ready(function(){
        $('#hidden_page').val(1);
        get_data();
    });

    $(document).on('click', '.pagination a', function(event){
        event.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        $('#hidden_page').val(page);
        $('li').removeClass('active');
        $(this).parent().addClass('active');
        get_data();
    });



</script>
@endsection
