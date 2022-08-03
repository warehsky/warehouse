<?php

namespace App\Http\Controllers\Admin;

use App\Model\Articles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class ArticlesController extends Controller
{
    /**
     * Показ одной статьи
     */
    public function article(Request $request, $id){
        if (! \Auth::guard('admin')->user()->can('articles_all')) {
            return redirect()->route('home');
        }
        if($id)
            $article = Articles::find($id);
        else
            $article = null;
        $api_token = \Auth::guard('admin')->user()->getToken();
        return view('Admin.article.edit', compact('article','api_token'));
    }
    /**
     * Показ списка статей
     */
    public function articles(Request $request){
        if (! \Auth::guard('admin')->user()->can('articles_all')) {
            return redirect()->route('home');
        }
        $articles = Articles::orderBy('updated_at', 'desc')->get();
        return view('Admin.article.index', compact('articles'));
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $article = Articles::find($id);
        $data = $request->all();
        $article->update($data);
        
        if ($request->hasFile('preview')) {
            if ($request->file('preview')->isValid()) {
                $extension = $request->preview->extension();
                $request->preview->move($_SERVER['DOCUMENT_ROOT']."/img/img/Articles/".$article->id, 'preview.' . $extension);
                $article->update(['image' => "/img/img/Articles/" . $article->id . '/preview.'.$extension]);
            }else
                abort(500, 'Could not upload image shadow :(');
        }
        return redirect("/admin/articles");
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! \Auth::guard('admin')->user()->can('articles_all')) {
            return redirect()->route('home');
        }
        $data = $request->all();
        $article = Articles::create($data);
        
        if ($request->hasFile('preview')) {
            if ($request->file('preview')->isValid()) {
                $extension = $request->preview->extension();
                $request->preview->move($_SERVER['DOCUMENT_ROOT']."/img/img/Articles/".$article->id, 'preview.' . $extension);
                $article->update(['image' => "/img/img/Articles/" . $article->id . '/preview.'.$extension]);
            }else
                abort(500, 'Could not upload image shadow :(');
        }
        
        return redirect("/admin/article/" . $article->id);
    }


}
