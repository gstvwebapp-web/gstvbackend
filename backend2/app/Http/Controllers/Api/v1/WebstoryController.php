<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Webstory;
use Route;

class WebstoryController extends Controller
{
    public function index(Request $request)
    {
        $value = $request->query('page');
        $pageNumber = Route::current()->parameter('pageNumber'); 
        if (is_numeric($pageNumber)) {
            $pageNumber = intval($pageNumber);
            if (is_int($pageNumber)) {
                $perPage = $request->input('per_page', 10); // Default to 10 items per page
                $page = $request->input('page', $pageNumber); 
                $webstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->paginate($perPage, ['*'], 'page', $page);
            	$webstorymore = '';
            } else {
                $webstory = DB::table('webstory')->where('slug',$pageNumber)->where('status','Active')->get(); 
              	$webstorymore = DB::table('webstory')->where('slug','!=',$pageNumber)->where('status','Active')->orderBy('id', 'DESC')->limit(4)->get();
            }
        } else {
            $webstory = DB::table('webstory')->where('slug',$pageNumber)->where('status','Active')->get(); 
          	$webstorymore = DB::table('webstory')->where('slug','!=',$pageNumber)->where('status','Active')->orderBy('id', 'DESC')->limit(4)->get();
        }
        $data =  array();
        $data['webstory'] =  $webstory;
      	$data['webstorymore'] =  $webstorymore;
        if (!$data['webstory']) {
            return response()->json($data,404, ['error' => 'Web story is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200);
    }
  	public function webPreview()
    {
        $slug = Route::current()->parameter('slug');
        $webstory = DB::table('webstory')->where('slug',$slug)->get(); 
        $webstorymore = DB::table('webstory')->where('slug','!=',$slug)->where('status','Active')->orderBy('id', 'DESC')->limit(4)->get();
        $data =  array();
        $data['webstory'] =  $webstory;
        $data['webstorymore'] =  $webstorymore;
        if (!$data['webstory']) {
            return response()->json($data,404, ['error' => 'Web Story is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
