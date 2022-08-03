<?php

namespace App\Http\Controllers\Api;

use App\Model\Articles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\Array_;
use Jenssegers\Agent\Agent;

class ArticlesController extends Controller
{
    /**
     * Возвращает n последних статей
     *
     * 
     */
    public function getArticlesLast()
    {
        $agent = new Agent();
        if($agent->isMobile())
            $n=4;
        else
            $n=3;
        $articles = Articles::select('id', 'title', 'text', 'image', 'created_at')
        ->orderBy('updated_at', 'desc')
        ->where('public', 1)
        ->limit($n)
        ->get();
        
        return json_encode( $articles, JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает статьи
     * Входные параметры:
     * inpage - кол-во статей на одной странице
     * page - номер страницы
     * 
     */
    public function getArticles(Request $request)
    {
        $inpage = 10;
        if($request->input('inpage'))
            $inpage = (int)$request->input('inpage');
        $articles = Articles::select('id', 'title', 'text', 'image', 'created_at')
        ->orderBy('updated_at', 'desc')
        ->where('public', 1)
        ->paginnate($inpage);
        
        return json_encode( $articles, JSON_UNESCAPED_UNICODE );
    }
    /**
     * сохраняет изображение для статьи
     */
    public function saveArticleImg(Request $request){
        //dd(get_class_methods($request->image));
        $fileName = $request->image->getClientOriginalName();
        $directory = $_SERVER['DOCUMENT_ROOT']."/img/img/Articles/".$request->articleId."/";
        if(!is_dir($directory))
            mkdir($directory, 0777, true);
        $fileName = $this->getNextFileName($directory).".".$request->image->getClientOriginalExtension();//0.png
        $targetPath = $directory.$fileName;//...dir.../0.png
        move_uploaded_file($request->image->getPathname() ,$targetPath);
        return json_encode($fileName);
        // if ($request->hasFile('p.png')) {
        //     if ($request->file('p.png')->isValid()) {
        //         $extension = $request->imgIcon->extension();
        //         $request->imgIcon->storeAs('/img/articles/icons', $item->id.".".$extension);
        //     }else
        //         abort(500, 'Could not upload image :(');
        // }
    }
    public function saveArticle(Request $request){
        if($status = ($request->json && $request->html)){
            $article = Articles::find($request->id);
            $article->update($request->all());
        }
        return json_encode($status);
    }
    public function getNextFileName($directory){
        $names = scandir($directory);
        $max = -1;
        foreach($names as $i => $name){
            if($i<2)
                continue;
            $n = strval($name);
            $max = $n>$max?(int)$n:$max;
        }
        return $max+1;
    }
}
