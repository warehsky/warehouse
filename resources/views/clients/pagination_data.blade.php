
@foreach($cargos as $el)
    <tr>
        <td>{{ $el->id}}</td>
        <td>{{ $el->cargo}}</td>
        <td>{{ $el->created_at}}</td>
        <td>{{ $el->deleted ? 'удален' : 'активен' }}</td>
        <td>
            <a class='btn btn-xs btn-info' href='/cargos/{{$el->id}}/edit?ids={{$id ?? ""}}&name={{$name ?? ""}}&longName={{$longName ?? ""}}&page={{$page ?? ""}}&date={{$date ?? ""}}&sorting={{$sorting ?? ""}}&id1c={{$id1c ?? ""}}&weightId={{$weightId ?? ""}}'>изменить</a>
            <form action='/cargos/{{$el->id}}?id={{$id ?? ""}}&name={{$name ?? ""}}&longName={{$longName ?? ""}}&page={{$page ?? ""}}&date={{$date ?? ""}}&sorting={{$sorting ?? ""}}&id1c={{$id1c ?? ""}}&weightId={{$weightId ?? ""}}' method='POST' onsubmit="return confirm('Уверен');" style='display: inline-block;'>
                <input type='hidden' name='_method' value='DELETE'>
                <input type='hidden' name='_token' value='{{ csrf_token() }}'>
                <input type='submit' class='btn btn-xs btn-danger' value='удалить'>
            </form>      
        </td>

    </tr>
@endforeach
    <tr>
       <td colspan="7" align="right">
        {!! $cargos->links() !!}
       </td>
    </tr>