<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\News;
use Route;

class FeedController extends Controller
{
    public function index()
    {
        $feednews = DB::table('news as c1')
                    ->where('c1.status','Active')
                    ->orderBy('c1.id','DESC')
                    ->get();
        
        $data =  array();
        $data['feednews'] =  $feednews;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function CategoryNews(Request $request)
    {
        $value = $request->query('page');
        $slug = Route::current()->parameter('slug'); 
        $pageNumber = Route::current()->parameter('pageNumber'); 
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', $pageNumber); // Default to the first page
        $newcategory = DB::table('categories')->select('id','title as catTitle', 'slug as catSlug', 'metatitle','metadesc')->where('slug',$slug)->where('status','Active')->first();
        $ids = explode(',', $newcategory->id);
        $news = DB::table('news')->whereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id])->where('status','Active')->orderBy('id','DESC')->paginate($perPage, ['*'], 'page', $page);
        $data =  array();
        $data['news'] =  $news;
        $data['catTitle'] =  $newcategory->catTitle;
        $data['catSlug'] =  $newcategory->catSlug;
        $data['catMetatitle'] =  $newcategory->metatitle;
        $data['catMetadesc'] =  $newcategory->metadesc;
        if (!$data['news']) {
            return response()->json($data,404, ['error' => 'News is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, ['success' => 'Sucess'], JSON_UNESCAPED_UNICODE);
    }
    
}
