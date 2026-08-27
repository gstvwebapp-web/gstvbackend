<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Userregister;
use App\Models\News;
use App\Models\Webstory;
use Illuminate\Support\Str;
use Route;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
  	public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials',
            ], 401);
        }

        $user = Auth::user();
        return response()->json($user, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function totalreport(){
		$news = DB::table('news')->whereRaw('NOT FIND_IN_SET(9, news.catID)')->where('news.status', 'Active')->count();
        $videos = DB::table('news')->whereRaw('FIND_IN_SET(9, news.catID)')->count();
        $user = DB::table('userregister')->count();
        $webstory = DB::table('webstory')->where('status','Active')->count();
      
        $data = array();
        $data['news'] = $news;
        $data['videos'] = $videos;
        $data['user'] = $user;
        $data['webstory'] = $webstory;
        
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  	public function newsdaterange(Request $request)
    {
      	$startDate = request()->get('startDate');
        $endDate = request()->get('endDate');
      	if($startDate == '' && $endDate == '') {
            $today = Carbon::today()->format('Y-m-d');
            $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $newsDays = DB::table('news as n1')
                ->select('n1.id', 'n1.title', 'n1.viewer', 'n1.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categoryNames')
                ->leftJoin('users as u1', 'n1.userID', '=', 'u1.id')
                ->whereRaw('NOT FIND_IN_SET(?, n1.catID)', [9])
                ->whereDate('n1.created_at', $today)
                ->where('n1.status', 'Active')
                ->orderBy('n1.viewer', 'desc')->limit(10)->get();
            $newsWeek = DB::table('news as n1')->select('n1.id', 'n1.title', 'n1.viewer', 'n1.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categoryNames')
                ->leftJoin('users as u1', 'n1.userID', '=', 'u1.id')
                ->whereRaw('NOT FIND_IN_SET(?, n1.catID)', [9])
                ->whereBetween('n1.created_at', [$startOfWeek, $endOfWeek])
                ->where('n1.status', 'Active')
                ->orderBy('n1.viewer', 'desc')
                ->limit(10)
                ->get();
            $newsMonth = DB::table('news as n1')->select('n1.id', 'n1.title', 'n1.viewer', 'n1.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categoryNames')
                ->leftJoin('users as u1', 'n1.userID', '=', 'u1.id')
                ->whereRaw('NOT FIND_IN_SET(?, n1.catID)', [9])
                ->whereBetween('n1.created_at', [$startOfMonth, $endOfMonth])
                ->where('n1.status', 'Active')
                ->orderBy('n1.viewer', 'desc')
                ->limit(10)
                ->get();
			$news = [];
        } else {
            $query = DB::table('news')
                ->select('news.id', 'news.title', 'news.viewer', 'news.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categoryNames')
                ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')->whereRaw('NOT FIND_IN_SET(9, news.catID)');
            if ($startDate) {
                $query->whereDate('news.created_at', '>=', date('Y-m-d',strtotime($startDate)));
            }
            if ($endDate) {
                $query->whereDate('news.created_at', '<=', date('Y-m-d',strtotime($endDate)));
            }
            $news = $query->orderBy('news.viewer', 'DESC')->get();
          	$newsDays = [];
            $newsWeek = [];
            $newsMonth = [];
        }
      		$data = array();
      		$data['newsDays'] = $newsDays;
            $data['newsWeek'] = $newsWeek;
            $data['newsMonth'] = $newsMonth;
        	$data['newsdaterange'] = $news;
	    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  
  	public function videosdaterange(Request $request)
    {
      	$startDate = request()->get('startDate');
        $endDate = request()->get('endDate');
      	
      	if($startDate == '' && $endDate == '') {
            $today = Carbon::today()->format('Y-m-d');
            $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $videoDays = DB::table('news as n1')->select('n1.id', 'n1.title', 'n1.viewer', 'n1.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categoryNames')
                ->leftJoin('users as u1', 'n1.userID', '=', 'u1.id')
                ->whereRaw('FIND_IN_SET(?, n1.catID)', [9])
                ->whereDate('n1.created_at', $today)
                ->where('n1.status', 'Active')
                ->orderBy('n1.viewer', 'desc')
                ->limit(10)
                ->get();

            $videoWeek = DB::table('news as n1')->select('n1.id', 'n1.title', 'n1.viewer', 'n1.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categoryNames')
                ->leftJoin('users as u1', 'n1.userID', '=', 'u1.id')
                ->whereRaw('FIND_IN_SET(?, n1.catID)', [9])
                ->whereBetween('n1.created_at', [$startOfWeek, $endOfWeek])
                ->where('n1.status', 'Active')
                ->orderBy('n1.viewer', 'desc')
                ->limit(10)
                ->get();

            $videoMonth = DB::table('news as n1')->select('n1.id', 'n1.title', 'n1.viewer', 'n1.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, n1.catID)) as categoryNames')
                ->leftJoin('users as u1', 'n1.userID', '=', 'u1.id')
                ->whereRaw('FIND_IN_SET(?, n1.catID)', [9])
                ->whereBetween('n1.created_at', [$startOfMonth, $endOfMonth])
                ->where('n1.status', 'Active')
                ->orderBy('n1.viewer', 'desc')
                ->limit(10)
                ->get();
            $news = [];
            
        } else {
            $query = DB::table('news')
                ->select('news.id', 'news.title', 'news.viewer', 'news.created_at', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categoryNames')
                ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')->whereRaw('FIND_IN_SET(9, news.catID)');
            if ($startDate) {
                $query->whereDate('news.created_at', '>=', date('Y-m-d',strtotime($startDate)));
            }
            if ($endDate) {
                $query->whereDate('news.created_at', '<=', date('Y-m-d',strtotime($endDate)));
            }
            $news = $query->orderBy('news.viewer', 'DESC')->get();
          	
          	$videoDays = [];
          	$videoWeek = [];
          	$videoMonth = [];
        }
          $data = array();
          $data['videoDays'] = $videoDays;
          $data['videoWeek'] = $videoWeek;
          $data['videoMonth'] = $videoMonth;
          $data['videosdaterange'] = $news;
      	
	    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  
  	public function webstorydaterange(Request $request)
    {
      	$startDate = request()->get('startDate');
        $endDate = request()->get('endDate');
      	if($startDate == '' && $endDate == '') {
          $today = Carbon::today()->format('Y-m-d');
          $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
          $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
          $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
          $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

          $webstoryDays = DB::table('webstory as c1')->select('c1.id', 'c1.title', 'c1.viewer', 'c1.created_at', 'u1.name as authorName')
              ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
              ->whereDate('c1.created_at', $today)
              ->where('c1.status', 'Active')
              ->orderBy('c1.viewer', 'desc')
              ->limit(10)
              ->get();

          $webstoryWeek = DB::table('webstory as c1')->select('c1.id', 'c1.title', 'c1.viewer', 'c1.created_at', 'u1.name as authorName')
              ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
              ->whereBetween('c1.created_at', [$startOfWeek, $endOfWeek])
              ->where('c1.status', 'Active')
              ->orderBy('c1.viewer', 'desc')
              ->limit(10)
              ->get();

          $webstoryMonth = DB::table('webstory as c1')->select('c1.id', 'c1.title', 'c1.viewer', 'c1.created_at', 'u1.name as authorName')
              ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
              ->whereBetween('c1.created_at', [$startOfMonth, $endOfMonth])
              ->where('c1.status', 'Active')
              ->orderBy('c1.viewer', 'desc')
              ->limit(10)
              ->get();
          $webstory = [];
        } else {
          $query = DB::table('webstory as c1')->select('c1.id', 'c1.title', 'c1.viewer', 'c1.created_at', 'u1.name as authorName')
            ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id');
          if ($startDate) {
              $query->whereDate('c1.created_at', '>=', date('Y-m-d',strtotime($startDate)));
          }
          if ($endDate) {
              $query->whereDate('c1.created_at', '<=', date('Y-m-d',strtotime($endDate)));
          }
          $webstory = $query->orderBy('c1.viewer', 'DESC')->get();
          $webstoryDays = [];
          $webstoryWeek = [];
          $webstoryMonth = [];
        }
      	$data = array();
      	$data['webstoryDays'] = $webstoryDays;
        $data['webstoryWeek'] = $webstoryWeek;
        $data['webstoryMonth'] = $webstoryMonth;
        $data['webstorydaterange'] = $webstory;
      
	    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  
  	public function usersdaterange(Request $request)
    {
      	$startDate = request()->get('startDate');
        $endDate = request()->get('endDate');
      	if($startDate == '' && $endDate == '') {
            $today = Carbon::today()->format('Y-m-d');
            $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
            $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

            $usersDays = DB::table('user_durations as n1')
                ->select('n1.id', 'n1.date', 'n1.total_duration', 'n1.created_at','u1.mobile', DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"))
                ->leftJoin('userregister as u1', 'n1.user_id', '=', 'u1.id')
                ->whereDate('n1.created_at', $today)
                ->orderBy('n1.total_duration', 'desc')->limit(10)->get();
            $usersWeek = DB::table('user_durations as n1')
                ->select('n1.id', 'n1.date', 'n1.total_duration', 'n1.created_at','u1.mobile', DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"))
                ->leftJoin('userregister as u1', 'n1.user_id', '=', 'u1.id')
                ->whereBetween('n1.created_at', [$startOfWeek, $endOfWeek])
                 ->orderBy('n1.total_duration', 'desc')
                ->limit(10)
                ->get();
            $usersMonth = DB::table('user_durations as n1')
                ->select('n1.id', 'n1.date', 'n1.total_duration', 'n1.created_at','u1.mobile', DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"))
                ->leftJoin('userregister as u1', 'n1.user_id', '=', 'u1.id')
                ->whereBetween('n1.created_at', [$startOfMonth, $endOfMonth])
                ->orderBy('n1.total_duration', 'desc')
                ->limit(10)
                ->get();
			$users = [];
        } else {
            $query = DB::table('user_durations as n1')
                ->select('n1.id', 'n1.date', 'n1.total_duration', 'n1.created_at','u1.mobile', DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"))
                ->leftJoin('userregister as u1', 'n1.user_id', '=', 'u1.id');
            if ($startDate) {
                $query->whereDate('n1.created_at', '>=', date('Y-m-d',strtotime($startDate)));
            }
            if ($endDate) {
                $query->whereDate('n1.created_at', '<=', date('Y-m-d',strtotime($endDate)));
            }
            $users = $query->orderBy('n1.total_duration', 'DESC')->get();
          	$usersDays = [];
            $usersWeek = [];
            $usersMonth = [];
        }
      		$data = array();
      		$data['usersDays'] = $usersDays;
            $data['usersWeek'] = $usersWeek;
            $data['usersMonth'] = $usersMonth;
        	$data['usersdaterange'] = $users;
	    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
