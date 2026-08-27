<?php
namespace App\Http\Controllers\Api\v15;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Userregister;
use App\Models\Usercategory;
use App\Models\Usercity;
use App\Models\Category;
use App\Models\News;
use App\Models\Users;
use App\Models\Media;
use App\Models\Usernews;
use App\Models\Newscategory;
use App\Models\Livenews;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use File;
use Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\Gif;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redis;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessVideoPreviewgif;
use App\Jobs\ProcessVideoPreview;
use Route;

class AdminAppController extends Controller
{
    protected $firebaseService;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->status === 'Active') {
                $data = [
                    'userID' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email' => $user->email,
                    'profileImg' => config('app.backendnewsurl').'/public/uploads/profile/'.$user->profileImg,
                     // 'moduleID' => $user->moduleID,
                    // 'catID' => $user->catID,
                    // 'roleID' => $user->roleID,
                    'created_at' => $user->created_at,
                    'status' => $user->status,
                ];

                return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(['message' => 'User is inactive'], 403, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function newslist(Request $request)
    {
        $status = $request->input('status', 'Active');
        $searchValue = $request->input('search');
        $filters = $request->only('title', 'userID', 'start_date', 'end_date', 'catID','newssource');
        $currentPage = $request->input('page', 1);
        $perPage = 20;

        $orderColumn = 'news.created_at';
        $orderDirection = 'DESC';

        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $isVideo = $request->input('status') === "Videos";

        $query = DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.viewer',
                'news.viewer_app',
                'news.userID',
                'news.created_at',
                'news.updated_at',
                'news.status',
                'news.newsOrder',
                'news.is_breaking',
                'news.is_live_news',
                'news.slug',
                'u1.name as authorName',
                'news.img_credit_txt',
                'news.is_home','news.topnewsOrder','news.newssource',
                DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id SEPARATOR ", ") as categoryNames'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT news_category.category_id ORDER BY news_category.category_id) as catIDs'),
                'newscount.view_count as total_view_count'
            )
            ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
            ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
            ->leftJoin('categories', 'categories.id', '=', 'news_category.category_id')
            ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id');

        // ✅ Filtering based on category
        if ($isVideo) {
            $query->where('news_category.category_id', 9);
        } else {
            $query->where('news.status', $status)
                ->where('news_category.category_id', '!=', 9);
        }

        if (!empty($filters['title'])) {
            $query->where('news.title', 'like', "%{$filters['title']}%");
        }

        if (!empty($filters['userID'])) {
            $query->where('news.userID', $filters['userID']);
        }
        if (!empty($filters['newssource'])) {
            $query->where('news.newssource', $filters['newssource']);
        }
        

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('news.created_at', [
                "{$filters['start_date']} 00:00:00",
                "{$filters['end_date']} 23:59:59"
            ]);
        }

        if (!empty($filters['catID']) && $filters['catID'] != 9) {
            $query->where('news_category.category_id', $filters['catID'])
                ->where('news.is_home', '!=', 1);
        }

        // $newsList = $query->groupBy('news.id', 'u1.name')
        //                 ->orderBy('news.created_at', 'desc')
        //                 ->paginate($perPage);

         $query->groupBy('news.id', 'u1.name');

        // ✅ Updated Ordering Logic
        $query->orderByRaw(
            (!empty($filters['catID']) 
                ? "$orderColumn $orderDirection" 
                : "CASE 
                        WHEN news.is_live_news = 1 THEN 0 
                        WHEN news.topnewsOrder != 0 THEN 1 
                        ELSE 2 
                END,
                news.topnewsOrder ASC,
                $orderColumn $orderDirection"
            )
        );

        $newsList = $query->paginate($perPage);

        $responseData = [];

        foreach ($newsList as $news) {
            $responseData[] = [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'viewer' => $news->total_view_count ?? 0,
                'viewer_app' => $news->viewer_app,
                'authorName' => $news->authorName,
                'categories' => $news->categoryNames,
                'categorySlugs' => $news->categorySlugs,
                'created_at' => $news->created_at,
                'status' => $news->status,
                'is_breaking' => $news->is_breaking,
                'is_live_news' => $news->is_live_news,
                'is_home' => (int)$news->is_home,
                'topnewsOrder' => $news->topnewsOrder
            ];
        }

        return response()->json([
            'newslist' => $responseData,
            'current_page' => $newsList->currentPage(),
            'last_page' => $newsList->lastPage(),
            'per_page' => $perPage,
            'total' => $newsList->total()
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function newsorder(Request $request)
    {
        $newOrder = $request->input('newOrder');
        // $oldOrder = $request->input('oldOrder');
        // $tabletop = $request->input('tabletop');
        $pageNum = 1;//$request->input('pageNum');
        $newliveOrder = $request->input('newliveOrder');
        // $entriesPerPage = $request->input('entriesPerPage');
        // $catID = $request->input('catID');
        
        try {
            // Start transaction
            DB::beginTransaction();

            if ($pageNum == 1) {
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
                        'userID' => Auth::user()->id ?? 1,
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
                
                // Category-specific order update
                // if ($tabletop != '' && $pageNum == 1) {
                //     // Fetch current updated_at values if not already fetched
                //     if (!isset($newsTimestamps)) {
                //         $newsTimestamps = DB::table('news')
                //             ->whereIn('id', $newOrder)
                //             ->pluck('updated_at', 'id');
                //     }
                    
                //     $categoryUpdateData = [];
                //     for ($i = 0; $i <= 3; $i++) {
                //         $categoryUpdateData[] = [
                //             'id' => $newOrder[$i],
                //             'catOrder' => $i + 1,
                //             'userID' => auth()->id() ?? 1,
                //             'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                //         ];
                //     }

                //     // Bulk update for category news order
                //     DB::table('news')->upsert($categoryUpdateData, ['id'], ['catOrder', 'updated_at']);

                //     // Reset remaining category orders
                //     $categoryResetData = [];
                //     for ($i = 4; $i <= 19; $i++) {
                //         $categoryResetData[] = [
                //             'id' => $newOrder[$i],
                //             'catOrder' => 0,
                //             'userID' => auth()->id() ?? 1,
                //             'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                //         ];
                //     }

                //     DB::table('news')->upsert($categoryResetData, ['id'], ['catOrder', 'updated_at']);
                // }
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

    public function addnews(Request $request)
    { 
        $minutes = 30;
        $data = array();
        // Redis: Parent + Child Categories (eventCat = 0)
        $categoryKey = 'categories_parent_child_' . config('app.webtype');
        if (Redis::exists($categoryKey)) {
            $category = collect(json_decode(Redis::get($categoryKey)));
        } else {
            $category = DB::table('categories')
                ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
                ->select('categories.id', 'categories.title', 'categories.parentID', 'children.id as child_id', 'children.title as child_name')
                ->where('categories.status', 'Active')
                ->where('categories.eventCat', 0)
                ->orderBy('categories.catOrder', 'ASC')
                ->get();

            Redis::setex($categoryKey, $minutes * 60, $category->toJson());
        }
        $uniqueParentCategories = $category->unique('id')->values();

        // Redis: Event Categories (eventCat = 1)
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

        // Redis: Users
        $userKey = 'active_users_' . config('app.webtype');
        if (Redis::exists($userKey)) {
            $userList = collect(json_decode(Redis::get($userKey)));
        } else {
            $userList = DB::table('users')->where('status', 'Active')->get();
            Redis::setex($userKey, $minutes * 60, $userList->toJson());
        }

        $newssourceKey = 'newssource_' . config('app.webtype');
        if (Redis::exists($newssourceKey)) {
            $newssourceList = collect(json_decode(Redis::get($newssourceKey)));
        } else {
            $newssourceList = DB::table('tags')->where('status', 'Active')->get();
            Redis::setex($newssourceKey, $minutes * 60, $newssourceList->toJson());
        }

        // Final Response
        $data = [
            'category' => $uniqueParentCategories,
            'eventcategory' => $eventcategory,
            'user' => $userList,
            'newssourceList'=>$newssourceList
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function medialist(Request $request)
    { 
        $perPage = 10;
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $minutes = 30;
        $data = array();
    
        // Media - Images
        $media = DB::table('medias')
            ->where(function ($query) {
                $query->where('photos', 'like', '%.jpg')
                    ->orWhere('photos', 'like', '%.jpeg')
                    ->orWhere('photos', 'like', '%.png')
                    ->orWhere('photos', 'like', '%.webp')
                    ->orWhere('photos', 'like', '%.gif');
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('photos', 'like', '%' . $search . '%');
            })
            ->orderBy('id', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

        // Media - Videos
        $videomedia = DB::table('medias')
            ->where('photos', 'like', '%.mp4')
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('photos', 'like', '%' . $search . '%');
            })
            ->orderBy('id', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

        // Final Response
        $data = [
            'media' => $media,
            'videomedia' => $videomedia
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function storenews(Request $request)
    {

        DB::beginTransaction(); // ✅ start transaction

        try {
            $englishTitle = $request->englishTitle;
            $catID = $request->catID;
            $subcatID = $request->subcatID;
            $eventCat = $request->eventCat;
            $scheduleDate = $request->scheduleDate;
            $videoURL = $request->videoURL;
            $featureImage = $request->featureImage;
            $userID = $request->userID;
            $usernews_userID = $request->usernews_userID;
            $status = $request->status;
            $description = $request->description;
            $img_credit_txt = $request->img_credit_txt;

            $concatenatedValue = $catID;
            if (!empty($subcatID)) $concatenatedValue .= ",$subcatID";
            if (!empty($eventCat)) $concatenatedValue .= ",$eventCat";

            $metakeyword = implode(',', explode(',', $request->metakeyword));
            $plainText = strip_tags($description);
            $limitedText = Str::limit($plainText, 155 - Str::length($englishTitle), '');
            $metadesc = "$englishTitle | $limitedText";

            $slug = Str::slug($englishTitle, '-');
            $tags = $request->has('selectedTags') ? implode(',', $request->selectedTags) : '';
            $is_vertical_video = $request->boolean('is_vertical_video') ? 9 : 0;
            $hide_title = $request->boolean('hide_title') == 1 ? 1 : 0;

            $dataToInsert = [
                'title' => $request->title,
                'englishTitle' => $englishTitle,
                'slug' => $slug,
                'userID' => $userID,
                'assignUserID' => $userID,
                'featureImage' => $featureImage,
                'imageURL' => '',
                'uploadVideo' => '',
                'scheduleDate' => $scheduleDate,
                'hide_title' => $hide_title,
                'tags' => $tags,
                'newssource' => $request->newssource,
                'is_home' => $request->is_home == 1 ? 1 : 0,
                'inshorts' => $request->boolean('inshorts') ? 1 : 0,
                'videoURL' => $videoURL,
                'metatitle' => $englishTitle,
                'metakeyword' => $metakeyword,
                'metadesc' => $metadesc,
                'status' => $status,
                'description' => $description,
                'is_live_news' => $request->is_live_news == 1 ? 1 : 0,
                'is_breaking' => $request->boolean('is_breaking') ? 1 : 0,
                'notification' => $request->boolean('notification') ? 1 : 0,
                'is_vertical_video' => $is_vertical_video,
                'img_credit_txt'=>$img_credit_txt,
                'video_start' => $request->filled('video_start') ? $request->video_start : 10,
            ];

            $news = News::firstOrNew(['englishTitle' => $englishTitle]);
            $news->fill($dataToInsert)->save();
            $video_webp = '';
            if($videoURL != ""){
                $webpURL = preg_replace('/\.mp4$/i', '_video.webp', $videoURL);

                // $webpPath = rtrim(env('MEDIA_PUBLIC_PATH'), '/'). parse_url($webpURL, PHP_URL_PATH);

                // if (file_exists($webpPath)) {
                    $video_webp = $webpURL;
                // }
                ProcessVideoPreview::dispatch($videoURL,$is_vertical_video,$catID,$request->video_start);
            }
           
            foreach (explode(',', $concatenatedValue) as $catid) {
                if (is_numeric($catid)) {
                    Newscategory::create([
                        'news_id' => $news->id,
                        'category_id' => (int) $catid,
                    ]);
                }
            }
            // Livenews insert if needed
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

            // Ordering logic
            $orderCatID = $eventCat ?: ($subcatID ?: $catID);
            // if ($request->is_live_news != 1) {
            //     DB::table('news')
            //         ->whereRaw('FIND_IN_SET(?, catID)', [$orderCatID])
            //         ->where('catOrder', '!=', 0)
            //         ->where('catOrder', '>=', 1)
            //         ->where('id', '!=', $news->id)
            //         ->update(['catOrder' => DB::raw('catOrder + 1')]);
            // }

            $news->update([
                'newsOrder' => $news->id,
                'catOrder' => 1,
                'video_webp'=>$video_webp,
                'usernews_userID' => $usernews_userID,
                'relatednewsid' => is_array($request->relatednewsid) ? implode(',', $request->relatednewsid) : $request->relatednewsid,
            ]);

            // $lastRecord = DB::table('news')
            //     ->whereRaw('FIND_IN_SET(?, catID)', [$orderCatID])
            //     ->orderBy('catOrder', 'desc')
            //     ->first();

            // $lastRecord = DB::table('news')
            //     ->join('news_category', 'news.id', '=', 'news_category.news_id')
            //     ->where('news_category.category_id', $orderCatID)
            //     ->where('news.id', '!=', $news->id)
            //     ->orderByDesc('news.catOrder')
            //     ->limit(1)
            //     ->value('news.id');
            $lastRecord = DB::table('news')
            ->join('news_category', 'news.id', '=', 'news_category.news_id')
            ->where('news_category.category_id', $orderCatID)
            ->orderBy('news.catOrder', 'desc')
            ->select('news.*') // ensure only news fields are returned
            ->first();

            if ($lastRecord) {
                DB::table('news')->where('id', $lastRecord->id)->update(['catOrder' => 0]);
            }

            if (!empty($usernews_userID)) {
                Usernews::where('id', $usernews_userID)->update(['status' => 'Active']);
            }

            // Notification build
            $catslug = DB::table('news')
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->join('categories', 'categories.id', '=', 'news_category.category_id')
                ->where('news.id', $news->id)
                ->selectRaw("GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR '/') AS categorySlugs")
                ->first();

            $type = ($catID == '9') ? 'Videos' : 'News';
            $baseUrl = ($type == 'Videos') ? config('app.front_url').'/' : config('app.front_url').'/news/';
            $url = $baseUrl . $catslug->categorySlugs . '/' . $slug;

            $fileExtension = strtolower(pathinfo($featureImage, PATHINFO_EXTENSION));
            $newFeatureImage = ($videoURL)
                ? preg_replace("/\.$fileExtension$/i", "_video.gif", $videoURL)
                : preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $featureImage);

            // Firebase notification send
            if ($request->notification==1 && $status == 'Active') {
                $title = $request->title;
                $body = Str::limit(strip_tags($description), 120, '...');
                // $this->firebaseService->sendNotification($title, $body, 'news', $type, $newFeatureImage, $url, $slug);
                if(config('app.webtype')== 'live'){
                    $this->firebaseService->sendNotification($title, $body, 'news', $type, $newFeatureImage, $url, $slug);
                } else {
                    $this->firebaseService->sendNotification($title, $body, 'news_staging', $type, $newFeatureImage, $url, $slug);
                }
            }

          
            DB::commit();  // ✅ Commit transaction

            return response()->json([
                'success' => true,
                'message' => 'News has been added successfully',
                'news_id' => $news->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // ❌ Rollback transaction on error
            return response()->json([
                'success' => false,
                'message' => 'Error adding news: ' . $e->getMessage()
            ], 500);
        }
    } 
    public function editnews(Request $request)
    {
        $id= $request->news_id;
        $news = News::select(['title','englishTitle','slug','featureImage','imageURL','uploadVideo','videoURL','scheduleDate','tags','description','metatitle','metakeyword','metadesc','is_home','relatednewsid','notification','is_live_news','usernews_userID','is_breaking','is_vertical_video','hide_title','status','img_credit_txt'])
        ->where('id', $id)
        ->firstOrFail();
        $categoryIds = DB::table('news_category')
            ->where('news_id', $id)
            ->pluck('category_id') // returns a collection of just the category_id values
            ->toArray();
        // Related News
        $relatedNewsIds = array_filter(explode(',', $news->relatednewsid));
        $relatedNews = $relatedNewsIds ? News::whereIn('id', $relatedNewsIds)->select('id', 'title')->get() : collect();

        // Live news
        $livenews = DB::table('livenews')
            ->select('lid','livetitle','livedesc','live_new_story')
            ->where('news_id', $id)
            ->orderBy('lid', 'DESC')
            ->get();

        //News Source
        $newssourceList = DB::table('tags')->where('status', 'Active')->get();

        // Prepare data
        $data = [
            'relatedNews' => $relatedNews,
            'news' => $news,
            'categoryIds'=>$categoryIds,
            'livenews' => $livenews,
            'newssourceList'=>$newssourceList
        ];
        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
        
    }
    public function updatenews(Request $request)
    {
        // $rules = [
        //     'nid' => 'required|exists:news,id',
        //     'title' => 'required',
        //     'englishTitle' => [
        //         'required',
        //         Rule::unique('news', 'englishTitle')->ignore($request->nid),
        //     ],
        //     'catID' => 'required'
        // ];

        // $customMessages = [
        //     'title.required' => 'The title field is required.',
        //     'englishTitle.required' => 'The english title field is required.',
        //     'englishTitle.unique' => 'The English Title is already in use.',
        //     'catID.required' => 'At least one category must be selected.',
        //     'nid.required' => 'News ID is required.',
        //     'nid.exists' => 'News ID not found.',
        // ];

        // $validator = Validator::make($request->all(), $rules, $customMessages);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'msg' => $validator->errors()->all() // returns array of messages
        //     ], 422);
        // }

        DB::beginTransaction();
        try {
            $news = News::findOrFail($request->nid);

            $englishTitle = $request->englishTitle;
            $catID = $request->catID;
            $subcatID = $request->subcatID;
            $eventCat = $request->eventCat;
            $img_credit_txt = $request->img_credit_txt;
            $concatenatedValue = $catID;
            if (!empty($subcatID)) $concatenatedValue .= ",$subcatID";
            if (!empty($eventCat)) $concatenatedValue .= ",$eventCat";

            // Handle tags
            $tags = $request->has('selectedTags') ? implode(',', $request->selectedTags) : '';

            // Meta desc creation
            $plainText = strip_tags($request->description);
            $limitedText = Str::limit($plainText, 155 - Str::length($englishTitle), '');
            $metadesc = "$englishTitle | $limitedText";

            $hide_title = $request->get('hide_title') == 1 ? 1 : 0;
            $is_vertical_video = $request->boolean('is_vertical_video') ? 9 : 0;
            $is_live_news = $request->boolean('is_live_news') ? 1 : 0;
            $dataToUpdate = [
                'title' => $request->title,
                'englishTitle' => $englishTitle,
                'slug' => Str::slug($englishTitle),
                // 'userID' => $request->userID,
                'assignUserID' => $request->userID,
               // 'catID' => $concatenatedValue,
                'featureImage' => $request->featureImage,
                'imageURL' => '',
                'uploadVideo' => '',
                'scheduleDate' => $request->scheduleDate,
                'tags' => $tags,
                'newssource' => $request->newssource,
                'is_home' => $request->is_home == 1 ? 1 : 0,
                'inshorts' => $request->boolean('inshorts') ? 1 : 0,
                'videoURL' => $request->videoURL,
                'metatitle' => $englishTitle,
                'metakeyword' => $request->metakeyword ?? '',
                'metadesc' => $metadesc,
                'status' => $request->status,
                'description' => $request->description,
                'is_live_news' => $is_live_news,
                'is_breaking' => $request->boolean('is_breaking') ? 1 : 0,
                'notification' => $request->boolean('notification') ? 1 : 0,
                'is_vertical_video' => $is_vertical_video,
                'hide_title' => $hide_title,
                'img_credit_txt'=>$img_credit_txt,
                'video_start' => $request->video_start
            ];
            $videoURL = $request->videoURL;
            $video_webp = '';
            if($videoURL != ""){ 
                $webpURL = preg_replace('/\.mp4$/i', '_video.webp', $videoURL);
                // $webpPath = rtrim(env('MEDIA_PUBLIC_PATH'), '/'). parse_url($webpURL, PHP_URL_PATH);
                // echo $webpPath;exit;
                // if (file_exists($webpPath)) {
                    $video_webp = $webpURL;
                // }

                ProcessVideoPreview::dispatch($videoURL,$is_vertical_video,$catID,$request->video_start);    
            }
            $news->fill($dataToUpdate)->save();

            // Update related news IDs
            $relatedNewsIds = $request->relatednewsid;
            $news->relatednewsid = is_array($relatedNewsIds) ? implode(',', $relatedNewsIds) : $relatedNewsIds;
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
         
            if ($is_live_news && $request->has('live_titles')) {
                Livenews::where('news_id', $news->id)->delete();
                foreach ($request->get('live_titles') as $key => $title) {
                    $desc = $request->get('live_contents')[$key] ?? '';
                    
                        Livenews::create([
                            'livetitle' => $title,
                            'livedesc' => $desc,
                            'news_id' => $news->id,
                            'user_id' => $request->get('userID'),
                        ]);
                 }
            }

            // Generate category slugs for notification
            $catslug = DB::table('news')
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->join('categories', 'news_category.category_id', '=', 'categories.id')
                ->where('news.id', $news->id)
                ->selectRaw("GROUP_CONCAT(categories.slug ORDER BY categories.id SEPARATOR '/') AS categorySlugs")
                ->first();

            $type = ($catID == '9') ? 'Videos' : 'News';
            $baseUrl = ($type == 'Videos') ? config('app.front_url').'/' : config('app.front_url').'/news/';
            $url = $baseUrl . $catslug->categorySlugs . '/' . Str::slug($englishTitle);

            $fileExtension = strtolower(pathinfo($request->featureImage, PATHINFO_EXTENSION));
            $newFeatureImage = ($request->videoURL)
                ? preg_replace("/\.$fileExtension$/i", "_video.gif", $request->videoURL)
                : preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $request->featureImage);

            // Firebase notification send
            if ($request->notification==1 && $request->status === "Active") {
                $title = $request->title;
                $body = Str::limit(strip_tags($request->description), 120, '...');
                if(config('app.webtype')== 'live'){
                    $this->firebaseService->sendNotification($title, $body, 'news', $type, $newFeatureImage, $url, $news->slug);
                } else {
                    $this->firebaseService->sendNotification($title, $body, 'news_staging', $type, $newFeatureImage, $url, $news->slug);
                }
            }

            // Save log
            // Log::create([
            //     'userID' => $request->userID,
            //     'message' => 'News updated: ' . $request->title,
            //     'action' => 'Update',
            //     'contents' => json_encode($request->all()),
            // ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'News has been updated successfully',
                'news_id' => $news->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating news: ' . $e->getMessage()
            ], 500);
        }
    }
    public function searchRelatedNews(Request $request)
    { 
        $search = $request->input('searchnews');

        $relatedNews = \App\Models\News::select('id', 'title')->where('title', 'LIKE', '%' . $search . '%')
                        ->where('status', 'Active')
                        ->orderBy('id', 'DESC')
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedNews
        ], 200);
    }
    public function subCategory(Request $request)
    {
        $subcat = $request->input('subcat');
        $category = DB::table('categories')->where('parentID',$subcat)->where('status','Active')->orderBy('catOrder','ASC')->get();
        return response()->json([
            'success' => true,
            'data' => $category
        ], 200);
    }
    public function addmedia(Request $request) 
    {
         if ($files = $request->file('images')) { 
            $year = date('Y');
            $month = date('m');
            $day = date('d');
    
            // Create the folder structure (year/month/date) if it doesn't exist
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . "/backend/public/media/$year/$month/$day/";
            // Check and create directories
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $cleanFilename = preg_replace('/[^A-Za-z0-9\-]/', '', $files->getClientOriginalName());
            $extension = pathinfo($files->getClientOriginalName(), PATHINFO_EXTENSION);
            $imgname = str_replace(' ', '-',$cleanFilename);
            $fileNameWithoutExtension = pathinfo($imgname, PATHINFO_FILENAME);
            $featureImage = $this->getUniqueFileName($fileNameWithoutExtension, $extension);
            $imgname = pathinfo($featureImage, PATHINFO_FILENAME);
            $randomCode = Str::random(8).time();
            $mimeType = $files->getMimeType();
            if (str_starts_with($mimeType, 'image')  && $extension !== 'gif') { 
                $files->move($destinationPath, $featureImage);
                $img = Image::make($destinationPath . $featureImage);
                $webpimg = pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.'.$extension;
                $webpPath = $destinationPath . pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.'.$extension;
                $img->save($webpPath, 90);

                $img->resize(350, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $smallImgPath = $destinationPath . pathinfo($featureImage, PATHINFO_FILENAME).'_'.$randomCode.'_small.'.$extension;
                $img->save($smallImgPath, 30);

                $img = Image::make($destinationPath . $featureImage); // Resetting the image
                $img->resize(700, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $mediumImgPath = $destinationPath . pathinfo($featureImage, PATHINFO_FILENAME).'_'.$randomCode.'_medium.'.$extension;
                $img->save($mediumImgPath, 70);
              
                unlink($destinationPath.$featureImage);
            } elseif ($extension === 'gif') {
            // Handle direct GIF uploads
                $originalGif = $imgname . '_' . $randomCode . '_video.gif';
                $smallGif    = $imgname . '_' . $randomCode . '_video_small.gif';
                $mediumGif   = $imgname . '_' . $randomCode . '_video_medium.gif';
                $smallJpg    = $imgname . '_' . $randomCode . '_video_small.jpg';

                // Move original GIF
                $files->move($destinationPath, $originalGif);

                // ✅ Just copy the original GIF without resizing
                try {
                    copy($destinationPath . $originalGif, $destinationPath . $smallGif);
                    copy($destinationPath . $originalGif, $destinationPath . $mediumGif);
                } catch (\Exception $e) {
                    \Log::error("GIF copy failed: " . $e->getMessage());
                }
                // ✅ Create JPG thumbnail (first frame only)
                try {
                    $thumb = Image::make($destinationPath . $originalGif);
                    $thumb->resize(320, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->save($destinationPath . $smallJpg, 90);
                } catch (\Exception $e) {
                    \Log::error("Thumbnail JPG creation failed: " . $e->getMessage());
                }
                // Save to DB
                $webpimg = $originalGif;
               
            }elseif (str_starts_with($mimeType, 'video')) {
                $uploadVideo = $imgname . '_' . $randomCode . '.' . $files->extension();
                $webpimg = pathinfo($imgname, PATHINFO_FILENAME) . '_' . $randomCode . '.' . $files->extension();
                $files->move($destinationPath, $uploadVideo);
                ProcessVideoPreviewgif::dispatch(
                    $destinationPath,
                    $uploadVideo,
                    $imgname,
                    $randomCode
                );
                // $videoPath = $destinationPath . $uploadVideo;

                // $ffmpeg = FFMpeg::create([
                //     'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                //     'ffprobe.binaries' => '/usr/bin/ffprobe',
                // ]);

                // $ffprobe = \FFMpeg\FFProbe::create([
                //     'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                //     'ffprobe.binaries' => '/usr/bin/ffprobe',
                // ]);

                // $durationInSeconds = (int) $ffprobe->format($videoPath)->get('duration');

                // // Common output paths
                // $screenshotPathGif = $destinationPath . $imgname . '_' . $randomCode . '_video.gif';
                // $screenshotPathsmallGif = $destinationPath . $imgname . '_' . $randomCode . '_video_small.gif';
                // $smallImgPath = $destinationPath . $imgname . '_' . $randomCode . '_video_small.jpg';

                // // Open the video
                // $video = $ffmpeg->open($videoPath);

                // if ($durationInSeconds < 5) {
                //     // For videos shorter than 7s, generate GIF from 1s to 3s
                //     $gifStart = 1;
                //     $gifDuration = 2;
                // } else {
                //     // For videos 7s or more, generate GIF from 5s to 7s
                //     $gifStart = 5;
                //     $gifDuration = 2;
                // }

                // try {
                //     // Main GIF
                //     $video->gif(TimeCode::fromSeconds($gifStart), new Dimension(320, 300), TimeCode::fromSeconds($gifDuration))
                //           ->save($screenshotPathGif);

                //     // Small GIF (0.2s)
                //     $video->gif(TimeCode::fromSeconds($gifStart), new Dimension(320, 300), TimeCode::fromSeconds(0.2))
                //           ->save($screenshotPathsmallGif);

                //     // Extract frame and resize for JPG
                //     $tempFramePath = tempnam(sys_get_temp_dir(), 'frame') . '.jpg';
                //     $video->frame(TimeCode::fromSeconds($gifStart))->save($tempFramePath);

                //     if (file_exists($tempFramePath)) {
                //         $img = Image::make($tempFramePath);
                //         $img->resize(320, 300, function ($constraint) {
                //             $constraint->aspectRatio();
                //             $constraint->upsize();
                //         });
                //         $img->save($smallImgPath, 90);
                //         unlink($tempFramePath);
                //     } else {
                //         \Log::error("FFmpeg failed to extract frame for $smallImgPath");
                //     }

                // } catch (\Exception $e) {
                //     \Log::error("Video processing failed: " . $e->getMessage());
                // }
            }
            elseif (str_starts_with($mimeType, 'application/pdf')) {
                $featureImage = $imgname.'_'.$randomCode.'.'.$files->extension(); 
                $webpimg = pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.'.$files->extension();  
                $files->move($destinationPath, $featureImage);
            } else {
                return response()->json(['message' => 'Unsupported file type'], 400);
            }
            //$path = url('/public/media/');
          	//$path = 'https://www.gstv.in/backend/public/media';
            $path = config('app.front_url')."/backend/public/media/$year/$month/$day";
            $fullpath = $path.'/'.$webpimg;
            $media = new Media([
                'userID' => $request->get('userID'),
                'photos'=> $webpimg,
                'photoTitle' => $request->get('photoTitle'),
                'photoAlt' => $request->get('photoAlt'),
                'fullpath' => $fullpath
            ]);
            $media->save();
            $imageId = $media->id;
        }

            return response()->json([
                'success' => true,
                'imageId' => $imageId,
                'imageUrl' => $fullpath
            ], 200);
 
    }

    private function getUniqueFileName($originalFileName, $extension)
    {
        
        $newFileName = $originalFileName . '.' . $extension;
        $counter = 1;
        
        $fileExistsInDatabase = Media::where('photos', $newFileName)->exists();
        
        if($fileExistsInDatabase) {
            $newFileName = $originalFileName . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $newFileName;
    }
    public function updateStatus(Request $request)
    { 
        $id = $request->input('id');
        $status = $request->input('status');
        $news = News::find($id);
        $news->status = $status;
        if ($status === 'Active') {
            $news->created_at = now(); // Reset publish time if reactivating
        }

        $news->save();
        return response()->json([
                'success' => true,
                'message' => 'News Status has been updated successfully',
                'news_id' => $id
            ], 200);
 
    }

    public function destroy(Request $request)
    { 
        $id = $request->input('id');
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'News not found.',
            ], 404);
        }

        $featureImage = $news->featureImage;
        $videoURL = $news->videoURL;

        // Define the public media path
        $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/backend/public/media/';

        // Delete feature image
        if (!empty($featureImage)) {
            $fimage = explode('backend/public/media/', $featureImage);
            if (isset($fimage[1])) {
                $featureImagePath = $mediaPath . '/' . $fimage[1];
                if (File::exists($featureImagePath)) {
                    File::delete($featureImagePath);
                }
            }
        }

        // Delete video
        if (!empty($videoURL)) {
            $vurl = explode('backend/public/media/', $videoURL);
            if (isset($vurl[1])) {
                $videoPath = $mediaPath . '/' . $vurl[1];
                if (File::exists($videoPath)) {
                    File::delete($videoPath);
                }
            }
        }

        $news->delete();
        return response()->json([
            'success' => true,
            'message' => 'News has been deleted successfully.',
            'news_id' => $id
        ], 200);
    }
    public function deletemedia(Request $request)
    {  
        $id = $request->input('id');
        $media = Media::find($id); 
        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found.',
            ], 404);
        }
        $photos = $media->photos;
        $mediapath =  explode('media',$media->fullpath); 
        $path =  dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/backend/public/media'.$mediapath[1];
        
         
        $filePath = $path;
        //unlink($filePath);
        // Generate paths for other image sizes
        $pathInfo = pathinfo($filePath);
        $smallImagePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_small.' . $pathInfo['extension'];
        $mediumImagePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_medium.' . $pathInfo['extension'];
        $mediumviedogifPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_video.gif';
        $mediumviedogifPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_video.png';

        // Delete the original and other sized images
        File::delete($filePath);
        File::delete($smallImagePath);
        File::delete($mediumImagePath);
        File::delete($mediumviedogifPath);

        // Delete the media record
        $media->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Media has been deleted successfully.',
            'news_id' => $id
        ], 200);
    }

     public function userslist(Request $request)
    {
        $perPage = 20;
        $currentPage = $request->input('page', 1);
        $search = $request->input('search');

        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $filters = $request->only('name', 'email', 'roleID', 'status');

        $query = DB::table('users')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.status',
                    'users.created_at',
                    'users.profileImg',
                    'roles.title as roleTitle',
                    DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                    DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = CURDATE()) as today_news_count")
                )
                ->leftJoin('roles', 'roles.id', '=', 'users.roleID');
        $search = $request->input('search');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('roles.title', 'like', "%{$search}%");
            });
        }
        // ✅ Filters
        if (!empty($filters['name'])) {
            $query->where('users.name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['email'])) {
            $query->where('users.email', 'like', "%{$filters['email']}%");
        }

        if (!empty($filters['roleID'])) {
            $query->where('users.roleID', $filters['roleID']);
        }

        if (!empty($filters['status'])) {
            $query->where('users.status', $filters['status']);
        }

        // ✅ Ordering
        $query->orderByRaw("FIELD(users.status,'Active','Inactive')")
         ->orderBy('users.created_at','ASC');

        // ✅ Pagination
        $users = $query->paginate($perPage);

        // ✅ Format Response
        $responseData = [];

        foreach ($users as $user) {
            $profileImgUrl = $user->profileImg
            ? config('app.backendnewsurl') . '/public/uploads/profile/' . $user->profileImg
            : config('app.backendnewsurl') . '/public/uploads/profile/avatar_male.png'; // or '' if you want empty string instead of null
            $responseData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roleTitle,
                'status' => $user->status,
                'profileImg' => $profileImgUrl,
                'created_at' => $user->created_at,
                'today_news_count' => $user->today_news_count,
                'total_news_count' => $user->total_news_count,
            ];
        }

        return response()->json([
            'users' => $responseData,
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $perPage,
            'total' => $users->total()
        ]);
    }
    public function userProfileNewsList(Request $request)
    {
        $userId = $request->input('userid');

        $perPage = 20;
        $currentPage = $request->input('page', 1);

        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        $search = $request->input('search');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // ✅ Date parsing
        if (!empty($startDate) && !empty($endDate)) {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        $query = DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.created_at',
                'news.userID',
                'u1.name as authorName',
                DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.id) as categoryIds'),
                'newscount.view_count as total_view_count'
            )
            ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
            ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
            ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
            ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id')
            ->where('news.userID', $userId)
            ->groupBy('news.id');
	 
        // ✅ Date filter
    	if (!empty($startDate) && !empty($endDate)) {

           $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
           $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

          $query->whereBetween('news.created_at', [$startDate, $endDate]);
		}

        // ✅ Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('news.title', 'like', "%{$search}%")
                ->orWhere('categories.title', 'like', "%{$search}%");
            });
        }

        // ✅ Order
        $query->orderBy('news.id', 'DESC');

        // ✅ Pagination
        $newsList = $query->paginate($perPage);

        $baseUrl = config('app.front_url');

        // ✅ Format response
        $responseData = [];

        foreach ($newsList as $news) {

            $catIDs = explode(',', $news->categoryIds ?? '');
            $viewer = $news->total_view_count ?? 0;

            // ✅ URL logic (no HTML)
            if (in_array('9', $catIDs)) {
                $url = $baseUrl . "/" . $news->categorySlugs . "/" . $news->slug;
            } else {
                $url = $baseUrl . "/news/" . $news->categorySlugs . "/" . $news->slug;
            }

            $responseData[] = [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'url' => $url,
                'categories' => $news->categoryNames,
                'category_ids' => $catIDs,
                'authorName' => $news->authorName,
                'viewer' => $viewer,
                'created_at' => $news->created_at,
                'date' => \Carbon\Carbon::parse($news->created_at)->format('d-m-Y')
            ];
        }

        return response()->json([
            'news' => $responseData,
            'current_page' => $newsList->currentPage(),
            'last_page' => $newsList->lastPage(),
            'per_page' => $perPage,
            'total' => $newsList->total()
        ]);
    }
    public function reporterforceupdate(Request $request)
    {
         $data = Redis::get('settingsappvreporter13'. config('app.webtype'));
        if (!$data) {
            $data = DB::table('reporter_force')->first(); // get single row
            Redis::set('settingsappvreporter13'. config('app.webtype'), json_encode($data)); // Store in Redis
        } else {
            $data = json_decode($data, true);
        }
 
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
