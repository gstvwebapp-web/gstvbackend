<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\News;
use App\Models\Users;
use App\Models\Usernews;
use App\Models\Newscategory;
use App\Models\Livenews;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Imports\NewsDataImport; 
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Tag;
use App\Models\Media;
use Carbon\Carbon;
use File;
use Image;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\FirebaseService;
use App\Services\CloudflareService;
use App\Models\Notification;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessVideoPreview;
use App\Jobs\ProcessImagePreview;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use App\Services\GoogleAnalyticsService;
use Google\Client;
use Illuminate\Support\Facades\Http;


class NewsController extends Controller
{
    protected $firebaseService;
    protected $cloudflareService;

    public function __construct(FirebaseService $firebaseService, CloudflareService $cloudflareService,GoogleAnalyticsService $analyticsService)
    {
        $this->firebaseService = $firebaseService;
        $this->cloudflareService = $cloudflareService;
        $this->analyticsService = $analyticsService;

    }
    // Get all pages
    public function index(Request $request){
        $status = $request->get('status', 'Active'); // Default to Active if no status is passed
        $userID = $request->get('userID');
        $newssource = $request->get('newssource');
        $catID = $request->get('catID');
        $title = $request->get('title');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        // Sort params from dashboard "View More" link
        $sort_col = (int) $request->get('sort_col', 5);   // default: Created Date col
        $sort_dir = $request->get('sort_dir', 'desc');
        $sort_dir = in_array(strtolower($sort_dir), ['asc', 'desc']) ? strtolower($sort_dir) : 'desc';
        $tab      = $request->get('tab', '');  // e.g. 'video' from dashboard View More

        if($status == "video"){
            $status = "Active";
        }

        // Fetch counts for status
        // $publish = DB::table('news')->whereRaw('NOT FIND_IN_SET(9, news.catID)')
        //     ->where('news.status', 'Active')->count();
        // $unpublish = DB::table('news')->where('news.status', 'Inactive')->count();
        // $schedule = DB::table('news')->where('news.status', 'Schedule')->count();
        // $videos = DB::table('news')->whereRaw('FIND_IN_SET(9, news.catID)')->count();
        // $draft = DB::table('news')->where('news.status', 'Draft')->count();
        $publish = DB::table('news as n')
            ->join('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->where('n.status', 'Active')
            ->where('nc.category_id', '!=', 9)
            ->distinct('n.id') // Avoid duplicate count due to multiple categories
            ->count('n.id');

        // Count Videos (only category_id = 9)
        $videos = DB::table('news as n')
            ->join('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->where('nc.category_id', 9)
            ->distinct('n.id')
            ->count('n.id');

        // Other status-based counts (still using `news` table directly)
        $unpublish = DB::table('news')->where('status', 'Inactive')->count();
        $schedule   = DB::table('news')->where('status', 'Schedule')->count();
        $draft      = DB::table('news')->where('status', 'Draft')->count();

        // Fetch users and categories
        $users = DB::table('users')->where('status', 'Active')->get();
        $newssourceList = DB::table('tags')->where('status', 'Active')->get();
        $category = DB::table('categories')->where('status', 'Active')->orderBy('catOrder', 'ASC')->get();

        // Prepare data array
        $data = [
            'all'      => DB::table('news')->where('created_at', '>=', Carbon::now()->subDays(60))->count(),
            'publish'  => $publish,
            'unpublish' => $unpublish,
            'schedule' => $schedule,
            'draft'    => $draft,
            'videos'   => $videos,
            'users'    => $users,
            'category' => $category,
            'title'    => $title,
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'userID'   => $userID,
            'catID'    => $catID,
            'status'   => $status,
            'sort_col' => $sort_col,
            'sort_dir' => $sort_dir,
            'tab'      => $tab,
            'newssource'=>$newssource,
            'newssourceList'=>$newssourceList
        ];

        return view('news.allpublish', compact('data'));
    }
   


    
    // Add new page
    public function add(Request $request)
{
    $user = Auth::user();
    $ids = explode(',', $user->catID);

    $perPage = $request->input('per_page', 12);
    $page = $request->input('page', Route::current()->parameter('pageNumber', 1));
    $minutes = 30;

    // 🧠 Redis: Parent + Child Categories (eventCat = 0)
    $categoryKey = 'categories_parent_child_' . config('app.webtype');
    if (Redis::exists($categoryKey)) {
        $category = collect(json_decode(Redis::get($categoryKey)));
    } else {
        $categoryQuery = DB::table('categories')
            ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
            ->select(
                'categories.id',
                'categories.title',
                'categories.parentID',
                'children.id as child_id',
                'children.title as child_name'
            )
            ->where('categories.status', 'Active')
            ->where('categories.eventCat', 0);

        // Apply category restriction only for role_id = 5
        if ($user->roleID == 7) {
            $categoryQuery->whereIn('categories.id', $ids);
        }

        $category = $categoryQuery
            ->orderBy('categories.catOrder', 'ASC')
            ->get();

        Redis::setex($categoryKey, $minutes * 60, $category->toJson());
    }
    $uniqueParentCategories = $category->unique('id');

    // 🧠 Redis: Event Categories
    $eventKey = 'categories_event_' . config('app.webtype');
    if (Redis::exists($eventKey)) {
        $eventcategory = collect(json_decode(Redis::get($eventKey)));
    } else {
        $eventcategory = DB::table('categories')
            ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
            ->select('categories.id', 'categories.title', 'categories.parentID', 'children.id as child_id', 'children.title as child_name')
            ->where('categories.status', 'Active')
            ->where('categories.eventCat', 1)
            ->orderBy('categories.catOrder', 'ASC')
            ->get();

        Redis::setex($eventKey, $minutes * 60, $eventcategory->toJson());
    }

    // 🧠 Redis: Users
    $userKey = 'active_users_' . config('app.webtype');
    if (Redis::exists($userKey)) {
        $userList = collect(json_decode(Redis::get($userKey)));
    } else {
        $userList = DB::table('users')->where('status', 'Active')->get();
        Redis::setex($userKey, $minutes * 60, $userList->toJson());
    }

    $newssourceKey = 'news_source_' . config('app.webtype');
    if (Redis::exists($newssourceKey)) {
        $newssourceList = collect(json_decode(Redis::get($newssourceKey)));
    } else {
        $newssourceList = DB::table('tags')->where('status', 'Active')->get();
        Redis::setex($newssourceKey, $minutes * 60, $newssourceList->toJson());
    }

    // ✅ Media (no Redis – paginated)
    $media = DB::table('medias')
        ->where(function ($query) {
            $query->where('photos', 'like', '%.jpg')
                ->orWhere('photos', 'like', '%.jpeg')
                ->orWhere('photos', 'like', '%.png')
                ->orWhere('photos', 'like', '%.webp')
                ->orWhere('photos', 'like', '%.gif');
        })
        ->orderBy('id', 'DESC')
        ->paginate($perPage, ['*'], 'page', $page);

    $videomedia = DB::table('medias')
        ->where('photos', 'like', '%.mp4')
        ->orderBy('id', 'DESC')
        ->paginate($perPage, ['*'], 'page', $page);

    // Final Data Array
    $data = [
        'media' => $media,
        'videomedia' => $videomedia,
        'selectedMedia' => [],
        'selectedvideoMedia' => [],
        'category' => $uniqueParentCategories,
        'eventcategory' => $eventcategory,
        'user' => $userList,
        'newssourceList'=>$newssourceList
    ];
    return view('news.add', compact('data'));
}
public function loadmoredata(Request $request)
{
    $nid = $request->query('nid');
    $inputValue = trim($request->query('inputValue'));
    $pageNumber = $request->input('page', Route::current()->parameter('pageNumber', 1));
    $defaultPerPage = $request->input('per_page', 12);
    $perPage = ($pageNumber == 1) ? 6 : $defaultPerPage;

    // ✅ Get feature image if nid is defined and numeric
    $featureImages = [];

    if (!empty($nid) && $nid !== 'undefined' && is_numeric($nid)) {
        $featureImageResult = DB::table('news')->where('id', $nid)->value('featureImage');
        if ($featureImageResult) {
            $featureImages = explode(',', $featureImageResult);
        }
    }

    // ✅ Build media query
    $mediaQuery = DB::table('medias')
        ->when(strlen($inputValue) >= 3, function ($query) use ($inputValue) {
            // Search based on input value
            $query->where('photos', 'like', '%' . $inputValue . '%');
        }, function ($query) use ($inputValue) {
            // Match images with extensions or optional search value
            $query->where(function ($subQuery) use ($inputValue) {
                $subQuery->where('photos', 'like', '%.jpg')
                    ->orWhere('photos', 'like', '%.jpeg')
                    ->orWhere('photos', 'like', '%.png')
                    ->orWhere('photos', 'like', '%.webp')
                    ->orWhere('photos', 'like', '%.gif');

                // Optional input text even when short
                if ($inputValue !== '') {
                    $subQuery->orWhere('photos', 'like', '%' . $inputValue . '%');
                }
            });
        });

    // ✅ Avoid already used feature images
    if (!empty($featureImages)) {
        $mediaQuery->whereNotIn('fullpath', $featureImages);
    }

    // ✅ Get paginated results
    $media = $mediaQuery->orderBy('id', 'DESC')->paginate($perPage, ['*'], 'page', $pageNumber);

    return response()->json($media);
}
public function loadmorevideodata(Request $request)
{
    $nid = $request->query('nid');
    $inputValue = trim($request->query('inputValue'));
    $page = $request->input('page', Route::current()->parameter('pageNumber', 1));
    $perPage = ($page == 1) ? 6 : $request->input('per_page', 12);

    // ✅ Get videoURL from news if nid is set
    $featureVideos = [];

    if (!empty($nid) && $nid !== 'undefined' && is_numeric($nid)) {
        $videoURL = DB::table('news')->where('id', $nid)->value('videoURL');
        if ($videoURL) {
            $featureVideos = explode(',', $videoURL);
        }
    }

    // ✅ Build media query
    $mediaQuery = DB::table('medias')
        ->where('photos', 'LIKE', '%.mp4');

    if (strlen($inputValue) >= 3) {
        $mediaQuery->where('photos', 'LIKE', '%' . $inputValue . '%');
    }

    if (!empty($featureVideos)) {
        $mediaQuery->whereNotIn('fullpath', $featureVideos);
    }

    // ✅ Final paginated data
    $media = $mediaQuery->orderBy('id', 'DESC')->paginate($perPage, ['*'], 'page', $page);

    return response()->json($media);
}

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'englishTitle' => 'required|unique:news,englishTitle',
            'catID' => 'required',
            'newssource' => 'required'
        ];

        $customMessages = [
            'title.required' => 'The title field is required.',
            'englishTitle.required' => 'The english title field is required.',
            'englishTitle.unique' => 'The English Title is already in use. Please choose a different title.',
            'catID.required' => 'At least one category must be selected.',
            'newssource.required' => 'News source must be selected.',
        ];

        $request->validate($rules, $customMessages);

        $englishTitle = $request->englishTitle;
        $catID = $request->catID;
        $subcatID = $request->subcatID;
        $eventCat = $request->eventCat;
        $scheduleDate = $request->scheduleDate;
        $featureImage = $request->featureImage;
        $newssource=$request->newssource;
        $jacket_title=$request->jacket_title;
        // $is_vertical_video = $request->has('is_vertical_video') ? 9 : 0;
        $is_video_horizontal = $request->has('is_video_horizontal') ? 1 : 0;
        $is_jacket = $request->has('is_jacket') ? 1 : 0;
        
        $videoURL = $request->videoURL;
        $video_webp = '';
        /*if($videoURL != ""){
            $video_webp = preg_replace('/\.mp4$/i', '_video.webp', $videoURL);
        }*/
        $is_vertical_video = 0;
         if(($videoURL == "" || $videoURL == null) && $featureImage != ""){  
            $jacket_title_head = '';
            if($is_jacket == 1){
                $jacket_title_head = $request->get('jacket_title');
            }
            ProcessImagePreview::dispatch($featureImage,$catID,$jacket_title_head,$is_jacket);
        }
        $voiceovertext = '';
        if($videoURL != ""){
            $is_vertical_video = 9;
            $webpURL = preg_replace('/\.mp4$/i', '_video.webp', $videoURL);

            $webpPath = rtrim(env('MEDIA_PUBLIC_PATH'), '/'). parse_url($webpURL, PHP_URL_PATH);
            $jacket_title_head = '';
            if($is_jacket == 1){
                $jacket_title_head = $jacket_title;
                $voiceovertext = $request->voiceovertext;
            }
            ProcessVideoPreview::dispatch($videoURL,$is_vertical_video,$catID,$request->get('video_start'),$jacket_title_head,$is_jacket,$voiceovertext);
            //if (file_exists($webpPath)) {
                $video_webp = $webpURL;
            //}
		   if($featureImage == null || $featureImage == "" || $featureImage == "null" || $featureImage == "undefined"){
             $featureImage = null;
           }
          
        }
        
        
        $userID = $request->userID;
        $usernews_userID = $request->usernews_userID;
        $status = $request->status;
        $description = $request->description;

        $concatenatedValue = $catID;
        if (!empty($subcatID)) $concatenatedValue .= ",{$subcatID}";
        if (!empty($eventCat)) $concatenatedValue .= ",{$eventCat}";

        // Redis clear
        if ($catID == '9') Redis::del('topvideos');
        Redis::del("news_category_{$catID}");
        Redis::del("news{$catID}");
        for ($i = 1; $i <= 5; $i++) Redis::del("videolist{$i}");
        if ($request->inshorts == 1) Redis::del('topnews_inshorts');

        $metakeyword = implode(',', explode(',', $request->metakeyword));
        $plainText = strip_tags($description);
        $limitedText = Str::limit($plainText, 155 - Str::length($englishTitle), '');
        $metadesc = "$englishTitle | $limitedText";

        $slug = Str::slug($englishTitle, '-');
        $tags = $request->has('selectedTags') ? implode(',', $request->selectedTags) : '';
        $hide_title = $request->get('hide_title') == 1 ? 1 : 0;
        $is_home = $request->is_home == 1 ? 1 : 0;
        if ($request->is_live_news == 1) $is_home = 0;
        if($videoURL != null || $videoURL != ""){
            $title = "VIDEO: ".$request->title;
        } else{
            $title =$request->title;
        }
        if($catID == 149){
            $video_webp = $featureImage;
        }
        $news = News::create([
            'title' => $title,
            'englishTitle' => $englishTitle,
            'slug' => $slug,
            'userID' => $userID,
            'assignUserID' => Auth::id(),
            'featureImage' => $featureImage,
            'imageURL' => '',
            'uploadVideo' => '',
            'scheduleDate' => $scheduleDate,
            'related_news' => $request->related_news == 1 ? 1 : 0,
            'hide_title' => $hide_title,
            'tags' => $tags,
            'newssource'=>$newssource,
            'viewer' => 0,
            'is_home' => $is_home,
            'inshorts' => $request->has('inshorts') ? 1 : 0,
          	'is_valid' => $request->has('is_valid') ? 1 : 0,
            'videoURL' => $videoURL,
            'video_webp' => $video_webp,
            'metatitle' => $englishTitle,
            'metakeyword' => $metakeyword,
            'metadesc' => $metadesc,
            'status' => $status,
            'description' => $description,
            'is_live_news' => $request->is_live_news == 1 ? 1 : 0,
            'is_breaking' => $request->has('is_breaking') ? 1 : 0,
            'notification' => $request->has('notification') ? 1 : 0,
            'is_vertical_video' => $is_vertical_video,
            'is_video_horizontal'=>$is_video_horizontal,
            'is_jacket'=>$is_jacket,
            'jacket_title'=>$request->has('is_jacket') ? $request->jacket_title : null,
            'voiceovertext'=>$request->has('is_jacket') ? $request->voiceovertext : null,
            'img_credit_txt' => $request->get('img_credit_txt'),
            'video_start' => $request->filled('video_start') ? $request->get('video_start') : 10,
        ]);

        // if (!empty($videoURL)) {
        //     $backupPath = "\\\\192.168.2.250\\script new\\Jahnvi Web\\test\\";
        //     if (!file_exists($backupPath)) {
        //         mkdir($backupPath, 0777, true);
        //     }
        //     $fileName = basename(parse_url($videoURL, PHP_URL_PATH));
        //     $response = Http::timeout(300)->get($videoURL);
        //     if ($response->successful()) {
        //         file_put_contents($backupPath . $fileName, $response->body());
        //     }

        // }

		$audioPath = null;
        if($videoURL == null || $videoURL == ""){
            $text = strip_tags($description);
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);

            $client = new TextToSpeechClient([
                'credentials' => storage_path('app/google-tts.json')
            ]);

            $chunks = [];
            $currentChunk = '';

            $words = preg_split('/\s+/', $text);

            foreach ($words as $word) {
                $testChunk = $currentChunk . ' ' . $word;

                if (strlen($testChunk) > 4500) { 
                    $chunks[] = $currentChunk;
                    $currentChunk = $word;
                } else {
                    $currentChunk = $testChunk;
                }
            }

            if (!empty($currentChunk)) {
                $chunks[] = $currentChunk;
            }

            $finalAudio = '';

            foreach ($chunks as $chunk) {

                $input = new \Google\Cloud\TextToSpeech\V1\SynthesisInput();
                $input->setText($chunk);

                $voice = new \Google\Cloud\TextToSpeech\V1\VoiceSelectionParams();
                $voice->setLanguageCode('gu-IN');

                $audioConfig = new \Google\Cloud\TextToSpeech\V1\AudioConfig();
                $audioConfig->setAudioEncoding(\Google\Cloud\TextToSpeech\V1\AudioEncoding::MP3);

                $response = $client->synthesizeSpeech($input, $voice, $audioConfig);

                $finalAudio .= $response->getAudioContent();
            }
            $year  = date('Y');
            $month = date('m');
            $day   = date('d');

            // Destination path
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/audio/$year/$month/$day/";

            // Create folder if not exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // File name
            $fileName = "news_" . $news->id . ".mp3";

            // Save file
            file_put_contents($destinationPath . $fileName, $finalAudio);

            // Optional: store relative path in DB
            $audioPath = config('app.front_url')."/backend/public/audio/$year/$month/$day/" . $fileName;
            // Storage::disk('public')->put('news_audio/news_'.$news->id.'.mp3', $finalAudio);
        }
        foreach (explode(',', $concatenatedValue) as $catid) {
            if (is_numeric($catid)) {
                Newscategory::create([
                    'news_id' => $news->id,
                    'category_id' => (int) $catid,
                ]);
            }
        }

