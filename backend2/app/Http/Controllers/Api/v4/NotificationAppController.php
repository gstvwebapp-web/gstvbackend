<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\Category;
use App\Models\News;
use App\Models\Webstory;
use Carbon\Carbon;
use Route;

 
class NotificationAppController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function send(Request $request)
    {
        $tokens = $request->input('tokens');
        $title = $request->input('title');
        $body = $request->input('body');
        $notificationtype = $request->input('notificationtype');
        $newFeatureImage = $request->input('newFeatureImage');
        $notificationurl = $request->input('notificationurl');
        $notificationslug = $request->input('notificationslug');

        $response = $this->firebaseService->sendNotification($title, $body, $tokens, $notificationtype, $newFeatureImage, $notificationurl,$notificationslug);

        return response()->json($response);
    }
    public function adddevice(Request $request)
    {
        $token = $request->input('token');
        $device_id = $request->input('device_id');
        $platform = $request->input('platform');
        $referralcode = $request->input('referralcode');
		// Prepare data for the notification table
      	$deviceidCheck = DB::table('notification')->where('device_id', $device_id)->first();
      	if(!empty($deviceidCheck) && !empty($deviceidCheck->referralcode)){
            $data = [
              'token' => $token,
              'device_id' => $device_id,
              'platform' => $platform,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
          ];
        }
        else{
            $data = [
              'token' => $token,
              'device_id' => $device_id,
              'platform' => $platform,
              'referralcode' => $referralcode, // Retain the existing referral code if it exists
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
          ];
        }
        

        // Insert or update the notification table
        DB::table('notification')->updateOrInsert(
            ['device_id' => $device_id], // The condition to check
            $data // The data to update or insert
        );
        // Check if the referral code already exists for the given device_id
        $existingRecord = DB::table('notification')->where('device_id', $device_id)->where('referralcode', $referralcode)->first();

        // Preserve the existing referral code if it exists
        if (!empty($existingRecord) && !empty($existingRecord->referralcode)) {
            $referralcode = $existingRecord->referralcode;
          
            $dataref = [
                'referralcode' => $referralcode,
                'device_id'=>$device_id,
                'referaltitle'=>'Referral',
                'points' => 50,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $existingReferral = DB::table('referral')
                ->where('device_id', $device_id)
                ->where('referralcode', $referralcode)
                ->exists();
			
            if (!$existingReferral) {
                // Insert only if the combination does not exist
                DB::table('referral')->insert($dataref);
            }
        }

        $data['msg'] = 'Device stored successfully.';
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    
    public function getNotificationList1(Request $request)
    {
        $pageNumber = $request->input('page');
        // Get current page number from request body, default to 1
        //$pageNumber = $request->input('page', 1);
        $perPage = 10; // Number of items per page

        // Paginate top videos
        $topvideosQuery = DB::table('news')
            ->select('englishTitle', 'catID', 'id', 'title', 'description', 'featureImage', 'slug', 'videoURL', 'created_at')
            ->where('status', 'Active')
            ->where('notification', 1)
            ->orderBy('created_at', 'DESC');

        $topvideos = $topvideosQuery->paginate($perPage, ['*'], 'page', $pageNumber);

        // Process top videos
        $topvideosArr = $topvideos->map(function ($tv) {
            $catslug = DB::table('news')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')
                ->where('id', $tv->id)
                ->first();

            $type = 'News';
            $url = 'https://gstv.in/news/'.$catslug->categorySlugs.'/'.Str::slug($tv->englishTitle, '-');
            $featureImage = $tv->featureImage;
            $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
            $newFeatureImage = preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $featureImage);

            if ($tv->catID == '9') {
                $type = 'Videos';
                $url = 'https://gstv.in/'.$catslug->categorySlugs.'/'.Str::slug($tv->englishTitle, '-');
                $featureImage = $tv->videoURL;
                $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
                $newFeatureImage = preg_replace("/\.$fileExtension$/i", "_video.gif", $featureImage);
            }

            $description = Str::limit($tv->description, 120, '...');

            return [
                'title' => $tv->title,
                'description' => $description,
                'featureImage' => $newFeatureImage,
                'type' => $type,
                'url' => $url,
                'slug' => $tv->slug,
                'date' => $tv->created_at,
            ];
        });

        // Paginate web stories
        $webstoryQuery = DB::table('webstory')
            ->select('englishTitle', 'catID', 'id', 'title', 'slug', 'Story_data', 'created_at')
            ->where('status', 'Active')
            ->where('notification', 1)
            ->orderBy('created_at', 'DESC');

        $webstories = $webstoryQuery->paginate($perPage, ['*'], 'page', $pageNumber);

        // Process web stories
        $webstoriesArr = $webstories->map(function ($web) {
            $type = 'Web Stories';
            $url = 'https://www.gstv.in/web-stories/'.$web->slug;
            $newFeatureImage = str_replace('.webp', '_medium.webp', json_decode($web->Story_data)[0]->webimage);
            $description = str_replace('.webp', '_medium.webp', json_decode($web->Story_data)[0]->webtitles);

            return [
                'title' => $web->title,
                'description' => $description,
                'featureImage' => $newFeatureImage,
                'type' => $type,
                'url' => $url,
                'slug' => $web->slug,
                'date' => $web->created_at,
            ];
        });

        // Combine both paginated results
        $data = [
            'topvideos' => $topvideosArr,
            'webstories' => $webstoriesArr,
            'currentPage' => $topvideos->currentPage(),
            'lastPage' => $topvideos->lastPage(),
            'perPage' => $topvideos->perPage(),
            'total' => $topvideos->total(),
            'webstories_currentPage' => $webstories->currentPage(),
            'webstories_lastPage' => $webstories->lastPage(),
            'webstories_perPage' => $webstories->perPage(),
            'webstories_total' => $webstories->total(),
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function getNotificationList(Request $request)
    {
        $data = $topvideosarr = $epapersarr = $webstoryarr = array();
        $topvideos = DB::table('news')->select('englishTitle','catID','id','title', 'description','featureImage','slug','videoURL','created_at')->where('status','Active')->where('notification',1)->orderBy('created_at','DESC')->limit(30)->get();
        foreach($topvideos as $tv){
                    $catslug = DB::table('news')
                        ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')      
                        ->where('id',$tv->id)
                        ->first();            
                    $type = 'News';
                    $url = 'https://gstv.in/news/'.$catslug->categorySlugs.'/'.Str::slug($tv->englishTitle, '-');
                    $featureImage = $tv->featureImage;
                    $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
                    $newFeatureImage = preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $featureImage);
                    if($tv->catID == '9'){
                        $type = 'Videos';
                        $url = 'https://gstv.in/'.$catslug->categorySlugs.'/'.Str::slug($tv->englishTitle, '-');
                        $featureImage = $tv->videoURL;
                        $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
                        $newFeatureImage = preg_replace("/\.$fileExtension$/i", "_video.gif", $featureImage);
                    }
                    $description = Str::limit($tv->description, 120, '...');
                    $topvideosarr[] = array('title'=>$tv->title,'description'=>$description,'featureImage'=>$newFeatureImage,'type'=>$type,'url'=>$url,'slug'=>$tv->slug,'date'=>$tv->created_at);
                    // $topvideosarr = array('title'=>$topvideos->title,'description'=>$description,'featureImage'=>$featureImage,'type'=>$type,'url'=>$url,'slug'=>$topvideos->slug,'date'=>$topvideos->created_at,'like_count'=>$topvideos->like_count,'dislike_count'=>$topvideos->dislike_count,'liked_by_user'=>$topvideos->liked_by_user,'disliked_by_user'=>$topvideos->disliked_by_user,'bookmark'=>$bookmark);
        }
        $webstory = DB::table('webstory')->select('englishTitle','catID','id','title','slug','Story_data','created_at')->where('status','Active')->where('notification',1)->orderBy('created_at','DESC')->limit(30)->get();
        foreach($webstory as $web){  
                    $type = 'Web Stories';
                    $url = 'https://www.gstv.in/web-stories/'.$web->slug;
                    $newFeatureImage = str_replace('.webp', '_medium.webp', json_decode($web->Story_data)[0]->webimage);
                    $description = str_replace('.webp', '_medium.webp', json_decode($web->Story_data)[0]->webtitles);
                    $webstoryarr = array('title'=>$web->title,'description'=>$description,'featureImage'=>$newFeatureImage,'type'=>$type,'url'=>$url,'slug'=>$web->slug,'date'=>$web->created_at);
                    array_push($topvideosarr,$webstoryarr);
        }
        $epaperlist = DB::table('epaper')->select('etitle','pdf','ecatID','slug','Story_data', 'newspaperdate','status','created_at')->where('status','Active')->where('notification',1)->orderBy('created_at','DESC')->limit(20)->get();
        foreach($epaperlist as $ep){
                    $adsCat = DB::table('epapercategory')
                        ->select('slug')
                        ->where('id', $ep->ecatID)
                        ->first();         

                    $path =config('app.front_url');
                    $imagePath =json_decode($ep->Story_data)[0]->image;
                    $filenameWithoutExt = pathinfo($imagePath, PATHINFO_FILENAME);
                    //$newImagePath = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . $filenameWithoutExt . '_small.jpg';
                    if($ep->pdf == ""){
                        $newFeatureImage = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . $filenameWithoutExt . '_small.webp';
                    } else {
                        $newFeatureImage =json_decode($ep->Story_data)[0]->image;
                    }
                    $type = 'epaper';
                    $epaperurl = config('app.front_url').'/epaper/'.$adsCat->slug.'/'.date('d-m-Y',strtotime($ep->newspaperdate));
                    // $epaperimage = json_decode($ep->Story_data);
                    // $imageUrl = $epaperimage[0]->image;
                    // $newFeatureImage = preg_replace('/(\.webp)$/', '_small$1', $imageUrl);
                    $title = $ep->etitle.' Epaper';
                    $body = $ep->etitle;
                    $epapersarr = array('title'=>$title,'description'=>$body,'featureImage'=>$newFeatureImage,'type'=>$type,'url'=>$epaperurl,'slug'=>$adsCat->slug,'date'=>$ep->created_at);
                    array_push($topvideosarr,$epapersarr);
                    // $topvideosarr = array('title'=>$topvideos->title,'description'=>$description,'featureImage'=>$featureImage,'type'=>$type,'url'=>$url,'slug'=>$topvideos->slug,'date'=>$topvideos->created_at,'like_count'=>$topvideos->like_count,'dislike_count'=>$topvideos->dislike_count,'liked_by_user'=>$topvideos->liked_by_user,'disliked_by_user'=>$topvideos->disliked_by_user,'bookmark'=>$bookmark);
        }
       // array_push($topvideosarr,$webstoryarr);
        // array_push($topvideosarr,$epapersarr);
        // $topvideosarr = array_merge($topvideosarr, $webstoryarr, $epapersarr);
        $data['notificationlist'] =  $topvideosarr;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
	
	public function getVideoSlug(Request $request)
{
    $slug = $request->input('slug');
    $user_id = $request->input('user_id');
    $device_id = $request->input('device_id');

    $topvideosarr = [];

    // ✅ Debug log (to check incoming params)
    \Log::info("getVideoSlug called", ['slug' => $slug, 'user_id' => $user_id, 'device_id' => $device_id]);

    // ✅ Subquery for likes/dislikes
    $subquery = DB::table('videolike')
        ->select(
            'video_id',
            DB::raw('COUNT(CASE WHEN status = "1" THEN 1 END) as like_count'),
            DB::raw('COUNT(CASE WHEN status = "0" THEN 1 END) as dislike_count'),
            DB::raw('MAX(CASE WHEN status = "1" AND ipAddress = "' . $device_id . '" THEN 1 ELSE 0 END) as liked_by_user'),
            DB::raw('MAX(CASE WHEN status = "0" AND ipAddress = "' . $device_id . '" THEN 1 ELSE 0 END) as disliked_by_user')
        )
        ->groupBy('video_id');

    // ✅ Main query (FIND_IN_SET fix)
    $topvideos = DB::table('news')
        ->leftJoinSub($subquery, 'vl', function ($join) {
            $join->on('news.id', '=', 'vl.video_id');
        })
        ->select(
            'news.id', 'news.englishTitle', 'news.catID1', 'news.title',
            'news.description', 'news.featureImage', 'news.slug', 'news.videoURL',
            'news.created_at', 'vl.like_count', 'vl.dislike_count',
            'vl.liked_by_user', 'vl.disliked_by_user'
        )
        ->where('news.status', 'Active')
        ->where('news.slug', $slug)
        ->when(true, function ($q) { // ✅ Debug: Remove FIND_IN_SET if you want to test
            $q->whereRaw('FIND_IN_SET(?, news.catID1)', [9]);
        })
        ->first();

    \Log::info("Video Query Result", ['topvideos' => $topvideos]);

    if (!empty($topvideos)) {
        $bookmark = "";
        if (!empty($user_id)) {
            $bookmark = DB::table('bookmark')
                ->where('user_id', $user_id)
                ->where('news_id', $topvideos->id)
                ->value('status') ?? "";
        }

        // ✅ Category Slug(s)
        $catslug = DB::table('news')
            ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") 
                          FROM categories 
                          WHERE FIND_IN_SET(categories.id, news.catID1)) as categorySlugs')
            ->where('id', $topvideos->id)
            ->first();

        $categorySlugs = $catslug->categorySlugs ?? '';
        $type = 'Videos';

        // ✅ URL Build
        $url = config('app.front_url') . '/' . $categorySlugs . '/' . Str::slug($topvideos->englishTitle, '-');
        if (strpos($categorySlugs, '/') !== false) {
            $catSlugParts = explode('/', $categorySlugs);
            $url = config('app.front_url') . '/' . $catSlugParts[0] . '/' . Str::slug($topvideos->englishTitle, '-');
        }

        // ✅ Image & Description
        $featureImage = $topvideos->videoURL;
        $description = Str::limit($topvideos->description, 120, '...');

        // ✅ Response
        $topvideosarr = [
            'id' => $topvideos->id,
            'title' => $topvideos->title,
            'description' => $description,
            'featureImage' => $featureImage,
            'type' => $type,
            'url' => $url,
            'slug' => $topvideos->slug,
            'date' => $topvideos->created_at,
            'like_count' => $topvideos->like_count ?? 0,
            'dislike_count' => $topvideos->dislike_count ?? 0,
            'liked_by_user' => $topvideos->liked_by_user ?? 0,
            'disliked_by_user' => $topvideos->disliked_by_user ?? 0,
            'bookmark' => $bookmark
        ];
    }

    return response()->json($topvideosarr, 200, [], JSON_UNESCAPED_UNICODE);
}

    public function getVideoSlug22(Request $request)
    {
        $slug = $request->input('slug');
        $user_id = $request->input('user_id');
        $device_id = $request->input('device_id');
        $topvideosarr = array();
         
        
            // Define the subquery for likes/dislikes
        $subquery = DB::table('videolike')
        ->select(
            'video_id',
            DB::raw('COUNT(CASE WHEN status = "1" THEN 1 END) as like_count'),
            DB::raw('COUNT(CASE WHEN status = "0" THEN 1 END) as dislike_count'),
            DB::raw('COUNT(CASE WHEN status = "1" AND ipAddress = "' . $device_id . '" THEN 1 END) as liked_by_user'),
            DB::raw('COUNT(CASE WHEN status = "0" AND ipAddress = "' . $device_id . '" THEN 1 END) as disliked_by_user')
        )
        ->groupBy('video_id');

        // Retrieve the top videos and include like/dislike counts
        $topvideos = DB::table('news')
        ->leftJoinSub($subquery, 'vl', function ($join) {
            $join->on('news.id', '=', 'vl.video_id');
        })
        ->select('news.id','news.englishTitle', 'news.catID1', 'news.id', 'news.title', 'news.description', 'news.featureImage', 'news.slug', 'news.videoURL', 'news.created_at', 
                'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user')
        ->whereRaw('FIND_IN_SET(?, catID1)', [9]) // Assuming catID = 9 is the category filter
        ->where('news.status', 'Active')
        ->where('news.slug', $slug)
        ->first();
        if(!empty($topvideos)){
            $bookmark = "";
            if (!empty($user_id)) {
                $newsDetailbookmark = DB::table('bookmark')
                    ->where('user_id', $user_id)
                    ->where('news_id', $topvideos->id)
                    ->first();
    
                if ($newsDetailbookmark) {
                    $bookmark = $newsDetailbookmark->status;
                }
            }
            $catslug = DB::table('news')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID1)) as categorySlugs')      
                ->where('id',$topvideos->id)
                ->first();   
            $type = 'Videos';
            $url = config('app.front_url').'/'.$catslug->categorySlugs.'/'.Str::slug($topvideos->englishTitle, '-');
            if (strpos($catslug->categorySlugs, '/') !== false) {
                // If '/' exists in categorySlug, get the top-level category
                $catSlugParts = explode('/', $catslug->categorySlugs);
                $url = config('app.front_url').'/' . $catSlugParts[0].'/'.Str::slug($topvideos->englishTitle, '-');
            }
            $featureImage = $topvideos->videoURL;
            $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
            $newFeatureImage = preg_replace("/\.$fileExtension$/i", "_video.gif", $featureImage);
            $description = Str::limit($topvideos->description, 120, '...');
            $topvideosarr = array('id'=>$topvideos->id,'title'=>$topvideos->title,'description'=>$description,'featureImage'=>$featureImage,'type'=>$type,'url'=>$url,'slug'=>$topvideos->slug,'date'=>$topvideos->created_at,'like_count'=>$topvideos->like_count,'dislike_count'=>$topvideos->dislike_count,'liked_by_user'=>$topvideos->liked_by_user,'disliked_by_user'=>$topvideos->disliked_by_user,'bookmark'=>$bookmark);
        }
        return response()->json($topvideosarr, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function getWebStorySlug(Request $request)
    {
        $slug = $request->input('slug');
        $topvideosarr = array();
        $webstory = DB::table('webstory')->select('englishTitle','catID','id','title','slug','Story_data','created_at')->where('status','Active')->where('slug',$slug)->first();
        if(!empty($webstory)){   
            $type = 'Web Stories';
            $url = 'https://www.gstv.in/web-stories/'.$webstory->slug;
            $topvideosarr = array('title'=>$webstory->title,'Story_data'=>$webstory->Story_data,'type'=>$type,'url'=>$url,'slug'=>$webstory->slug,'date'=>$webstory->created_at);
        }
        return response()->json($topvideosarr, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
