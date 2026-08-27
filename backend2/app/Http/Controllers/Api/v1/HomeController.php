<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\News;
use Route;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $slug = Route::current()->parameter('slug');
        $data =  array();
        /*if($slug=='topnews') {*/
            $topnews = DB::table('news')
                        ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
                        ->leftJoin('categories', function ($join) {
                            $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
                        })
                        ->where('news.status', 'Active')
                        ->where('news.catID', '!=', 9)
              			->where('news.is_home', 1)
                        ->groupBy('news.id')
                        ->orderBy('news.topnewsOrder', 'ASC')
                        ->limit(10)
                        ->get();
            $data['topnews'] =  $topnews;
        /*} else {
        $category = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status','Active')
                    ->where('c1.slug',$slug)
                    ->where('c1.id','!=',9)
                    ->get();
        //for($i=0;$i<count($category);$i++)
        //{
            
            $categoryId = $category[0]->id;
            $newsData = DB::table('news')
                        ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
                        ->join('categories', function ($join) use ($categoryId) {
                            $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
                        })
                        ->whereRaw('FIND_IN_SET(?, news.catID) > 0', [$categoryId])
                        ->where('news.status', 'Active')
              			->where('news.is_home', '!=', 1)
                        ->groupBy('news.id')
                        ->orderBy('news.newsOrder', 'DESC')
                        ->limit(4)
                        ->get();
            $data[$category[0]->slug] = $newsData;
        }
            //$data[$category[$i]->title] = DB::table('news')->whereRaw('FIND_IN_SET(?, catID) > 0', $category[$i]->id)->where('status','Active')->orderBy('newsOrder','DESC')->limit(4)->get();
        //}
       */
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function TopSlider()
    {
        $topnews = DB::table('news')->where('status','Active')->where('catID',9)->orderBy('newsOrder','DESC')->inRandomOrder()->limit(10)->get();
        $data =  array();
        $data['topslider'] =  $topnews;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    
  	public function TopWebSlider()
    {
        $topwebstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->limit(10)->get();
        $data =  array();
        $data['topwebstory'] =  $topwebstory;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
     public function HomeCategory()
    {
        $category = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status','Active')
                    ->where('c1.setHome',1)
                    ->where('c1.id','!=',9)
                    ->orderBy('c1.catOrder', 'ASC')
                    ->get();
        $data =  array();
        $data['homecategory'] =  $category;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function HomeCategorybyId()
    {
        $id = Route::current()->parameter('id');
        $newsData = DB::table('news')
                        ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
                        ->join('categories', function ($join) use ($id) {
                            $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
                        })
                        ->whereRaw('FIND_IN_SET(?, news.catID) > 0', [$id])
                        ->where('news.status', 'Active')
                        ->where('news.is_home', '!=', 1)
                        ->groupBy('news.id')
                        ->orderBy('news.newsOrder', 'DESC')
                        ->limit(4)
                        ->get();
        $data =  array();
        $data['homecategoryNews'] =  $newsData;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }  
}