        if ($request->is_live_news == 1 && !empty($request->live_titles)) {
            foreach ($request->live_titles as $index => $title) {
                if (!empty($title)) {
                    Livenews::create([
                        'livetitle' => $title,
                        'livedesc' => $request->live_contents[$index] ?? '',
                        'live_new_story' => isset($request->live_new_story[$index]) && $request->live_new_story[$index] == 1 ? 1 : 0,
                        'news_id' => $news->id,
                        'user_id' => $userID,
                        'livenewsdate' => now()
                    ]);
                }
            }
        }

        $orderCatID = $eventCat ?: ($subcatID ?: $catID);
        if ($request->is_live_news != 1) {
            DB::table('news')
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->where('news_category.category_id', $orderCatID)
                ->where('news.catOrder', '!=', 0)
                ->where('news.catOrder', '>=', 1)
                ->where('news.id', '!=', $news->id)
                ->update(['news.catOrder' => DB::raw('news.catOrder + 1')]);
        }

        $news->update([
            'audio_path'=>$audioPath,
            'newsOrder' => $news->id,
            'catOrder' => 1,
            'usernews_userID' => $usernews_userID,
            'relatednewsid' => is_array($request->relatednewsid) ? implode(',', $request->relatednewsid) : $request->relatednewsid,
        ]);

        $lastCatOrderId = DB::table('news')
            ->join('news_category', 'news.id', '=', 'news_category.news_id')
            ->where('news_category.category_id', $orderCatID)
            ->where('news.id', '!=', $news->id)
            ->orderByDesc('news.catOrder')
            ->limit(1)
            ->value('news.id');

        if ($lastCatOrderId) {
            News::where('id', $lastCatOrderId)->update(['catOrder' => 0]);
        }

        if (!empty($usernews_userID)) {
            Usernews::where('id', $usernews_userID)->update(['status' => 'Active']);
        }

        $catslug = DB::table('news_category as nc')
            ->join('categories as c', 'nc.category_id', '=', 'c.id')
            ->where('nc.news_id', $news->id)
            ->orderBy('c.id')
            ->pluck('c.slug')
            ->implode('/');

        $type = ($catID == '9') ? 'Videos' : 'News';
        $baseUrl = ($type == 'Videos') ? 'https://www.gstv.in/' : 'https://www.gstv.in/news/';
        $url = $baseUrl . $catslug . '/' . $slug;
		
      	$catArray = explode('/', $catslug);

        $newsUrl = '';
        if (count($catArray) === 1) {
            $newsUrl = 'https://www.gstv.in/category/' . $catArray[0];
        } 

        $categoryUrl = 'https://www.gstv.in/category/'.$catslug;
        $urlsToPurge = [$newsUrl, $categoryUrl];

        $this->cloudflareService->clearCachenewscategory($urlsToPurge);
      	$catSlugredis = (strpos($catslug, '/') !== false)
        ? explode('/', $catslug)[1]
        : $catslug;

        $catKey = "category_slug_" . $catSlugredis . config('app.webtype');
        Redis::del($catKey);
        if($subcatID == ""){
            $newsKey = 'news_page_' . $catID . '_1' .  config('app.webtype');
        } else {
            $newsKey = 'news_page_' . $subcatID . '_1' .  config('app.webtype');
        }
        Redis::del($newsKey);
      
