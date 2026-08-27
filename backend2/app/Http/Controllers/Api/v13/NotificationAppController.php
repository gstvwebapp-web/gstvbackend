<?php

namespace App\Http\Controllers\Api\v13;

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
        $user_id = $request->input('user_id');
        $device_id = $request->input('device_id');
        $platform = $request->input('platform');
        $referralcode = $request->input('referralcode');
		// Prepare data for the notification table
      	$deviceidCheck = DB::table('notification')->where('device_id', $device_id)->first();
      	if(!empty($deviceidCheck) && !empty($deviceidCheck->referralcode)){
            $data = [
              'token' => $token,
              'user_id' => $user_id,
              'device_id' => $device_id,
              'platform' => $platform,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now()
          ];
        }
        else{
            $data = [
              'token' => $token,
              'user_id' => $user_id,
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
    
    
    public function getNotificationList(Request $request)
    {
        $data = $topvideosarr = $epapersarr = $webstoryarr = array();
        $topvideos = DB::table('news')
            ->select('englishTitle', 'id', 'title', 'description', 'featureImage', 'slug', 'videoURL', 'created_at')
            ->where('status', 'Active')
            ->where('notification', 1)
            ->orderBy('created_at', 'DESC')
            ->limit(30)
            ->get();

        $topvideosarr = [];

        foreach ($topvideos as $tv) {
            // Get category slugs and category IDs using news_category table
            $categoryData = DB::table('news_category as nc')
                ->join('categories as c', 'c.id', '=', 'nc.category_id')
                ->where('nc.news_id', $tv->id)
                ->selectRaw('GROUP_CONCAT(c.slug ORDER BY c.id SEPARATOR "/") as categorySlugs')
                ->selectRaw('GROUP_CONCAT(c.id ORDER BY c.id) as catIDs') // this replaces old catID
                ->first();

            $catIDs = explode(',', $categoryData->catIDs ?? '');
            $categorySlugs = $categoryData->categorySlugs ?? '';

            $type = 'News';
            $url = 'https://gstv.in/news/' . $categorySlugs . '/' . Str::slug($tv->englishTitle, '-');
            $featureImage = $tv->featureImage;
            $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
            $newFeatureImage = preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $featureImage);

            // Use actual category ID from news_category
            if (in_array(9, $catIDs)) {
                $type = 'Videos';
                $url = 'https://gstv.in/' . $categorySlugs . '/' . Str::slug($tv->englishTitle, '-');
                $featureImage = $tv->videoURL;
                $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
                $newFeatureImage = preg_replace("/\.$fileExtension$/i", "_video.gif", $featureImage);
            }

            $description = Str::limit($tv->description, 120, '...');

            $topvideosarr[] = [
                'title'         => $tv->title,
                'description'   => $description,
                'featureImage'  => $newFeatureImage,
                'type'          => $type,
                'url'           => $url,
                'slug'          => $tv->slug,
                'date'          => $tv->created_at
            ];
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

        // ✅ Step 1: Get videoId
        $videoId = DB::table('news')->where('slug', $slug)->value('id');
        if (!$videoId) {
            return response()->json(['error' => 'Video not found', 'slug' => $slug], 404);
        }

        // ✅ Step 2: Get Like/Dislike Data
        $likeData = DB::table('videolike')
            ->select(
                DB::raw('SUM(status = 1) as like_count'),
                DB::raw('SUM(status = 0) as dislike_count'),
                DB::raw('MAX(CASE WHEN status = 1 AND ipAddress = "' . $device_id . '" THEN 1 ELSE 0 END) as liked_by_user'),
                DB::raw('MAX(CASE WHEN status = 0 AND ipAddress = "' . $device_id . '" THEN 1 ELSE 0 END) as disliked_by_user')
            )
            ->where('video_id', $videoId)
            ->first();

        // ✅ Step 3: Get Video Details
        $topvideos = DB::table('news')
            ->join('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->where('news.status', 'Active')
            ->where('news.slug', $slug)
            //->where('nc.category_id', 9) // Remove if you want all videos
            ->select(
                'news.id',
                'news.englishTitle',
                'news.title',
                'news.description',
                'news.featureImage',
                'news.slug',
                'news.videoURL',
                'news.created_at',
                'nc.category_id as catID'
            )
            ->orderBy('news.id', 'desc')
            ->first();

        if (!$topvideos) {
            return response()->json(['error' => 'Active video not found'], 404);
        }

        // ✅ Step 4: Bookmark Check
        $bookmark = "";
        if (!empty($user_id)) {
            $bookmark = DB::table('bookmark')
                ->where('user_id', $user_id)
                ->where('news_id', $topvideos->id)
                ->value('status') ?? "";
        }

        // ✅ Step 5: Get Category Slugs
        $catslug = DB::table('news as n')
            ->join('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->join('categories as c', 'nc.category_id', '=', 'c.id')
            ->where('n.id', $topvideos->id)
            ->selectRaw('GROUP_CONCAT(c.slug ORDER BY c.id SEPARATOR "/") as categorySlugs')
            ->first();

        $categorySlugs = $catslug->categorySlugs ?? '';

        // ✅ Step 6: Build URL
        $type = 'Videos';
        $url = config('app.front_url') . '/' . $categorySlugs . '/' . Str::slug($topvideos->englishTitle, '-');
        if (strpos($categorySlugs, '/') !== false) {
            $catSlugParts = explode('/', $categorySlugs);
            $url = config('app.front_url') . '/' . $catSlugParts[0] . '/' . Str::slug($topvideos->englishTitle, '-');
        }

        // ✅ Step 7: Build Response Array
        $topvideosarr = [
            'featureImage' => $topvideos->videoURL,
            'type' => $type,
            'date' => $topvideos->created_at    
        ];

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
