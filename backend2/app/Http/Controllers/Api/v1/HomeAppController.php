<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\News;
use App\Models\Setting;
use App\Models\Newslike;
use App\Models\Videolike;
use App\Models\Bookmark;
use App\Models\Feedback;
use App\Models\Poll;
use App\Models\Vote;
use App\Models\Iframe;
use App\Models\Votegame;
use App\Models\Gameresult;
use App\Models\VisitorSessions;
use App\Models\Userregister;
use App\Models\Usercategory;
use App\Models\Usercity;
use App\Models\UserDuration;
use App\Models\Userplan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Route;
use Carbon\Carbon;

class HomeAppController extends Controller
{
    public function index(Request $request)
    {
        $data =  array();
        
        //$topvideos = DB::table('news')->where('status','Active')->where('catID',9)->orderBy('newsOrder','DESC')->inRandomOrder()->limit(10)->get();
        
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
        
       // $topwebstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->limit(10)->get();
        
        
        
        //$data['topvideo'] =  $topvideos;
        $data['topnews'] =  $topnews;
        //$data['topwebstory'] =  $topwebstory;
        
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
            $data['homecategory'][$catnews->title] =  $newsData;
        }
       
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  
  	
  	public function topvideos()
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
                    ->where('news.topnewsOrder','!=', 0)
                    ->groupBy('news.id')
                    ->orderBy('news.topnewsOrder', 'ASC')
                    ->limit(10)
                    ->get()
          			->map(function ($news) {
                        $news->description = Str::limit($news->description, 120);
                        return $news;
                    });
        	
