<?php

namespace App\Http\Controllers\Admin;

use App\Model\ChatMessages;
use App\Model\ChatUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class ChatMessagesController extends Controller
{
    
    /**
     * Показ списка пользователей и списка сообщений пользователя
     */
    public function chat(Request $request){
        $dFrom = $request->input('dFrom') ?? null;
        $dTo = $request->input('dTo') ?? null;
        $status = $request->input('status') ?? -1;

        $api_token = \Auth::guard('admin')->user()->getToken();
        $moderatorname = \Auth::guard('admin')->user()->chatName;
        $canWriting= \Auth::guard('admin')->user()->can('chat_edit');
        
        return view('Admin.chat', compact('api_token', 'moderatorname','canWriting'));
    }
    
}
