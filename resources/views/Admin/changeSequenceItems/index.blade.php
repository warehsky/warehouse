@extends('layouts.admin')

@section('content')
<h3>Окно редактирования последовательности вывода товара в карусели </h3>
<div style="margin-bottom: 10px;" class="row">
	<input type="hidden"  id="selectedTag"/>
	<input type="hidden"  id="selectedParentTag"/>
	<div class="col-lg-8">
		<span>Группа тегов: </span>
		<select style="width: 300px" class="TagGroups" id="id">
		</select>

		<span>Тег: </span>
		<select style="width: 300px" class="tagOnGroup" name="tag">
		</select>
	</div>
	<div class="col-lg-4">
			<span>Найти по номеру тега: </span>
			<input type="number" id="findForTagInput" size="10" />
			<input id='findForTag' class="btn btn-success btn-sm findForTag" value="Найти">
	</div>
</div>

<div class="card">
    <div class="card-header">
        Группы товаров
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
            </table>
        </div>
    </div>
</div>


<style>
p{
	margin:0;
}
.unShow{
    display:none;
}
.findForTag{
	max-width: 90px;
}
</style>



@endsection
@section('scripts')
@parent
<script>

$(document).ready(function(){
	$('#selectedTag').val(330);
	$('#selectedParentTag').val(1);
	updateTable(330);
	updateParentTag();
	

});



$(document).on('change','.tagOnGroup',function(){
	$('#selectedTag').val($(this).val());
	updateTable($(this).val());
});

$(document).on('click','#findForTag',function(){
	$('#selectedTag').val($('#findForTagInput').val());
	updateTable($('#findForTagInput').val());


	$.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"getParentId/?tag="+$('#findForTagInput').val(),
        dataType: 'json',
        success:function(data)
        {
			$('#selectedParentTag').val(data.groupId);
			updateParentTag();
        }
        });
	
});



$(document).on('click','.editSequence',function(){
	var id=$(this).val();
	$.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"update/?id="+id+"&carouselOrder="+$('#input_'+id).val(),
        dataType: 'json',
        success:function(data)
        {
			updateTable($('#selectedTag').val());
			$('#input_'+id).toggleClass('unShow');
			$('#button_'+id).toggleClass('unShow');
        }
        });
});


$(document).on('click','#edit',function(){
	$('#input_'+$(this).val()).toggleClass('unShow');
	$('#button_'+$(this).val()).toggleClass('unShow');
	if ($(this).html()=='Редактировать')
		$(this).html('Отмена');
	else 
		$(this).html('Редактировать');
});


function updateParentTag()
{
	$.ajax({
		type:'get',
		url:"{{ route('changeSequenceItemsGetAllParentTag') }}",
		success:function(data){
			var selected="";
			var op="";
			op+='<option value="0" selected disabled>Выбор группы тегов</option>';
			
			for(var i=0;i<data.length;i++){
				if (data[i].id==$('#selectedParentTag').val())
					{
						selected='selected';
						updateTag(data[i].id);
					}
				else
					selected='';
				op+='<option '+selected+' value="'+data[i].id+'">'+' ('+data[i].id+') '+data[i].title+'</option>';
			}

		$('.TagGroups').html(" ");
		$('.TagGroups').append(op);
		
		},
		error:function(){

		}
	});
}

function updateTag(tag)
{
	$.ajax({
	type:'get',
	url:"{{ route('changeSequenceItemsAllTag') }}",
	data:{'id':tag},
	success:function(data){
		var op="";
        op+='<option value="0" selected disabled>Выбор тега</option>';
		for(var i=0;i<data.length;i++)
		{
			if (data[i].id==$('#selectedTag').val())
					selected='selected';
				else
					selected='';
			op+='<option '+selected+' value="'+data[i].id+'">'+' ('+data[i].id+') '+data[i].title+'</option>';
	   	}

	  $('.tagOnGroup').html(" ");
	  $('.tagOnGroup').append(op);
	},
	error:function(){

	}
});
}

/*
	Обновление таблицы товаров
	Входящие параметры 
		tag - тег по которому искать товары
*/
function updateTable(tag)
{
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url:"findTagItems/?tag="+tag,
        dataType: 'json',
        success:function(data)
        {
            if ( $.fn.dataTable.isDataTable('#outPutTable') ) {
                $('#outPutTable').DataTable().destroy();
                $('#outPutTable').empty();
            }
            var col=[
                    { title: "ID" },
                    { title: "Название" },
                    { title: "Позиция" },
                    { title: "" },
                    { title: "" },
                    
                    

                ];
            createTable(data.items,col);
        }
        });
}



/*
    Постороение таблицы отчета
    Входящие параметры: 
        data - массив с данными 
        col - структура для заголовка таблицы (Пример: [{ title: "ID" },{ title: "Название" },{ title: "Цена" },] )
*/
function createTable(data,col)
{
    
        $.extend( $.fn.dataTable.defaults, {
            searching: true,
            select:false,
            destroy: true,
            });

            $('.datatable').DataTable( {
                data: data,
                columns: col,
        columnDefs: [{ 
            className: 'select-checkbox'},
            { targets:   0
        } ],
        
    } );
}

$(document).on('change','.TagGroups',function(){
	var tag=$(this).val();
	updateTag(tag);

});




</script>
@endsection















