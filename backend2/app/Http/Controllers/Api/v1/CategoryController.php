<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\News;
use App\Models\Setting;
use Route;

class CategoryController extends Controller
{
    public function index()
    {
        $category = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status','Active')
                    ->orderBy('c1.catOrder','ASC')
                    ->get();
        
        $data =  array();
        $data['category'] =  $category;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function CategoryNews(Request $request)
    {
        $value = $request->query('page');
        $slug = Route::current()->parameter('slug'); 
        $subslug = Route::current()->parameter('subslug'); 
        $pageNumber = Route::current()->parameter('pageNumber'); 
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', $pageNumber); // Default to the first page
        if($subslug=='undefined') {
            $newcategory = DB::table('categories')->select('id','title as catTitle', 'slug as catSlug', 'metatitle','metadesc')->where('slug',$slug)->where('status','Active')->first();
        } else if($subslug!='undefined') {
            $newcategory = DB::table('categories')->select('id','title as catTitle', 'slug as catSlug', 'metatitle','metadesc')->where('slug',$subslug)->where('status','Active')->first();
        }
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
    public function relatednews()
    {
        $catid = Route::current()->parameter('catid');
        $excludedId = Route::current()->parameter('id');
        $catIDs = explode('-', $catid);
        $query = DB::table('news')->select('id','catID','title','englishTitle','slug','featureImage','videoURL');
        $query->where(function ($query) use ($catIDs) {
        foreach ($catIDs as $catID) {
            $query->orWhereRaw("FIND_IN_SET(?, catID) > 0", [$catID]);
        }
        });
        $relatednews = $query->where('status', 'Active')->where('id', '!=', $excludedId)->orderBy('newsOrder', 'desc')->limit(3)->get();
        $data =  array();
        $data['relatednews'] =  $relatednews;
        return response()->json($data, 200, ['success' => 'Sucess'], JSON_UNESCAPED_UNICODE);
    }
    public function NewsDetail()
    {
        $slug = Route::current()->parameter('slug');
        $newsDetail = DB::table('news')->where('slug',$slug)->where('status','Active')->get();
      	DB::table('news')->where('slug', $slug)->update(['viewer' => DB::raw('COALESCE(viewer, 0) + 1')]);
        $data =  array();
        if ($newsDetail) {
            $nextNews = DB::table('news')
                ->select('slug AS nextnewslug')
                ->whereRaw("FIND_IN_SET(?, catID) > 0", [$newsDetail[0]->catID])
                ->where('id', '>', $newsDetail[0]->id)
                ->orderBy('newsOrder')
                ->first();
            $previousNews = DB::table('news')
                ->select('slug AS prenewslug')
                ->whereRaw("FIND_IN_SET(?, catID) > 0", [$newsDetail[0]->catID])
                ->where('id', '<', $newsDetail[0]->id)
                ->orderByDesc('newsOrder')
                ->first();
            
            $categoryIds = explode(',', $newsDetail[0]->catID);
            $categories = Category::whereIn('id', $categoryIds)
                ->orderByRaw("FIND_IN_SET(id, '{$newsDetail[0]->catID}')") // Order by the order in the category_ids string
                ->get();
        }
        $setting = DB::table('settings')->get();
        $data['categories'] =  $categories;
        $data['newsDetail'] =  $newsDetail;
        $data['nextNews'] =  $nextNews;
        $data['previousNews'] =  $previousNews;
        $data['newsdefaulttxt'] =  $setting[0]->newsdefaulttxt;
        if (!$data['newsDetail']) {
            return response()->json($data,404, ['error' => 'News is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function NewsPreview()
    {
        $slug = Route::current()->parameter('slug');
        $newsDetail = DB::table('news')->where('slug',$slug)->get();
        $data =  array();
        if ($newsDetail) {
            $categoryIds = explode(',', $newsDetail[0]->catID);
            $categories = Category::whereIn('id', $categoryIds)
                ->orderByRaw("FIND_IN_SET(id, '{$newsDetail[0]->catID}')") // Order by the order in the category_ids string
                ->get();
        }
        $setting = DB::table('settings')->get();
        $data['categories'] =  $categories;
        $data['newsDetail'] =  $newsDetail;
        $data['newsdefaulttxt'] =  $setting[0]->newsdefaulttxt;
        if (!$data['newsDetail']) {
            return response()->json($data,404, ['error' => 'News is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function newsViewer($id)
    {
        $news = News::find($id);
        $news->update([
            'viewer' => $news->viewer + 1
           ]);
        $data =  array();
        $data['news'] =  $news;
        //$news->increment('viewer');
        return response()->json($data, 200, ['success' => 'Sucess'], JSON_UNESCAPED_UNICODE);
    }

    public function Newstags(Request $request)
    {
        $value = $request->query('page');
        $tagslug = Route::current()->parameter('slug'); 
        $pageNumber = Route::current()->parameter('pageNumber'); 
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', $pageNumber); // Default to the first page
        $news = DB::table('news')->whereRaw('FIND_IN_SET(?, tags) > 0', [$tagslug])->where('status','Active')->orderBy('newsOrder','DESC')->paginate($perPage, ['*'], 'page', $page);
        $data =  array();
        $data['news'] =  $news;
        if (!$data['news']) {
            return response()->json($data,404, ['error' => 'News tag is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, ['success' => 'Sucess'], JSON_UNESCAPED_UNICODE);
    }
}
