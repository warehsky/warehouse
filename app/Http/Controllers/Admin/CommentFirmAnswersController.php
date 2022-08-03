<?php

namespace App\Http\Controllers\Admin;

use App\Model\CommentFirmAnswers;
use App\Model\CommentsFirm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class CommentFirmAnswersController extends Controller
{
    
    /**
     * Добавить отзыв к комментарию
     */
    public function create(Request $request){
        
        $commentId = $request->input('commentId') ?? 0;
        
        $api_token = \Auth::guard('admin')->user()->getToken();

        $comment = CommentsFirm::find($commentId);
        $answer = 0;
        return view('Admin.commentFirmAnswer', compact('comment', 'answer', 'api_token'));
    }
    /**
     * сохраняет новый отзыв
     */
    public function store(Request $request){
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        $answer = CommentFirmAnswers::create($data);
        return redirect()->route('commentsFirm');

    }
    /**
     * Редактировать отзыв к комментарию
     */
    public function edit($id, Request $request){
        $answer = CommentFirmAnswers::find($id);
        if($answer)
            $comment = CommentsFirm::find($answer->commentId);
        $api_token = \Auth::guard('admin')->user()->getToken();

        

        return view('Admin.commentFirmAnswer', compact('comment', 'answer', 'api_token'));
    }
    /**
     * сохраняет изменения отзыва
     */
    public function update($id, Request $request){
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        $answer = CommentFirmAnswers::find($id);
        
        if($answer)
            $answer->update($data);
        return redirect()->route('commentsFirm');

    }

}
