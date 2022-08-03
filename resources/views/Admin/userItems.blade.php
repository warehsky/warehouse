@extends('layouts.app')

@section('content')
<script src="{{ asset('js/jquery-3.4.1.min.js') }}" ></script>
<script src="{{ asset('js/datatables.min.js') }}"></script> 
<link href="{{ asset('css/datatables.min.css') }}" rel="stylesheet">

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css"> 
 -->


<div class="container">
    <div class="row justify-content-center">
    <table id="users-tbl" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{$item->id}}</td>
                <td>{{$item->name}}</td>
                <td>{{$item->price}}</td>
                <th><a class="btn primary" href="#" >ХЗ</a></th>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
            <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Actions</th>
            </tr>
        </tfoot>
    </table>
        
    </div>
</div>
<script>
    var $ = jQuery;
    jQuery(document).ready(function($) {
        table = $('#users-tbl').DataTable({
                "scrollX": true,
                "order": [[ 1, "asc" ]],
                "columnDefs": [
                {
                    "targets": [ 0 ],
                    "visible": false,
                    "searchable": false
                },
                {
                    "targets": [ 2 ],
                    "searchable": false,
                    'bSortable': false
                },
                {
                    "targets": [ 3 ],
                    "searchable": false,
                    'bSortable': false
                },
                ],
                dom: 'Bfrtip',
    buttons: [
        'copyHtml5',
        'excelHtml5',
        'csvHtml5',
        'pdfHtml5'
    ]
    } );

    

    } );
    
</script>
@endsection
