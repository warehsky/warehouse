
@foreach($orders as $el)
    <tr>
        <td>{{ $el->id}}</td>
        <td>{{ $el->created_at}}</td>
        <td>{{ $el->client}}</td>
        <td>{{ $el->count}}</td>
        <td>{{ $el->sum_total}}</td>
        <td>{{ $el->days}}</td>
        <td>
            <a class='btn btn-xs btn-primary' href='/orders/{{$el->id}}?id={{$id ?? ""}}&name={{$name ?? ""}}&page={{$page ?? ""}}&sorting={{$sorting ?? ""}}'>просмотр</a>
            <a class='btn btn-xs btn-info' href='/orders/{{$el->id}}/edit?ids={{$id ?? ""}}&name={{$name ?? ""}}&page={{$page ?? ""}}'>изменить</a>
            <form action='/orders/{{$el->id}}?id={{$id ?? ""}}&name={{$name ?? ""}}&page={{$page ?? ""}}' method='POST' onsubmit="return confirm('Уверен');" style='display: inline-block;'>
                <input type='hidden' name='_method' value='DELETE'>
                <input type='hidden' name='_token' value='{{ csrf_token() }}'>
                <input type='submit' class='btn btn-xs btn-danger' value='удалить'>
            </form>      
        </td>

    </tr>
@endforeach
    <tr>
       <td colspan="7" align="right">
        {!! $orders->links() !!}
       </td>
    </tr>