        $data['topnews'] =  $topnews;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function topwebstories()
    {
        $data =  array();  
      	
          $topwebstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->limit(10)->get();
          
      
        $data['topwebstory'] =  $topwebstory;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function tophomecategory()
    {
        $data =  array();    
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
                        ->get()
              			->map(function ($news) {
                          $news->description = Str::limit($news->description, 120);
                          return $news;
                        });
            $data['homecategory'][$catnews->title] =  $newsData;
        }
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
        $data =  array();
        $data['category'] =  $category;
        $data['setting'] =  $setting;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);

    }
    public function CategoryNews(Request $request)
    {
        $value = $request->query('page');
        $slug = $request->input('slug');
        $device_id = $request->input('device_id');
        $subslug = $request->input('subslug');
        $pageNumber = $request->input('pageNumber');
        $user_id = $request->input('user_id');
        
        $perPage = $slug === 'videos' ? $request->input('per_page', 4) : $request->input('per_page', 10); // Default to 10 items per page
        $page = $request->input('page', $pageNumber); // Default to the first page
        
        $newcategory = DB::table('categories')
            ->select('id', 'title as catTitle', 'slug as catSlug', 'metatitle', 'metadesc')
            ->where('slug', $subslug === 'undefined' ? $slug : $subslug)
            ->where('status', 'Active')
            ->first();
        
        if (!$newcategory) {
            return response()->json(['error' => 'Category not found'], 404, JSON_UNESCAPED_UNICODE);
        }
    
        $ids = explode(',', $newcategory->id);
        
        if (empty($device_id)) {
            $news = DB::table('news')
                ->whereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id])
                ->where('status', 'Active')
                ->orderBy('newsOrder', 'DESC')
                ->paginate($perPage, ['*'], 'page', $page);
        } else {
            $subquery = DB::table('videolike')
                ->select(
                    'video_id',
                    DB::raw('COUNT(CASE WHEN status = "1" THEN 1 END) as like_count'),
                    DB::raw('COUNT(CASE WHEN status = "0" THEN 1 END) as dislike_count'),
                    DB::raw('COUNT(CASE WHEN status = "1" AND ipAddress = "' . $device_id . '" THEN 1 END) as liked_by_user'),
                    DB::raw('COUNT(CASE WHEN status = "0" AND ipAddress = "' . $device_id . '" THEN 1 END) as disliked_by_user')
                )
                ->groupBy('video_id');
            
            $news = DB::table('news')
                ->leftJoinSub($subquery, 'vl', function ($join) {
                    $join->on('news.id', '=', 'vl.video_id');
                })
                ->whereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id])
                ->where('status', 'Active')
                ->select('news.*', 'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user')
                ->orderBy('newsOrder', 'DESC')
                ->paginate($perPage, ['*'], 'page', $page);
        }
    
        $news->getCollection()->transform(function ($item) use ($user_id, $device_id) {
            $item->description = Str::limit(strip_tags($item->description), 120);
            $item->bookmark = '';
    
            if (!empty($user_id)) {
                $newsDetailbookmark = DB::table('bookmark')
                    ->where('user_id', $user_id)
                    ->where('news_id', $item->id)
                    ->first();
    
                if ($newsDetailbookmark) {
                    $item->bookmark = $newsDetailbookmark->status;
                }
            }
    
            return $item;
        });
    
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
     
    public function NewsDetails(Request $request)
    {
        $slug = $request->input('slug');
        $user_id = $request->input('user_id');
        $ipAddress = $request->input('device_id');
        // $newsDetail = DB::table('news as n')
        //               ->select('n.*', DB::raw('(SELECT slug FROM news WHERE id < n.id ORDER BY id DESC LIMIT 1) AS previousslug'), DB::raw('(SELECT slug FROM news WHERE id > n.id ORDER BY id ASC LIMIT 1) AS nextslug'))
        //               ->where('n.slug', $slug)
        //               ->first();
        $newsDetail = DB::table('news as n')
            ->select(
                'n.*',
                DB::raw('(SELECT slug FROM news WHERE id < n.id ORDER BY id DESC LIMIT 1) AS previousslug'),
                DB::raw('(SELECT slug FROM news WHERE id > n.id ORDER BY id ASC LIMIT 1) AS nextslug'),
                DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "1") as like_count'),
                DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "0") as dislike_count'),
                DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "1" AND newslike.ipAddress = "' . $ipAddress . '") as liked_by_user'),
                DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "0" AND newslike.ipAddress = "' . $ipAddress . '") as disliked_by_user')
            )
            ->where('n.slug', $slug)
            ->first();

                  
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
                $description = $newsDetail->description;
                $modifiedHtml = preg_replace_callback(
                '/(https:\/\/twitter\.com\/([a-zA-Z0-9_]+)\/status\/([0-9]+))|(https:\/\/fb\.watch\/([a-zA-Z0-9_]+))|(https:\/\/www\.instagram\.com\/p\/([a-zA-Z0-9_-]+)\/?)|(https:\/\/www\.instagram\.com\/reel\/([a-zA-Z0-9_-]+)\/?)|(https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]+))|(https:\/\/[^ \n]+\.pdf)/',
                function ($matches) {
                    $twitterUrl = $matches[1] ?? null;
                    $facebookWatchUrl = $matches[5] ?? null;
                    $instagramPostUrl = $matches[7] ?? null;
                    $instagramReelShortcode = $matches[9] ?? null;
                    $youtubeUrl = $matches[11] ?? null;
                    $youtubeVideoId = $matches[13] ?? null;
                    $pdfUrl = $matches[15] ?? null;

                    if ($twitterUrl) {
                        return '<blockquote class="twitter-tweet"><a data-height="100%" href="' . $twitterUrl . '">' . $twitterUrl . '</a></blockquote>';
                    } elseif ($facebookWatchUrl) {
                        return '<iframe style="width: 100%; height: 500px; border: none; overflow: hidden;" src="https://www.facebook.com/plugins/post.php?href=' . $facebookWatchUrl . '" frameborder="0" scrolling="no"></iframe>';
                    } elseif ($instagramPostUrl) {
                        return '<blockquote class="instagram-media" data-instgrm-permalink="' . $instagramPostUrl . '" data-instgrm-version="13"></blockquote>';
                    } elseif ($instagramReelShortcode) {
                        $instagramReelEmbedUrl = 'https://www.instagram.com/p/' . $instagramReelShortcode . '/embed';
                        return '<iframe src="' . $instagramReelEmbedUrl . '" width="400" height="500" frameborder="0" scrolling="no" allowtransparency="true"></iframe>';
                    } elseif ($youtubeVideoId) {
                        return '<iframe width="100%" height="400" src="https://www.youtube.com/embed/' . $youtubeVideoId . '" frameborder="0" allowfullscreen></iframe>';
                    } elseif ($youtubeUrl) {
                        $videoIdMatch = preg_match('/(?:\/|v=)([a-zA-Z0-9_-]+)/', $youtubeUrl, $videoIdMatches);
                        $videoId = $videoIdMatches[1] ?? '';
                        return '<iframe width="100%" height="400" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe>';
                    } elseif ($pdfUrl) {
                        return '<iframe src="' . $pdfUrl . '#toolbar=0&navpanes=0" width="100%" height="600px"></iframe>';
                    }

                    return $matches[0]; // If neither pattern matches, return the original match
                },
                $description
            );   
        }
        $setting = DB::table('settings')->get();

        $newsDetail->bookmark = '';
        if ($user_id != "") {
            $newsDetailbookmark = DB::table('bookmark')->where('user_id',$user_id)->where('news_id',$newsDetail->id)->first();
            if ($newsDetailbookmark) {
                 $newsDetail->bookmark = $newsDetailbookmark->status;
            } else {
                 $newsDetail->bookmark = '';
            }
        }

        $data['newsDetail'] =  $newsDetail;
      	$data['newsdescription'] =  $modifiedHtml;
        $data['categories'] =  $categories;        
        $data['relatednews'] =  $relatednews;
        $data['newsdefaulttxt'] =  $setting[0]->newsdefaultapptxt;
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
                $perPage = $request->input('per_page', 4); // Default to 10 items per page
                $page = $request->input('page', $pageNumber); 
                $webstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->paginate($perPage, ['*'], 'page', $page);
            } 
        }
        $data =  array();
        $data['webstory'] =  $webstory;
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
       
        $data =  array();
        $data['astrology'] =  $submitedata['formData']['fullname'];
        if (!$data['astrology']) {
            return response()->json($data,404, ['error' => 'Astrology data is not stored'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function citylist()
    {
        $city = DB::table('categories as c1')
                    ->select('id','c1.title as cityName')
                    ->where('c1.parentID',1)
                    ->where('c1.status','Active')
                    ->orderBy('c1.catOrder','ASC')
                    ->get();
        $data =  array();
        $data['city'] =  $city;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);

    }
    public function breakingnews(Request $request)
    {
        /*$data['berakingnews']= DB::table('news')
            ->select('news.title','news.id','news.slug')
            ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categoryNames')
            ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')      
            ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
            ->where('news.status', 'Active')
            ->orderBy('newsOrder', 'DESC')
            ->first();*/
      $data = array();
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function newslike(Request $request)
    {
        $news_id = $request->input('news_id');
        $ipAddress = $request->input('device_id');
        $status = $request->input('status');
        // Check if the news is already liked or disliked by this IP address
        $newslike  = DB::table('newslike')->where('ipAddress',$ipAddress)->where('news_id',$news_id)->first();
        // If the news is already liked by this IP address, update the status to '1' (liked)
        if ($newslike ) {
            DB::table('newslike')->where('news_id', $news_id)->where('ipAddress', $ipAddress)->update(['status'=>$status]);
        } else {
            // Otherwise, insert a new record indicating the news is liked by this IP address
            $datanews = [
                'ipAddress' => $ipAddress,
                'news_id' => $news_id,
                'status'=>$status
            ];
            $Newslike = new Newslike($datanews);
            $Newslike->save();
        }
        // Get the count of likes for the news
        $likeCount = DB::table('newslike')->where('news_id', $news_id)->where('status', '1')->count();
        $totlikeCount = $likeCount;
        if ($likeCount >= 1000000) {
            $totlikeCount =  ($likeCount / 1000000).toFixed($num % 1000000 !== 0 ? 1 : 0) + 'M';
        } else if ($likeCount >= 1000) {
            $totlikeCount =  ($likeCount / 1000).toFixed($num % 1000 !== 0 ? 1 : 0) + 'k';
        }
        $data['newslike'] = $totlikeCount;
        // Get the count of dislikes for the news
        $dislikeCount = DB::table('newslike')->where('news_id', $news_id)->where('status', '0')->count();
        $totdislikeCount = $dislikeCount;
        if ($dislikeCount >= 1000000) {
            $totdislikeCount =  ($dislikeCount / 1000000).toFixed($num % 1000000 !== 0 ? 1 : 0) + 'M';
        } else if ($dislikeCount >= 1000) {
            $totdislikeCount =  ($dislikeCount / 1000).toFixed($num % 1000 !== 0 ? 1 : 0) + 'k';
        }
        $data['newsdislike'] = $totdislikeCount;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function videolike(Request $request)
    {
        $video_id = $request->input('video_id');
        $ipAddress = $request->input('device_id');
        $status = $request->input('status');
        // Check if the news is already liked or disliked by this IP address
        $videolike  = DB::table('videolike')->where('ipAddress',$ipAddress)->where('video_id',$video_id)->first();
        // If the news is already liked by this IP address, update the status to '1' (liked)
        if ($videolike ) {
            DB::table('videolike')->where('video_id', $video_id)->where('ipAddress', $ipAddress)->update(['status'=>$status]);
        } else {
            // Otherwise, insert a new record indicating the news is liked by this IP address
            $datavideo = [
                'ipAddress' => $ipAddress,
                'video_id' => $video_id,
                'status'=>$status
            ];
            $Videolike = new Videolike($datavideo);
            $Videolike->save();
        }
        // Get the count of likes for the news
        $likeCount = DB::table('videolike')->where('video_id', $video_id)->where('status', '1')->count();
        $totlikeCount = $likeCount;
        if ($likeCount >= 1000000) {
            $totlikeCount =  ($likeCount / 1000000).toFixed($num % 1000000 !== 0 ? 1 : 0) + 'M';
        } else if ($likeCount >= 1000) {
            $totlikeCount =  ($likeCount / 1000).toFixed($num % 1000 !== 0 ? 1 : 0) + 'k';
        }
        $data['videolike'] = $totlikeCount;
        // Get the count of dislikes for the news
        $dislikeCount = DB::table('videolike')->where('video_id', $video_id)->where('status', '0')->count();
        $totdislikeCount = $dislikeCount;
        if ($dislikeCount >= 1000000) {
            $totdislikeCount =  ($dislikeCount / 1000000).toFixed($num % 1000000 !== 0 ? 1 : 0) + 'M';
        } else if ($dislikeCount >= 1000) {
            $totdislikeCount =  ($dislikeCount / 1000).toFixed($num % 1000 !== 0 ? 1 : 0) + 'k';
        }
        $data['videodislike'] = $totdislikeCount;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function newsbookmark(Request $request)
    {
        $news_id = $request->input('news_id');
        $user_id = $request->input('user_id');
        $status = $request->input('status');
        $bookmark_type = $request->input('bookmark_type');
        $newsDetail = DB::table('bookmark')->where('user_id',$user_id)->where('news_id',$news_id)->first();
        if (!$newsDetail) {
            $dataNews = [
                'user_id' => $user_id,
                'news_id' => $news_id,
                'status'=>$status,
                'bookmark_type'=>$bookmark_type
            ];
            $Bookmark = new Bookmark($dataNews);
            $Bookmark->save();
        } else {
            DB::table('bookmark')->where('news_id', $news_id)->where('user_id', $user_id)->update(['status'=>$status]);
        }
        $newsDetail = DB::table('bookmark')->where('user_id',$user_id)->where('news_id',$news_id)->first();
        $data['status']=$newsDetail->status;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function bookmarklist(Request $request)
    {
        $user_id = $request->input('user_id');
        // $data['bookmarklist'] = DB::table('bookmark')->select('news_id')->where('user_id',$user_id)->where('status',1)->get();
        $bookmarkList = DB::table('bookmark')
            ->join('news', 'bookmark.news_id', '=', 'news.id')
            ->leftJoin('categories', 'categories.id', '=', 'news.catID') // Assuming news.catID contains a single category ID
            ->select(
                'bookmark.news_id',
                'bookmark.bookmark_type',
                'news.title as newsTitle',
                'news.slug as newsSlug',
                'news.description',
         		'news.featureImage',
                'news.videoURL',
                'categories.title as catTitle',
                'categories.slug as catSlug',
            )
            ->where('bookmark.user_id', $user_id)
            ->where('bookmark.status','1')
            ->orderBy('bookmark.created_at', 'DESC')
            ->get(); 
      	
      	    // Modify the featureImage based on bookmark_type
        $bookmarkList = $bookmarkList->map(function ($item) {
            if ($item->bookmark_type === 'news') {
                $fileExtension = strtolower(pathinfo($item->featureImage, PATHINFO_EXTENSION));
                $item->imageURL = preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $item->featureImage);
            } elseif ($item->bookmark_type === 'video') {
                $fileExtension = strtolower(pathinfo($item->videoURL, PATHINFO_EXTENSION));
                $item->imageURL = preg_replace("/\.$fileExtension$/i", "_video.gif", $item->videoURL);
            }
            return $item;
        });

        $data['bookmarklist'] = $bookmarkList;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  	public function removebookmark(Request $request)
    {
        $user_id = $request->input('user_id');
        $news_id = $request->input('news_id');

        // Check if the bookmark exists
        $bookmark = DB::table('bookmark')
            ->where('user_id', $user_id)
            ->where('news_id', $news_id)
            ->first();

        if ($bookmark) {
            // Delete the bookmark
            DB::table('bookmark')
                ->where('user_id', $user_id)
                ->where('news_id', $news_id)
                ->delete();
			$data['msg'] = 'Bookmark removed successfully';
        	return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $data['msg'] = 'Bookmark removed successfully';
            return response()->json($data, 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function feedback(Request $request)
    {
        
        $submitedata = $request->all();
        $feedback = new Feedback([
            'name' => $request->input('name'),
            'email'=> $request->input('email'),
            'phone'=> $request->input('phone'),
            'comment'=> $request->input('comment')
        ]);
        
        $feedback->save();
       
        $data =  array();
        $data['feedback'] =  "Thank you for sharing your feedback";
        if (!$data['feedback']) {
            return response()->json($data,404, ['error' => 'Feedback is not stored'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } 
    public function poll(Request $request)
    {
        
        $poll = DB::table('polls')->where('status','Active')->get();
        $data =  array();
        $data['poll'] =  $poll;
        if (!$data['poll']) {
            return response()->json($data,404, ['error' => 'Poll is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } 
    public function submitpoll(Request $request)
    {
        $submitedata = $request->all();
        $ipAddress = $request->ip();
        $existingVote = Vote::where('IPaddress', $ipAddress)
                            ->where('pollID',  $request->input('pollID'))
                            ->first();

        if ($existingVote) {
            return response()->json(['message' => 'You have already voted for this poll.']);
        }
        $vote = new Vote([
            'pollID' =>  $request->input('pollID'),
            'selectedAnswer'=>  $request->input('selectedAnswer'),
            'IPaddress'=> $ipAddress
        ]);
        
        $vote->save();
        
        return response()->json(['message' => 'Vote submitted successfully']);
    } 
    public function pollresult(Request $request)
    {
        $pollresults = DB::table('polls')
                ->select('question', 'answerOption', 'id')
                ->orderByDesc('id')
                ->limit(10)
                ->get();

        $voteresults = array();
        if (!empty($pollresults)) {
            foreach ($pollresults as $val) {
                $totalVotes = DB::table('votes')
                    ->where('pollID', $val->id)
                    ->count();

                $results = DB::table('votes')
                    ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                    ->where('pollID', $val->id)
                    ->groupBy('selectedAnswer')
                    ->get();

                foreach ($results as $result) {
                    $result->percentage = $totalVotes > 0 ? round(($result->answerCount / $totalVotes) * 100, 2) : 0;
                }

                $voteresults[$val->id] = $results;
            }
        }
        $data['pollresults'] =  $pollresults;
        $data['voteresults'] =  $voteresults;
        $data['msgvote'] =  'Vote submitted successfully.';
        return response()->json($data, 200, ['message' => 'Vote submitted successfully.'], JSON_UNESCAPED_UNICODE);
    }  
    public function Iframe(Request $request)
    {
        
        $iframe = DB::table('iframe')->select('title','url')->where('status','Active')->get();
        $data =  array();
        $data['iframe'] =  $iframe;
        if (!$data['iframe']) {
            return response()->json($data,404, ['error' => 'Iframe is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } 
    public function addpoints(Request $request)
    {
        $ipAddress = $request->input('device_id');
        $user_id =  $request->input('user_id');
        if (!$user_id) {
            return response()->json(['message' => 'You are not login.']);
        }
      	$start = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('start_time'));
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('end_time'));

        $differenceInSeconds = $end->diffInSeconds($start);
        $differenceInSeconds = abs($end->diffInSeconds($start));
      
        $visitors = new VisitorSessions([
            'user_id' =>  $user_id,
            'start_time'=>  $request->input('start_time'),
            'end_time'=>  $request->input('end_time'),
          	'duration' => $differenceInSeconds,
            'session_id'=> $ipAddress
        ]);
        
        $visitors->save();
        
        return response()->json(['message' => 'Points submitted successfully']);
    } 
    public function userpoints(Request $request)
    {
        $user_id =  $request->input('user_id');
      	$settingdata = DB::table('settings')->select('spendtime','point','pointtxt','pointtitle')->get();
        if (!$user_id) {
            return response()->json($data,404, ['error' => 'User is not found'], JSON_UNESCAPED_UNICODE);
        }
        /*$data['userpoints'] = DB::table('user_durations')->select('date','total_duration')
            ->where('user_id', $user_id)
            ->orderBy('date', 'DESC')
            ->get();*/
        $data['userpoints'] = DB::table('user_durations as ud')
                  ->join('settings as s', DB::raw('1 = 1'), '>', DB::raw('0')) 
                  ->select(
                      'ud.date',
                      DB::raw('CAST(FLOOR(ud.total_duration / s.spendtime) * s.point AS UNSIGNED) AS points'),
          			  DB::raw("'Earning points' AS `title`")
                  )
                  ->where('ud.user_id', $user_id)
                  ->orderBy('ud.date', 'DESC')
                  ->get();
        
        $data['pointsTitle'] = $settingdata[0]->pointtitle;
      	$data['pointsNots'] = $settingdata[0]->pointtxt;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function categorysatrang()
    {
        $categorychildQuery = DB::table('categories as c1')
                ->select('c1.title as category_name', 'c1.*', 'u1.name as authorName', 'c2.title as parent_category_name')
                ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
                ->where('c1.parentID', 63)
          		->where('c1.status','Active')
          		->where('c2.status','Active')
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
    public function inshorts(){
        $inshorts = DB::table('news')
            ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
            ->leftJoin('categories', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
            })
            ->where('news.status', 'Active')
            ->where('news.catID', '!=', 9)
            ->where('news.inshorts', 1)
            ->groupBy('news.id')
            ->orderBy('news.topnewsOrder', 'ASC')
            ->limit(10)
            ->get();
        $data['inshorts'] = $inshorts;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  
  	public function deleteAccount(Request $request)
    {
        $userId =  $request->input('user_id');

        $existsInUserRegister = UserRegister::where('id', $userId)->exists();
        if($existsInUserRegister)
        {
            UserRegister::where('id', $userId)->delete();
        }
        $existsInBookmark = Bookmark::where('user_id', $userId)->exists();
        if($existsInBookmark)
        {
            Bookmark::where('user_id', $userId)->delete();
        }
        $existsInUserCategory = UserCategory::where('user_id', $userId)->exists();
        if($existsInUserCategory)
        {
            UserCategory::where('user_id', $userId)->delete();
        }
        $existsInUserCity = UserCity::where('user_id', $userId)->exists();
        if($existsInUserCity)
        {
            UserCity::where('user_id', $userId)->delete();
        }
        $existsInUserDuration = UserDuration::where('user_id', $userId)->exists();
        if($existsInUserDuration)
        {
            UserDuration::where('user_id', $userId)->delete();
        }
        $existsInUserPlan = Userplan::where('user_id', $userId)->exists();
        if($existsInUserPlan)
        {
            Userplan::where('user_id', $userId)->delete();
        }
        $existsInVisitorSession = VisitorSessions::where('user_id', $userId)->exists();
        if($existsInVisitorSession)
        {
            VisitorSessions::where('user_id', $userId)->delete();
        }
        return response()->json(['message' => 'User account data deleted successfully.']);

    }
   	public function showlogin()
    {
      $data =  array('login'=>0);
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
  	
  	public function electionresults()
    {
      $data =  array('election_status'=>1, 'height'=>150, 'election_url'=>'https://www.gstv.in/electionresults');
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

}
