
@foreach($reminds as $el)
    <tr>
        <td>{{ $el->itemId}}</td>
        <td>{{ $el->item}}</td>
        <td>{{ $el->remind}}</td>
        <td>{{ $el->price }}</td>
        <td>
        {{ $el->client }}
        </td>
        <td>{{ $el->created_at}}</td>
        <td><a href='/orders/{{$el->orderId}}/edit?e=0' target="_blank" title="нажмите для перехода к накладной №{{ $el->orderId}}">{{ $el->orderId}}</a></td>
    </tr>
@endforeach
    <tr>
       <td colspan="7" align="right">
        {!! $reminds->links() !!}
       </td>
    </tr>