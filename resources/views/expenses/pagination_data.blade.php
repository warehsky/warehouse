
@foreach($expenses as $el)
    <tr>
        <td>{{ $el->id}}</td>
        <td>{{ $el->created_at}}</td>
        <td>@if($el->client) {{$el->client->client}} @endif</td>
        
        <td>
            <a class='btn btn-xs btn-primary' href='/expenses/{{$el->id}}/edit?e=0&id={{$id ?? ""}}&name={{$name ?? ""}}&longName={{$longName ?? ""}}&page={{$page ?? ""}}&date={{$date ?? ""}}&sorting={{$sorting ?? ""}}&id1c={{$id1c ?? ""}}&weightId={{$weightId ?? ""}}'>просмотр</a>
            <a class='btn btn-xs btn-info' href='/expenses/{{$el->id}}/edit?ids={{$id ?? ""}}&name={{$name ?? ""}}&longName={{$longName ?? ""}}&page={{$page ?? ""}}&date={{$date ?? ""}}&sorting={{$sorting ?? ""}}&id1c={{$id1c ?? ""}}&weightId={{$weightId ?? ""}}'>изменить</a>
            <form action='/admin/items/{{$el->id}}?id={{$id ?? ""}}&name={{$name ?? ""}}&longName={{$longName ?? ""}}&page={{$page ?? ""}}&date={{$date ?? ""}}&sorting={{$sorting ?? ""}}&id1c={{$id1c ?? ""}}&weightId={{$weightId ?? ""}}' method='POST' onsubmit="return confirm('Уверен');" style='display: inline-block;'>
                <input type='hidden' name='_method' value='DELETE'>
                <input type='hidden' name='_token' value='{{ csrf_token() }}'>
                <input type='submit' class='btn btn-xs btn-danger' value='удалить'>
            </form>      
        </td>

    </tr>
@endforeach
    <tr>
       <td colspan="7" align="right">
        {!! $expenses->links() !!}
       </td>
    </tr>