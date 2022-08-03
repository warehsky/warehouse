<?php

namespace App\Http\Controllers\Api;

use App\Model\ChatMessages;
use App\Model\ChatUsers;
use App\Model\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Model\ChatAnswers;

class ChatMessagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('adminauth.access-token');
            // $user = Admin::where('id', 1)->first();
        //    \Auth::guard('admin')->login($user);
    }
    /**
     * Возвращает список пользователей в формате json
     */
    public function getChatUsers(Request $request){
        $dFrom = $request->input('dFrom') ?? null;
        $dTo = $request->input('dTo') ?? null;
        $status = $request->input('status') ?? -1;

        $users = ChatUsers::getUsers($dFrom, $dTo, $status)->orderBy('created_at', 'asc')->get();

        return json_encode( ['ChatUsers'=>$users, 'code' => 200], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает список сообщений пользователя в формате json
     */
    public function getChatMessages(Request $request){
        $dFrom = $request->input('dFrom') ?? null;
        $dTo = $request->input('dTo') ?? null;
        $userId = $request->input('userId') ?? 0;
        $status = $request->input('status') ?? -1;

        $messages = ChatMessages::getMessages($userId, $dFrom, $dTo, $status)->orderBy('created_at', 'asc')->get();

        return json_encode( ['ChatMessages'=>$messages, 'code' => 200], JSON_UNESCAPED_UNICODE );
    }
    /**
     * отправляет сообщение в чат пользователю
     * Входные параметры:
     * userId - ID пользователя
     * message - сообщение
     * 
     */
    public function addChatMessage(Request $request)
    {
        if(!$request->input('userId')) // нет пользователя чата
            return json_encode( ['msg'=>'сообщение не отправлено, нет пользователя', 'code' => 400], JSON_UNESCAPED_UNICODE );
        if(!$request->input('message')) // нет сообщения
            return json_encode( ['msg'=>'сообщение не отправлено, нет сообщения', 'code' => 400], JSON_UNESCAPED_UNICODE );
        ChatMessages::where('chatUserId', $request->input('userId'))->update(['status' => 1]);
        $mes = ChatMessages::create([
                            'chatUserId' => $request->input('userId'),
                            'message' => $request->input('message'), 
                            'moderatorId' =>  \Auth::guard('admin')->user()->id
                        ]);
        
        return json_encode( ['msg' => 'сообщение добавлено', 'code' => 200, 'id' => $mes->id], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Подтверждает отправку сообщения пользователю
     * Входные параметры:
     * 
     * ids - массив из id сообщений в формате json
    */
    public function confirmChatMessages(Request $request){
        $ids = $request->input('ids') ?? 0;
        
        if($ids === 0)
            return json_encode( ['code' => 400, 'mes' => 'нет обязательных полей'], JSON_UNESCAPED_UNICODE );
        $ids = json_decode($ids);
        
        if(!$ids || !is_array($ids))
            return json_encode( ['code' => 400, 'mes' => 'не верный формат строки json'], JSON_UNESCAPED_UNICODE );
        ChatMessages::whereIn('id', array_values($ids))->update(['status' => 1]);
        return json_encode( ['code' => 200, 'mes' => 'статус изменен'], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает шаблоны ответов для чата
    */
    public function getChatAnswers(Request $request){
        $answers = ChatAnswers::all();
        return response()->json( ['code' => 200, 'answers' => $answers], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Обновляет/добавляет шаблоны ответов для чата
    */
    public function updateChatAnswer(Request $request){
        $id = $request->input('id') ?? 0;
        $answer = $request->input('answer') ?? "";
        if(empty($answer))
            return response()->json( ['code' => 300, 'mes' => "не обновили, передана пустая строка"], JSON_UNESCAPED_UNICODE );
        $data = ["answer" => $answer];
        if($id)
            ChatAnswers::where('id', $id)->update($data);
        else
            ChatAnswers::create($data);
        return response()->json( ['code' => 200, 'mes' => "шаблон обновлен"], JSON_UNESCAPED_UNICODE );
    }
    /**
     * удаляет шаблон ответов для чата
    */
    public function deleteChatAnswer(Request $request){
        $id = $request->input('id') ?? 0;
        if($id)
            ChatAnswers::where('id', $id)->delete();
        else
            return response()->json( ['code' => 300, 'mes' => "шаблон не удален, нет ID"], JSON_UNESCAPED_UNICODE );
        return response()->json( ['code' => 200, 'mes' => "шаблон удален"], JSON_UNESCAPED_UNICODE );
    }
}
