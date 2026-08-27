<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Page;
use Route;

class PageController extends Controller
{
    public function index()
    {
        $pages = DB::table('pages as p1')
                    ->select('p1.title as page_name', 'p1.*', 'p2.title as parent_page_name')
                    ->leftJoin('pages as p2', 'p1.parentID', '=', 'p2.id')
                    ->where('p1.status','Active')
                    ->get();
        $data =  array();
        $data['pages'] =  $pages;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function show()
    {
        $slug = Route::current()->parameter('slug');
        $pageDetail = DB::table('pages')->where('slug',$slug)->where('status','Active')->get();
        $data =  array();
        $data['pageDetail'] =  $pageDetail;
        if (!$pageDetail) {
            return response()->json($data,404, ['error' => 'Page is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
