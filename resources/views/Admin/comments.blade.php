@extends('layouts.admin')

@section('content')
<div class="container">
<input type="hidden" id="api_token" value="{{$api_token}}">
     <h2>Отзывы</h2>   
    <table style="width: 100%;" id="tcomments">
    <th>ID</th>
    <th>Дата</th>
    <th>ID товара</th>
    <th>отзыв</th>
    <th>оценка</th>
    <th>статус</th>
    <th>действия</th>
    <tbody>
        @foreach($comments as $comment)
            <tr>
                <td>{{$comment->id}}</td>
                <td>{{$comment->created_at}}</td>
                <td><a href="{{ env('SHOP_URL', '/') }}single?id={{$comment->itemId}}" title="карточка товара" target="_blank">{{$comment->itemId}}</a></td>
                <td>{{$comment->comment}}</td>
                <td>{{$comment->estimate}}</td>
                <td id="st{{$comment->id}}">
                    @if($comment->status==0)
                        @if($comment->moderatorId==0)
                            <span class="bg-danger">не обработан</span>
                        @else
                            <span class="bg-secondary">отклонен</span>
                        @endif
                    @else
                        <span class="bg-success">опубликован</span>
                    @endif
                </td>
                <td id="ac{{$comment->id}}" answerid="{{count($comment->answers)>0?$comment->answers[0]->id:0}}">
                    @if($comment->status!=1)
                        <span class="btn bg-success" onclick="setComment({{$comment->id}}, 1)">публиковать</span>
                    @endif
                    @if($comment->status!=0 || $comment->moderatorId==0)
                        <span class="btn bg-danger" onclick="setComment({{$comment->id}}, 0)">отклонить</span>
                    @endif
                    @if(count($comment->answers)>0)
                        <a class="btn bg-info" href="/admin/commentAnswer/{{$comment->answers[0]->id}}/edit?commentId={{$comment->id}}">ответ</a>
                    @else
                        <a class="btn bg-info" href="/admin/commentAnswer/create?commentId={{$comment->id}}">ответить</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    </table>  
</div>

<style>
#tcomments{
    width: 100%;
}
#tcomments td{
    border: 1px solid black;
}
#tcomments td .btn{
    padding: 2px 2px;
    font-size: 12px;
}
</style>
<script>
/*
*  Изменяет статус комментария
*/
function setComment(id, stat){
    data = {
        id: id,
        status: stat
        
    }
    $.ajax({
        headers: {'X-Access-Token': $('#api_token').val()},
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-Access-Token", $('#api_token').val());
        },
        url: '/Api/setComment',         /* Куда пойдет запрос */
        method: 'get',             /* Метод передачи (post или get) */
        dataType: 'json',          /* Тип данных в ответе (xml, json, script, html). */
        data: data,     /* Параметры передаваемые в запросе. */
        success: function(data){   /* функция которая будет выполнена после успешного запроса.  */
            if(data.code==200){
                let st = $("#st"+id).empty();
                let ac = $("#ac"+id).empty();
                if(stat>0){
                    ac.append('<span class="btn bg-danger" onclick="setComment('+id+', 0)">отклонить</span>');
                    st.append('<span class="bg-success">опубликован</span>');
                }
                else{
                    
                    ac.append('<span class="btn bg-success" onclick="setComment('+id+', 1)">публиковать</span>');
                    st.append('<span class="bg-secondary">отклонен</span>');
                }
            }
        },
        error: function (error) {
            console.error('error; ' + eval(error));
        }
    });
}
</script>
@endsection
