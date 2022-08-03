<?php

namespace App\Http\Controllers\Admin;

use App\Model\CommentAnswers;
use App\Model\Comments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class CommentAnswersController extends Controller
{
    
    /**
     * Добавить отзыв к комментарию
     */
    public function create(Request $request){
        
        $commentId = $request->input('commentId') ?? 0;
        
        $api_token = \Auth::guard('admin')->user()->getToken();

        $comment = Comments::find($commentId);
        $answer = 0;
        return view('Admin.commentAnswer', compact('comment', 'answer', 'api_token'));
    }
    /**
     * сохраняет новый отзыв
     */
    public function store(Request $request){
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        $answer = CommentAnswers::create($data);
        return redirect()->route('comments');

    }
    /**
     * Редактировать отзыв к комментарию
     */
    public function edit($id, Request $request){
        $answer = CommentAnswers::find($id);
        if($answer)
            $comment = Comments::find($answer->commentId);
        $api_token = \Auth::guard('admin')->user()->getToken();

        

        return view('Admin.commentAnswer', compact('comment', 'answer', 'api_token'));
    }
    /**
     * сохраняет изменения отзыва
     */
    public function update($id, Request $request){
        $data = $request->except(['_method', '_token']);
        $data['moderatorId'] = \Auth::guard('admin')->user()->id;
        $answer = CommentAnswers::find($id);
        
        if($answer)
            $answer->update($data);
        return redirect()->route('comments');

    }

}
