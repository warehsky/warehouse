<?php

namespace App\Http\Controllers\Admin;

use App\Model\Comments;
use App\Model\CommentsFirm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class CommentsController extends Controller
{
    
    /**
     * Показ списка коментариев к товарам
     */
    public function comments(Request $request){
        if (! \Auth::guard('admin')->user()->can('comments_all')) {
            return redirect()->route('home');
        }
        $dFrom = $request->input('dFrom') ?? null;
        $dTo = $request->input('dTo') ?? null;
        $itemId = $request->input('itemId') ?? 0;
        $status = $request->input('status') ?? -1;
        $api_token = \Auth::guard('admin')->user()->getToken();

        $comments = Comments::getComments($dFrom, $dTo, $itemId, $status)->orderBy('created_at', 'desc')->get();

        return view('Admin.comments', compact('comments', 'api_token'));
    }
    /**
     * Показ списка коментариев к сервису
     */
    public function commentsFirm(Request $request){
        if (! \Auth::guard('admin')->user()->can('commentsFirm_all')) {
            return redirect()->route('home');
        }
        $dFrom = $request->input('dFrom') ?? null;
        $dTo = $request->input('dTo') ?? null;
        $status = $request->input('status') ?? -1;
        $api_token = \Auth::guard('admin')->user()->getToken();

        $commentsFirm = CommentsFirm::getComments($dFrom, $dTo, $status)->orderBy('created_at', 'desc')->get();

        return view('Admin.commentsFirm', compact('commentsFirm', 'api_token'));
    }

}