        $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
        $newFeatureImage = ($videoURL)
            ? preg_replace("/\.$fileExtension$/i", "_video.gif", $videoURL)
            : preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $featureImage);

        if ($request->has('notification') && $status == 'Active') {
            $this->firebaseService->sendNotification(
                $request->title,
                Str::limit(strip_tags($description), 120, '...'),
                'news',
                $type,
                $newFeatureImage,
                $url,
                $slug
            );
        }

        Log::create([
            'userID' => Auth::id(),
            'message' => 'News added: ' . $request->title,
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
		//Redis::flushall();
        //$this->cloudflareService->clearCacheall(); 
        return redirect('/news/')->with('success', 'News has been added');
    }


    // Update Category
public function edit($id, Request $request)
{
    $news = News::findOrFail($id);
    $categoryIds = DB::table('news_category')
        ->where('news_id', $id)
        ->pluck('category_id') // returns a collection of just the category_id values
        ->toArray();
    // Related News
    $relatedNewsIds = array_filter(explode(',', $news->relatednewsid));
    $relatedNews = $relatedNewsIds ? News::whereIn('id', $relatedNewsIds)->get() : collect();

    // Logged-in user and category access
    $user = Auth::user();
    $userCatIDs = array_filter(explode(',', $user->catID));

    // Parent Categories (eventCat = 0)
    $category = DB::table('categories')
        ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
        ->select(
            'categories.id', 'categories.title', 'categories.parentID',
            'children.id as child_id', 'children.title as child_name'
        )
        ->where('categories.status', 'Active')
        ->where('categories.eventCat', 0)
        ->orderBy('categories.catOrder', 'ASC')
        ->get()
        ->unique('id');

    // Event Categories (eventCat = 1)
    $eventcategory = DB::table('categories')
        ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
        ->select(
            'categories.id', 'categories.title', 'categories.parentID',
            'children.id as child_id', 'children.title as child_name'
        )
        ->where('categories.status', 'Active')
        ->where('categories.eventCat', 1)
        ->orderBy('categories.catOrder', 'ASC')
        ->get()
        ->unique('id');
     // Subcategories
    $subcategory = DB::table('categories')
        ->where('status', 'Active')
        ->whereIn('parentID', array_filter($categoryIds))
        ->orderBy('catOrder', 'ASC')
        ->get();

    // Feature images & videos
    $featureImages = array_filter(explode(',', $news->featureImage));
    $videoURL = $news->videoURL ?? '';

    $selectedMedia = $featureImages
        ? DB::table('medias')->whereIn('fullpath', $featureImages)->orderBy('id', 'DESC')->get()
        : collect();

    $selectedvideoMedia = $videoURL
        ? DB::table('medias')->where('fullpath', $videoURL)->orderBy('id', 'DESC')->get()
        : collect();

    // Pagination config
    $num = 12 - count($featureImages);
    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', $num);

    // Media library
    $media = DB::table('medias')
        ->where(function ($query) {
            $query->where('photos', 'like', '%.jpg')
                ->orWhere('photos', 'like', '%.jpeg')
                ->orWhere('photos', 'like', '%.png')
                ->orWhere('photos', 'like', '%.webp')
                ->orWhere('photos', 'like', '%.gif');
        })
        ->when($featureImages, function ($q) use ($featureImages) {
            $q->whereNotIn('fullpath', $featureImages);
        })
        ->orderBy('id', 'DESC')
        ->paginate($perPage, ['*'], 'page', $page);

    $videomedia = DB::table('medias')
        ->where('photos', 'like', '%.mp4')
        ->orderBy('id', 'DESC')
        ->paginate($perPage, ['*'], 'page', $page);

    // Live news
    $livenews = DB::table('livenews')
        ->where('news_id', $id)
        ->orderBy('lid', 'DESC')
        ->get();

    // Users list (Active only) - Can be cached or limited by roles
    $users = DB::table('users')->where('status', 'Active')->get();
    $newssourceList = DB::table('tags')->where('status', 'Active')->get();
    // Prepare data
    $data = [
        'media' => $media,
        'videomedia' => $videomedia,
        'selectedMedia' => $selectedMedia,
        'selectedvideoMedia' => $selectedvideoMedia,
        'category' => $category,
        'subcategory' => $subcategory,
        'eventcategory' => $eventcategory,
        'relatedNews' => $relatedNews,
        'news' => $news,
        'categoryIds'=>$categoryIds,
        'user' => $users,
        'newssourceList'=>$newssourceList,
        'livenews' => $livenews,
    ];

    return view('news.edit', compact('data'));
}

    
	public function editusernews($id, Request $request)
    {
        $news = Usernews::find($id); 
        $user = Auth::user();

        $ids = explode(',', $user->catID);
        $category = DB::table('categories')
                    ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
                    ->select('categories.id', 'categories.title', 'categories.parentID', 'children.id as child_id', 'children.title as child_name')
                    ->where('categories.status', 'Active')
          			->where('categories.eventCat', 0)
                    ->orderBy('categories.catOrder','ASC')
                    ->get();
        $uniqueParentCategories = $category->unique('id');
        $user = DB::table('users')->where('status','Active')->get();
      
        $eventcategory = DB::table('categories')
                    ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
                    ->select('categories.id', 'categories.title', 'categories.parentID', 'children.id as child_id', 'children.title as child_name')
                    ->where('categories.status', 'Active')
                    ->where('categories.eventCat', 1)
                    ->orderBy('categories.catOrder','ASC')
                    ->get();

        $value = $request->query('page');
        $pageNumber = Route::current()->parameter('pageNumber'); 
        $perPage = $request->input('per_page', 12); // Default to 10 items per page
        $page = $request->input('page', $pageNumber);
         
        $media = DB::table('medias')->where('photos', 'LIKE', '%.jpg')->orWhere('photos', 'LIKE', '%.jpeg')->orWhere('photos', 'LIKE', '%.png')->orWhere('photos', 'LIKE', '%.webp')->orWhere('photos', 'LIKE', '%.gif')->orderBy('id','DESC')->paginate($perPage, ['*'], 'page', $page);
        $videomedia = DB::table('medias')->where('photos', 'LIKE', '%.mp4')->orderBy('id','DESC')->paginate($perPage, ['*'], 'page', $page);;
        $data =  array();
        $data['media'] =  $media;
        $data['news'] =  $news;
        $data['videomedia'] =  $videomedia;
        $data['selectedMedia'] =  [];
        $data['selectedvideoMedia'] = [];
        $data['category'] =  $uniqueParentCategories;
        $data['eventcategory'] =  $eventcategory;
        $data['user'] =  $user;
        return view('news.add', compact('data'));
    }
 
 
    public function update(Request $request)
    {
        $news = News::findOrFail($request->nid);

        // Validation rules
        $rules = [
            'title' => 'required',
            'englishTitle' => [
                'required',
                Rule::unique('news', 'englishTitle')->ignore($news->id),
            ],
            'catID' => 'required',
            'newssource'=>'required',
        ];

        $customMessages = [
            'title.required' => 'The title field is required.',
            'englishTitle.required' => 'The english title field is required.',
            'englishTitle.unique' => 'The English Title is already in use. Please choose a different title.',
            'catID.required' => 'At least one category must be selected.',
            'newssource.required'=>'News Source must be selected.',
        ];

        $request->validate($rules, $customMessages);

        // Handle flags
        $is_home = $request->get('is_home') == 1 ? 1 : 0;
        if($request->is_live_news == 1){
            $is_home = 0;
        }
        $is_live_news = $request->boolean('is_live_news') ? 1 : 0;
        $related_news = $request->boolean('related_news') ? 1 : 0;
        $is_jacket = $request->boolean('is_jacket') ? 1 : 0;

        // Tags
        $tags = $request->has('selectedTags') ? implode(',', $request->get('selectedTags')) : '';
		$catID = $request->catID;
        $subcatID = $request->subcatID;
        $eventCat = $request->eventCat;
        // Concatenate category IDs
        $concatenatedValue = $request->get('catID');
        if (!empty($request->get('subcatID'))) {
            $concatenatedValue .= ',' . $request->get('subcatID');
        }
        if (!empty($request->get('eventCat'))) {
            $concatenatedValue .= ',' . $request->get('eventCat');
        }

        // Clear Redis cache
        Redis::del('news_category_' . $request->get('catID'));
        Redis::del('news' . $request->get('catID'));
        for ($i = 1; $i <= 5; $i++) {
            Redis::del('videolist' . $i);
        }
        if ($request->get('inshorts') == 1) {
            Redis::del('topnews_inshorts');
        }

        // Meta description generation
        $wordCount = Str::length($request->get('englishTitle'));
        $plainText = strip_tags($request->get('description'));
        $remainingword = 155 - $wordCount;
        $limitedText = Str::limit($plainText, $remainingword, '');
        $metadesc = $request->get('englishTitle') . ' | ' . $limitedText;

        // Vertical video flag
        // $is_vertical_video = $request->has('is_vertical_video') ? 9 : 0;
        $is_video_horizontal = $request->has('is_video_horizontal') ? 1 : 0;

        // Update created_at if status was previously Inactive
        if ($news->status == "Inactive") {
            $news->created_at = now();
        }

       
        $hide_title = 0;
        if($request->get('hide_title')==1) { $hide_title = 1; }

        $video_webp = '';
        $videoURL =  $request->get('videoURL');
        $featureImage = $request->get('featureImage');
        $oldfeatureImage = $request->get('oldfeatureImage');
        $oldvideoURL = $request->get('oldvideoURL');
        $is_vertical_video = 0;

        if(($videoURL == "" || $videoURL == null) && $featureImage != "" && $featureImage != $oldfeatureImage){  
         
            $jacket_title_head = '';
            if($is_jacket == 1){
                $jacket_title_head = $request->get('jacket_title');
            }
            ProcessImagePreview::dispatch($featureImage,$catID,$jacket_title_head,$is_jacket);
        }        
        if($videoURL != ""){
            $is_vertical_video = 9;
            $webpURL = preg_replace('/\.mp4$/i', '_video.webp', $videoURL);
            $video_webp = $webpURL;
        }
        $voiceovertext = '';
        if($videoURL != "" && $oldvideoURL != $videoURL){ 
            $is_vertical_video = 9;
            // $webpURL = preg_replace('/\.mp4$/i', '_video.webp', $videoURL);
            // $video_webp = $webpURL;
            $jacket_title_head = '';
            if($is_jacket == 1){
                $jacket_title_head = $request->get('jacket_title');
                $voiceovertext = $request->get('voiceovertext');
            }
            ProcessVideoPreview::dispatch($videoURL,$is_vertical_video,$catID,$request->get('video_start'),$jacket_title_head,$is_jacket,$voiceovertext);
            if($featureImage == null || $featureImage == "" || $featureImage == "null" || $featureImage == "undefined"){
                $featureImage = null;
            }
 
        }
        $title = trim($request->get('title'));

        if (!empty($videoURL) && stripos($title, 'VIDEO:') !== 0) {
            $title = 'VIDEO: ' . $title;
        }
        if($catID == 149){
            $video_webp = $featureImage;
        }
        // Update news record
        $news->fill([
            'title' =>  $title,
            'englishTitle' => $request->get('englishTitle'),
            'slug' => Str::slug($request->get('englishTitle')),
            'userID' => $request->get('userID'),
            'assignUserID' => Auth::id(),
            // 'catID' => $concatenatedValue,
            'featureImage' => $featureImage,
            'imageURL' => '',
            'uploadVideo' => '',
            'scheduleDate' => $request->get('scheduleDate'),
            'related_news' => $related_news,
            'tags' => $tags,
            'newssource'=>$request->get('newssource'),
            'is_home' => $is_home,
            'inshorts' => $request->has('inshorts') ? 1 : 0,
            'notification' => $request->has('notification') ? 1 : 0,
            'is_breaking' => $request->has('is_breaking') ? 1 : 0,
          	'is_valid' => $request->has('is_valid') ? 1 : 0,
            'hide_title' => $hide_title,
            'status' => $request->get('status'),
            'videoURL' => $request->get('videoURL'),
            'video_webp' => $video_webp,
            'metatitle' => $request->get('englishTitle'),
            'metadesc' => $metadesc,
            'description' => $request->get('description'),
            'is_live_news' => $is_live_news,
            'is_vertical_video' => $is_vertical_video,
            'is_video_horizontal'=>$is_video_horizontal,
            'updated_at' => $news->getOriginal('updated_at'),
            'img_credit_txt' => $request->img_credit_txt,
            'is_jacket' => $request->has('is_jacket') ? 1 : 0,
            'jacket_title' =>$request->has('is_jacket') ? $request->get('jacket_title') : null,
            'voiceovertext'=>$request->has('is_jacket') ? $request->get('voiceovertext') : null,
            'video_start' => $request->filled('video_start') ? $request->get('video_start') : 10,
        ]);
        // Handle related news IDs
        $relatedNewsIds = $request->get('relatednewsid');
        $news->relatednewsid = is_array($relatedNewsIds) ? implode(',', $relatedNewsIds) : $relatedNewsIds;
		if($request->is_live_news == 1){
           $news->topnewsOrder = 0;
        }
       $news->audio_path = null;
       if($videoURL == null || $videoURL == ""){
            $text = strip_tags($request->get('description'));
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);

            $client = new TextToSpeechClient([
                'credentials' => storage_path('app/google-tts.json')
            ]);

            $chunks = [];
            $currentChunk = '';

            $words = preg_split('/\s+/', $text);

            foreach ($words as $word) {
                $testChunk = $currentChunk . ' ' . $word;

                if (strlen($testChunk) > 4500) { 
                    $chunks[] = $currentChunk;
                    $currentChunk = $word;
                } else {
                    $currentChunk = $testChunk;
                }
            }

            if (!empty($currentChunk)) {
                $chunks[] = $currentChunk;
            }

            $finalAudio = '';

            foreach ($chunks as $chunk) {

                $input = new \Google\Cloud\TextToSpeech\V1\SynthesisInput();
                $input->setText($chunk);

                $voice = new \Google\Cloud\TextToSpeech\V1\VoiceSelectionParams();
                $voice->setLanguageCode('gu-IN');

                $audioConfig = new \Google\Cloud\TextToSpeech\V1\AudioConfig();
                $audioConfig->setAudioEncoding(\Google\Cloud\TextToSpeech\V1\AudioEncoding::MP3);

                $response = $client->synthesizeSpeech($input, $voice, $audioConfig);

                $finalAudio .= $response->getAudioContent();
            }
            // Date folders
            $year  = date('Y');
            $month = date('m');
            $day   = date('d');

            // Destination path
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/audio/$year/$month/$day/";

            // Create folder if not exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // File name
            $fileName = "news_" . $news->id . ".mp3";

            // Save file
            file_put_contents($destinationPath . $fileName, $finalAudio);

            // Optional: store relative path in DB
            $audioPath = config('app.front_url')."/backend/public/audio/$year/$month/$day/" . $fileName;

            $news->audio_path = $audioPath;
            // Storage::disk('public')->put('news_audio/news_'.$news->id.'.mp3', $finalAudio);
        }
        $news->save();
        Newscategory::where('news_id', $request->nid)->delete();
        $catval = explode(',',$concatenatedValue);
        foreach ($catval as $catid) {
            if (is_numeric($catid)) {
                Newscategory::create([
                    'news_id'     => $request->nid,
                    'category_id' => (int) $catid,
                ]);
            }
        }

        // Handle Live News
        if ($is_live_news && $request->has('live_titles')) {
            foreach ($request->get('live_lid') as $key => $lid) {
                $title = $request->get('live_titles')[$key] ?? null;
                $desc = $request->get('live_contents')[$key] ?? '';
                if (!empty($lid)) {
                    if (!empty($title)) {
                        Livenews::where('lid', $lid)->update([
                            'livetitle' => $title,
                            'livedesc' => $desc,
                            'live_new_story'=>isset($request->live_new_story[$key]) && $request->live_new_story[$key] == 1 ? 1 : 0,
                            'news_id' => $news->id,
                            'user_id' => $request->get('userID'),
                            'livenewsdate' => now()
                        ]);
                    } else {
                        Livenews::where('lid', $lid)->delete();
                    }
                } else {
                    Livenews::create([
                        'livetitle' => $title,
                        'livedesc' => $desc,
                        'news_id' => $news->id,
                        'user_id' => $request->get('userID'),
                    ]);
                }
            }
        }

        // Get category slugs
        // $catslug = DB::table('news')
        //     ->selectRaw("GROUP_CONCAT(categories.slug SEPARATOR '/') AS categorySlugs")
        //     ->join('categories', function ($join) {
        //         $join->whereRaw("FIND_IN_SET(categories.id, news.catID)");
        //     })
        //     ->where('news.id', $news->id)
        //     ->first();
        $catslug = DB::table('news')
            ->join('news_category', 'news.id', '=', 'news_category.news_id')
            ->join('categories', 'news_category.category_id', '=', 'categories.id')
            ->where('news.id', $news->id)
            ->selectRaw("GROUP_CONCAT(categories.slug ORDER BY categories.id SEPARATOR '/') AS categorySlugs")
            ->first();


        // Notification details
        $type = ($request->get('catID') == '9') ? 'Videos' : 'News';
        $fronturl = config('app.front_url'); // Example: https://www.gstv.in
        $slug = Str::slug($request->get('englishTitle'));
        $url = $type == 'Videos'
            ? "https://www.gstv.in/{$catslug->categorySlugs}/" . Str::slug($request->get('englishTitle'))
            : "https://www.gstv.in/news/{$catslug->categorySlugs}/" . Str::slug($request->get('englishTitle'));
      
		$newsUrl = $type === 'Videos'
        ? "https://www.gstv.in/{$catslug->categorySlugs}/" . Str::slug($request->get('englishTitle'))
        : "https://www.gstv.in/news/{$catslug->categorySlugs}/" . Str::slug($request->get('englishTitle'));

        $catArray = explode('/', $catslug->categorySlugs);
      	$firstcat = '';
        if (count($catArray) === 1) {
            $firstcat = 'https://www.gstv.in/category/' . $catArray[0];
        } 
        $categoryUrl = "https://www.gstv.in/category/{$catslug->categorySlugs}";
        $urlsToPurge = [$newsUrl, $categoryUrl, $firstcat];

        $this->cloudflareService->clearCachenewscategory($urlsToPurge); 
      
      	$firstPart = explode('/', $catslug->categorySlugs)[0];
        $firstKey = "category_slug_" . $firstPart  . config('app.webtype');
        Redis::del($firstKey);

        // If second part exists, remove that too
        if (strpos($catslug->categorySlugs, '/') !== false) {
            $secondPart = explode('/', $catslug->categorySlugs)[1];
            $secondKey = "category_slug_" . $secondPart . config('app.webtype');
            Redis::del($secondKey);
        }
        if($subcatID == ""){
            $newsKey = 'news_page_' . $catID . '_1' .  config('app.webtype');
        } else {
            $newsKey3 = 'news_page_' . $catID . '_1' .  config('app.webtype');
            Redis::del($newsKey3);
            $newsKey = 'news_page_' . $subcatID . '_1' .  config('app.webtype');
        }
        Redis::del($newsKey);
        $fullKey = "news_detail" . $slug . config('app.webtype');
        Redis::del($fullKey);
      
        // Feature image processing
        $featureImage = $type == 'Videos' ? $request->get('videoURL') : $request->get('featureImage');
        $extension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
        $newFeatureImage = $type == 'Videos'
            ? preg_replace("/\.$extension$/i", "_video.gif", $featureImage)
            : preg_replace("/\.$extension$/i", "_small.$extension", $featureImage);

        // Send Firebase Notification
        if ($request->has('notification') && $request->get('status') === "Active") {
            $tokens = DB::table('notification')->pluck('token');
            foreach ($tokens as $token) {
                $title = $request->get('title');
                $body = Str::limit(strip_tags($request->get('description')), 120, '...');
                $this->firebaseService->sendNotification($title, $body, $token, $type, $newFeatureImage, $url, $news->slug);
            }
        }
		//Redis::flushall();
        //$this->cloudflareService->clearCacheall();  
        return redirect('/news/edit/'.$request->nid)->with('success', 'News has been updated successfully.');
    }


    // Status Active/Inactive
    public function updateStatus()
{
    $id = Route::current()->parameter('id');
    $status = Route::current()->parameter('status');

    $news = News::find($id);

    if (!$news) {
        return redirect('/news')->with('error', 'News not found.');
    }

    $news->status = $status;

    if ($status === 'Active') {
        $news->created_at = now(); // Reset publish time if reactivating
    }

    $news->save();

    

    // Ensure at least 10 home news are active
    $newshomeCount = DB::table('news')
        ->where('status', 'Active')
        ->where('is_home', 1)
        ->count();

    if ($newshomeCount < 10) {
         $replacement = DB::table('news')
            ->join('news_category', 'news.id', '=', 'news_category.news_id')
            ->where('news.status', 'Active')
            ->where('news.is_home', 0)
            ->where('news_category.category_id', '!=', 9)
            ->orderByDesc('news.newsOrder')
            ->select('news.*') // ensure only news columns
            ->first();

        if ($replacement) {
            DB::table('news')
                ->where('id', $replacement->id)
                ->update(['is_home' => 1]);
        }
    }

    // Logging user action
    Log::create([
        'userID'   => Auth::id(),
        'message'  => "News published/Unpublished: {$news->title}",
        'action'   => $status,
        'contents' => json_encode($news->title),
    ]);

    return redirect('/news')->with('success', 'News status updated successfully.');
}

    public function updateStatus_old()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $news = News::find($id); 
        $newtitle = $news->title;
        $news->status = $status;
        if($status == "Active"){
            $news->created_at = now();	
        }
        $news->update();
      	//$this->cloudflareService->clearCache();
      
      	Redis::del('topvideos');
        Redis::del('topnews');
        
        for($i=1;$i<=110;$i++){
            Redis::del('videolist'.$i);
            Redis::del('news_category_'.$i);
            Redis::del('news'.$i);
        }       
        if($news->inshorts == 1){ 
            Redis::del('topnews_inshorts');
        }
      
      	$newshome = DB::table('news')->where('status','Active')->where('is_home','1')->count();

        if ($newshome < 10) {
            $newshomes = DB::table('news')->where('status', 'Active')->where('is_home', '0')->where('catID', '!=', '9')->latest('newsOrder')->first();
            DB::table('news')->where('id', $newshomes->id)->update(['is_home' => 1]);
        }      	

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'News published/Unpublished: ' . $newtitle,
            'action' => $status,
            'contents' => json_encode($newtitle),
        ]);
        return redirect('/news/')->with('success', 'News Status has been updated successfully');
    }

    // Dalete the page
    public function destroy($id)
    {
        $id = Route::current()->parameter('id');
        $news = News::find($id); 
        $newtitle = $news->title;
        $featureImage = $news->featureImage;
        $destinationPath = public_path('/uploads/news/images/'); // upload path
        if ($featureImage !== null && $featureImage !== '' && Storage::exists($destinationPath.$featureImage))
        {
            unlink($destinationPath.$featureImage);
        }
        $uploadVideo = $news->uploadVideo;
        $destinationPath = public_path('/uploads/news/videos/'); // upload path
        if ($uploadVideo !== null && $uploadVideo !== '' && Storage::exists($destinationPath.$uploadVideo))
        {
            unlink($destinationPath.$uploadVideo);
        }


        $news->delete();
      
      	

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'News deleted: ' . $newtitle,
            'action' => 'Delete',
            'contents' => json_encode($newtitle),
        ]);
        return redirect('/news/')->with('success', 'News has been deleted successfully');
    }

    public function taginserts(Request $request)
    {
        $query = $request->get('q');
	   if ($request->get('tag')=='tags') {
            $newTag = Tag::create([
                'tagsEnglishTitle' => $query,
                'userID' => Auth::user()->id, 
                'slug' => \Str::slug($query),
                'status' => 'Active'
            ]);
	        $tags = collect([$newTag]);
        }
	    $formattedTags = $tags->map(function ($tag) {
            return [
                'id' => $tag->slug,          
                'text' => $tag->tagsEnglishTitle, 
            ];
        });

        return response()->json($formattedTags);
    }
    // ── Auto-translate Gujarati title to English ──────────────────────────────
    public function translateTitle_google(Request $request)
    {
        $text = trim($request->get('text', ''));
        if (empty($text)) {
            return response()->json(['translated' => '']);
        }

        try {
            // Use Google Translate free endpoint
            $url      = 'https://translate.googleapis.com/translate_a/single'
                      . '?client=gtx&sl=gu&tl=en&dt=t&q=' . urlencode($text);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0',
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            if (!$response) {
                return response()->json(['translated' => '', 'error' => 'No response']);
            }

            $data = json_decode($response, true);

            // Google returns nested array: $data[0][n][0] = translated chunk
            $translated = '';
            if (isset($data[0]) && is_array($data[0])) {
                foreach ($data[0] as $chunk) {
                    if (isset($chunk[0])) {
                        $translated .= $chunk[0];
                    }
                }
            }

            return response()->json(['translated' => trim($translated)]);

        } catch (\Exception $e) {
            return response()->json(['translated' => '', 'error' => $e->getMessage()]);
        }
    }
    public function translateTitle(Request $request)
    {
        $text = trim($request->input('text', ''));

        if ($text === '') {
            return response()->json([
                'translated' => ''
            ]);
        }

        try {
            $email = "gstvwebapp@gmail.com";
            $url = 'https://api.mymemory.translated.net/get?' . http_build_query([
                'q'        => $text,
                'langpair' => 'gu|en',
                'mt'       => 1,
                'de' =>$email
            ]);

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT      => 'Mozilla/5.0',
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);

                return response()->json([
                    'translated' => '',
                    'error' => $error
                ], 500);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $data = json_decode($response, true);

            if ($httpCode !== 200) {
                return response()->json([
                    'translated' => '',
                    'error' => 'Translation API error',
                    'http_code' => $httpCode,
                    'response' => $data
                ], 500);
            }

            $translated = $data['responseData']['translatedText'] ?? '';

            return response()->json([
                'translated' => trim(html_entity_decode(
                    $translated,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                ))
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'translated' => '',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function tagSuggestions(Request $request)
    {
        $query = $request->get('q');
		if($query!='') {
        	$tags = Tag::where('tagsEnglishTitle', 'like', '%' . $query . '%')
                    ->where('status', 'Active')
                    ->get(['slug', 'tagsEnglishTitle']);
            $formattedTags = $tags->map(function ($tag) {
                return [
                    'id' => $tag->slug,           
                    'text' => $tag->tagsEnglishTitle, 
                ];
            });
        }
        return response()->json($formattedTags);
    }

    public function autosaveDraft(Request $request)
    {        
        $is_home = 0;
        if($request->get('is_home')==1) { $is_home = 1; }
        $related_news = 0;
        if($request->get('related_news')==1) { $related_news = 1;  }
        $tags = '';
        if($request->get('tags')!='')
        {
            $tags = implode(',', $request->get('tags'));
        }
        // $metakeyword = explode(',', $request->get('metakeyword'));
        $concatenatedValue = $request->get('catID');
        if (!empty($request->get('subcatID'))) {
            $concatenatedValue .= ',' . $request->get('subcatID');
        }
        if (!empty($request->get('eventCat'))) {
            $concatenatedValue .= ',' . $request->get('eventCat');
        }

        $wordCount = Str::length($request->get('englishTitle'));
        $plainText = strip_tags($request->get('description'));
        $remainingword = 155 - $wordCount; 
        $limitedText = Str::limit($plainText, $remainingword, '');       
        $metadesc = $request->get('englishTitle').' | '.$limitedText;
        
        if($request->get('catID') == ""){
            $concatenatedValue = 1;
        }
        $title = $request->get('title');
        if($request->get('title') == ""){
            $title = '';
        }
        $englishTitle = $request->get('englishTitle');
        if($request->get('englishTitle') == ""){
            $englishTitle = '';
        }
        if($request->get('autosaveid')!='') {
            $news = News::find($request->get('autosaveid'));
            $news->title = $title;
            $news->englishTitle = $englishTitle;
            $news->slug = Str::slug($request->get('englishTitle'), '-');
            $news->userID = $request->get('userID');
            $news->assignUserID = Auth::user()->id;
            $news->catID = $concatenatedValue;
            $news->featureImage = $request->get('featureImage');
            $news->imageURL = '';
            $news->uploadVideo = '';
            $news->scheduleDate = $request->get('scheduleDate');
            $news->related_news = $related_news;
            $news->tags = $tags;
            $news->status = 'Inactive';
            $news->videoURL = $request->get('videoURL');
            $news->metatitle = $request->get('englishTitle');
            // $news->metakeyword = implode(',', $metakeyword);
            $news->metadesc = $metadesc;
            $news->description = $request->get('description');
                    
            $news->update();
        
            Log::create([
                'userID'=> Auth::user()->id,
                'message' => 'News Auto Save updated: ' . $request->get('title'),
                'action' => 'Auto Save Edit',
                'contents' => json_encode($request->all()),
            ]);

            return response()->json($request->get('autosaveid'));
        } else {
            $news = new News([
                'title' => $title,
                'englishTitle' => $englishTitle,
                'slug' => Str::slug($request->get('englishTitle'), '-'),
                'userID' => $request->get('userID'),
                'assignUserID' => Auth::user()->id,
                'catID' => $concatenatedValue,
                'featureImage' => $request->get('featureImage'),
                'imageURL' => '',
                'uploadVideo' => '',
                'scheduleDate' => $request->get('scheduleDate'),
                'related_news' => $related_news,
                'tags' => $tags,
                'videoURL' => $request->get('videoURL'),
                'metatitle' => $request->get('englishTitle'),
                'metakeyword' => implode(',', $metakeyword),
                'metadesc' => $metadesc,
                'status' => 'Inactive',
                'description' => $request->get('description'),
            ]);
            $news->save();
            Log::create([
                'userID'=> Auth::user()->id,
                'message' => 'News Auto Save added: ' . $request->get('title'),
                'action' => 'Auto Save Add',
                'contents' => json_encode($request->all()),
            ]);
            return response()->json($news->id);
        }
    } 

    public function subCategory(Request $request)
    {
        $category = DB::table('categories')->where('parentID',$request->option)->where('status','Active')->orderBy('catOrder','ASC')->get();
        return response()->json($category);
    }

    

    public function newsorder1(Request $request)
    {
       
        $newOrder = $request->input('newOrder');
        $oldOrder = $request->input('oldOrder');
        $tabletop = $request->input('tabletop');
        $pageNum = $request->input('pageNum');
        $newliveOrder = $request->input('newliveOrder');
        $entriesPerPage = $request->input('entriesPerPage');

        // Clear Redis cache
        Redis::del('topnews');

        try {
            // Start transaction
            DB::beginTransaction();
           
            if ($pageNum == 1) {
                if ($tabletop=='') {
                    $cnt = 0;
                    $cntloop = 9;
                    $cntotherloop = 10;
                    $k = 0;
                    $cntlive = 0;
                    // print_r($newliveOrder); exit;
                    foreach($newliveOrder as $live){
                        if($live==1)
                        {
                            $cntlive++;
                        }
                    }
                    // echo $cntlive;
                    if($cntlive > 0)
                    {
                        $cnt = $cntlive;
                        $cntloop = 10 + $cntlive - 1;
                        $cntotherloop = 11+ $cntlive - 1;
                    }
                    for ($i = $cnt; $i <= $cntloop; $i++) {
                       
                        $news = News::where('id', $newOrder[$i])->where('is_live_news', '!=', 1)->first();
                       // $news = News::find($newOrder[$i]);
                        if ($news) {
                            $news->topnewsOrder = $k + 1;
                            $news->is_home = 1;
                            $news->catOrder = 0;
                            $news->timestamps = false; 
                            $news->update();
                        } else {
                            // Handle if news is not found (optional logging)
                        }
                        $k++;
                    }
                  	for ($i = $cntotherloop; $i <= 19; $i++) {
                        $newso = News::where('id', $newOrder[$i])->where('is_live_news', '!=', 1)->first();
                     // $newso = News::find($newOrder[$i]);
                      if ($newso) {
                        $newso->topnewsOrder = 0;
                        $newso->is_home = 0;
                        $newso->timestamps = false;
                        $newso->update(); 
                      } else {
                            // Handle if news is not found (optional logging)
                      }
                    }
                }
                
                if ($tabletop!='' && $pageNum == 1) {
                   
                    for ($i = 0; $i <= 3; $i++) {
                       
                        News::withoutTimestamps(function () use ($newOrder, $i, $tabletop) {
                            DB::table('news')
                                ->where('id', $newOrder[$i])
                                ->whereRaw('FIND_IN_SET(?, catID) > 0', [$tabletop])
                                ->update([
                                    'catOrder' => $i + 1,
                                    'created_at' => now()
                                ]);
                        });
                    }
                  	for ($i = 4; $i <= 19; $i++) {
                      
                      $newscat = News::find($newOrder[$i]);
                      if ($newscat) {
                        $newscat->catOrder = 0;
                        $newscat->timestamps = false;
                      	$newscat->update(); 
                      } else {
                            // Handle if news is not found (optional logging)
                      }
                    }
                }
            }

            // Commit transaction if everything is successful
            DB::commit();

            return response()->json(['message' => 'Order updated successfully']);
        } catch (\Exception $e) {
            // Rollback transaction if there is an error
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Error updating news order: ' . $e->getMessage());

            // Return an error response
            return response()->json(['message' => 'Failed to update order. Please try again later.'], 500);
        }
    }
 
public function newsorder(Request $request)
{
    $newOrder = $request->input('newOrder');
    $oldOrder = $request->input('oldOrder');
    $tabletop = $request->input('tabletop');
    $pageNum = $request->input('pageNum');
    $newliveOrder = $request->input('newliveOrder');
    $entriesPerPage = $request->input('entriesPerPage');
	$catID = $request->input('catID');
    
    // Clear Redis cache
    Redis::del('topnews');

    try {
        // Start transaction
        DB::beginTransaction();

        if ($pageNum == 1) {
            if ($tabletop == '') {
                $cnt = 0;
                $cntloop = 9;
                $cntotherloop = 10;
                $k = 0;
                $cntlive = 0;

                // Count live news
                foreach ($newliveOrder as $live) {
                    if ($live == 1) {
                        $cntlive++;
                    }
                }

                // Adjust loop limits if live news exist
                if ($cntlive > 0) {
                    $cnt = $cntlive;
                    $cntloop = 10 + $cntlive - 1;
                    $cntotherloop = 11 + $cntlive - 1;
                }

                // Fetch current updated_at values
                $newsTimestamps = DB::table('news')
                    ->whereIn('id', $newOrder)
                    ->pluck('updated_at', 'id');

                // Bulk update to reduce queries
                $updateData = [];
                for ($i = $cnt; $i <= $cntloop; $i++) {
                    $updateData[] = [
                        'id' => $newOrder[$i],
                        'topnewsOrder' => $k + 1,
                        'is_home' => 1,
                        'catOrder' => 0,
                        'userID' => auth()->id() ?? 1,
                        'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                    ];
                    $k++;
                }

                // Update news using upsert
                DB::table('news')->upsert($updateData, ['id'], ['topnewsOrder', 'is_home', 'catOrder', 'updated_at']);

                $userName = Auth::user()->name ?? 'System';
                $dateTime = now()->format('d-m-Y h:i A');

                // Get news titles
                $newsTitles = DB::table('news')
                    ->whereIn('id', array_column($updateData, 'id'))
                    ->pluck('title', 'id');

                // ===============================
                // WRITE LOG AT TOP (PREPEND)
                // ===============================

                $logFile = storage_path('logs/newsorder-' . date('Y-m-d') . '.log');

                // Build the log entry
                $logEntry  = "===================================\n";
                $logEntry .= $dateTime . " (" . $userName . ")\n\n";

                foreach ($updateData as $news) {
                    $order = $news['topnewsOrder'];
                    $title = $newsTitles[$news['id']] ?? 'Unknown Title';
                    $logEntry .= $order . " - " . $title . "\n";
                }

                $logEntry .= "\n\n";

                // PREPEND: Add new entry at the TOP of the file
                if (file_exists($logFile)) {
                    // Read existing content
                    $oldContent = file_get_contents($logFile);
                    // Write new entry FIRST, then old content
                    file_put_contents($logFile, $logEntry . $oldContent, LOCK_EX);
                } else {
                    // Create new file with the entry
                    file_put_contents($logFile, $logEntry, LOCK_EX);
                }
                
                // Also log to database for tracking
                \App\Models\Log::create([
                    'userID' => Auth::user()->id,
                    'message' => 'News Order Change ID wise',
                    'action' => 'News Order',
                    'contents' => json_encode($updateData),
                ]);

                // Reset news that are not in the top order
                $resetData = [];
                for ($i = $cntotherloop; $i <= 19; $i++) {
                    $resetData[] = [
                        'id' => $newOrder[$i],
                        'topnewsOrder' => 0,
                        'is_home' => 0,
                        'userID' => auth()->id() ?? 1,
                        'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                    ];
                }

                // Bulk update reset data
                DB::table('news')->upsert($resetData, ['id'], ['topnewsOrder', 'is_home', 'updated_at']);
            }

            // Category-specific order update
            if ($tabletop != '' && $pageNum == 1) { echo 1;exit;
                // Fetch current updated_at values if not already fetched
                if (!isset($newsTimestamps)) {
                    $newsTimestamps = DB::table('news')
                        ->whereIn('id', $newOrder)
                        ->pluck('updated_at', 'id');
                }
                
                $categoryUpdateData = [];
                for ($i = 0; $i <= 3; $i++) {
                    $categoryUpdateData[] = [
                        'id' => $newOrder[$i],
                        'catOrder' => $i + 1,
                        'userID' => auth()->id() ?? 1,
                        'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                    ];
                }

                // Bulk update for category news order
                DB::table('news')->upsert($categoryUpdateData, ['id'], ['catOrder', 'updated_at']);

                // Reset remaining category orders
                $categoryResetData = [];
                for ($i = 4; $i <= 19; $i++) {
                    $categoryResetData[] = [
                        'id' => $newOrder[$i],
                        'catOrder' => 0,
                        'userID' => auth()->id() ?? 1,
                        'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                    ];
                }

                DB::table('news')->upsert($categoryResetData, ['id'], ['catOrder', 'updated_at']);
            }
        }

        // Commit transaction
        DB::commit();

        return response()->json(['message' => 'Order updated successfully']);
    } catch (\Exception $e) {
        // Rollback transaction in case of error
        DB::rollBack();

        // Log error
        \Log::error('Error updating news order: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'message' => 'Failed to update order. Please check the logs.',
            'error' => $e->getMessage()
        ], 500);
    }
}
   

    public function tophome(Request $request)
    {
        $newID = $request->input('newID');
        $homeset = $request->input('is_home');
        $is_home = $homeset === 'true' ? 1 : 0;
        $news = News::find($newID);
        $news->is_home = $is_home;
        $news->update();           
        return response()->json(['message' => 'Top Stories set successfully']);
    }

    public function cloudflarecache()
    {
        Redis::flushall();
        $this->cloudflareService->clearCacheall();   
        return redirect()->back()->with('status', 'Cache Cleared!');
    }
    public function PublishedNews(Request $request)
    {
        $userID = $request->get('userID');
        $catID = $request->get('catID');
        $title = $request->get('title');
        $data =  array();
        $publish = DB::table('news')->whereRaw('NOT FIND_IN_SET(9, news.catID)')->where('news.status', 'Active')->count();

       $unpublish = DB::table('news')->where('news.status', 'Inactive')->count();

       $schedule = DB::table('news')->where('news.status', 'Schedule')->count();
       $videos = DB::table('news')->whereRaw('FIND_IN_SET(9, news.catID)')->count();
       $draft =  DB::table('news')->where('news.status', 'Draft')->count();
       $users = DB::table('users')->where('status', 'Active')->get();
       $category = DB::table('categories')->where('status', 'Active')->get();
       $data = [
       
        'publish' => $publish,
        'unpublish' => $unpublish,
        'schedule' => $schedule,
        'draft' => $draft, 
        'videos'=>$videos,
        'users' => $users, 
        'category'=>$category,
        'title'=>$title,
        'userID'=>$userID,
        'catID'=>$catID
    ];

        // Return the Blade view where the DataTable is displayed
        return view('news.allpublish', compact('data'));
    }
    public function fetchData_old(Request $request)
{
    // Column index → actual DB column for ORDER BY
    // 0=No.(id), 1=title, 2=categoryNames, 3=viewer(view_count), 4=authorName, 5=created_at, 6=action
    $columnMap = [
        0 => 'news.created_at',          // No. — default to date
        1 => 'news.title',
        2 => 'news.created_at',          // categoryNames not sortable in DB
        3 => 'newscount.view_count',     // Count column → sort by DB view_count
        4 => 'u1.name',
        5 => 'news.created_at',
        6 => 'news.created_at',          // action not sortable
    ];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20);
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = (int) $request->input('order.0.column', 5);
    $orderDirection = strtoupper($request->input('order.0.dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

    $orderColumn = $columnMap[$orderColumnIndex] ?? 'news.created_at';

    $filters = $request->only(['title', 'userID', 'catID', 'start_date', 'end_date', 'status']);
    $status = $filters['status'] ?? 'Active';
    if ($status === 'video') $status = 'Active';

    // Base query
     $query = DB::table('news')
        ->select(
            'news.id', 'news.title', 'news.viewer', 'news.userID', 'news.viewer_app',
            'news.created_at', 'news.updated_at', 'news.status', 'news.newsOrder', 'news.is_breaking',
            'news.is_live_news', 'news.slug',
            'u1.name as authorName',
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            'newscount.view_count as total_view_count'
        )
        ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
        ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
        ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
        ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id') // <-- JOIN newscount
        ->groupBy('news.id');

    // 🔒 Always restrict to last 10 days
    $query->where('news.created_at', '>=', Carbon::now()->subDays(60));

    // Handle "video" as category_id = 9
    $isVideo = $request->input('status') === "video";

    if ($isVideo) {
        $query->whereIn('news.id', function ($q) {
            $q->select('news_id')->from('news_category')->where('category_id', 9);
        });
    } else {
        $query->where('news.status', $status)
              ->whereNotIn('news.id', function ($q) {
                  $q->select('news_id')->from('news_category')->where('category_id', 9);
              });
    }

    // 🔍 Search filters
    if ($searchValue) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('news.title', 'like', "%$searchValue%")
              ->orWhere('u1.name', 'like', "%$searchValue%")
              ->orWhere('categories.title', 'like', "%$searchValue%");
        });
    }

    if (!empty($filters['title'])) {
        $query->where('news.title', 'like', "%{$filters['title']}%");
    }

    if (!empty($filters['userID'])) {
        $query->where('news.userID', $filters['userID']);
    }

    // ✅ Removed: else block that overwrote 10-day date filter
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
       $query->whereBetween('news.created_at', ["{$filters['start_date']} 00:00:00", "{$filters['end_date']} 23:59:59"]);
    }

    if (!empty($filters['catID']) && $filters['catID'] != 9) {
        $query->where('news_category.category_id', $filters['catID'])
              ->where('news.is_home', '!=', 1);
    }

    // 🔄 Correct filtered count using subquery (for GROUP BY)
    $filteredRecords = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings($query)
        ->count();

    // 🔄 Total count for last 10 days
    $totalRecords = DB::table('news')
        ->where('news.created_at', '>=', Carbon::now()->subDays(10))
        ->count();

    $sortByCount = ($orderColumn === 'newscount.view_count');

    // 🌐 Format
    $baseUrl    = config('app.front_url');
    $backendUrl = config('app.backend_url');

    // ── Fetch GA data — initialize all vars first so they're always defined ──
    $pageViews       = [];
    $titleViews      = [];
    $pathTitleMap    = [];
    $pageViews365    = [];
    $pageViews30     = [];
    $pathTitleMap365 = [];
    $pathTitleMap30  = [];
    $titleToAppCount = [];

    try {
        $pageViews365    = Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
        $titleViews365   = Cache::remember('ga_title_views_365days',    43200, fn() => $this->analyticsService->getPageViewsByTitle(365));
        $pathTitleMap365 = Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
        $pageViews30     = Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
        $titleViews30    = Cache::remember('ga_title_views_30days',     3600,  fn() => $this->analyticsService->getPageViewsByTitle(30));
        $pathTitleMap30  = Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

        // Merge page views (use higher count per path)
        $pageViews = $pageViews365;
        foreach ($pageViews30 as $path => $count) {
            $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $count) : $count;
        }

        $titleViews = $titleViews365;
        foreach ($titleViews30 as $title => $count) {
            $titleViews[$title] = isset($titleViews[$title]) ? max($titleViews[$title], $count) : $count;
        }

        $pathTitleMap = $pathTitleMap365;
        foreach ($pathTitleMap30 as $path => $title) {
            $pathTitleMap[$path] = $title;
        }

        // ── Build title → app GA count map ONCE (O(n) instead of O(n×m) per row) ──
        // Collect all app paths (GSTV/*) with their counts
        $appPathCounts = [];
        foreach ($pathTitleMap as $path => $title) {
            if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                if (!isset($appPathCounts[$title])) {
                    $appPathCounts[$title] = 0;
                }
                $appPathCounts[$title] += $c;
            }
        }
        $titleToAppCount = $appPathCounts;

        \Log::info('GA data loaded. URLs: ' . count($pageViews) . ', App titles: ' . count($titleToAppCount));
    } catch (\Exception $e) {
        \Log::error('Google Analytics fetch failed: ' . $e->getMessage());
    }

    // ── Helper: compute total for one row using O(1) lookups ─────────────────
    $computeRowTotal = function ($row) use ($pageViews, $titleToAppCount) {
        $isVid  = Str::contains($row->categoryNames ?? '', 'Videos');
        $pp     = $isVid
            ? "/{$row->categorySlugs}/{$row->slug}"
            : "/news/{$row->categorySlugs}/{$row->slug}";
        $gaWeb  = $pageViews[$pp] ?? 0;
        $gaApp  = $titleToAppCount[$row->title] ?? 0;
        $dbCnt  = (int)($row->total_view_count ?? 0);
        return (int)$gaWeb + (int)$gaApp + $dbCnt;
    };

    // ── Ordering & Pagination ─────────────────────────────────────────────────
    if ($sortByCount) {
        // Two-phase approach: fetch lightweight rows, sort by GA+DB total in PHP,
        // then fetch only the page's full rows using FIELD() ordering.

        // Phase 1: fetch only the columns needed for scoring (id, slug, title, db_count)
        $lightQuery = clone $query;
        $lightRows  = $lightQuery->select(
            'news.id',
            'news.slug',
            'news.title',
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug  ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('IFNULL(newscount.view_count, 0) as total_view_count')
        )->get();

        // Phase 2: score each row with GA website + GA app + DB count
        $scored = $lightRows->map(function ($r) use ($pageViews, $titleToAppCount) {
            $isVid = Str::contains($r->categoryNames ?? '', 'Videos');
            $pp    = $isVid
                ? "/{$r->categorySlugs}/{$r->slug}"
                : "/news/{$r->categorySlugs}/{$r->slug}";
            $r->_total = (int)($pageViews[$pp] ?? 0)           // GA website
                       + (int)($titleToAppCount[$r->title] ?? 0) // GA app (keyed by title)
                       + (int)$r->total_view_count;              // DB count
            return $r;
        });

        // Phase 3: sort by total, slice for current page, get ordered IDs
        $sortedIds = $scored->sortBy(
            fn($r) => $r->_total,
            SORT_NUMERIC,
            $orderDirection === 'DESC'
        )->values()->slice($start, $length)->pluck('id')->toArray();

        if (empty($sortedIds)) {
            $data = collect();
        } else {
            // Phase 4: fetch full rows for only this page's IDs, preserve PHP sort order
            $idList = implode(',', array_map('intval', $sortedIds));
            $data   = $query->whereIn('news.id', $sortedIds)
                            ->orderByRaw("FIELD(news.id, {$idList})")
                            ->get();
        }
    } else {
        // Non-count columns: fast DB-level sort
        $query->orderByRaw(
            !empty($filters['catID'])
                ? "{$orderColumn} {$orderDirection}"
                : "CASE WHEN is_live_news = 1 THEN 0 WHEN topnewsOrder != 0 THEN 1 ELSE 2 END, topnewsOrder ASC, {$orderColumn} {$orderDirection}"
        );
        $data = $query->offset($start)->limit($length)->get();
    }

    foreach ($data as $index => $row) {
        $isVideoNews = Str::contains($row->categoryNames, 'Videos');

        $newsUrl = $isVideoNews
            ? "$baseUrl/{$row->categorySlugs}/{$row->slug}"
            : "$baseUrl/news/{$row->categorySlugs}/{$row->slug}";

        // Compute GA+DB total for display
        $totalViews = $computeRowTotal($row);

        $highlight = $row->is_live_news
            ? '<span style="color: red; font-weight: bold;">Live: </span>'
            : ($row->is_breaking
                ? '<span style="color: red; font-weight: bold;">Breaking: </span>'
                : '');

        $row->rowNumber  = $row->is_live_news ? '' : $start + $index + 1;
        $row->authorName = "<a href='$backendUrl/user/profile/{$row->userID}'>{$row->authorName}</a>";
        $createdDate     = Carbon::parse($row->created_at);

        if ($createdDate->isToday()) {
            $row->created_at = "<span style='color:red;'>" . $createdDate->format('d M Y h:i A') . "</span>";
        } else {
            $row->created_at = $createdDate->format('d M Y h:i A');
        }

        $row->viewer = number_format($totalViews);
        
        $row->title = $highlight . "<a href='$newsUrl' target='_blank'>" . htmlspecialchars($row->title) . "</a>";
        $row->action = $this->generateActionButtons($row);
    }

    return response()->json([
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data,
    ]);
}
  public function fetchData_latest(Request $request)
{
    // Column index → actual DB column for ORDER BY
    // 0=No.(id), 1=title, 2=categoryNames, 3=viewer(view_count), 4=source, 5=authorName, 6=created_at, 7=action
    $columnMap = [
        0 => 'news.created_at',          // No. — default to date
        1 => 'news.title',
        2 => 'news.created_at',          // categoryNames not sortable in DB
        3 => 'newscount.view_count',     // Count column → sort by DB view_count
        4 => 'news.created_at',          // source not sortable
        5 => 'u1.name',
        6 => 'news.created_at',
        7 => 'news.created_at',          // action not sortable
    ];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20);
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = (int) $request->input('order.0.column', 5);
    $orderDirection = strtoupper($request->input('order.0.dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

    $orderColumn = $columnMap[$orderColumnIndex] ?? 'news.created_at';

    $filters = $request->only(['title', 'userID', 'catID', 'start_date', 'end_date','newssource', 'status']);
    $status = $filters['status'] ?? 'Active';
    if ($status === 'video') $status = 'Active';

    // Base query
     $query = DB::table('news')
        ->select(
            'news.id', 'news.title', 'news.viewer', 'news.userID', 'news.viewer_app',
            'news.created_at', 'news.updated_at', 'news.status', 'news.newsOrder', 'news.is_breaking',
            'news.is_live_news', 'news.slug',
            DB::raw('IFNULL(tg.title, "") as newssource'),
            'u1.name as authorName',
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            'newscount.view_count as total_view_count'
        )
        ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
        ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
        ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
        ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id')
        ->leftJoin('tags as tg', 'news.newssource', '=', 'tg.id')  // join tags for source name
        ->groupBy('news.id');

    // 🔒 Restrict to last 60 days ONLY when no filters are active.
    // When user filters by title, date range, user, or category → search full database.
    $hasFilter = !empty($searchValue)
        || !empty($filters['title'])
        || !empty($filters['start_date'])
        || !empty($filters['end_date'])
        || !empty($filters['userID'])
        || !empty($filters['catID'])
        || !empty($filters['newssource']);

    if (!$hasFilter) {
        $query->where('news.created_at', '>=', Carbon::now()->subDays(60));
    }

    // Handle "video" as category_id = 9
    $isVideo = $request->input('status') === "video";

    if ($isVideo) {
        $query->whereIn('news.id', function ($q) {
            $q->select('news_id')->from('news_category')->where('category_id', 9);
        });
    } else {
        $query->where('news.status', $status)
              ->whereNotIn('news.id', function ($q) {
                  $q->select('news_id')->from('news_category')->where('category_id', 9);
              });
    }

    // 🔍 Search filters
    if ($searchValue) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('news.title', 'like', "%$searchValue%")
              ->orWhere('u1.name', 'like', "%$searchValue%")
              ->orWhere('news.newssource', 'like', "%$searchValue%")
              ->orWhere('categories.title', 'like', "%$searchValue%");
        });
    }

    if (!empty($filters['title'])) {
        $query->where('news.title', 'like', "%{$filters['title']}%");
    }
    if (!empty($filters['newssource'])) {
          $query->where('news.newssource', $filters['newssource']);
    }

    if (!empty($filters['userID'])) {
        $query->where('news.userID', $filters['userID']);
    }

    // Date range filter — applies to full database when set
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
       $query->whereBetween('news.created_at', ["{$filters['start_date']} 00:00:00", "{$filters['end_date']} 23:59:59"]);
    } elseif (!empty($filters['start_date'])) {
        $query->where('news.created_at', '>=', "{$filters['start_date']} 00:00:00");
    } elseif (!empty($filters['end_date'])) {
        $query->where('news.created_at', '<=', "{$filters['end_date']} 23:59:59");
    }

    if (!empty($filters['catID']) && $filters['catID'] != 9) {
        $query->where('news_category.category_id', $filters['catID'])
              ->where('news.is_home', '!=', 1);
    }

    // 🔄 Correct filtered count using subquery (for GROUP BY)
    $filteredRecords = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings($query)
        ->count();


    // Total count — full DB when filters active, else last 60 days
    $totalRecords = $hasFilter
        ? DB::table('news')->count()
        : DB::table('news')->where('created_at', '>=', Carbon::now()->subDays(60))->count();
    $sortByCount = ($orderColumn === 'newscount.view_count');

    // 🌐 Format
    $baseUrl    = config('app.front_url');
    $backendUrl = config('app.backend_url');

    // ── Fetch GA data — initialize all vars first so they're always defined ──
    $pageViews       = [];
    $titleViews      = [];
    $pathTitleMap    = [];
    $pageViews365    = [];
    $pageViews30     = [];
    $pathTitleMap365 = [];
    $pathTitleMap30  = [];
    $titleToAppCount = [];

    try {
        $pageViews365    = Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
        $titleViews365   = Cache::remember('ga_title_views_365days',    43200, fn() => $this->analyticsService->getPageViewsByTitle(365));
        $pathTitleMap365 = Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
        $pageViews30     = Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
        $titleViews30    = Cache::remember('ga_title_views_30days',     3600,  fn() => $this->analyticsService->getPageViewsByTitle(30));
        $pathTitleMap30  = Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

        // Merge page views (use higher count per path)
        $pageViews = $pageViews365;
        foreach ($pageViews30 as $path => $count) {
            $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $count) : $count;
        }

        $titleViews = $titleViews365;
        foreach ($titleViews30 as $title => $count) {
            $titleViews[$title] = isset($titleViews[$title]) ? max($titleViews[$title], $count) : $count;
        }

        $pathTitleMap = $pathTitleMap365;
        foreach ($pathTitleMap30 as $path => $title) {
            $pathTitleMap[$path] = $title;
        }

        // ── Build title → app GA count map ONCE (O(n) instead of O(n×m) per row) ──
        // Collect all app paths (GSTV/*) with their counts
        $appPathCounts = [];
        foreach ($pathTitleMap as $path => $title) {
            if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                if (!isset($appPathCounts[$title])) {
                    $appPathCounts[$title] = 0;
                }
                $appPathCounts[$title] += $c;
            }
        }
        $titleToAppCount = $appPathCounts;

        \Log::info('GA data loaded. URLs: ' . count($pageViews) . ', App titles: ' . count($titleToAppCount));
    } catch (\Exception $e) {
        \Log::error('Google Analytics fetch failed: ' . $e->getMessage());
    }

    // ── Helper: compute total for one row using O(1) lookups ─────────────────
    $computeRowTotal = function ($row) use ($pageViews, $titleToAppCount) {
        $isVid  = Str::contains($row->categoryNames ?? '', 'Videos');
        $pp     = $isVid
            ? "/{$row->categorySlugs}/{$row->slug}"
            : "/news/{$row->categorySlugs}/{$row->slug}";
        $gaWeb  = $pageViews[$pp] ?? 0;
        $gaApp  = $titleToAppCount[$row->title] ?? 0;
        $dbCnt  = (int)($row->total_view_count ?? 0);
        return (int)$gaWeb + (int)$gaApp + $dbCnt;
    };

    // ── Ordering & Pagination ─────────────────────────────────────────────────
    if ($sortByCount) {
        // Two-phase approach: fetch lightweight rows, sort by GA+DB total in PHP,
        // then fetch only the page's full rows using FIELD() ordering.

        // Phase 1: fetch only the columns needed for scoring (id, slug, title, db_count)
        $lightQuery = clone $query;
        $lightRows  = $lightQuery->select(
            'news.id',
            'news.slug',
            'news.title',
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug  ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('IFNULL(newscount.view_count, 0) as total_view_count')
        )->get();

        // Phase 2: score each row with GA website + GA app + DB count
        $scored = $lightRows->map(function ($r) use ($pageViews, $titleToAppCount) {
            $isVid = Str::contains($r->categoryNames ?? '', 'Videos');
            $pp    = $isVid
                ? "/{$r->categorySlugs}/{$r->slug}"
                : "/news/{$r->categorySlugs}/{$r->slug}";
            $r->_total = (int)($pageViews[$pp] ?? 0)           // GA website
                       + (int)($titleToAppCount[$r->title] ?? 0) // GA app (keyed by title)
                       + (int)$r->total_view_count;              // DB count
            return $r;
        });

        // Phase 3: sort by total, slice for current page, get ordered IDs
        $sortedIds = $scored->sortBy(
            fn($r) => $r->_total,
            SORT_NUMERIC,
            $orderDirection === 'DESC'
        )->values()->slice($start, $length)->pluck('id')->toArray();

        if (empty($sortedIds)) {
            $data = collect();
        } else {
            // Phase 4: fetch full rows for only this page's IDs, preserve PHP sort order
            $idList = implode(',', array_map('intval', $sortedIds));
            $data   = $query->whereIn('news.id', $sortedIds)
                            ->orderByRaw("FIELD(news.id, {$idList})")
                            ->get();
        }
    } else {
        // Non-count columns: fast DB-level sort
        $query->orderByRaw(
            !empty($filters['catID'])
                ? "{$orderColumn} {$orderDirection}"
                : "CASE WHEN is_live_news = 1 THEN 0 WHEN topnewsOrder != 0 THEN 1 ELSE 2 END, topnewsOrder ASC, {$orderColumn} {$orderDirection}"
        );
        $data = $query->offset($start)->limit($length)->get();
    }

    foreach ($data as $index => $row) {
        $isVideoNews = Str::contains($row->categoryNames, 'Videos');

        $newsUrl = $isVideoNews
            ? "$baseUrl/{$row->categorySlugs}/{$row->slug}"
            : "$baseUrl/news/{$row->categorySlugs}/{$row->slug}";

        // Compute GA+DB total for display
        $totalViews = $computeRowTotal($row);

        $highlight = $row->is_live_news
            ? '<span style="color: red; font-weight: bold;">Live: </span>'
            : ($row->is_breaking
                ? '<span style="color: red; font-weight: bold;">Breaking: </span>'
                : '');

        $row->rowNumber  = $row->is_live_news ? '' : $start + $index + 1;
        $row->newssource  = ucfirst($row->newssource);
        $row->authorName = "<a href='$backendUrl/user/profile/{$row->userID}'>{$row->authorName}</a>";
        $createdDate     = Carbon::parse($row->created_at);

        if ($createdDate->isToday()) {
            $row->created_at = "<span style='color:red;'>" . $createdDate->format('d M Y h:i A') . "</span>";
        } else {
            $row->created_at = $createdDate->format('d M Y h:i A');
        }

        $row->viewer = number_format($totalViews);
        
        $row->title = $highlight . "<a href='$newsUrl' target='_blank'>" . htmlspecialchars($row->title) . "</a>";
        $row->action = $this->generateActionButtons($row);
    }

    return response()->json([
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data,
    ]);
}
  public function fetchData_lll(Request $request)
{
    // Column index → actual DB column for ORDER BY
    // 0=No.(id), 1=title, 2=categoryNames, 3=viewer(view_count), 4=source, 5=authorName, 6=created_at, 7=action
    $columnMap = [
        0 => 'news.created_at',        // No
        1 => 'news.title',             // Title
        2 => 'news.created_at',        // Category (not sortable)
        3 => 'newscount.view_count',   // Views
        4 => 'u1.name',                // Author
        5 => 'news.created_at',        // Date
        6 => 'news.created_at',        // Action
    ];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20);
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = (int) $request->input('order.0.column', 5);
    $orderDirection = strtoupper($request->input('order.0.dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

    $orderColumn = $columnMap[$orderColumnIndex] ?? 'news.created_at';

    $filters = $request->only(['title', 'userID', 'catID', 'start_date', 'end_date','newssource', 'status']);
    $status = $filters['status'] ?? 'Active';
    if ($status === 'video') $status = 'Active';

    // Base query
     $query = DB::table('news')
        ->select(
            'news.id', 'news.title', 'news.viewer', 'news.userID', 'news.viewer_app',
            'news.created_at', 'news.updated_at', 'news.status', 'news.newsOrder', 'news.is_breaking',
            'news.is_live_news', 'news.slug',
            // DB::raw('IFNULL(tg.title, "") as newssource'),
            'u1.name as authorName',
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            'newscount.view_count as total_view_count'
        )
        ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
        ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
        ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
        ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id')
        // ->leftJoin('tags as tg', 'news.newssource', '=', 'tg.id')  // join tags for source name
        ->groupBy('news.id');

    // 🔒 Restrict to last 60 days ONLY when no filters are active.
    // When user filters by title, date range, user, or category → search full database.
    $hasFilter = !empty($searchValue)
        || !empty($filters['title'])
        || !empty($filters['start_date'])
        || !empty($filters['end_date'])
        || !empty($filters['userID'])
        || !empty($filters['catID'])
        || !empty($filters['newssource']);

    if (!$hasFilter) {
        $query->where('news.created_at', '>=', Carbon::now()->subDays(60));
    }

    // Handle "video" as category_id = 9
    $isVideo = $request->input('status') === "video";

    if ($isVideo) {
        $query->whereIn('news.id', function ($q) {
            $q->select('news_id')->from('news_category')->where('category_id', 9);
        });
    } else {
        $query->where('news.status', $status)
              ->whereNotIn('news.id', function ($q) {
                  $q->select('news_id')->from('news_category')->where('category_id', 9);
              });
    }

    // 🔍 Search filters
    if ($searchValue) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('news.title', 'like', "%$searchValue%")
              ->orWhere('u1.name', 'like', "%$searchValue%")
              ->orWhere('tg.title', 'like', "%$searchValue%")
              ->orWhere('categories.title', 'like', "%$searchValue%");
        });
    }

    if (!empty($filters['title'])) {
        $query->where('news.title', 'like', "%{$filters['title']}%");
    }
    // if (!empty($filters['newssource'])) {
    //     $query->where('tg.id', $filters['newssource']);
    // }

    if (!empty($filters['userID'])) {
        $query->where('news.userID', $filters['userID']);
    }

    // Date range filter — applies to full database when set
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
       $query->whereBetween('news.created_at', ["{$filters['start_date']} 00:00:00", "{$filters['end_date']} 23:59:59"]);
    } elseif (!empty($filters['start_date'])) {
        $query->where('news.created_at', '>=', "{$filters['start_date']} 00:00:00");
    } elseif (!empty($filters['end_date'])) {
        $query->where('news.created_at', '<=', "{$filters['end_date']} 23:59:59");
    }

    if (!empty($filters['catID']) && $filters['catID'] != 9) {
        $query->where('news_category.category_id', $filters['catID'])
              ->where('news.is_home', '!=', 1);
    }

    // 🔄 Correct filtered count using subquery (for GROUP BY)
    $filteredRecords = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings($query)
        ->count();


    // Total count — full DB when filters active, else last 60 days
    $totalRecords = $hasFilter
        ? DB::table('news')->count()
        : DB::table('news')->where('created_at', '>=', Carbon::now()->subDays(60))->count();
    $sortByCount = ($orderColumn === 'newscount.view_count');

    // 🌐 Format
    $baseUrl    = config('app.front_url');
    $backendUrl = config('app.backend_url');

    // ── Fetch GA data — initialize all vars first so they're always defined ──
    $pageViews       = [];
    $titleViews      = [];
    $pathTitleMap    = [];
    $pageViews365    = [];
    $pageViews30     = [];
    $pathTitleMap365 = [];
    $pathTitleMap30  = [];
    $titleToAppCount = [];

    try {
        $pageViews365    = Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
        $titleViews365   = Cache::remember('ga_title_views_365days',    43200, fn() => $this->analyticsService->getPageViewsByTitle(365));
        $pathTitleMap365 = Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
        $pageViews30     = Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
        $titleViews30    = Cache::remember('ga_title_views_30days',     3600,  fn() => $this->analyticsService->getPageViewsByTitle(30));
        $pathTitleMap30  = Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

        // Merge page views (use higher count per path)
        $pageViews = $pageViews365;
        foreach ($pageViews30 as $path => $count) {
            $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $count) : $count;
        }

        $titleViews = $titleViews365;
        foreach ($titleViews30 as $title => $count) {
            $titleViews[$title] = isset($titleViews[$title]) ? max($titleViews[$title], $count) : $count;
        }

        $pathTitleMap = $pathTitleMap365;
        foreach ($pathTitleMap30 as $path => $title) {
            $pathTitleMap[$path] = $title;
        }

        // ── Build title → app GA count map ONCE (O(n) instead of O(n×m) per row) ──
        // Collect all app paths (GSTV/*) with their counts
        $appPathCounts = [];
        foreach ($pathTitleMap as $path => $title) {
            if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                if (!isset($appPathCounts[$title])) {
                    $appPathCounts[$title] = 0;
                }
                $appPathCounts[$title] += $c;
            }
        }
        $titleToAppCount = $appPathCounts;

        \Log::info('GA data loaded. URLs: ' . count($pageViews) . ', App titles: ' . count($titleToAppCount));
    } catch (\Exception $e) {
        \Log::error('Google Analytics fetch failed: ' . $e->getMessage());
    }

    // ── Helper: compute total for one row using O(1) lookups ─────────────────
    $computeRowTotal = function ($row) use ($pageViews, $titleToAppCount) {
        $isVid  = Str::contains($row->categoryNames ?? '', 'Videos');
        $pp     = $isVid
            ? "/{$row->categorySlugs}/{$row->slug}"
            : "/news/{$row->categorySlugs}/{$row->slug}";
        $gaWeb  = $pageViews[$pp] ?? 0;
        $gaApp  = $titleToAppCount[$row->title] ?? 0;
        $dbCnt  = (int)($row->total_view_count ?? 0);
        return (int)$gaWeb + (int)$gaApp + $dbCnt;
    };

    // ── Ordering & Pagination ─────────────────────────────────────────────────
    if ($sortByCount) {
        // Two-phase approach: fetch lightweight rows, sort by GA+DB total in PHP,
        // then fetch only the page's full rows using FIELD() ordering.

        // Phase 1: fetch only the columns needed for scoring (id, slug, title, db_count)
        $lightQuery = clone $query;
        $lightRows  = $lightQuery->select(
            'news.id',
            'news.slug',
            'news.title',
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug  ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('IFNULL(newscount.view_count, 0) as total_view_count')
        )->get();

        // Phase 2: score each row with GA website + GA app + DB count
        $scored = $lightRows->map(function ($r) use ($pageViews, $titleToAppCount) {
            $isVid = Str::contains($r->categoryNames ?? '', 'Videos');
            $pp    = $isVid
                ? "/{$r->categorySlugs}/{$r->slug}"
                : "/news/{$r->categorySlugs}/{$r->slug}";
            $r->_total = (int)($pageViews[$pp] ?? 0)           // GA website
                       + (int)($titleToAppCount[$r->title] ?? 0) // GA app (keyed by title)
                       + (int)$r->total_view_count;              // DB count
            return $r;
        });

        // Phase 3: sort by total, slice for current page, get ordered IDs
        $sortedIds = $scored->sortBy(
            fn($r) => $r->_total,
            SORT_NUMERIC,
            $orderDirection === 'DESC'
        )->values()->slice($start, $length)->pluck('id')->toArray();

        if (empty($sortedIds)) {
            $data = collect();
        } else {
            // Phase 4: fetch full rows for only this page's IDs, preserve PHP sort order
            $idList = implode(',', array_map('intval', $sortedIds));
            $data   = $query->whereIn('news.id', $sortedIds)
                            ->orderByRaw("FIELD(news.id, {$idList})")
                            ->get();
        }
    } else {
        // Non-count columns: fast DB-level sort
        $query->orderByRaw(
            !empty($filters['catID'])
                ? "{$orderColumn} {$orderDirection}"
                : "CASE WHEN is_live_news = 1 THEN 0 WHEN topnewsOrder != 0 THEN 1 ELSE 2 END, topnewsOrder ASC, {$orderColumn} {$orderDirection}"
        );
        $data = $query->offset($start)->limit($length)->get();
    }

    foreach ($data as $index => $row) {
        $isVideoNews = Str::contains($row->categoryNames, 'Videos');

        $newsUrl = $isVideoNews
            ? "$baseUrl/{$row->categorySlugs}/{$row->slug}"
            : "$baseUrl/news/{$row->categorySlugs}/{$row->slug}";

        // Compute GA+DB total for display
        $totalViews = $computeRowTotal($row);

        $highlight = $row->is_live_news
            ? '<span style="color: red; font-weight: bold;">Live: </span>'
            : ($row->is_breaking
                ? '<span style="color: red; font-weight: bold;">Breaking: </span>'
                : '');

        $row->rowNumber  = $row->is_live_news ? '' : $start + $index + 1;
        // $row->newssource = ucfirst($row->newssource ?? '');
        $row->newssource = '';
        $row->authorName = "<a href='$backendUrl/user/profile/{$row->userID}'>{$row->authorName}</a>";
        $createdDate     = Carbon::parse($row->created_at);

        if ($createdDate->isToday()) {
            $row->created_at = "<span style='color:red;'>" . $createdDate->format('d M Y h:i A') . "</span>";
        } else {
            $row->created_at = $createdDate->format('d M Y h:i A');
        }

        $row->viewer = number_format($totalViews);
        
        $row->title = $highlight . "<a href='$newsUrl' target='_blank'>" . htmlspecialchars($row->title) . "</a>";
        $row->action = $this->generateActionButtons($row);
    }

    return response()->json([
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data,
    ]);
}
public function fetchData(Request $request)
{
    // Column index → actual DB column for ORDER BY
    // 0=No.(id), 1=title, 2=categoryNames, 3=viewer(view_count), 4=source(not sortable), 5=authorName, 6=created_at, 7=action
    $columnMap = [
        0 => 'news.created_at',        // No
        1 => 'news.title',             // Title
        2 => 'news.created_at',        // Category (not sortable)
        3 => 'newscount.view_count',   // Views
        4 => 'news.created_at',        // Source (not sortable)
        5 => 'u1.name',                // Author
        6 => 'news.created_at',        // Date
        7 => 'news.created_at',        // Action
    ];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20);
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = (int) $request->input('order.0.column', 6);
    $orderDirection = strtoupper($request->input('order.0.dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

    $orderColumn = $columnMap[$orderColumnIndex] ?? 'news.created_at';

    $filters = $request->only(['title', 'userID', 'catID', 'start_date', 'end_date','newssource', 'status']);
    $status = $filters['status'] ?? 'Active';
    if ($status === 'video') $status = 'Active';

    // Base query
     $query = DB::table('news')
        ->select(
            'news.id', 'news.title', 'news.viewer', 'news.userID', 'news.viewer_app',
            'news.created_at', 'news.updated_at', 'news.status', 'news.newsOrder', 'news.is_breaking',
            'news.is_live_news', 'news.slug',
            DB::raw('IFNULL(tg.title, "") as newssource'),
            'u1.name as authorName',
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            'newscount.view_count as total_view_count'
        )
        ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
        ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
        ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
        ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id')
        ->leftJoin('tags as tg', 'news.newssource', '=', 'tg.id')
        ->groupBy('news.id');

    // 🔒 Filter by userID if roleID = 7 (content creator)
    $currentUser = Auth::user();
    if ($currentUser && $currentUser->roleID == 7) {
        $query->where('news.userID', $currentUser->id);
    }
    // 🔒 If roleID = 6, show only news from roleID = 7 users
    elseif ($currentUser && $currentUser->roleID == 6) {
        $query->whereIn('news.userID', function ($q) {
            $q->select('id')->from('users')->where('roleID', 7);
        });
    }

    // 🔒 Restrict to last 60 days ONLY when no filters are active.
    // When user filters by title, date range, user, or category → search full database.
    $hasFilter = !empty($searchValue)
        || !empty($filters['title'])
        || !empty($filters['start_date'])
        || !empty($filters['end_date'])
        || !empty($filters['userID'])
        || !empty($filters['catID'])
        || !empty($filters['newssource']);

    if (!$hasFilter) {
        $query->where('news.created_at', '>=', Carbon::now()->subDays(60));
    }

    // Handle "video" as category_id = 9
    $isVideo = $request->input('status') === "video";

    if ($isVideo) {
        $query->whereIn('news.id', function ($q) {
            $q->select('news_id')->from('news_category')->where('category_id', 9);
        });
    } else {
        $query->where('news.status', $status)
              ->whereNotIn('news.id', function ($q) {
                  $q->select('news_id')->from('news_category')->where('category_id', 9);
              });
    }

    // 🔍 Search filters
    if ($searchValue) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('news.title', 'like', "%$searchValue%")
              ->orWhere('u1.name', 'like', "%$searchValue%")
              ->orWhere('tg.title', 'like', "%$searchValue%")
              ->orWhere('categories.title', 'like', "%$searchValue%");
        });
    }

    if (!empty($filters['title'])) {
        $query->where('news.title', 'like', "%{$filters['title']}%");
    }

    // Filter by news source (tag id)
    if (!empty($filters['newssource'])) {
        $query->where('news.newssource', $filters['newssource']);
    }

    if (!empty($filters['userID'])) {
        $query->where('news.userID', $filters['userID']);
    }

    // Date range filter — applies to full database when set
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
       $query->whereBetween('news.created_at', ["{$filters['start_date']} 00:00:00", "{$filters['end_date']} 23:59:59"]);
    } elseif (!empty($filters['start_date'])) {
        $query->where('news.created_at', '>=', "{$filters['start_date']} 00:00:00");
    } elseif (!empty($filters['end_date'])) {
        $query->where('news.created_at', '<=', "{$filters['end_date']} 23:59:59");
    }

    if (!empty($filters['catID']) && $filters['catID'] != 9) {
        $query->where('news_category.category_id', $filters['catID'])
              ->where('news.is_home', '!=', 1);
    }

    // 🔄 Correct filtered count using subquery (for GROUP BY)
    $filteredRecords = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings($query)
        ->count();

    // Total count — full DB when filters active, else last 60 days
    $totalRecords = $hasFilter
        ? DB::table('news')->count()
        : DB::table('news')->where('created_at', '>=', Carbon::now()->subDays(60))->count();

    $sortByCount = ($orderColumn === 'newscount.view_count');

    // 🌐 Format
    $baseUrl    = config('app.front_url');
    $backendUrl = config('app.backend_url');

    // ── Fetch GA data — initialize all vars first so they're always defined ──
    $pageViews       = [];
    $titleViews      = [];
    $pathTitleMap    = [];
    $pageViews365    = [];
    $pageViews30     = [];
    $pathTitleMap365 = [];
    $pathTitleMap30  = [];
    $titleToAppCount = [];

    try {
        $pageViews365    = Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
        $titleViews365   = Cache::remember('ga_title_views_365days',    43200, fn() => $this->analyticsService->getPageViewsByTitle(365));
        $pathTitleMap365 = Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
        $pageViews30     = Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
        $titleViews30    = Cache::remember('ga_title_views_30days',     3600,  fn() => $this->analyticsService->getPageViewsByTitle(30));
        $pathTitleMap30  = Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

        // Merge page views (use higher count per path)
        $pageViews = $pageViews365;
        foreach ($pageViews30 as $path => $count) {
            $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $count) : $count;
        }

        $titleViews = $titleViews365;
        foreach ($titleViews30 as $title => $count) {
            $titleViews[$title] = isset($titleViews[$title]) ? max($titleViews[$title], $count) : $count;
        }

        $pathTitleMap = $pathTitleMap365;
        foreach ($pathTitleMap30 as $path => $title) {
            $pathTitleMap[$path] = $title;
        }

        // ── Build title → app GA count map ONCE (O(n) instead of O(n×m) per row) ──
        // Collect all app paths (GSTV/*) with their counts
        $appPathCounts = [];
        foreach ($pathTitleMap as $path => $title) {
            if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                if (!isset($appPathCounts[$title])) {
                    $appPathCounts[$title] = 0;
                }
                $appPathCounts[$title] += $c;
            }
        }
        $titleToAppCount = $appPathCounts;

        \Log::info('GA data loaded. URLs: ' . count($pageViews) . ', App titles: ' . count($titleToAppCount));
    } catch (\Exception $e) {
        \Log::error('Google Analytics fetch failed: ' . $e->getMessage());
    }

    // ── Helper: compute total for one row using O(1) lookups ─────────────────
    $computeRowTotal = function ($row) use ($pageViews, $titleToAppCount) {
        $isVid  = Str::contains($row->categoryNames ?? '', 'Videos');
        $pp     = $isVid
            ? "/{$row->categorySlugs}/{$row->slug}"
            : "/news/{$row->categorySlugs}/{$row->slug}";
        $gaWeb  = $pageViews[$pp] ?? 0;
        $gaApp  = $titleToAppCount[$row->title] ?? 0;
        $dbCnt  = (int)($row->total_view_count ?? 0);
        return (int)$gaWeb + (int)$gaApp + $dbCnt;
    };

    // ── Ordering & Pagination ─────────────────────────────────────────────────
    if ($sortByCount) {
        // Two-phase approach: fetch lightweight rows, sort by GA+DB total in PHP,
        // then fetch only the page's full rows using FIELD() ordering.

        // Phase 1: fetch only the columns needed for scoring (id, slug, title, db_count)
        $lightQuery = clone $query;
        $lightRows  = $lightQuery->select(
            'news.id',
            'news.slug',
            'news.title',
            DB::raw('GROUP_CONCAT(DISTINCT categories.slug  ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
            DB::raw('IFNULL(newscount.view_count, 0) as total_view_count')
        )->get();

        // Phase 2: score each row with GA website + GA app + DB count
        $scored = $lightRows->map(function ($r) use ($pageViews, $titleToAppCount) {
            $isVid = Str::contains($r->categoryNames ?? '', 'Videos');
            $pp    = $isVid
                ? "/{$r->categorySlugs}/{$r->slug}"
                : "/news/{$r->categorySlugs}/{$r->slug}";
            $r->_total = (int)($pageViews[$pp] ?? 0)           // GA website
                       + (int)($titleToAppCount[$r->title] ?? 0) // GA app (keyed by title)
                       + (int)$r->total_view_count;              // DB count
            return $r;
        });

        // Phase 3: sort by total, slice for current page, get ordered IDs
        $sortedIds = $scored->sortBy(
            fn($r) => $r->_total,
            SORT_NUMERIC,
            $orderDirection === 'DESC'
        )->values()->slice($start, $length)->pluck('id')->toArray();

        if (empty($sortedIds)) {
            $data = collect();
        } else {
            // Phase 4: fetch full rows for only this page's IDs, preserve PHP sort order
            $idList = implode(',', array_map('intval', $sortedIds));
            $data   = $query->whereIn('news.id', $sortedIds)
                            ->orderByRaw("FIELD(news.id, {$idList})")
                            ->get();
        }
    } else {
        // Non-count columns: fast DB-level sort
        $query->orderByRaw(
            !empty($filters['catID'])
                ? "{$orderColumn} {$orderDirection}"
                : "CASE WHEN is_live_news = 1 THEN 0 WHEN topnewsOrder != 0 THEN 1 ELSE 2 END, topnewsOrder ASC, {$orderColumn} {$orderDirection}"
        );
        $data = $query->offset($start)->limit($length)->get();
    }

    foreach ($data as $index => $row) {
        $isVideoNews = Str::contains($row->categoryNames, 'Videos');

        $newsUrl = $isVideoNews
            ? "$baseUrl/{$row->categorySlugs}/{$row->slug}"
            : "$baseUrl/news/{$row->categorySlugs}/{$row->slug}";

        // Compute GA+DB total for display
        $totalViews = $computeRowTotal($row);

        $highlight = $row->is_live_news
            ? '<span style="color: red; font-weight: bold;">Live: </span>'
            : ($row->is_breaking
                ? '<span style="color: red; font-weight: bold;">Breaking: </span>'
                : '');

        $row->rowNumber  = $row->is_live_news ? '' : $start + $index + 1;
        $row->newssource = $row->newssource ?? "";

        $row->authorName = "<a href='$backendUrl/user/profile/{$row->userID}'>{$row->authorName}</a>";
        $createdDate     = Carbon::parse($row->created_at);

        if ($createdDate->isToday()) {
            $row->created_at = "<span style='color:red;'>" . $createdDate->format('d M Y h:i A') . "</span>";
        } else {
            $row->created_at = $createdDate->format('d M Y h:i A');
        }

        $row->viewer = number_format($totalViews);
        
        $row->title = $highlight . "<a href='$newsUrl' target='_blank'>" . htmlspecialchars($row->title) . "</a>";
        $row->action = $this->generateActionButtons($row);
    }

    return response()->json([
        'draw' => intval($draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data,
    ]);
}

    private function generateActionButtons($row)
    {
        $editUrl = url('/news/edit', $row->id);
        $deleteUrl = url('/news/destroy', $row->id);
        $updateStatusUrl = url('/news/updateStatus', [$row->status == 'Active' ? 'Inactive' : 'Active', $row->id]);
        $user = Auth::user()->roleID;

        $deleteButton = '';

        if ($user != 4) {
            $deleteButton = "
                <button type='submit'
                        onclick='return confirm(\"Are you sure?\")'
                        title='Delete'
                        class='btn delCls iconbtn btn-danger'>
                    <i class='bi bi-trash'></i>
                </button>
            ";
        }
         return "
            <form action='$deleteUrl' method='POST' style='display:inline;'>
                " . csrf_field() . method_field('DELETE') . "

                <a class='btn iconbtn btn-success'
                title='Toggle Status'
                href='$updateStatusUrl'>
                    <i class='bi bi-" . ($row->status == 'Active' ? 'eye' : 'eye-slash') . "'></i>
                </a>

                <a class='btn iconbtn editCls btn-primary'
                title='Edit'
                href='$editUrl'>
                    <i class='bi bi-pencil'></i>
                </a>

                $deleteButton
            </form>
        ";
    }

    public function searchRelatedNews(Request $request)
    {
        $search = $request->get('q');
        $relatedNews = \App\Models\News::where('title', 'LIKE', '%' . $search . '%')->where('status','Active')->orderBy('id', 'DESC')->get();

        return response()->json($relatedNews);
    }
    
}
