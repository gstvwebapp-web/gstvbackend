<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\News;
use App\Models\Setting;
use App\Models\Astrology;
use App\Models\Webstory;
use App\Models\Adsimpression;
use Illuminate\Support\Facades\Log;
use Route;


class HomeWebController extends Controller
{
    public function index(Request $request)
    {
        
      	$data =  array();
      	
        /*$topvideos = DB::table('news')->where('status','Active')->where('catID',9)->orderBy('newsOrder','DESC')->inRandomOrder()->limit(10)->get();
        
        $topnews = DB::table('news')
          			->select('news.*', DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(categories.slug ORDER BY categories.id), ",", 2) as category_slugs'))
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
        
        $topwebstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->limit(10)->get();
        
        
        
        $data['topvideo'] =  $topvideos;
        $data['topnews'] =  $topnews;
        $data['topwebstory'] =  $topwebstory;*/
        
        $category = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status','Active')
                    ->where('c1.setHome',1)
                    ->where('c1.id','!=',9)
                    ->orderBy('c1.catOrder', 'ASC')
                    ->get();
        foreach($category as $catnews)
        {
            $id = $catnews->id;
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
            $data['homecategory'][] = [
                'categorytitle' => $catnews->title,
              	'catid' => $id,
                'catslug' => $catnews->slug,
                'newsData' => $newsData                
            ];
            //$data['homecategory'][$catnews->title] =  $newsData;
            //$data['homecategory'][$id] =  $id;
            //$data['homecategory'][$catnews->slug] =  $catnews->slug;
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  
  	public function topvideo()
    {
      $data =  array();
      	
      $topvideos = DB::table('news')->where('status','Active')->where('catID',9)->orderBy('newsOrder','DESC')->inRandomOrder()->limit(10)->get();
      $data['topvideo'] =  $topvideos;
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
	public function topnews()
    {
      $data =  array();
      	
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
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  	public function topwebstory()
    {
      $data =  array();      	
      $topwebstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->limit(10)->get();
      $data['topwebstory'] =  $topwebstory;
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function CategorySetting()
    {
      	 $category = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status','Active')
                    ->orderBy('c1.catOrder','ASC')
                    ->get();
        $setting = DB::table('settings')->get();
      	$poll = DB::table('polls')->where('status','Active')->get();
        $data =  array();
        $data['category'] =  $category;
        $data['setting'] =  $setting;
      	$data['poll'] =  $poll;
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
       // $news = DB::table('news')->whereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id])->where('status','Active')->orderBy('created_at', 'DESC')->orderBy('newsOrder','DESC')->paginate($perPage, ['*'], 'page', $page);
        
      	$news = DB::table('news')
                ->whereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id])
                ->where('status', 'Active')
                ->orderByDesc('created_at')
                ->orderByDesc('newsOrder')
                ->paginate($perPage, ['*'], 'page', $page);
      	
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

    public function NewsDetails()
    {
        $slug = Route::current()->parameter('slug');
      	
      
        $newsDetail = DB::table('news as n')
                      ->select('n.*', DB::raw('(SELECT slug FROM news WHERE id < n.id ORDER BY id DESC LIMIT 1) AS previousslug'), DB::raw('(SELECT slug FROM news WHERE id > n.id ORDER BY id ASC LIMIT 1) AS nextslug'))
                      ->where('n.slug', $slug)
          			  ->where('n.status', 'Active')
                      ->first();
      
       	DB::table('news')->where('slug', $slug)->update(['viewer' => DB::raw('COALESCE(viewer, 0) + 1')]);
        
       $data =  array();
       if ($newsDetail) {
            $categoryIds = explode(',', $newsDetail->catID);
            $categories = Category::whereIn('id', $categoryIds)
                ->orderByRaw("FIND_IN_SET(id, '{$newsDetail->catID}')") 
                ->get();
            
            $catid = $newsDetail->catID;
            $excludedId = $newsDetail->id;
            $catIDs = explode(',', $catid);
            $query = DB::table('news')->select('id','catID','title','englishTitle','slug','featureImage','videoURL');
            $query->where(function ($query) use ($catIDs) {
            foreach ($catIDs as $catID) {
                $query->orWhereRaw("FIND_IN_SET(?, catID) > 0", [$catID]);
            }
            });
            $relatednews = $query->where('status', 'Active')->where('id', '!=', $excludedId)->orderBy('newsOrder', 'desc')->limit(3)->get();
            
        }
      	
        $setting = DB::table('settings')->get();
        $data['newsDetail'] =  $newsDetail;
        $data['categories'] =  $categories;        
        $data['relatednews'] =  $relatednews;
        $data['newsdefaulttxt'] =  $setting[0]->newsdefaulttxt;
      	
      
        if (!$data['newsDetail']) {
            return response()->json($data,404, ['error' => 'News is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
	
    public function webstory(Request $request)
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
            	DB::table('webstory')->where('slug', $pageNumber)->update(['viewer' => DB::raw('COALESCE(viewer, 0) + 1')]);
            }
        } else {
            $webstory = DB::table('webstory')->where('slug',$pageNumber)->where('status','Active')->get(); 
          	$webstorymore = DB::table('webstory')->where('slug','!=',$pageNumber)->where('status','Active')->orderBy('id', 'DESC')->limit(4)->get();
        	DB::table('webstory')->where('slug', $pageNumber)->update(['viewer' => DB::raw('COALESCE(viewer, 0) + 1')]);
        }
        $data =  array();
        $data['webstory'] =  $webstory;
      	$data['webstorymore'] =  $webstorymore;
      	if (!$data['webstory']) {
            return response()->json($data,404, ['error' => 'Web story is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200);
    }

    public function webstoryDetail(Request $request)
    {
        $slug = Route::current()->parameter('slug');
        $webstory = DB::table('webstory')->where('slug',$slug)->where('status','Active')->get(); 
        $webstorymore = DB::table('webstory')->where('slug','!=',$slug)->where('status','Active')->orderBy('id', 'DESC')->limit(4)->get();
         
      	$data =  array();
        $data['webstory'] =  $webstory;
        $data['webstorymore'] =  $webstorymore;
      	if (!$data['webstory']) {
            return response()->json($data,404, ['error' => 'Web story is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200);
    }
  
    public function AstrologyData(Request $request)
    {
        $submitedata = $request->all();
        $astrology = new Astrology([
            'fullname' => $submitedata['formData']['fullname'],
            'email'=> $submitedata['formData']['email'],
            'phone'=> $submitedata['formData']['phone'],
            'birth_date'=> $submitedata['formData']['birth_date'],
            'birth_time'=> $submitedata['formData']['birth_time'],
            'birth_place'=> $submitedata['formData']['birth_place']
        ]);
        
        $astrology->save();
      
      	$htmlContent = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Get a Free Kundli</title>
            </head>
            <body>
                <p>Hello, '.$submitedata['formData']['fullname'].',</p>
                <p>&nbsp;</p>
                <p>Name: '.$submitedata['formData']['fullname'].'</p>
                <p>Email: '.$submitedata['formData']['email'].'</p>
                <p>Phone: '.$submitedata['formData']['phone'].'</p>
                <p>Birth Date: '.$submitedata['formData']['birth_date'].'</p>
                <p>Birth Time: '.$submitedata['formData']['birth_time'].'</p>
                <p>Birth Place: '.$submitedata['formData']['birth_place'].'</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>Regards,</p>
                <p>'.$submitedata['formData']['fullname'].'</p>
            </body>
            </html>';
		
      
      	$headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <'.$submitedata['formData']['email'].'>' . "\r\n";
        $to = 'todinesh13@gmail.com';
        $subject = 'For Free Kundli';    
        $sent = mail($to,$subject,$htmlContent,$headers);
      
      
        
       
        $data =  array();
        $data['astrology'] =  $submitedata['formData']['fullname'];
        if (!$data['astrology']) {
            return response()->json($data,404, ['error' => 'Astrology data is not stored'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function adsimpression(Request $request)
    {
        $impressions = $request->input('impressions');
        $ipAddress = $request->ip();
        $currentDate = now()->toDateString();

        foreach ($impressions as $impression) {
            $imgValue = $impression['imgvalue'];
            $click = $impression['click'];
            $pageUrl = $impression['currentPageUrl'];

            // Check if a record with the same image, pageurl, ip, and date already exists
            $adsim = Adsimpression::where('image', $imgValue)
                // ->where('pageurl', $pageUrl)
                // ->where('ip', $ipAddress)
                ->whereDate('created_at', $currentDate) // Check if the created_at date matches the current date
                ->first();

            if ($adsim) {
                // If a matching record exists, increment the view count
                $adsim->increment('view');
                if ($click == 1) {
                    // If click is 1, increment the click count
                    $adsim->increment('click');
                }
                $adsim->save();
                // Log the impression update
                Log::channel('daily_text')->info("Updated impression: Image - {$imgValue}, Page URL - {$pageUrl}, Views - {$adsim->view}, Clicks - {$adsim->click}, Date - {$currentDate}, ip - {$ipAddress}");
            } else {
                // If no matching record exists, create a new entry
                $adsim = new Adsimpression([
                    'image' => $imgValue,
                    'view' => 1, // Initialize view count to 1 for a new record
                    'click' => $click,
                    'pageurl' => $pageUrl,
                    // 'ip' => $ipAddress,
                    'created_at' => $currentDate // Set the created_at date
                ]);

                $adsim->save();
                // Log the new impression
                Log::channel('daily_text')->info("Created new impression: Image - {$imgValue}, Page URL - {$pageUrl}, Views - 1, Clicks - {$click}, Date - {$currentDate}, ip - {$ipAddress}");
            }
        }

        return response()->json(['message' => 'Impressions logged successfully']);
    }
    public function categorysatrang()
    {
        $categorychildQuery = DB::table('categories as c1')
                ->select('c1.title as category_name', 'c1.*', 'u1.name as authorName', 'c2.title as parent_category_name')
                ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
                ->where('c1.parentID', 63)
          		->where('c1.status','Active')
                ->orderBy('c1.parentID', 'ASC')
                ->orderBy('c1.catOrder', 'ASC')->get();
        $data['categorychildQuery']=$categorychildQuery;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function satarangCat()
    { 
      $data =  array();
      $gstvsatrang = DB::table('categories')->where('parentID',63)->where('status','Active')->orderBy('catOrder','ASC')->limit(10)->get();
      $data['gstvsatrang'] =  $gstvsatrang;
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    
}
