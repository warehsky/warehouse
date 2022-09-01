@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Накладная выдачи товара
    </div>

    <div class="card-body">
        <a href="{{ route("expenses.edit", [0]) }}" class="btn btn-primary">Добавить</a>
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            ID<br>
                            <input type="text" id="searchID" class="search" value="{{$_GET['id'] ?? ""}}" placeholder="ID" size="4"></input>
                        </th>
                        <th id="sort" class="search" style="cursor: pointer;">
                            <div>
                                <div id="arrow"  style="min-width:150px;">Дата создания &uarr;</div>                     
                                <input type="date" id="searchDate" class="search" value="{{$_GET['date'] ?? ""}}" placeholder="Дата создания" size="10" style="float: left;"></input>
                            </div>
                        </th>
                        <th>
                            Клиент<br>
                            <input type="text" id="searchWeightId" class="search" value="{{$_GET['clientId'] ?? ""}}" placeholder="ID клиента" size="10"></input>
                        </th>

                        <th style="min-width:250px;">
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody id="searchTbody">
                    @include('expenses.pagination_data')
                </tbody>
            </table>
            <input type="hidden"  id="sorting" value={{$_GET['sorting'] ?? 'desc'}} />
            <input type="hidden" name="hidden_page" id="hidden_page" value={{$_GET['page'] ?? 1}} />
        </div>

        

    </div>
</div>



@endsection
@section('scripts')
@parent
<script>

    function fetch_data(page, id, name, longName,date, sorting)
    {
        var id1c = $('#searchID1C').val();
        var weightId = $('#searchWeightId').val();
        $.ajax({
        url:"itemsSearch?page="+page+"&id="+id+"&name="+name+"&longName="+longName+"&date="+date+"&sorting="+sorting+"&id1c="+id1c+"&weightId="+weightId,
        success:function(data)
        {
            $('#searchTbody').html('');
            $('#searchTbody').html(data);
        }
        })
    }


function get_data()
{
        var IDVal = $('#searchID').val();
        var NameVal = $('#searchName').val();
        var LongNameVal = $('#searchLongName').val();
        var page = $('#hidden_page').val();
        var date = $('#searchDate').val();
        var sorting = $('#sorting').val();
        fetch_data(page, IDVal, NameVal, LongNameVal,date,sorting);
}

    $(document).on('click', '#sort', function(event){
        var sr=$('#sorting').val();
        $('#hidden_page').val(1);
        var dawn= 'Дата создания &darr;';
        var up='Дата создания &uarr;' ;
        if(sr=='asc')
        {
            $('#sorting').val('desc');
            $('#arrow').html(up);
        }
        else 
        {
            $('#sorting').val('asc');
            $('#arrow').html(dawn);
        }
        
        get_data()
    });


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