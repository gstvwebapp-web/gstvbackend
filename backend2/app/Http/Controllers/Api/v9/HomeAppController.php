<?php
namespace App\Http\Controllers\Api\v9;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\News;
use App\Models\Setting;
use App\Models\Newslike;
use App\Models\Newsread;
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
use App\Models\Epapercategory;
use App\Models\Epaper;
use App\Models\Gift;
use App\Models\Usernews;
use App\Models\Giftredeemcoins;
use App\Models\Sidemenu;
use App\Models\Drawpoll;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Route;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccessMail;
use App\Services\ViewCountService;
use App\Models\UserSubscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use App\Mail\CareerEmail;


class HomeAppController extends Controller
{
    protected $viewCountService;
    protected $api;

    public function __construct(ViewCountService $viewCountService)
    {
        $this->viewCountService = $viewCountService;
        $this->api  = new Api('rzp_test_RGayUf4jrYOXOT', 'wlQKkIhATwdrOmf7ModT2jn9');
    }
    public function tophomecategory(Request $request)
  {
      $data = []; 
      $pageNumber = $request->input('pageNumber', 1); 
      $itemsPerPage = 1;
      $cacheKey = "tophomecategory_page_{$pageNumber}_" . config('app.webtype');

      // ✅ Check Redis Cache
      if (Redis::exists($cacheKey)) {
          return response()->json(json_decode(Redis::get($cacheKey)), 200, [], JSON_UNESCAPED_UNICODE);
      }

      // ✅ Get Paginated Categories (Only required fields)
      $category = DB::table('categories as c1')
          ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
          ->where('c1.status', 'Active')
          ->where('c1.setHome', 1)
          ->where('c1.id', '!=', 9)
          ->orderBy('c1.catOrder', 'ASC')
          ->select('c1.id', 'c1.title as category_name', 'c1.category_name_guj', 'c2.title as parent_category_name')
          ->paginate($itemsPerPage, ['*'], 'page', $pageNumber);

      // ✅ No categories found
      if ($category->isEmpty()) {
          return response()->json(['message' => 'No categories found'], 404);
      }

      $selectedCategory = $category->first();
      $categoryId = $selectedCategory->id;

      // ✅ Get News related to the category (with slugs using a subquery join)
      $newsItems = DB::table('news as n')
          ->join('news_category as nc', 'n.id', '=', 'nc.news_id')
          ->where('nc.category_id', $categoryId)
          ->where('n.status', 'Active')
          ->where('n.is_home', '!=', 1)
        //   ->select('n.*')
         ->select(
                    'n.id','n.slug','n.title','n.featureImage','n.videoURL','n.created_at',
                    DB::raw('(SELECT GROUP_CONCAT(category_id ORDER BY category_id) FROM news_category WHERE news_id = n.id) as catID')   
                )
         ->orderByDesc('n.created_at')
          //->orderByRaw("CASE WHEN n.catOrder != 0 THEN 0 ELSE 1 END, n.catOrder ASC")
          ->limit(4)
          ->get();

      // Get all news IDs
      $newsIds = $newsItems->pluck('id')->toArray();

      // Fetch all related category slugs and IDs for these news in one go
      $categoryMap = DB::table('news_category as nc')
          ->join('categories as c', 'nc.category_id', '=', 'c.id')
          ->whereIn('nc.news_id', $newsIds)
          ->orderBy('c.id')
          ->get()
          ->groupBy('news_id');

      // Map results with category data
      $newsData = $newsItems->map(function ($news) use ($categoryMap) {
          $categories = $categoryMap[$news->id] ?? collect();
          $news->category_slugs = $categories->pluck('slug')->implode(',');
          $news->catID = $categories->pluck('category_id')->implode(',');
          return $news;
      });

      // ✅ Prepare response data
      $data['homecategory'][$selectedCategory->category_name . '|' . $selectedCategory->category_name_guj] = $newsData;
      $data['currentPage'] = $category->currentPage();
      $data['nextPage'] = $category->hasMorePages() ? $category->currentPage() + 1 : null;
      $data['totalPages'] = $category->lastPage();

      // ✅ Cache data for 30 mins
      Redis::setex($cacheKey, 1800, json_encode($data, JSON_UNESCAPED_UNICODE));

      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
  }
  public function tophomecategoryweb(Request $request)
  {
      $data = []; 
      $pageNumber = $request->input('pageNumber', 1); 
      $itemsPerPage = 1;
      $cacheKey = "tophomecategorytop_page_{$pageNumber}_" . config('app.webtype');

      // ✅ Check Redis Cache
      if (Redis::exists($cacheKey)) {
          return response()->json(json_decode(Redis::get($cacheKey)), 200, [], JSON_UNESCAPED_UNICODE);
      }

      // ✅ Get Paginated Categories (Only required fields)
      $category = DB::table('categories as c1')
          ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
          ->where('c1.status', 'Active')
          ->where('c1.setHome', 1)
          ->where('c1.id', '!=', 9)
          ->orderBy('c1.catOrder', 'ASC')
          ->select('c1.id', 'c1.title as category_name', 'c1.category_name_guj', 'c2.title as parent_category_name')
          ->paginate($itemsPerPage, ['*'], 'page', $pageNumber);

      // ✅ No categories found
      if ($category->isEmpty()) {
          return response()->json(['message' => 'No categories found'], 404);
      }

      $selectedCategory = $category->first();
      $categoryId = $selectedCategory->id;

      // ✅ Get News related to the category (with slugs using a subquery join)
      $newsItems = DB::table('news as n')
          ->join('news_category as nc', 'n.id', '=', 'nc.news_id')
          ->where('nc.category_id', $categoryId)
          ->where('n.status', 'Active')
          ->where('n.is_home', '!=', 1)
         ->select(
                    'n.id','n.slug','n.title','n.description','n.featureImage','n.videoURL','n.created_at',
                    DB::raw('(SELECT GROUP_CONCAT(category_id ORDER BY category_id) FROM news_category WHERE news_id = n.id) as catID')  
         )
          ->orderByDesc('n.created_at')
          //->orderByRaw("CASE WHEN n.catOrder != 0 THEN 0 ELSE 1 END, n.catOrder ASC")
          ->limit(4)
          ->get();

      // Get all news IDs
      $newsIds = $newsItems->pluck('id')->toArray();

      // Fetch all related category slugs and IDs for these news in one go
      $categoryMap = DB::table('news_category as nc')
          ->join('categories as c', 'nc.category_id', '=', 'c.id')
          ->whereIn('nc.news_id', $newsIds)
          ->orderBy('c.id')
          ->get()
          ->groupBy('news_id');

      // Map results with category data
      $newsData = $newsItems->map(function ($news) use ($categoryMap) {
          $categories = $categoryMap[$news->id] ?? collect();
          $news->category_slugs = $categories->pluck('slug')->implode(',');
          $news->catID = $categories->pluck('category_id')->implode(',');
          return $news;
      });

      // ✅ Prepare response data
      $data['category_name_guj'] = $selectedCategory->category_name_guj;
      $data['homecategory'][$selectedCategory->category_name . '|' . $selectedCategory->category_name_guj] = $newsData;
      $data['currentPage'] = $category->currentPage();
      $data['nextPage'] = $category->hasMorePages() ? $category->currentPage() + 1 : null;
      $data['totalPages'] = $category->lastPage();

      // ✅ Cache data for 30 mins
      Redis::setex($cacheKey, 1800, json_encode($data, JSON_UNESCAPED_UNICODE));

      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
  }

    public function electionresults()
    {
      $data =  array('election_status'=>0, 'height'=>400, 'election_url'=>'https://www.gstv.in/electionresults');
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function getcategorylist()
    {
        $cacheKey = "category_list".config('app.webtype');
    
        // Check if data exists in Redis
        $cachedData = Redis::get($cacheKey);
    
        if ($cachedData) {
            return response()->json(json_decode($cachedData), 200, [], JSON_UNESCAPED_UNICODE);
        }
    
        $category = DB::table('categories as c1')
            ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
            ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
            ->where('c1.status', 'Active')
            ->orderBy('c1.catOrder', 'ASC')
            ->get();
    
        $data['category'] = $category;
    
        // Store data in Redis without expiration
        Redis::set($cacheKey, json_encode($data));
    
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function addusernews(Request $request)
    {
        $uploadedImages = [];
        if ($request->hasFile('uploadimages')) {
            foreach ($request->file('uploadimages') as $file) {
                $imageName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . "/backend/public/media/";
                $file->move($destinationPath, $imageName);
                $uploadedImages[] = $imageName;
            }
        }
    
        $usernewsData = [
            'userID' => $request->user_id,
            'title' => $request->title,
            'imgtype' => $request->imgtype,
            'description' => $request->description,
            'featureImage' => json_encode($uploadedImages), // Store filenames as JSON
            'status' => 'Under Review',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    
        // Insert the data and get the last inserted ID
        $insertedId = DB::table('usernews')->insertGetId($usernewsData);
    
        if ($insertedId) {
            // Add additional parameters
            $newslist = DB::table('usernews')
            ->leftJoin('news', 'usernews.id', '=', 'news.usernews_userID')
            ->select(
                'usernews.id',
                'usernews.title',
                'usernews.description',
                'usernews.status',
                'usernews.created_at',
                'usernews.featureImage as usersimg',
                'news.featureImage',
                'news.slug',
                'news.videoURL',
                'news.viewer',
                'news.catID'
            )
            ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categoryNames')
            ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')
            ->where('usernews.id', $insertedId)
            ->first();
        
            // Wrap the result in a collection to use transform()
            $newslist = collect([$newslist])->transform(function ($news) {
                $userimgarr = json_decode($news->usersimg);
                $imgs = [];
                if (!empty($userimgarr)) {
                    foreach ($userimgarr as $img) {
                        $imgs[] = config('app.backendnewsurl') . '/public/media/' . $img;
                    }
                } else {
                    $imgs[] = config('app.backendnewsurl') . '/public/assets/img/news-default.png';
                }
                $imgsencode = implode('|', $imgs);
            
                if ($news->status == "Active") {
                    $news->status = 'Published';
                    if ($news->featureImage == "") {
                        $news->featureImage = config('app.backendnewsurl') . '/public/assets/img/news-default.png';
                    }
                } elseif ($news->status == "Inactive") {
                    $news->status = 'Rejected';
                } else {
                    $news->featureImage = $imgsencode;
                }
                return $news;
            })->first(); // Extract the first item back as an object
            
            $data = [
                'message' => 'User News added successfully.',
                'newsdata' => $newslist
            ];
    
            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $data = ['message' => 'Failed to add User News.'];
            return response()->json($data, 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function getusernews(Request $request)
    {
        try {
            $perPage = 10; // Define the number of news items per page
            $pageNumber = $request->input('pageNumber');
            $userId = $request->input('user_id'); // User ID from request

            // Check if user ID is provided
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required',
                ], 400); // Bad Request
            }
    		 
            $newslist = DB::table('usernews')
                        ->leftJoin('news', 'usernews.id', '=', 'news.usernews_userID')
                        ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
                        ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
                        ->select(
                            'usernews.id',
                            'usernews.title',
                            'usernews.description',
                            'usernews.status',
                            'usernews.imgtype',
                            'usernews.created_at',
                            'usernews.featureImage as usersimg',
                            'news.featureImage',
                            'news.slug',
                            'news.videoURL',
                            'news.viewer',
                            'news_category.category_id as catID',
                            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id SEPARATOR ", ") as categoryNames'),
                            DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR "/") as categorySlugs')
                        )
                        ->where('usernews.userID', $userId)
                        ->groupBy(
                            'usernews.id',
                            'usernews.title',
                            'usernews.description',
                            'usernews.status',
                            'usernews.imgtype',
                            'usernews.created_at',
                            'usernews.featureImage',
                            'news.featureImage',
                            'news.slug',
                            'news.videoURL',
                            'news.viewer',
                            'news_category.category_id'
                        )
                        ->orderBy('usernews.id', 'desc')
                        ->paginate($perPage, ['*'], 'page', $pageNumber);

            
            
            $newslist->transform(function ($news) {
                $userimgarr = json_decode($news->usersimg);
                $imgs = [];
                if(!empty($userimgarr)){
                    foreach($userimgarr as $img){
                        $imgs[] = config('app.backendnewsurl').'/public/media/'.$img;
                    }
                } else {
                    $imgs[] = config('app.backendnewsurl').'/public/assets/img/news-default.png';
                }
                $imgsencode = implode('|', $imgs);
                if ($news->status == "Active") {
                    $news->status = 'Published';
                    if($news->featureImage == ""){
                        $news->featureImage =config('app.backendnewsurl').'/public/assets/img/news-default.png';
                    }
                    $news->featureImage = $news->featureImage;
                } elseif ($news->status == "Inactive") {
                    $news->status = 'Rejected';
                } else {
                    $news->featureImage = $imgsencode;
                }
                return $news;
            }); 
            // Prepare the response data
            $responseData = [
                'success' => true,
                'message' => 'News list retrieved successfully',
                'currentPage' => $newslist->currentPage(),
                'lastPage' => $newslist->lastPage(),
                'newslist' => $newslist->items(),
            ];
            // Return the JSON response
            return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500); // Server error response
        }
    }
    public function editusernews(Request $request) {
        $userId = $request->user_id;
        $newsid = $request->news_id;
        $data = array();
        
        // Fetch user data using the user ID
        $newslist = Usernews::where('id', $newsid)
                    ->where('userID', $userId)
                    ->first();
    
        if ($newslist) {
            // Decode and modify featureImage
            $userimgarr = json_decode($newslist->featureImage);
            $imgs = [];
            if (!empty($userimgarr)) {
                foreach ($userimgarr as $img) {
                    $imgs[] = config('app.url') . '/public/media/' . $img;
                }
            }
            // Encode the modified image array
            $newslist->featureImage = json_encode($imgs);
    
            $data['newslist'] = $newslist;
            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json($data, 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function updateusernews(Request $request) {
        $userId = $request->user_id;
        $newsId = $request->news_id;
        $data = array();
        $news = DB::table('usernews')->where('id', $newsId)->first();
        if (!$news) {
            return response()->json($data, 404, [], JSON_UNESCAPED_UNICODE);
        }
        // Handle the uploaded files
        $uploadedImages = [];
        if ($request->hasFile('uploadimages')) {
            foreach ($request->file('uploadimages') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . "/backend/public/media/";
                $file->move($destinationPath, $filename);
                //$file->move($mediaPath, $filename);
                $uploadedImages[] = $filename;
            }
        } 
        // If new images are uploaded, merge them with existing images
        $existingImages = json_decode($news->featureImage, true) ?? [];
        $updatedImages = []; 
        if (!empty($uploadedImages)) {
            $updatedImages = array_merge($uploadedImages,$existingImages);
        } else {
            $updatedImages = $existingImages; // If no new images, keep existing ones
        }
        // Prepare data for updating the news
        $data = [
            'userID' => $userId,
            'title' => $request->title,
            'description' => $request->description,
            'featureImage' => json_encode($updatedImages),
            'updated_at' => now()
        ];

        // Update the news entry in the database
        DB::table('usernews')->where('id', $newsId)->update($data);
       $newslist = DB::table('usernews')
                ->leftJoin('news', 'usernews.id', '=', 'news.usernews_userID')
                ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
                ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
                ->select(
                    'usernews.id',
                    'usernews.title',
                    'usernews.description',
                    'usernews.status',
                    'usernews.created_at',
                    'usernews.featureImage as usersimg',
                    'news.featureImage',
                    'news.slug',
                    'news.videoURL',
                    'news.viewer',
                    'news_category.category_id as catID',
                    DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id SEPARATOR ", ") as categoryNames'),
                    DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR "/") as categorySlugs')
                )
                ->where('usernews.id', $newsId)
                ->groupBy(
                    'usernews.id',
                    'usernews.title',
                    'usernews.description',
                    'usernews.status',
                    'usernews.created_at',
                    'usernews.featureImage',
                    'news.featureImage',
                    'news.slug',
                    'news.videoURL',
                    'news.viewer',
                    'news_category.category_id'
                )
                ->first();

            // Wrap the result in a collection to use transform()
            $newslist = collect([$newslist])->transform(function ($news) {
                $userimgarr = json_decode($news->usersimg);
                $imgs = [];
                if (!empty($userimgarr)) {
                    foreach ($userimgarr as $img) {
                        $imgs[] = config('app.backendnewsurl') . '/public/media/' . $img;
                    }
                } else {
                    $imgs[] = config('app.backendnewsurl') . '/public/assets/img/news-default.png';
                }
                $imgsencode = implode('|', $imgs);
            
                if ($news->status == "Active") {
                    $news->status = 'Published';
                    if ($news->featureImage == "") {
                        $news->featureImage = config('app.backendnewsurl') . '/public/assets/img/news-default.png';
                    }
                } elseif ($news->status == "Inactive") {
                    $news->status = 'Rejected';
                } else {
                    $news->featureImage = $imgsencode;
                }
                return $news;
            })->first(); // Extract the first item back as an object
        $data = array('message' => 'User News updated successfully.','newsdata'=>$newslist);
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function deleteusernewsimg(Request $request) {
        $userId = $request->user_id;
        $images = $request->image_name;
        $newsId = $request->news_id;
        $news = DB::table('usernews')->where('id', $newsId)->first();
        $ff = json_decode($news->featureImage);
        $updatedImages = array_filter($ff, function($image) use ($images) {
            return $image !== $images; // Keep images that are not equal to $imagesToRemove
        });
         // Re-index the array to maintain the numeric keys
        $updatedImages = array_values($updatedImages);
        if ($images) {
            $updateData = [
                'featureImage' => json_encode($updatedImages),
            ];

            $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/media';
            if ($images && file_exists($mediaPath.'/' . $images)) {
                unlink($mediaPath.'/' . $images);
            }

            // Update the news entry in the database
            DB::table('usernews')->where('id', $newsId)->where('userID', $userId)->update($updateData);
            $data = array('message' => 'User Image deleted successfully.');
            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        } 
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
     public function CategoryNews(Request $request)
    {
        $slug = $request->input('slug');
        $subslug = $request->input('subslug');
        $device_id = $request->input('device_id');
        $user_id = $request->input('user_id');
        $perPage = $request->input('per_page', 4);
        $page = $request->input('page', $request->input('pageNumber', 1));
        /*if($slug == "videos" && $page == 1){
            $perPage = 8;
        }*/

        $cacheSlug = $subslug === 'undefined' ? $slug : $subslug;
        $categoryKey = "category_detailsnews:{$slug}_{$subslug}_" . config('app.webtype');

        // 🧠 Fetch category from Redis or DB
        $newcategory = Redis::get($categoryKey);
        if (!$newcategory) {
            $newcategory = DB::table('categories')
                ->select('id', 'title as catTitle', 'slug as catSlug', 'metatitle', 'metadesc')
                ->where('slug', $cacheSlug)
                ->where('status', 'Active')
                ->first();

            if (!$newcategory) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            Redis::setex($categoryKey, 1800, json_encode($newcategory));
        } else {
            $newcategory = json_decode($newcategory);
        }

        $categoryId = $newcategory->id;
        $redisKey = "category_news:{$categoryId}:page_{$page}_" . config('app.webtype');

        $cachedNews = Redis::get($redisKey);
        if ($cachedNews) {
            return response()->json(json_decode($cachedNews, true));
        }

        // 📊 Like/Dislike subquery
        $subquery = DB::table('videolike')
            ->select(
                'video_id',
                DB::raw('COUNT(CASE WHEN status = "1" THEN 1 END) as like_count'),
                DB::raw('COUNT(CASE WHEN status = "0" THEN 1 END) as dislike_count'),
                DB::raw('COUNT(CASE WHEN status = "1" AND ipAddress = "' . $device_id . '" THEN 1 END) as liked_by_user'),
                DB::raw('COUNT(CASE WHEN status = "0" AND ipAddress = "' . $device_id . '" THEN 1 END) as disliked_by_user')
            )
            ->groupBy('video_id');

        $newsQuery = DB::table('news')
            ->leftJoinSub($subquery, 'vl', function ($join) {
                $join->on('news.id', '=', 'vl.video_id');
            })
            ->join('news_category', 'news.id', '=', 'news_category.news_id')
            ->leftJoin('categories', 'categories.id', '=', 'news_category.category_id')
            ->where(function ($query) use ($newcategory) {
                if ($newcategory->id == 9) {
                    $query->orWhere('news.is_vertical_video', 9);
                }
                if (!empty($newcategory->id)) {
                    $query->orWhere('news_category.category_id', $newcategory->id);
                }
            })
            ->where('news.status', 'Active')
            ->select(
                'news.*',
                'vl.like_count',
                'vl.dislike_count',
                'vl.liked_by_user',
                'vl.disliked_by_user'
            )
            ->groupBy('news.id', 'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user')
            //->orderBy('news.created_at', 'DESC')
            ->orderByRaw("CASE WHEN news.catOrder != 0 THEN 0 ELSE 1 END, news.catOrder ASC, news.created_at DESC")
            ->paginate($perPage, ['*'], 'page', $page);

        $newsIds = $newsQuery->pluck('id')->toArray();

        // 🗂️ Map news_id => [category_id, slug]
        $catMap = DB::table('news_category')
            ->join('categories', 'categories.id', '=', 'news_category.category_id')
            ->whereIn('news_category.news_id', $newsIds)
            ->orderBy('news_category.category_id')
            ->get()
            ->groupBy('news_id');

        // 🔖 Bookmark
        $bookmarkData = [];
        if (!empty($user_id)) {
            $bookmarkData = DB::table('bookmark')
                ->where('user_id', $user_id)
                ->whereIn('news_id', $newsIds)
                ->pluck('status', 'news_id')
                ->toArray();
        }

        // 🔄 Final Transform
        $newsQuery->getCollection()->transform(function ($news) use ($catMap, $bookmarkData) {
            $categories = $catMap[$news->id] ?? collect();
            $news->catID = $categories->pluck('category_id')->implode(',');
            $news->category_slugs = $categories->pluck('slug')->implode(',');
            $news->like_count = (int) $news->like_count;
            $news->dislike_count = (int) $news->dislike_count;
            $news->liked_by_user = (int) $news->liked_by_user;
            $news->disliked_by_user = (int) $news->disliked_by_user;
            $news->bookmark = isset($bookmarkData[$news->id]) ? (int)$bookmarkData[$news->id] : 0;
            $news->description = Str::limit($news->description, 120);
            return $news;
        });

        // ✅ Final Response
        $data = [
            'news' => $newsQuery,
            'catTitle' => $newcategory->catTitle,
            'catSlug' => $newcategory->catSlug,
            'catMetatitle' => $newcategory->metatitle,
            'catMetadesc' => $newcategory->metadesc
        ];
        if($slug == "videos"){
            $cacheKeyepaer = 'getepaperimg_' . config('app.webtype');
            $epaper = Redis::get($cacheKeyepaer);
            if (!$epaper) {
                $epaper = DB::table('epaper')
                    ->select([
                        'epaper.id',
                        'epaper.newspaperdate', 
                        'epaper.Story_data',
                        'epapercategory.slug'
                    ])
                    ->join('epapercategory', 'epaper.ecatID', '=', 'epapercategory.id')
                    ->where('epaper.status', 'Active')
                    ->where('epapercategory.cattype', 'Newspaper')
                    ->orderBy('epaper.id', 'DESC')
                    ->limit(1)
                    ->first();
                if (!$epaper) {
                    return response()->json(['error' => 'Epaper not found'], 404);
                }
                Redis::setex($cacheKeyepaer, 1800, json_encode($epaper)); // 30 mins
            } else {
                $epaper = json_decode($epaper);
            }
              
            if ($epaper) {
                $storyData = json_decode($epaper->Story_data, true);
                $firstImage = $storyData[0]['image'] ?? null; 
            } 
            $newspaperdt = date('d-m-Y',strtotime($epaper->newspaperdate));
            $data['epaper_slug'] = $epaper->slug;
            $data['epaper_date'] = $newspaperdt;
            $data['epaper_img'] = $firstImage;
        }

        Redis::setex($redisKey, 600, json_encode($data));
        return response()->json($data);
    }
    public function CategoryNews0708(Request $request)
    {
        $slug = $request->input('slug');
        $subslug = $request->input('subslug');
        $device_id = $request->input('device_id');
        $user_id = $request->input('user_id');
        $perPage = $request->input('per_page', 4);
        $page = $request->input('page', $request->input('pageNumber', 1));

        $cacheSlug = $subslug === 'undefined' ? $slug : $subslug;
        $categoryKey = "category_detailsnews:{$slug}_{$subslug}_" . config('app.webtype');

        // 🧠 Fetch category from Redis or DB
        $newcategory = Redis::get($categoryKey);
        if (!$newcategory) {
            $newcategory = DB::table('categories')
                ->select('id', 'title as catTitle', 'slug as catSlug', 'metatitle', 'metadesc')
                ->where('slug', $cacheSlug)
                ->where('status', 'Active')
                ->first();

            if (!$newcategory) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            Redis::setex($categoryKey, 1800, json_encode($newcategory)); // 30 mins
        } else {
            $newcategory = json_decode($newcategory);
        }

        $categoryId = $newcategory->id;
        $redisKey = "category_news:{$categoryId}:page_{$page}_" . config('app.webtype');

        // 🧠 Fetch cached news
        $cachedNews = Redis::get($redisKey);
        if ($cachedNews) {
            return response()->json(json_decode($cachedNews, true));
        }

        // 💡 Like/Dislike subquery with binding device_id only once
        if (empty($device_id)) {  
                    $subquery = DB::table('videolike')
                        ->select(
                            'video_id',
                            DB::raw('COUNT(CASE WHEN status = "1" THEN 1 END) as like_count'),
                            DB::raw('COUNT(CASE WHEN status = "0" THEN 1 END) as dislike_count'),
                            DB::raw('COUNT(CASE WHEN status = "1" THEN 1 END) as liked_by_user'),
                            DB::raw('COUNT(CASE WHEN status = "0" THEN 1 END) as disliked_by_user')
                        )
                        ->groupBy('video_id');

                    // Step 2: Fetch paginated news + 1 category ID (just for join)
                    $newsQuery = DB::table('news')
                        ->leftJoinSub($subquery, 'vl', function ($join) {
                            $join->on('news.id', '=', 'vl.video_id');
                        })
                        ->join('news_category', 'news.id', '=', 'news_category.news_id')
                        ->where(function ($query) use ($newcategory) {
                            $query->where('news.is_vertical_video', 9);
                            if (!empty($newcategory->id)) {
                                $query->orWhere('news_category.category_id', $newcategory->id);
                            }
                        })
                        ->where('news.status', 'Active')
                        ->select(
                            'news.*',
                            'vl.like_count',
                            'vl.dislike_count',
                            'vl.liked_by_user',
                            'vl.disliked_by_user'
                        )
                        ->groupBy('news.id', 'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user')
                        ->orderBy('news.created_at', 'DESC')
                        ->paginate($perPage, ['*'], 'page', $page);

                    // Step 3: Collect all news IDs
                    $newsIds = $newsQuery->pluck('id')->toArray();

                    // Step 4: Get all related category IDs for all news
                    $catMap = DB::table('news_category')
                        ->whereIn('news_id', $newsIds)
                        ->orderBy('category_id')
                        ->get()
                        ->groupBy('news_id');

                    // Step 5: Add catID as comma-separated string
                    $newsQuery->getCollection()->transform(function ($news) use ($catMap) {
                        $categories = $catMap[$news->id] ?? collect();
                        $news->catID = $categories->pluck('category_id')->implode(',');
                        return $news;
                    });

        } else { 
          // Step 1: Like/Dislike Subquery
                $subquery = DB::table('videolike')
                    ->select(
                        'video_id',
                        DB::raw('COUNT(CASE WHEN status = "1" THEN 1 END) as like_count'),
                        DB::raw('COUNT(CASE WHEN status = "0" THEN 1 END) as dislike_count'),
                        DB::raw('COUNT(CASE WHEN status = "1" AND ipAddress = "' . $device_id . '" THEN 1 END) as liked_by_user'),
                        DB::raw('COUNT(CASE WHEN status = "0" AND ipAddress = "' . $device_id . '" THEN 1 END) as disliked_by_user')
                    )
                    ->groupBy('video_id');

                // Step 2: Base News Query (with LEFT JOIN for video stats)
                $newsQuery = DB::table('news')
                    ->leftJoinSub($subquery, 'vl', function ($join) {
                        $join->on('news.id', '=', 'vl.video_id');
                    })
                    ->join('news_category', 'news.id', '=', 'news_category.news_id')
                    ->where(function ($query) use ($newcategory) {
                        if ($newcategory->id == 9) {
                            $query->orWhere('news.is_vertical_video', 9);
                        }
                        if (!empty($newcategory->id)) {
                            $query->orWhere('news_category.category_id', $newcategory->id);
                        }
                    })
                    ->where('news.status', 'Active')
                    ->select(
                        'news.*',
                        'vl.like_count',
                        'vl.dislike_count',
                        'vl.liked_by_user',
                        'vl.disliked_by_user'
                    )
                    ->groupBy(
                        'news.id',
                        'vl.like_count',
                        'vl.dislike_count',
                        'vl.liked_by_user',
                        'vl.disliked_by_user'
                    )
                    ->orderBy('news.created_at', 'DESC')
                    ->paginate($perPage, ['*'], 'page', $page);

                // Step 3: Get all news IDs from result
                $newsIds = $newsQuery->pluck('id')->toArray();

                // Step 4: Fetch all category IDs for those news
                $categoryMap = DB::table('news_category')
                    ->whereIn('news_id', $newsIds)
                    ->orderBy('category_id')
                    ->get()
                    ->groupBy('news_id');

                // Step 5: Attach catID as string of category IDs
                $newsQuery->getCollection()->transform(function ($item) use ($categoryMap) {
                    $item->catID = $categoryMap[$item->id]->pluck('category_id')->implode(',');
                    return $item;
                });


        }
        
        // 🔖 Bookmark data if user is logged in
        $bookmarkData = [];
        if (!empty($user_id)) {
            $newsIds = $newsQuery->pluck('id')->toArray();
            $bookmarkData = DB::table('bookmark')
                ->where('user_id', $user_id)
                ->whereIn('news_id', $newsIds)
                ->pluck('status', 'news_id')
                ->toArray();
        } 
        // 🔄 Map bookmark & shorten description
        $newsQuery->getCollection()->transform(function ($item) use ($bookmarkData) {
        	$item->like_count = (int) $item->like_count;
            $item->dislike_count = (int) $item->dislike_count;
            $item->liked_by_user = (int) $item->liked_by_user;
            $item->disliked_by_user = (int) $item->disliked_by_user;
            $item->bookmark = isset($bookmarkData[$item->id]) ? (int)$bookmarkData[$item->id] : 0;
            $item->description = Str::limit($item->description, 120);
            return $item;
        });

        // ✅ Prepare response
        $data = [
            'news' => $newsQuery,
            'catTitle' => $newcategory->catTitle,
            'catSlug' => $newcategory->catSlug,
            'catMetatitle' => $newcategory->metatitle,
            'catMetadesc' => $newcategory->metadesc
        ];

        // ✅ Cache for 10 minutes
        Redis::setex($redisKey, 600, json_encode($data));

        return response()->json($data);
    }
     public function topVideos()
    {
        $data = [];

        $webType = config('app.webtype');
        $redisTopVideosKey = 'topvides' . $webType;

        $cacheTTL = 600; // 10 minutes

        // --- TOP NEWS ---
        if (Redis::exists($redisTopVideosKey)) {
            $topvideos = collect(json_decode(Redis::get($redisTopVideosKey)));
        } else {
            $topvideos = DB::table('news')
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->join('categories', 'news_category.category_id', '=', 'categories.id')
                ->select(
                    'news.*',
                    DB::raw('(SELECT GROUP_CONCAT(category_id ORDER BY category_id) FROM news_category WHERE news_id = news.id) as catID'),
                    DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs')
                )
                ->where('news.status', 'Active')
                ->where(function ($query) {
                    $query->where('news.is_vertical_video', 9)
                       ->orWhere('news_category.category_id', 9);
                })
                ->groupBy('news.id')
                ->orderBy('news.created_at', 'DESC')
                ->limit(10)
                ->get();


            Redis::setex($redisTopVideosKey, $cacheTTL, json_encode($topvideos));
        }

        // Final JSON response
        return response()->json([
            'topvideos' => $topvideos,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    
    }
    
    public function CategorySetting(Request $request)
    {
        $userId = $request->input('user_id');
        $backendNewsUrl = config('app.backendnewsurl');
        $iconBaseUrl = $backendNewsUrl . '/public/uploads/category/icon/';
        $logoBaseUrl = $backendNewsUrl . '/public/uploads/logo/';
        $sidemenuUrl = $backendNewsUrl . '/public/uploads/sidemenu/';

        // Fetch category data from Redis or set it if not available
        $category = Redis::get('categoriesapp'.config('app.webtype'));
        if (!$category) {
            $category = DB::table('categories as c1')
                ->select(
                    'c1.id', 'c1.userID', 'c1.parentID','c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug',
                    'c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 
                    'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome','c1.setHome_app', 'c1.status',
                    'c1.created_at', 'c1.updated_at',
                    'c2.title as parent_category_name'
                )
                ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                ->where('c1.status', 'Active')
                ->orderBy('c1.catOrder', 'ASC')
                ->get()
                ->map(function ($item) use ($iconBaseUrl) {
                    $item->icon = $iconBaseUrl . $item->icon;
                    return $item;
                })
                ->toArray();
            
            Redis::set('categoriesapp'.config('app.webtype'), json_encode($category)); // Store in Redis
        } else {
            $category = json_decode($category, true);
        }

        // Fetch settings data from Redis or set it if not available
        $setting = Redis::get('settingsapp'.config('app.webtype'));
        if (!$setting) {
            $setting = DB::table('settings')
                ->select('logo', 'newsdefaulttxt', 'epaper_url', 'announcement', 'announcement_link','metatitle','metakeyword','metadesc',
                        'announcement_ios_link', 'announcement_status', 'election_status', 'election_link', 
                        'election_height', 'newfeatures', 'newfeatures_status','stockmarket','livescore','rashi_api','rashi_language','rashi_status','podcast_status','gujrat_agree_text','campus_agree_text','video_size','fullcontent_status','fullcontent_login','plan_days','plan_news_count','applypost','sudoku_link','ios_version','android_version','is_ios_update','is_android_update','livetv_url')
                ->get()
                ->map(function ($item) use ($logoBaseUrl) {
                    $item->logo = $logoBaseUrl . $item->logo;
                     return (array) $item;
                })
                ->toArray();
            Redis::set('settingsapp'.config('app.webtype'), json_encode($setting)); // Store in Redis
        } else {
            $setting = json_decode($setting, true);
        }
       
        // Fetch side menu data from Redis or set it if not available
        $sidemenu = Redis::get('sidemenuapp'.config('app.webtype'));
        if (!$sidemenu) {
            $sidemenu = DB::table('sidemenu')
                ->where('menustatus', '1')
                ->orderBy('menuorder', 'ASC')
                ->get()
                ->map(function ($item) use ($sidemenuUrl) {
                    $item->menuicon = $sidemenuUrl . $item->menuicon;
                    return $item;
                })
                ->toArray();
            
            Redis::set('sidemenuapp'.config('app.webtype'), json_encode($sidemenu)); // Store in Redis
        } else {
            $sidemenu = json_decode($sidemenu, true);
        }

        $sidemenuweb = Redis::get('sidemenuweb'.config('app.webtype'));
        if (!$sidemenuweb) {
            $sidemenuweb = DB::table('sidemenu')
                ->where('menuweb_status', '1')
                ->orderBy('menuorderweb', 'ASC')
                ->get()
                ->map(function ($item) use ($sidemenuUrl) {
                    $item->menuicon = $sidemenuUrl . $item->webicon;
                    return $item;
                })
                ->toArray();
            
            Redis::set('sidemenuweb'.config('app.webtype'), json_encode($sidemenuweb)); // Store in Redis
        } else {
            $sidemenuweb = json_decode($sidemenuweb, true);
        }

        // Fetch language JSON from Redis or set it if not available
        $jsonfile = base_path('language.json');
        $json = Redis::get('language_json'.config('app.webtype'));
        if (!$json) {
            if (File::exists($jsonfile)) {
                $contents = File::get($jsonfile);
                $json = json_decode($contents, true) ?? [];
            } else {
                $json = [];
            }
            Redis::set('language_json'.config('app.webtype'), json_encode($json)); // Store in Redis
        } else {
            $json = json_decode($json, true);
        }

        // Append 'Rashi' category
        $extraCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Rashi', 
                'category_name' => 'Rashifal', 'category_name_guj' => 'રાશિફળ', 'slug' => 'gstv-rashifal',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'rashifal.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
      	$campuscornerCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Campus Corner', 
                'category_name' => 'Campus Corner', 'category_name_guj' => 'કેમ્પસ કોર્નર', 'slug' => 'gstv-campuscorner',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'campuscorner.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
       /*$ganeshCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'ગણેશ ઉત્સવ', 
                'category_name' => 'ગણેશ ઉત્સવ', 'category_name_guj' => 'ગણેશ ઉત્સવ', 'slug' => 'gstv-ganapati',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'ganeshutsav.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];

        $tapasviCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'તપસ્વીઓના તપ', 
                'category_name' => 'તપસ્વીઓના તપ', 'category_name_guj' => 'તપસ્વીઓના તપ', 'slug' => 'gstv-athaitap',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'tapasavinatap.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];*/
        $journalistCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'આઈ એમ જર્નાલિસ્ટ', 
                'category_name' => 'આઈ એમ જર્નાલિસ્ટ', 'category_name_guj' => 'આઈ એમ જર્નાલિસ્ટ', 'slug' => 'gstv-journalist',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'journalist_mobile.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];

       
         $category = array_merge($campuscornerCategory,$journalistCategory, $category, $extraCategory);
        // $category = array_merge($campuscornerCategory,$journalistCategory, $category, $extraCategory,$tapasviCategory, $ganeshCategory);
        $plan = Redis::get('plandtl'.config('app.webtype'));
        if (!$plan) {
            $plan = DB::table('plan')
                ->where('status', 'Active')
                ->get();
            
            Redis::set('plandtl'.config('app.webtype'), json_encode($plan)); // Store in Redis
        } else {
            $plan = json_decode($plan, true);
        }

        
        $planexist = 0;
        $plan_expire = '';
        if (!empty($setting) && isset($setting[0]['fullcontent_login'])) {
            $planexist = $setting[0]['fullcontent_login'] == 0 ? 1 : 0;
        }

        if ($userId != "" || $userId != null || $userId != 0) {
            $plandetals = DB::table('user_plan')
                ->select('plan_status','plan_expire')
                ->where('user_id', $userId)
                ->where('plan_status', 'Active')
                ->orderBy('id', 'desc')
                ->first();
             $settings = (object) $setting[0];
             //if ($settings->fullcontent_login == 1) {
			   if (isset($settings->fullcontent_login) && $settings->fullcontent_login == 1) {
                // $planexist = 1;
                if (!empty($settings) && isset($settings->fullcontent_login)) {  
                    $planexist = $settings->fullcontent_login == 0 ? 1 : 0;
                }
                $news_readcount = DB::table('news_read')
                        ->where('user_id', $userId)
                        ->count();
                if ($settings->fullcontent_status == 1 && empty($plandetals)  && $news_readcount > $settings->plan_news_count) {
                    $planexist = 0;
                }

                if ($settings->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Inactive") {
                    $planexist = 0;
                    $plan_expire = $plandetals->plan_expire;
                }

                if ($settings->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Active") {
                    $planexist = 1;
                    $plan_expire = $plandetals->plan_expire;
                }
            }
        }

       if (!empty($setting)) {
            $setting[0]['planexist'] = $planexist;
            $setting[0]['plan_expire'] = $plan_expire;
        }
        // Prepare response data
        $data = [
            'category' => $category,
            'setting' =>  $setting[0],
            'sidemenu' => $sidemenu,
            'sidemenuweb' => $sidemenuweb,
            'plan' => $plan,
            // 'fullcontent_login'=>$settings->fullcontent_login,
            // 'fullcontent_status'=>$settings->fullcontent_status,
            // 'planexist' => $planexist,
            // 'plan_expire'=>$plan_expire,
            'language' => $json
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function CategorySettingWeb(Request $request)
    {
        $backendNewsUrl = config('app.backendnewsurl');
        $iconBaseUrl = $backendNewsUrl . '/public/uploads/category/icon/';
        $logoBaseUrl = $backendNewsUrl . '/public/uploads/logo/';
        $sidemenuUrl = $backendNewsUrl . '/public/uploads/sidemenu/';

        // Fetch category data from Redis or set it if not available
        $category = Redis::get('categoriesapp'.config('app.webtype'));
        if (!$category) {
            $category = DB::table('categories as c1')
                ->select(
                    'c1.id', 'c1.userID', 'c1.parentID','c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug',
                    'c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 
                    'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status',
                    'c1.created_at', 'c1.updated_at',
                    'c2.title as parent_category_name'
                )
                ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                ->where('c1.status', 'Active')
                ->orderBy('c1.catOrder', 'ASC')
                ->get()
                ->map(function ($item) use ($iconBaseUrl) {
                    $item->icon = $iconBaseUrl . $item->icon;
                    return $item;
                })
                ->toArray();
            
            Redis::set('categoriesapp'.config('app.webtype'), json_encode($category)); // Store in Redis
        } else {
            $category = json_decode($category, true);
        }

        // Fetch settings data from Redis or set it if not available
        $setting = Redis::get('settingsapp'.config('app.webtype'));
        if (!$setting) {
            $setting = DB::table('settings')
                ->select('logo', 'newsdefaulttxt', 'epaper_url', 'announcement', 'announcement_link','metatitle','metakeyword','metadesc',
                        'announcement_ios_link', 'announcement_status', 'election_status', 'election_link', 
                        'election_height', 'newfeatures', 'newfeatures_status','stockmarket','livescore','rashi_api','rashi_language','rashi_status','podcast_status','gujrat_agree_text','campus_agree_text','video_size','fullcontent_status','fullcontent_login','plan_days','plan_news_count','applypost','sudoku_link')->get()
                ->map(function ($item) use ($logoBaseUrl) {
                    $item->logo = $logoBaseUrl . $item->logo;
                    return $item;
                })
                ->toArray();
            
            Redis::set('settingsapp'.config('app.webtype'), json_encode($setting)); // Store in Redis
        } else {
            $setting = json_decode($setting, true);
        }

        // Fetch side menu data from Redis or set it if not available
        $sidemenu = Redis::get('sidemenuapp'.config('app.webtype'));
        if (!$sidemenu) {
            $sidemenu = DB::table('sidemenu')
                ->where('menustatus', '1')
                ->orderBy('menuorder', 'ASC')
                ->get()
                ->map(function ($item) use ($sidemenuUrl) {
                    $item->menuicon = $sidemenuUrl . $item->menuicon;
                    return $item;
                })
                ->toArray();
            
            Redis::set('sidemenuapp'.config('app.webtype'), json_encode($sidemenu)); // Store in Redis
        } else {
            $sidemenu = json_decode($sidemenu, true);
        }

        $sidemenuweb = Redis::get('sidemenuweb'.config('app.webtype'));
        if (!$sidemenuweb) {
            $sidemenuweb = DB::table('sidemenu')
                ->where('menuweb_status', '1')
                ->orderBy('menuorderweb', 'ASC')
                ->get()
                ->map(function ($item) use ($sidemenuUrl) {
                    $item->menuicon = $sidemenuUrl . $item->webicon;
                    return $item;
                })
                ->toArray();
            
            Redis::set('sidemenuweb'.config('app.webtype'), json_encode($sidemenuweb)); // Store in Redis
        } else {
            $sidemenuweb = json_decode($sidemenuweb, true);
        }

        // Fetch language JSON from Redis or set it if not available
        $jsonfile = base_path('language.json');
        $json = Redis::get('language_json'.config('app.webtype'));
        if (!$json) {
            if (File::exists($jsonfile)) {
                $contents = File::get($jsonfile);
                $json = json_decode($contents, true) ?? [];
            } else {
                $json = [];
            }
            Redis::set('language_json'.config('app.webtype'), json_encode($json)); // Store in Redis
        } else {
            $json = json_decode($json, true);
        }

        // Append 'Rashi' category
        $extraCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Rashi', 
                'category_name' => 'Rashifal', 'category_name_guj' => 'રાશિફળ', 'slug' => 'rashifal',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'rashifal.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 1000, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
        $webstoriesCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Web Stories', 
                'category_name' => 'Web Stories', 'category_name_guj' => 'વેબ સ્ટોરીઝ', 'slug' => 'web-stories',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'side-web-stories.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 1001, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
        $liveTVCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Live TV', 
                'category_name' => 'LiveTV', 'category_name_guj' => 'લાઇવ ટીવી', 'slug' => 'livetv',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'icon-live.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 1002, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
        $extraCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Rashi', 
                'category_name' => 'Rashifal', 'category_name_guj' => 'રાશિફળ', 'slug' => 'rashifal',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'rashifal.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 1000, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
      	$campuscornerCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Campus Corner', 
                'category_name' => 'Campus Corner', 'category_name_guj' => 'કેમ્પસ કોર્નર', 'slug' => 'campuscorner',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'campuscorner.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
      /* $ganeshCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'ગણેશ ઉત્સવ', 
                'category_name' => 'ગણેશ ઉત્સવ', 'category_name_guj' => 'ગણેશ ઉત્સવ', 'slug' => 'ganapati',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'ganeshutsav.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];

        $tapasviCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'તપસ્વીઓના તપ', 
                'category_name' => 'તપસ્વીઓના તપ', 'category_name_guj' => 'તપસ્વીઓના તપ', 'slug' => 'athaitap',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'tapasavinatap.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];*/
        $journalistCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'આઈ એમ જર્નાલિસ્ટ', 
                'category_name' => 'આઈ એમ જર્નાલિસ્ટ', 'category_name_guj' => 'આઈ એમ જર્નાલિસ્ટ', 'slug' => 'journalist',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'journalist_mobile.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];

        $category = array_merge($campuscornerCategory,$journalistCategory, $category, $extraCategory,$webstoriesCategory,$liveTVCategory);
        //$category = array_merge($campuscornerCategory,$journalistCategory, $category, $extraCategory,$webstoriesCategory,$liveTVCategory,$tapasviCategory, $ganeshCategory);
        $plan = Redis::get('plandtl'.config('app.webtype'));
        if (!$plan) {
            $plan = DB::table('plan')
                ->where('status', 'Active')
                ->get();
            
            Redis::set('plandtl'.config('app.webtype'), json_encode($plan)); // Store in Redis
        } else {
            $plan = json_decode($plan, true);
        }

        $settingsKey = "settingsappkey_" . config('app.webtype');
        $settings = Redis::get($settingsKey);
        if (!$settings) {
            $settings = DB::table('settings')->first();
            Redis::set($settingsKey, json_encode($settings));
        } else {
            $settings = json_decode($settings);
        }
        
        
        // Prepare response data
        $data = [
            'category' => $category,
            'setting' => $setting,
            'sidemenu' => $sidemenu,
            'sidemenuweb' => $sidemenuweb,
            'plan' => $plan,
            'language' => $json
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function PlanSetting(Request $request)
    {
        $userId = $request->input('user_id');
        $backendNewsUrl = config('app.backendnewsurl');
        $logoBaseUrl = $backendNewsUrl . '/public/uploads/logo/';
        
        // Fetch settings data from Redis or set it if not available
        $setting = Redis::get('settingsapp'.config('app.webtype'));
        if (!$setting) {
            $setting = DB::table('settings')
                ->select('logo', 'newsdefaulttxt', 'epaper_url', 'announcement', 'announcement_link','metatitle','metakeyword','metadesc',
                        'announcement_ios_link', 'announcement_status', 'election_status', 'election_link', 
                        'election_height', 'newfeatures', 'newfeatures_status','stockmarket','livescore','rashi_api','rashi_language','rashi_status','podcast_status','gujrat_agree_text','campus_agree_text','video_size','fullcontent_status','fullcontent_login','plan_days','plan_news_count','applypost','sudoku_link')
                ->get()
                ->map(function ($item) use ($logoBaseUrl) {
                    $item->logo = $logoBaseUrl . $item->logo;
                     return (array) $item;
                })
                ->toArray();
            Redis::set('settingsapp'.config('app.webtype'), json_encode($setting)); // Store in Redis
        } else {
            $setting = json_decode($setting, true);
        }
       
        
     
        $plan = Redis::get('plandtlapi'.config('app.webtype'));
        if (!$plan) {
            $plan = DB::table('plan')
                ->where('status', 'Active')
                ->get();
            
            Redis::set('plandtlapi'.config('app.webtype'), json_encode($plan)); // Store in Redis
        } else {
            $plan = json_decode($plan, true);
        }

        
        $planexist = 0;
        $plan_expire = '';
        if (!empty($setting) && isset($setting[0]['fullcontent_login'])) {
            $planexist = $setting[0]['fullcontent_login'] == 0 ? 1 : 0;
        }

        if ($userId != "" || $userId != null || $userId != 0) {
            $plandetals = DB::table('user_plan')
                ->select('plan_status','plan_expire')
                ->where('user_id', $userId)
                ->where('plan_status', 'Active')
                ->orderBy('id', 'desc')
                ->first();
             $settings = (object) $setting[0];
             if ($settings->fullcontent_login == 1) {
                $planexist = 1;
                $news_readcount = DB::table('news_read')
                        ->where('user_id', $userId)
                        ->count();
                if ($settings->fullcontent_status == 1 && empty($plandetals)  && $news_readcount > $settings->plan_news_count) {
                    $planexist = 0;
                }

                if ($settings->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Inactive") {
                    $planexist = 0;
                    $plan_expire = $plandetals->plan_expire;
                }

                if ($settings->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Active") {
                    $planexist = 1;
                    $plan_expire = $plandetals->plan_expire;
                }
            }
        }

       if (!empty($setting)) {
            $setting[0]['planexist'] = $planexist;
            $setting[0]['plan_expire'] = $plan_expire;
        }
        // Prepare response data
        $data = [
            'planexist' =>  $planexist,
            'plan_expire' => $plan_expire
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function categorysettingbyuser(Request $request)
    {
        $userId = $request->user_id;
        $backendNewsUrl = config('app.backendnewsurl');
        $iconBaseUrl = $backendNewsUrl . '/public/uploads/category/icon/';
        $logoBaseUrl = $backendNewsUrl . '/public/uploads/logo/';
        $sidemenuUrl = $backendNewsUrl . '/public/uploads/sidemenu/';
        $cacheKey = 'categoriesapp' . config('app.webtype');

        $mergearr = [];
        $categoryarr = [];
        $cityarr = [];

        if ($userId) {
            $cacheKey .= '_userapi_' . $userId;

            $categoryarr = DB::table('user_category')->where('user_id', $userId)->pluck('category_id')->toArray();
            $cityarr = DB::table('user_city')->where('user_id', $userId)->pluck('city_id')->toArray();

            // Merge for full filter only if needed later
            $mergearr = array_merge($categoryarr, $cityarr);
        }

        $category = Redis::get($cacheKey);

        if (!$category) {
            $query = DB::table('categories as c1')
                ->select(
                    'c1.id', 'c1.userID', 'c1.parentID', 'c1.title as title', 'c1.title as category_name',
                    'c1.category_name_guj', 'c1.slug', 'c1.featureImage', 'c1.icon', 'c1.description',
                    'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat',
                    'c1.setHome', 'c1.status', 'c1.created_at', 'c1.updated_at',
                    'c2.title as parent_category_name'
                )
                ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                ->where('c1.status', 'Active');

            if ($userId) {
                $query->where(function ($subQuery) use ($categoryarr, $cityarr) {
                    $subQuery->where(function ($q1) use ($cityarr) {
                        // Only allow city categories from user for category ID 1
                        $q1->where('c1.parentID', 1)
                        ->whereIn('c1.id', $cityarr);
                    })->orWhere(function ($q2) {
                        // All other categories (not cities)
                        $q2->where('c1.parentID', '!=', 1);
                    });
                });
            }

            $category = $query->orderBy('c1.catOrder', 'ASC')
                ->get()
                ->map(function ($item) use ($iconBaseUrl) {
                    $item->icon = $iconBaseUrl . $item->icon;
                    return $item;
                })
                ->toArray();

            Redis::set($cacheKey, json_encode($category));
        } else {
            $category = json_decode($category, true);
        }

        // Fetch settings data from Redis or set it if not available
        $setting = Redis::get('settingsapp'.config('app.webtype'));
        if (!$setting) {
            $setting = DB::table('settings')
                ->select('logo', 'newsdefaulttxt', 'epaper_url', 'announcement', 'announcement_link', 
                        'announcement_ios_link', 'announcement_status', 'election_status', 'election_link', 
                        'election_height', 'newfeatures', 'newfeatures_status','stockmarket','livescore','rashi_api','rashi_language','rashi_status','podcast_status')
                ->get()
                ->map(function ($item) use ($logoBaseUrl) {
                    $item->logo = $logoBaseUrl . $item->logo;
                    return $item;
                })
                ->toArray();
            
            Redis::set('settingsapp'.config('app.webtype'), json_encode($setting)); // Store in Redis
        } else {
            $setting = json_decode($setting, true);
        }

        // Fetch side menu data from Redis or set it if not available
        $sidemenu = Redis::get('sidemenuapp'.config('app.webtype'));
        if (!$sidemenu) {
            $sidemenu = DB::table('sidemenu')
                ->where('menustatus', '1')
                ->orderBy('menuorder', 'ASC')
                ->get()
                ->map(function ($item) use ($sidemenuUrl) {
                    $item->menuicon = $sidemenuUrl . $item->menuicon;
                    return $item;
                })
                ->toArray();
            
            Redis::set('sidemenuapp'.config('app.webtype'), json_encode($sidemenu)); // Store in Redis
        } else {
            $sidemenu = json_decode($sidemenu, true);
        }

        // Fetch language JSON from Redis or set it if not available
        $jsonfile = base_path('language.json');
        $json = Redis::get('language_json'.config('app.webtype'));
        if (!$json) {
            if (File::exists($jsonfile)) {
                $contents = File::get($jsonfile);
                $json = json_decode($contents, true) ?? [];
            } else {
                $json = [];
            }
            Redis::set('language_json'.config('app.webtype'), json_encode($json)); // Store in Redis
        } else {
            $json = json_decode($json, true);
        }

        // Append 'Rashi' category
        $extraCategory = [
            [
                'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Rashi', 
                'category_name' => 'Rashifal', 'category_name_guj' => 'રાશિફળ', 'slug' => '',
                'featureImage' => '', 'icon' => $iconBaseUrl . 'rashifal.svg',
                'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0,'setHome_app' => 0, 'status' => '',
                'created_at' => '', 'updated_at' => ''
            ]
        ];
        $category = array_merge($category, $extraCategory);

        // Prepare response data
        $data = [
            'category' => $category,
            'setting' => $setting,
            'sidemenu' => $sidemenu,
            'language' => $json
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function getallCity()
    { 
        // Fetch category data from Redis or set it if not available
        $city = Redis::get('citysapp'.config('app.webtype'));
        if (!$city) {
            $city = DB::table('categories as c1')
                ->select(
                    'c1.id', 'c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug',
                )
                ->where('c1.parentID', '1')
                ->where('c1.status', 'Active')
                ->orderBy('c1.catOrder', 'ASC')
                ->get();
            
            Redis::set('citysapp'.config('app.webtype'), json_encode($city)); // Store in Redis
        } else {
            $city = json_decode($city, true);
        }
 
        $data = [
            'city' => $city
        ];

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function webstory(Request $request)
    {
        $pageNumber = Route::current()->parameter('pageNumber') ?? $request->input('page', 1);
        $perPage = $request->input('per_page', 4); // Default to 4 items per page

        // Unique Redis key for caching based on page number and perPage value
        $redisKey = "webstory_page_{$pageNumber}_perPage_{$perPage}".config('app.webtype');

        // Check if data exists in Redis
        $webstory = Redis::get($redisKey);

        if (!$webstory) {
            // Fetch data from the database if not in Redis
            $webstory = DB::table('webstory')
                ->where('status', 'Active')
                ->orderBy('id', 'DESC')
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            // Store in Redis for 10 minutes (600 seconds)
            Redis::setex($redisKey, 600, json_encode($webstory));
        } else {
            // Decode JSON from Redis
            $webstory = json_decode($webstory);
        }

        $data = ['webstory' => $webstory];

        if (!$webstory) {
            return response()->json($data, 404, ['error' => 'Web story is not found'], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($data, 200);
    }
     public function topwebstory()
    {
        $redisKey = "webstory_page_10_perPage_".config('app.webtype');

        $topwebstory = Redis::get($redisKey);

        if (!$topwebstory) {
            $topwebstory = DB::table('webstory')
                ->where('status', 'Active')
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get();
            Redis::setex($redisKey, 600, json_encode($topwebstory));
        } else {
            $topwebstory = json_decode($topwebstory);
        }

        $data = ['topwebstory' => $topwebstory];

        if (!$topwebstory) {
            return response()->json($data, 404, ['error' => 'Web story is not found'], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($data, 200);
    }
    public function webstoryDetail(Request $request)
    {
        $slug = Route::current()->parameter('slug');

        // Redis key for webstory detail
        $redisKey = "webstory_detail_{$slug}".config('app.webtype');

        // Check if the webstory exists in Redis
        $webstory = Redis::get($redisKey);

        if (!$webstory) {
            // Fetch from database if not found in Redis
            $webstory = DB::table('webstory')
                ->where('slug', $slug)
                ->where('status', 'Active')
                ->first();

            // If web story not found, return 404 response
            if (!$webstory) {
                return response()->json(['error' => 'Web story not found'], 404, [], JSON_UNESCAPED_UNICODE);
            }

            // Store in Redis for 10 minutes (600 seconds)
            Redis::setex($redisKey, 600, json_encode($webstory));
        } else {
            // Decode the JSON string from Redis
            $webstory = json_decode($webstory);
        }

        $this->viewCountService->incrementViewCount('webstorycount', $webstory->id);
        // Redis key for related web stories
        $moreRedisKey = "webstory_more_{$slug}".config('app.webtype');

        // Check if related stories exist in Redis
        $webstorymore = Redis::get($moreRedisKey);

        if (!$webstorymore) {
            // Fetch related stories from database
            $webstorymore = DB::table('webstory')
                ->where('slug', '!=', $slug)
                ->where('status', 'Active')
                ->orderBy('id', 'DESC')
                ->limit(4)
                ->get();

            // Store in Redis for 10 minutes
            Redis::setex($moreRedisKey, 600, json_encode($webstorymore));
        } else {
            // Decode the JSON string from Redis
            $webstorymore = json_decode($webstorymore);
        }

        // Increment viewer count in database (not cached)
        // DB::table('webstory')->where('slug', $slug)->update([
        //     'viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')
        // ]);

        return response()->json([
            'webstory' => $webstory,
            'webstorymore' => $webstorymore
        ], 200);
    }
    public function topnews()
    {
        $data = [];

        $webType = config('app.webtype');
        $redisTopNewsKey = 'topnews' . $webType;
        $redisLiveNewsKey = 'livenews' . $webType;

        $cacheTTL = 600; // 10 minutes

        // --- TOP NEWS ---
        if (Redis::exists($redisTopNewsKey)) {
            $topnews = collect(json_decode(Redis::get($redisTopNewsKey)));
        } else {
            $topnews = DB::table('news')
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->join('categories', 'news_category.category_id', '=', 'categories.id')
                ->select(
                    'news.id','news.slug','news.title','news.featureImage','news.videoURL','news.created_at',
                    DB::raw('(SELECT GROUP_CONCAT(category_id ORDER BY category_id) FROM news_category WHERE news_id = news.id) as catID'),
                    DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs')
                )
                ->where('news.status', 'Active')
                ->where('news.is_home', 1)
                ->where('news.topnewsOrder','!=', 0)
                ->where('news_category.category_id', '!=', 9)
                ->groupBy('news.id')
                ->orderBy('news.topnewsOrder', 'ASC')
                ->limit(10)
                ->get();


            Redis::setex($redisTopNewsKey, $cacheTTL, json_encode($topnews));
        }

        // --- LIVE NEWS ---
        if (Redis::exists($redisLiveNewsKey)) {
            $livenews = collect(json_decode(Redis::get($redisLiveNewsKey)));
        } else {
           // Step 1: Get all live news entries
             $livenewsQuery = DB::table('livenews')
                ->select('lid', 'livetitle', 'created_at', 'news_id')
                ->orderByDesc('lid')
                ->get();

            // Step 2: Group live news by news_id
            $groupedLiveNews = $livenewsQuery->groupBy('news_id');

            // Step 3: Extract news IDs
            $newsIds = $groupedLiveNews->keys()->all();

            // Step 4: Get all relevant news in one query
            $newsItems = DB::table('news')
                ->whereIn('id', $newsIds)
                ->where('status', 'Active')
                ->where('is_live_news', 1)
                ->select('id', 'slug', 'title', 'featureImage', 'videoURL', 'created_at')
                ->get()
                ->keyBy('id');

            // Step 5: Get all category IDs for these news items (in one query)
            $newsCategoryMap = DB::table('news_category')
                ->whereIn('news_id', $newsIds)
                ->get()
                ->groupBy('news_id');

            // Step 6: Get all category slugs (in one query)
            $allCategoryIds = $newsCategoryMap->flatten()->pluck('category_id')->unique()->all();
            $categorySlugsMap = DB::table('categories')
                ->whereIn('id', $allCategoryIds)
                ->pluck('slug', 'id');

            // Step 7: Merge everything
            $finalNews = $newsItems->map(function ($news) use ($groupedLiveNews, $newsCategoryMap, $categorySlugsMap) {
                $news->news_id = $news->id; // duplicate for key consistency
                $news->live = $groupedLiveNews[$news->id]->map(function ($liveItem) {
                    return [
                        'livetitle' => $liveItem->livetitle,
                        'livenewsdate' => $liveItem->created_at,
                    ];
                })->values();

                $news->max_lid = $groupedLiveNews[$news->id]->max('lid');

                $catIDs = $newsCategoryMap[$news->id]->pluck('category_id')->toArray();
                $news->catID = implode(',', $catIDs);

                $slugs = collect($catIDs)->map(fn($id) => $categorySlugsMap[$id] ?? null)->filter()->values();
                $news->category_slugs = $slugs->isNotEmpty() ? $slugs->implode(',') : null;

                return $news;
            })->values();

            // ✅ Store correct final merged object in cache
            Redis::setex($redisLiveNewsKey, $cacheTTL, json_encode($finalNews));

            $livenews = $finalNews;
        }

        // Final JSON response
        return response()->json([
            'topnews' => $topnews,
            'livenews' => $livenews
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function topnewsweb()
    {
        $data = [];

        $webType = config('app.webtype');
        $redisTopNewsKey = 'topnewsweb_' . $webType;
        $redisLiveNewsKey = 'livenewsweb_' . $webType;

        $cacheTTL = 600; // 10 minutes

        // --- TOP NEWS ---
        if (Redis::exists($redisTopNewsKey)) {
            $topnews = collect(json_decode(Redis::get($redisTopNewsKey)));
        } else {
            $topnews = DB::table('news')
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->join('categories', 'news_category.category_id', '=', 'categories.id')
                ->select(
                    'news.id','news.slug','news.title','news.description','news.featureImage','news.videoURL','news.created_at',
                    DB::raw('(SELECT GROUP_CONCAT(category_id ORDER BY category_id) FROM news_category WHERE news_id = news.id) as catID'),
                    DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs')
                )
                ->where('news.status', 'Active')
                ->where('news.is_home', 1)
                ->where('news_category.category_id', '!=', 9)
                ->groupBy('news.id')
                ->orderBy('news.topnewsOrder', 'ASC')
                ->limit(10)
                ->get();


            Redis::setex($redisTopNewsKey, $cacheTTL, json_encode($topnews));
        }

        // --- LIVE NEWS ---
        if (Redis::exists($redisLiveNewsKey)) {
            $livenews = collect(json_decode(Redis::get($redisLiveNewsKey)));
        } else {
           // Step 1: Get all live news entries
             $livenewsQuery = DB::table('livenews')
                ->select('lid', 'livetitle', 'created_at', 'news_id')
                ->orderByDesc('lid')
                ->get();

            // Step 2: Group live news by news_id
            $groupedLiveNews = $livenewsQuery->groupBy('news_id');

            // Step 3: Extract news IDs
            $newsIds = $groupedLiveNews->keys()->all();

            // Step 4: Get all relevant news in one query
            $newsItems = DB::table('news')
                ->whereIn('id', $newsIds)
                ->where('status', 'Active')
                ->where('is_live_news', 1)
                ->select('id', 'slug', 'title', 'featureImage', 'videoURL', 'created_at')
                ->get()
                ->keyBy('id');

            // Step 5: Get all category IDs for these news items (in one query)
            $newsCategoryMap = DB::table('news_category')
                ->whereIn('news_id', $newsIds)
                ->get()
                ->groupBy('news_id');

            // Step 6: Get all category slugs (in one query)
            $allCategoryIds = $newsCategoryMap->flatten()->pluck('category_id')->unique()->all();
            $categorySlugsMap = DB::table('categories')
                ->whereIn('id', $allCategoryIds)
                ->pluck('slug', 'id');

            // Step 7: Merge everything
            $finalNews = $newsItems->map(function ($news) use ($groupedLiveNews, $newsCategoryMap, $categorySlugsMap) {
                $news->news_id = $news->id; // duplicate for key consistency
                $news->live = $groupedLiveNews[$news->id]->map(function ($liveItem) {
                    return [
                        'livetitle' => $liveItem->livetitle,
                        'livenewsdate' => $liveItem->created_at,
                    ];
                })->values();

                $news->max_lid = $groupedLiveNews[$news->id]->max('lid');

                $catIDs = $newsCategoryMap[$news->id]->pluck('category_id')->toArray();
                $news->catID = implode(',', $catIDs);

                $slugs = collect($catIDs)->map(fn($id) => $categorySlugsMap[$id] ?? null)->filter()->values();
                $news->category_slugs = $slugs->isNotEmpty() ? $slugs->implode(',') : null;

                return $news;
            })->values();

            // ✅ Store correct final merged object in cache
            Redis::setex($redisLiveNewsKey, $cacheTTL, json_encode($finalNews));

            $livenews = $finalNews;
        }

        // Final JSON response
        return response()->json([
            'topnews' => $topnews,
            'livenews' => $livenews
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function addpoints(Request $request)
    {
        $ipAddress = $request->input('device_id');
        $user_id =  $request->input('user_id');
        if (!$user_id) {
            return response()->json(['message' => 'You are not login.']);
        }
        $visitors = new VisitorSessions([
            'user_id' =>  $user_id,
            'start_time'=>  $request->input('start_time'),
            'end_time'=>  $request->input('end_time'),
            'session_id'=> $ipAddress
        ]);
        
        $visitors->save();
        
        return response()->json(['message' => 'Points submitted successfully']);
    } 
    public function NewsDetails(Request $request)
    {
        $slug = $request->input('slug');
        $user_id = $request->input('user_id');
        $ipAddress = $request->input('device_id');
        $newsDetail = DB::table('news as n')
                    ->select(
                        'n.*',
                        DB::raw('(SELECT slug FROM news WHERE id < n.id ORDER BY id DESC LIMIT 1) AS previousslug'),
                        DB::raw('(SELECT slug FROM news WHERE id > n.id ORDER BY id ASC LIMIT 1) AS nextslug'),
                        DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "1") as like_count'),
                        DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "0") as dislike_count'),
                        DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "1" AND newslike.ipAddress = "' . $ipAddress . '") as liked_by_user'),
                        DB::raw('(SELECT COUNT(*) FROM newslike WHERE newslike.news_id = n.id AND newslike.status = "0" AND newslike.ipAddress = "' . $ipAddress . '") as disliked_by_user'),
                        DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs')
                    )
                    ->leftJoin('categories', function ($join) {
                        $join->on(DB::raw('FIND_IN_SET(categories.id, n.catID)'), '>', DB::raw('0'));
                    })
                    ->where('n.slug', $slug)
                    ->groupBy('n.id')  // Add groupBy clause for compatibility with ONLY_FULL_GROUP_BY
                    ->first();
        
        // DB::table('news')->where('slug', $slug)->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);
                  
        $data =  array();
        if ($newsDetail) {
            $categoryIds = explode(',', $newsDetail->catID);
            $categories = Category::whereIn('id', $categoryIds)
                ->orderByRaw("FIND_IN_SET(id, '{$newsDetail->catID}')") 
                ->get();
            
            $catid = $newsDetail->catID;
            $excludedId = $newsDetail->id;
            $catIDs = explode(',', $catid);
            $relatednews = DB::table('news')
                ->select(
                    'news.id',
                    DB::raw('GROUP_CONCAT(DISTINCT news_category.category_id ORDER BY news_category.category_id) as catID'),
                    'news.title',
                    'news.englishTitle',
                    'news.slug',
                    'news.featureImage',
                    'news.videoURL'
                )
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->whereIn('news_category.category_id', $catIDs)
                ->where('news.status', 'Active')
                ->where('news.id', '!=', $excludedId)
                ->groupBy('news.id')
                ->orderBy('news.newsOrder', 'desc')
                ->limit(3)
                ->get();
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
        $newsDetail->live = [];

        $livenewsarr = DB::table('livenews')
        ->where('news_id', $newsDetail->id)
        ->orderBy('lid', 'DESC') 
        ->get(['livetitle', 'livedesc', 'created_at']); 

        if ($livenewsarr->isNotEmpty()) {
            $newsDetail->live = $livenewsarr->map(function ($item) {
                return [
                    'livetitle' => $item->livetitle,
                    'livedesc' => $item->livedesc,
                    'livenewsdate' => $item->created_at,
                ];
            })->toArray(); // Transform each item and convert to an array
        }
        $relatedNewsIddata = [];
           
        if($newsDetail->relatednewsid != ""){
            $relatedNewsIds = explode(',', $newsDetail->relatednewsid);
            // $relatedNewsIddata = DB::table('news')->select('id','title','featureImage','videoURL')->whereIn('id', $relatedNewsIds)->get();
        
           $relatedNewsIddata = DB::table('news')
                                ->select(
                                    'news.id', 
                                    'news.created_at', 
                                    'news.slug', 
                                    'news.title',
                                    'news.videoURL', 
                                    'news.featureImage', 
                                    DB::raw('GROUP_CONCAT(DISTINCT news_category.category_id ORDER BY news_category.category_id) as catID'),
                                    DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs')
                                )
                                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                                ->join('categories', 'news_category.category_id', '=', 'categories.id')
                                ->where('news.status', 'Active')
                                ->whereIn('news.id', $relatedNewsIds)
                                ->groupBy('news.id')
                                ->get();
        }
        $newsDetail->relatedNewsIddata =$relatedNewsIddata;
 
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
 
    public function newsnextContent(Request $request)
    {
        $perPage = 1;
        $newsPerPage = 1;
        $page = $request->input('pageNumber', 1);
        $user_id = $request->input('user_id', null);
        $ipAddress = $request->input('device_id', null);
        
        $categoryIds = $request->input('categoryIds', null);
        if ($categoryIds && str_contains($categoryIds, ',')) {
            $categoryIds = explode(',', $categoryIds)[0];
        }
    
        $slug = $request->input('slug');
        $loadedSlugs = explode(',', $request->input('loadedSlugs', ''));
        $lastSlug = end($loadedSlugs);
    
        // Generate a unique cache key for the request
        $slugHash = md5($slug . $page . json_encode($loadedSlugs) . $categoryIds);
        $cacheKey = "newsnextContent:$slugHash";
        $news_readcount = 0;
        $setting = DB::table('settings')->first();
        
        if (Redis::exists($cacheKey)) {
            // $cachedResponse = json_decode(Redis::get($cacheKey), true);
            
            // if($setting->fullcontent_status == 1  && $setting->fullcontent_login == 1){
            //     if (!empty($user_id) || $user_id != "" || $user_id != null) {
            //         $news_readcount = DB::table('news_read')
            //             ->where('user_id', $user_id)
            //             ->count();
            //     }
            //     $cachedResponse['news_readcount'] = $news_readcount;
                
            //     Redis::setex($cacheKey, 600, json_encode($cachedResponse));
            // }
            // if (!empty($cachedResponse['newsDetail']['id'])) {
            //     $this->viewCountService->incrementViewCount('newscount', $cachedResponse['newsDetail']['id']);
            // }
            // return response()->json($cachedResponse, 200, [], JSON_UNESCAPED_UNICODE);
            $cachedResponse = json_decode(Redis::get($cacheKey), true);

            $newsDetail = isset($cachedResponse['newsDetail']) ? (object) $cachedResponse['newsDetail'] : null;
            $planexist = 0;
            $news_readcount = 0;

            // --- Apply planexist logic when newsDetail is available ---
            if (!empty($newsDetail)) {
                $plandetals = DB::table('user_plan')
                    ->select('plan_status')
                    ->where('user_id', $user_id)
                    ->where('plan_status', 'Active')
                    ->first();

                if ($setting->fullcontent_login == 1) {
                    //$planexist = 1;
                    if (!empty($setting) && isset($setting->fullcontent_login)) {
                        $planexist = $setting->fullcontent_login == 0 ? 1 : 0;
                    }
                    if (!empty($user_id) && $setting->fullcontent_status == 1 && isset($newsDetail->premium_news) && $newsDetail->premium_news == 1) {
                        $news_readcount = DB::table('news_read')
                            ->where('user_id', $user_id)
                            ->count();
                    }

                    if ($setting->fullcontent_status == 1 && empty($plandetals) && $news_readcount > $setting->plan_news_count) {
                        $planexist = 0;
                    }

                    if ($setting->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Inactive") {
                        $planexist = 0;
                    }

                    if ($setting->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Active") {
                        $planexist = 1;
                    }
                }
            }

            // --- Update read count ---
            if ($setting->fullcontent_status == 1 && $setting->fullcontent_login == 1) {
                if (!empty($user_id)) {
                    $news_readcount = DB::table('news_read')
                        ->where('user_id', $user_id)
                        ->count();
                }
            }

            // --- Update cached response with latest dynamic values ---
            $cachedResponse['planexist'] = $planexist;
            $cachedResponse['news_readcount'] = $news_readcount;

            // Re-cache for 10 minutes
            Redis::setex($cacheKey, 600, json_encode($cachedResponse));

            // Increment view count if valid
            if (!empty($newsDetail?->id)) {
                $this->viewCountService->incrementViewCount('newscount', $newsDetail->id);
            }

            return response()->json($cachedResponse, 200, [], JSON_UNESCAPED_UNICODE);
        }
      	$ip = $ipAddress; // for reuse in bindings

        $baseQuery = DB::table('news as n')
                    ->select(
                        'n.*',
                        DB::raw("LAG(n.slug) OVER (ORDER BY n.id) AS previousslug"),
                        DB::raw("LEAD(n.slug) OVER (ORDER BY n.id) AS nextslug"),
                        DB::raw("COUNT(CASE WHEN nl.status = 1 THEN 1 END) AS like_count"),
                        DB::raw("COUNT(CASE WHEN nl.status = 0 THEN 1 END) AS dislike_count"),
                        DB::raw("COUNT(CASE WHEN nl.status = 1 AND nl.ipAddress = '$ip' THEN 1 END) AS liked_by_user"),
                        DB::raw("COUNT(CASE WHEN nl.status = 0 AND nl.ipAddress = '$ip' THEN 1 END) AS disliked_by_user"),
                        DB::raw("GROUP_CONCAT(DISTINCT c.slug ORDER BY c.id) AS category_slugs"),
                        DB::raw("GROUP_CONCAT(DISTINCT nc.category_id ORDER BY nc.category_id) AS catID"),
          				DB::raw("GROUP_CONCAT(DISTINCT c.category_name_guj ORDER BY c.id) AS category_name_guj")
                    )
                    ->leftJoin('newslike as nl', 'nl.news_id', '=', 'n.id')
                    ->leftJoin('news_category as nc', 'nc.news_id', '=', 'n.id')
                    ->leftJoin('categories as c', 'c.id', '=', 'nc.category_id')
                    ->where('n.status', 'Active')
                    ->whereNotIn('n.slug', $loadedSlugs)
                    ->groupBy('n.id')
                    ->orderBy('n.id', 'DESC')
                    ->offset(($page - 1) * $newsPerPage)
                    ->limit($newsPerPage);

                if (!empty($categoryIds)) {
                    $baseQuery->whereIn('c.id', (array) $categoryIds)->where('n.slug', '!=', $slug);
                } else {
                    $baseQuery->where('n.slug', '=', $slug);
                }

                $newsDetail1 = $baseQuery->get()
                    ->map(fn($item) => (array) $item)
                    ->toArray();

    	
        $newsDetail = !empty($newsDetail1) ? (object) $newsDetail1[0] : null;
        
        $this->viewCountService->incrementViewCount('newscount', $newsDetail->id);

        $modifiedHtml = $newsDetail ? $this->processEmbeddedContent($newsDetail->description) : '';
    	$newsDetail->live = [];
        $livenewsarr = DB::table('livenews')
        ->where('news_id', $newsDetail->id)
        ->orderBy('lid', 'DESC') 
        ->get(['livetitle', 'livedesc', 'created_at']); 

        if ($livenewsarr->isNotEmpty()) {
            $newsDetail->live = $livenewsarr->map(function ($item) {
                return [
                    'livetitle' => $item->livetitle,
                    'livedesc' => $item->livedesc,
                    'livenewsdate' => $item->created_at,
                ];
            })->toArray(); // Transform each item and convert to an array
        }
        $relatednews = [];
        if ($newsDetail) {
            $catIDs = explode(',', $newsDetail->catID);
            /*$relatednews = DB::table('news')
                            ->select(
                                'news.id',
                                DB::raw('GROUP_CONCAT(DISTINCT news_category.category_id ORDER BY news_category.category_id) as catID'),
                                'news.title',
                                'news.slug',
                                'news.featureImage',
                                'news.videoURL'
                            )
                            ->join('news_category', 'news.id', '=', 'news_category.news_id')
                            ->whereIn('news_category.category_id', $catIDs)
                            ->where('news.status', 'Active')
                            ->where('news.id', '!=', $newsDetail->id)
                            ->groupBy('news.id')
                            ->orderBy('news.newsOrder', 'desc')
                            ->limit(3)
                            ->get();*/
          $relatednews = DB::table('news')
                            ->select(
                                'news.id',
                                DB::raw('GROUP_CONCAT(DISTINCT nc.category_id ORDER BY nc.category_id) as catID'),
                                DB::raw("GROUP_CONCAT(DISTINCT c.slug ORDER BY c.id) AS category_slugs"),
                                'news.title',
                                'news.slug',
                                'news.featureImage',
                                'news.videoURL'
                            )
                            ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
                            ->leftJoin('categories as c', 'c.id', '=', 'nc.category_id')
                            ->whereIn('nc.category_id', $catIDs)
                            ->where('news.status', 'Active')
                            ->where('news.id', '!=', $newsDetail->id)
                            ->groupBy('news.id')
                            ->orderBy('news.newsOrder', 'desc')
                            ->limit(3)
                            ->get();
        }
        
        if (!empty($newsDetail)) {
            $newsDetail->bookmark = 0;
            if (!empty($user_id)) {
                $bookmark = DB::table('bookmark')
                    ->where('user_id', $user_id)
                    ->where('news_id', $newsDetail->id)
                    ->first();
                $newsDetail->bookmark = $bookmark ? (int)$bookmark->status : 0;
            }
           $relatedNewsIddata = [];
           if($newsDetail->relatednewsid != ""){
                $relatedNewsIds = explode(',', $newsDetail->relatednewsid);
                // $relatedNewsIddata = DB::table('news')->select('id','title','featureImage','videoURL')->whereIn('id', $relatedNewsIds)->get();

                $relatedNewsIddata = DB::table('news')
                  ->select(
                      'news.id', 
                      'news.created_at', 
                      'news.slug', 
                      'news.title',
                      'news.videoURL', 
                      'news.featureImage', 
                       DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs'),
                       DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID')
            )
            ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
                  ->where('news.status', 'Active')
                  ->whereIn('news.id', $relatedNewsIds)
                  ->groupBy('news.id')
                  ->get();
          }
          $newsDetail->relatedNewsIddata =$relatedNewsIddata;
          $planexist = 0;
          if($newsDetail->premium_news == 1 && $setting->fullcontent_status == 1  && $setting->fullcontent_login == 1){
            if(!empty($user_id) || $user_id != "" || $user_id != null){
                $news_read = DB::table('news_read')
                        ->where('user_id', $user_id)
                        ->where('news_id', $newsDetail->id)
                        ->first();
                if(empty($news_read)){
                $dataNews = [
                    'user_id' => $user_id,
                    'news_id' => $newsDetail->id,
                ];
                DB::table('news_read')->insert($dataNews);
                }
            }
         }
           $plandetals = DB::table('user_plan')
                ->select('plan_status')
                ->where('user_id', $user_id)
                ->where('plan_status', 'Active')
                ->first();

            if ($setting->fullcontent_login == 1) {
                 $planexist = 1;
                if (!empty($user_id) && $setting->fullcontent_status == 1  && $newsDetail->premium_news == 1) {
                    $news_readcount = DB::table('news_read')
                        ->where('user_id', $user_id)
                        ->count();
                }
                // echo $setting->plan_news_count.'===='.$news_readcount.'---';
                if ($setting->fullcontent_status == 1 && empty($plandetals) && $news_readcount > $setting->plan_news_count) {
                    $planexist = 0;
                }

                if ($setting->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Inactive") {
                    $planexist = 0;
                }

                if ($setting->fullcontent_status == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Active") {
                    // echo 8;exit;
                    $planexist = 1;
                }
            }
        }

      
    
        $response = [
            'newsDetail' => $newsDetail,
            'planexist'=>$planexist,
            'newsdescription' => $modifiedHtml,
            'relatednews' => $relatednews,
            'newsdefaulttxt' => $setting->newsdefaultapptxt ?? '',
        ];

        // Cache the static part only
        Redis::setex($cacheKey, 600, json_encode($response));
        $news_readcount = 0;

        if (!empty($user_id)) {
            $news_readcount = DB::table('news_read')
                ->where('user_id', $user_id)
                ->count();
        }

        // Add it AFTER caching
        $response['news_readcount'] = $news_readcount;
    
        return response()->json($response, $newsDetail ? 200 : 404, [], JSON_UNESCAPED_UNICODE);
    }
    public function newsnextContentweb(Request $request)
{
    $page = (int) $request->input('pageNumber', 1);
    $newsPerPage = 1;
    $user_id = $request->input('user_id');
    $slug = $request->input('slug');
    $categoryIds = $request->has('categoryIds') ? explode(',', $request->input('categoryIds')) : [];

    // Base Query
    $baseQuery = DB::table('news as n')
        ->select(
            'n.id', 'n.slug', 'n.title', 'n.englishTitle', 'n.featureImage',
            'n.videoURL', 'n.created_at', 'n.description', 'n.tags',
            'n.is_live_news', 'n.is_breaking', 'n.is_vertical_video',
            'n.hide_title', 'n.relatednewsid', 'n.img_credit_txt'
        )
        ->where('n.status', 'Active')
        ->orderBy('n.id', 'DESC');

    // ✅ First page: filter by slug if requested
    if ($page === 1 && !empty($slug)) {
        $baseQuery->where('n.slug', '=', $slug);
    } else {
        $baseQuery->where('n.slug', '!=', $slug);

        if (!empty($categoryIds)) {
            $baseQuery->whereIn('n.id', function ($query) use ($categoryIds) {
                $query->select('news_id')
                    ->from('news_category')
                    ->whereIn('category_id', $categoryIds);
            });
        }
    }

    // ✅ Laravel Pagination (automatically handles "next" and "prev" URLs)
    $newsDetail = $baseQuery->paginate($newsPerPage, ['*'], 'page', $page);

    if ($newsDetail->isEmpty()) {
        return response()->json(['message' => 'No more news available'], 200);
    }

    // ✅ Loop through each news item for additional details
    $newsDetail->getCollection()->transform(function ($item) use ($user_id) {
        $categoryIds = DB::table('news_category')
            ->where('news_id', $item->id)
            ->pluck('category_id')
            ->toArray();

        $categories = Category::select('id', 'title', 'category_name_guj', 'slug')
            ->whereIn('id', $categoryIds)
            ->orderByRaw("FIELD(id, " . implode(',', $categoryIds) . ")")
            ->get();

        $relatednews = DB::table('news as n')
            ->select('n.id', 'n.title', 'n.englishTitle', 'n.slug', 'n.featureImage', 'n.videoURL')
            ->join('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->whereIn('nc.category_id', $categoryIds)
            ->where('n.id', '!=', $item->id)
            ->where('n.status', 'Active')
            ->groupBy('n.id')
            ->orderByDesc('n.newsOrder')
            ->limit(3)
            ->get();

        $description = $item->description ?? '';
        $modifiedHtml = preg_replace_callback(
            '/(https:\/\/twitter\.com\/[a-zA-Z0-9_]+\/status\/[0-9]+)|
            (https:\/\/fb\.watch\/[a-zA-Z0-9_]+)|
            (https:\/\/www\.instagram\.com\/(p|reel)\/[a-zA-Z0-9_-]+\/?)|
            (https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]+))|
            (https:\/\/[^ \n]+\.pdf)/x',
            function ($matches) {
                $url = $matches[0];
                if (str_contains($url, 'twitter.com')) {
                    return '<blockquote class="twitter-tweet"><a href="' . $url . '"></a></blockquote>';
                }
                if (str_contains($url, 'fb.watch')) {
                    return '<iframe style="width:100%;height:500px;" src="https://www.facebook.com/plugins/post.php?href=' . $url . '" frameborder="0" scrolling="no"></iframe>';
                }
                if (str_contains($url, 'instagram.com/p/') || str_contains($url, 'instagram.com/reel/')) {
                    return '<iframe src="' . $url . 'embed" width="400" height="500" frameborder="0" scrolling="no"></iframe>';
                }
                if (preg_match('/v=([a-zA-Z0-9_-]+)/', $url, $yt)) {
                    return '<iframe width="100%" height="400" src="https://www.youtube.com/embed/' . $yt[1] . '" frameborder="0" allowfullscreen></iframe>';
                }
                if (str_ends_with($url, '.pdf')) {
                    return '<iframe src="' . $url . '#toolbar=0" width="100%" height="600px"></iframe>';
                }
                return $url;
            },
            $description
        );

        $liveNews = DB::table('livenews')
            ->where('news_id', $item->id)
            ->orderByDesc('lid')
            ->get(['livetitle', 'livedesc', 'created_at'])
            ->map(fn($ln) => [
                'livetitle' => $ln->livetitle,
                'livedesc' => $ln->livedesc,
                'livenewsdate' => $ln->created_at,
            ])
            ->toArray();

        $bookmarkStatus = 0;
        if (!empty($user_id)) {
            $bookmark = DB::table('bookmark')
                ->where('user_id', $user_id)
                ->where('news_id', $item->id)
                ->first();
            $bookmarkStatus = $bookmark->status ?? 0;
        }

        $relatedNewsIddata = [];
        if (!empty($item->relatednewsid)) {
            $relatedNewsIds = array_filter(explode(',', $item->relatednewsid));
            $relatedNewsIddata = DB::table('news as n')
                ->select('n.id', 'n.created_at', 'n.slug', 'n.title', 'n.videoURL', 'n.featureImage')
                ->where('n.status', 'Active')
                ->whereIn('n.id', $relatedNewsIds)
                ->get();
        }

        $settings = DB::table('settings')->pluck('newsdefaultapptxt')->first();

        // Attach additional data
        $item->categories = $categories;
        $item->relatednews = $relatednews;
        $item->newsdescription = $modifiedHtml;
        $item->live = $liveNews;
        $item->bookmark = $bookmarkStatus;
        $item->relatedNewsIddata = $relatedNewsIddata;
        $item->newsdefaulttxt = $settings;

        return $item;
    });

    return response()->json($newsDetail, 200, [], JSON_UNESCAPED_UNICODE);
}
    private function processEmbeddedContent($description)
    {
        return preg_replace_callback(
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
                    return '<blockquote class="twitter-tweet"><a href="' . $twitterUrl . '">' . $twitterUrl . '</a></blockquote>';
                } elseif ($facebookWatchUrl) {
                    return '<iframe style="width: 100%; height: 500px; border: none;" src="https://www.facebook.com/plugins/post.php?href=' . $facebookWatchUrl . '" frameborder="0"></iframe>';
                } elseif ($instagramPostUrl) {
                    return '<blockquote class="instagram-media" data-instgrm-permalink="' . $instagramPostUrl . '"></blockquote>';
                } elseif ($instagramReelShortcode) {
                    return '<iframe src="https://www.instagram.com/p/' . $instagramReelShortcode . '/embed" width="400" height="500" frameborder="0"></iframe>';
                } elseif ($youtubeVideoId) {
                    return '<iframe width="100%" height="400" src="https://www.youtube.com/embed/' . $youtubeVideoId . '" frameborder="0" allowfullscreen></iframe>';
                } elseif ($pdfUrl) {
                    return '<iframe src="' . $pdfUrl . '#toolbar=0&navpanes=0" width="100%" height="600px"></iframe>';
                }

                return $matches[0];
            },
            $description
        );
    }  
    
    public function tagdetail(Request $request)
    {
        $slug = $request->input('slug');
        $device_id = $request->input('device_id');
        $pageNumber = $request->input('pageNumber');
        $user_id = $request->input('user_id');
        $perPage = $request->input('per_page', 4);
        $page = $request->input('page', $pageNumber);

        $redisKey = "tagdetail:{$slug}:page:{$page}:perPage:{$perPage}:".config('app.webtype');

        // ✅ Check Redis Cache
        if (Redis::exists($redisKey)) {
            return response()->json(json_decode(Redis::get($redisKey), true), 200, ['success' => 'Success'], JSON_UNESCAPED_UNICODE);
        }

        // ✅ Define Base Query
        $query = DB::table('news')
            ->join('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->whereRaw('FIND_IN_SET(?, REPLACE(news.tags, "-", " ")) > 0', [$slug])
            ->where('news.status', 'Active')
            ->groupBy('news.id')
            ->select(
                'news.*',
                DB::raw('GROUP_CONCAT(DISTINCT nc.category_id ORDER BY nc.category_id) as catID'),
                DB::raw('GROUP_CONCAT(DISTINCT c.slug ORDER BY c.id) as category_slugs')
            );

        // ✅ Add Video Likes Data (Only if device_id exists)
        if (!empty($device_id)) {
            $subquery = DB::table('videolike')
                ->select(
                    'video_id',
                    DB::raw('SUM(status = "1") as like_count'),
                    DB::raw('SUM(status = "0") as dislike_count'),
                    DB::raw("SUM(CASE WHEN status = '1' AND ipAddress = '$device_id' THEN 1 ELSE 0 END) as liked_by_user"),
                    DB::raw("SUM(CASE WHEN status = '0' AND ipAddress = '$device_id' THEN 1 ELSE 0 END) as disliked_by_user")
                )
                ->groupBy('video_id');

            $query->leftJoinSub($subquery, 'vl', function ($join) {
                $join->on('news.id', '=', 'vl.video_id');
            })->addSelect('vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user');
        }

        // ✅ Execute Pagination
        $news = $query
            ->orderBy('news.newsOrder', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);


        // ✅ Get Bookmarks in One Query
        $bookmarks = !empty($user_id)
            ? DB::table('bookmark')
                ->where('user_id', $user_id)
                ->whereIn('news_id', $news->pluck('id'))
                ->pluck('status', 'news_id')
            : [];

        // ✅ Transform News Data
        $news->getCollection()->transform(function ($item) use ($bookmarks) {
            $item->description = Str::limit($item->description, 120);
            $item->bookmark = $bookmarks[$item->id] ?? 0;
            return $item;
        });

        // ✅ Prepare Response
        $data = [
            'news' => $news,
            'catTitle' => $slug,
            'catSlug' => $slug,
            'catMetatitle' => str_replace("-", " ", $slug),
            'catMetadesc' => str_replace("-", " ", $slug),
        ];

        // ✅ Cache Response in Redis
        Redis::setex($redisKey, 3600, json_encode($data));

        return response()->json($data, 200, ['success' => 'Success'], JSON_UNESCAPED_UNICODE);
    }
   
    public function newsbookmark(Request $request)
    {
        $news_id = $request->input('news_id');
        $user_id = $request->input('user_id');
        $status = $request->input('status');
        $bookmark_type = $request->input('bookmark_type');
		
      	$bookmarlistdel = "bookmark_list_{$user_id}".config('app.webtype');
        Redis::del($bookmarlistdel);
      
        // Define Redis key for user's news bookmark
        $bookmarkKey = "news_bookmark_{$user_id}_{$news_id}".config('app.webtype');

        // Check if the bookmark exists in Redis or fetch from DB
        if (Redis::exists($bookmarkKey)) {
            $newsDetail = json_decode(Redis::get($bookmarkKey));
        } else {
            $newsDetail = DB::table('bookmark')->where('user_id', $user_id)->where('news_id', $news_id)->first();
            if ($newsDetail) {
                Redis::setex($bookmarkKey, 600, json_encode($newsDetail)); // Cache for 10 minutes
            }
        }

        if (!$newsDetail) {
            $dataNews = [
                'user_id' => $user_id,
                'news_id' => $news_id,
                'status' => $status,
                'bookmark_type' => $bookmark_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            DB::table('bookmark')->insert($dataNews);
        } else {
            DB::table('bookmark')->where('news_id', $news_id)->where('user_id', $user_id)->update(['status' => $status,'updated_at' => date('Y-m-d H:i:s')]);
        }

        // Fetch updated bookmark from DB
        $newsDetail = DB::table('bookmark')->where('user_id', $user_id)->where('news_id', $news_id)->first();
        
        // Update Redis cache
        Redis::setex($bookmarkKey, 600, json_encode($newsDetail));

        return response()->json(['status' => $newsDetail->status], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function bookmarklist(Request $request)
    {
        $user_id = $request->input('user_id');

        // Define Redis key for bookmark list
        $redisKey = "bookmark_list_{$user_id}".config('app.webtype');

        // Check if the bookmark list exists in Redis
        if (Redis::exists($redisKey)) {
            $bookmarkList = json_decode(Redis::get($redisKey));
        } else {
            // Fetch bookmark list from DB
            $bookmarkList = DB::table('bookmark')
                    ->join('news', 'bookmark.news_id', '=', 'news.id')
                    ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
                    ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
                    ->select(
                        'bookmark.news_id',
                        'bookmark.bookmark_type',
                        DB::raw('MAX(bookmark.created_at) as created_at'), // ✅ Aggregate for ORDER BY
                        'news.title as newsTitle',
                        'news.slug as newsSlug',
                        'news.description',
                        'news.featureImage',
                        'news.videoURL',
                        DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id) as catTitle'),
                        DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as catSlug'),
                        DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID')
                    )
                    ->where('bookmark.user_id', $user_id)
                    ->where('bookmark.status', '1')
                    ->groupBy(
                        'bookmark.news_id',
                        'bookmark.bookmark_type',
                        'news.title',
                        'news.slug',
                        'news.description',
                        'news.featureImage',
                        'news.videoURL'
                    )
                    ->orderByDesc('created_at') // ✅ Now compatible
                    ->get();

                // ✅ Modify image URLs as needed
                $bookmarkList = $bookmarkList->map(function ($item) {
                    if ($item->bookmark_type === 'news' && !empty($item->featureImage)) {
                        $fileExtension = strtolower(pathinfo($item->featureImage, PATHINFO_EXTENSION));
                        $item->imageURL = preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $item->featureImage);
                    } elseif ($item->bookmark_type === 'video' && !empty($item->videoURL)) {
                        $fileExtension = strtolower(pathinfo($item->videoURL, PATHINFO_EXTENSION));
                        $item->imageURL = preg_replace("/\.$fileExtension$/i", "_video.gif", $item->videoURL);
                    }
                    return $item;
                });

            // Store result in Redis for 10 minutes (600 seconds)
            Redis::setex($redisKey, 600, json_encode($bookmarkList));
        }

        return response()->json(['bookmarklist' => $bookmarkList], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function bookmarklistWithpagination11(Request $request)
    {
        $user_id = $request->input('user_id');
        $page = (int) $request->input('pageNumber', 1);
        $perPage = (int) $request->input('per_page', 10);
        // Define Redis key for bookmark list
        $redisKey = "bookmarkpage_list_{$user_id}".config('app.webtype');

        // Check if the bookmark list exists in Redis
        if (Redis::exists($redisKey)) {
            $bookmarkList = json_decode(Redis::get($redisKey));
        } else {
            // Fetch bookmark list from DB
            $bookmarkList = DB::table('bookmark')
                    ->join('news', 'bookmark.news_id', '=', 'news.id')
                    ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
                    ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
                    ->select(
                        'bookmark.news_id',
                        'bookmark.bookmark_type',
                        DB::raw('MAX(bookmark.created_at) as created_at'), // ✅ Aggregate for ORDER BY
                        'news.title as newsTitle',
                        'news.slug as newsSlug',
                        'news.description',
                        'news.featureImage',
                        'news.videoURL',
                        DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id) as catTitle'),
                        DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as catSlug'),
                        DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID')
                    )
                    ->where('bookmark.user_id', $user_id)
                    ->where('bookmark.status', '1')
                    ->groupBy(
                        'bookmark.news_id',
                        'bookmark.bookmark_type',
                        'news.title',
                        'news.slug',
                        'news.description',
                        'news.featureImage',
                        'news.videoURL'
                    )
                    ->orderByDesc('created_at') // ✅ Now compatible
                    ->get();

                // ✅ Modify image URLs as needed
                $bookmarkList = $bookmarkList->map(function ($item) {
                    if ($item->bookmark_type === 'news' && !empty($item->featureImage)) {
                        $fileExtension = strtolower(pathinfo($item->featureImage, PATHINFO_EXTENSION));
                        $item->imageURL = preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $item->featureImage);
                    } elseif ($item->bookmark_type === 'video' && !empty($item->videoURL)) {
                        $fileExtension = strtolower(pathinfo($item->videoURL, PATHINFO_EXTENSION));
                        $item->imageURL = preg_replace("/\.$fileExtension$/i", "_video.gif", $item->videoURL);
                    }
                    return $item;
                });

            // Store result in Redis for 10 minutes (600 seconds)
            Redis::setex($redisKey, 600, json_encode($bookmarkList));
        }

        return response()->json(['bookmarklist' => $bookmarkList], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function bookmarklistWithpagination(Request $request)
    {
        $user_id = $request->input('user_id');
        $page = (int) $request->input('pageNumber', 1);
        $perPage = (int) $request->input('per_page', 10);

        // Get paginated bookmarks directly from DB
        $query = DB::table('bookmark')
            ->join('news', 'bookmark.news_id', '=', 'news.id')
            ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
            ->select(
                'bookmark.news_id',
                'bookmark.bookmark_type',
                DB::raw('MAX(bookmark.created_at) as created_at'),
                'news.title as newsTitle',
                'news.slug as newsSlug',
                'news.description',
                'news.featureImage',
                'news.videoURL',
                DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id) as catTitle'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as catSlug'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID')
            )
            ->where('bookmark.user_id', $user_id)
            ->where('bookmark.status', '1')
            ->groupBy(
                'bookmark.news_id',
                'bookmark.bookmark_type',
                'news.title',
                'news.slug',
                'news.description',
                'news.featureImage',
                'news.videoURL'
            )
            ->orderByDesc(DB::raw('MAX(bookmark.created_at)'));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Modify image URLs
        $paginator->getCollection()->transform(function ($item) {
            if ($item->bookmark_type === 'news' && !empty($item->featureImage)) {
                $fileExtension = strtolower(pathinfo($item->featureImage, PATHINFO_EXTENSION));
                $item->imageURL = preg_replace("/\.$fileExtension$/i", "_small.$fileExtension", $item->featureImage);
            } elseif ($item->bookmark_type === 'video' && !empty($item->videoURL)) {
                $fileExtension = strtolower(pathinfo($item->videoURL, PATHINFO_EXTENSION));
                $item->imageURL = preg_replace("/\.$fileExtension$/i", "_video.gif", $item->videoURL);
            }
            return $item;
        });

        return response()->json([
            'bookmarklist' => $paginator->items(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
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
    public function newslike(Request $request)
    {
        $news_id = $request->input('news_id');
        $ipAddress = $request->input('device_id');
        $status = $request->input('status');

        // Define Redis keys for like & dislike counts
        $likeKey = "news_likes_{$news_id}".config('app.webtype');
        $dislikeKey = "news_dislikes_{$news_id}".config('app.webtype');

        // Check if user has already liked/disliked
        $newslike = DB::table('newslike')->where('ipAddress', $ipAddress)->where('news_id', $news_id)->first();

        if ($newslike) {
            DB::table('newslike')->where('news_id', $news_id)->where('ipAddress', $ipAddress)->update(['status' => $status]);
        } else {
            $datanews = [
                'ipAddress' => $ipAddress,
                'news_id' => $news_id,
                'status' => $status
            ];
            DB::table('newslike')->insert($datanews);
        }

        // Fetch from Redis or DB if not cached
        if (Redis::exists($likeKey)) {
            $likeCount = Redis::get($likeKey);
        } else {
            $likeCount = DB::table('newslike')->where('news_id', $news_id)->where('status', '1')->count();
            Redis::setex($likeKey, 600, $likeCount); // Cache for 10 minutes
        }

        if (Redis::exists($dislikeKey)) {
            $dislikeCount = Redis::get($dislikeKey);
        } else {
            $dislikeCount = DB::table('newslike')->where('news_id', $news_id)->where('status', '0')->count();
            Redis::setex($dislikeKey, 600, $dislikeCount); // Cache for 10 minutes
        }

        // Format like count
        $data['newslike'] = $this->formatCount($likeCount);
        $data['newsdislike'] = $this->formatCount($dislikeCount);

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Format numbers to K or M notation
     */
    private function formatCount($num)
    {
        if ($num >= 1000000) {
            return number_format($num / 1000000, ($num % 1000000 !== 0 ? 1 : 0)) . 'M';
        } elseif ($num >= 1000) {
            return number_format($num / 1000, ($num % 1000 !== 0 ? 1 : 0)) . 'K';
        }
        return $num;
    }
    public function videolike(Request $request)
    {
        $video_id = $request->input('video_id');
        $ipAddress = $request->input('device_id');
        $status = $request->input('status');

        // Define Redis keys for like & dislike counts
        $likeKey = "video_likes_{$video_id}".config('app.webtype');
        $dislikeKey = "video_dislikes_{$video_id}".config('app.webtype');

        // Check if user has already liked/disliked
        $videolike = DB::table('videolike')->where('ipAddress', $ipAddress)->where('video_id', $video_id)->first();

        if ($videolike) {
            DB::table('videolike')->where('video_id', $video_id)->where('ipAddress', $ipAddress)->update(['status' => $status]);
        } else {
            $datavideo = [
                'ipAddress' => $ipAddress,
                'video_id' => $video_id,
                'status' => $status
            ];
            DB::table('videolike')->insert($datavideo);
        }

        // Fetch from Redis or DB if not cached
        // if (Redis::exists($likeKey)) {
        //     $likeCount = Redis::get($likeKey);
        // } else {
        //     $likeCount = DB::table('videolike')->where('video_id', $video_id)->where('status', '1')->count();
        //     Redis::setex($likeKey, 600, $likeCount); // Cache for 10 minutes
        // }

        // if (Redis::exists($dislikeKey)) {
        //     $dislikeCount = Redis::get($dislikeKey);
        // } else {
        //     $dislikeCount = DB::table('videolike')->where('video_id', $video_id)->where('status', '0')->count();
        //     Redis::setex($dislikeKey, 600, $dislikeCount); // Cache for 10 minutes
        // }
        // Format like and dislike count
        // $data['videolike'] = $this->formatCount($likeCount);
        // $data['videodislike'] = $this->formatCount($dislikeCount);

        $likeCount = DB::table('videolike')
        ->where('video_id', $video_id)
        ->where('status', '1')
        ->count();

        // $dislikeCount = DB::table('videolike')
        //     ->where('video_id', $video_id)
        //     ->where('status', '0')
        //     ->count();

        //Redis::setex($likeKey, 600, $likeCount);
        // Redis::setex($dislikeKey, 600, $dislikeCount);
        $data = [
            'ipAddress' => $ipAddress,
            'video_id' => $video_id,
            'status'=>'1'
        ];
        // Always return integer counts
        $data['videolike'] = (int) $likeCount;
        $data['videodislike'] = 0;

       

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function userpoints(Request $request)
    {
        $user_id = $request->input('user_id');

        if (!$user_id) {
            return response()->json(['error' => 'User is not found'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        try {
            // Define Redis key
            $redisKey = "userpoints_{$user_id}".config('app.webtype');

            // Check if data exists in Redis
            if (Redis::exists($redisKey)) {
                return response()->json(json_decode(Redis::get($redisKey)), 200, [], JSON_UNESCAPED_UNICODE);
            }

            // Fetch settings data
            $settingdata = DB::table('settings')
                ->select('spendtime', 'point', 'pointtxt', 'pointtitle', 'pointinfo', 'fullcontent_status', 'pricemonth', 'pricemonthDiscount', 'priceyear', 'priceyearDiscount')
                ->first();

            // Fetch user points
            $userpoints = DB::table('user_durations as ud')
                ->join('settings as s', DB::raw('1 = 1'), '>', DB::raw('0'))
                ->select(
                    'ud.date',
                    DB::raw('CAST(FLOOR(ud.total_duration * s.point) / s.spendtime AS UNSIGNED) AS points'),
                    DB::raw("'Earning coins' AS `title`")
                )
                ->where('ud.user_id', $user_id)
                ->orderBy('ud.date', 'DESC')
                ->get();

            // Fetch user data
            $userreg = Userregister::find($user_id);

            // Fetch referral points
            $userreferalpoints = DB::table('referral')
                ->where('referralcode', optional($userreg)->referralcode)
                ->orderBy('id', 'DESC')
                ->get();

            // Add registration/signup points
            if ($userreg && $userreg->user_points) {
                $userpoints->push([
                    'date' => date('Y-m-d', strtotime($userreg->created_at)),
                    'points' => $userreg->user_points,
                    'title' => 'Registration/Signup coins',
                ]);
            }

            // Add referral points
            if ($userreferalpoints->isNotEmpty()) {
                foreach ($userreferalpoints as $referral) {
                    $userpoints->push([
                        'date' => date('Y-m-d', strtotime($referral->created_at)),
                        'points' => $referral->points,
                        'title' => $referral->referaltitle,
                    ]);
                }
            }

            // Fetch spent points
            $spentpoints = DB::table('giftredeemcoins')
                ->select(
                    'giftredeemcoins.id', 'giftredeemcoins.user_id', 'giftredeemcoins.giftid', 'giftredeemcoins.spentcoins', 
                    'giftredeemcoins.address', 'giftredeemcoins.mobilenumber', 'giftredeemcoins.status', 'giftredeemcoins.drawcode',
                    DB::raw("DATE_FORMAT(giftredeemcoins.created_at, '%Y-%m-%d') as created_at"),
                    DB::raw("
                        CASE 
                            WHEN giftredeemcoins.drawcode IS NOT NULL AND giftredeemcoins.drawcode != '' 
                            THEN CONCAT(gift.gifttitle, ' ', giftredeemcoins.drawcode) 
                            ELSE gift.gifttitle 
                        END as gift_title
                    ")
                )
                ->leftJoin('gift', 'gift.id', '=', 'giftredeemcoins.giftid')
                ->where('giftredeemcoins.user_id', $user_id)
                ->orderBy('giftredeemcoins.id', 'DESC')
                ->get();

            // Fetch active gifts
            $giftlist = DB::table('gift')
                ->where('status', 'Active')
                ->where('qty', '!=', 0)
                ->orderBy('id', 'ASC')
                ->get();

            // Prepare response data
            $data = [
                'userpoints' => $userpoints,
                'spentpoints' => $spentpoints,
                'giftlist' => $giftlist,
                'pointsTitle' => $settingdata->pointtitle ?? '',
                'pointsNots' => $settingdata->pointtxt ?? '',
                'pointinfo' => $settingdata->pointinfo ?? '',
                'fullcontent_status' => $settingdata->fullcontent_status ?? 0,
                'pricemonth' => $settingdata->pricemonth ?? 0,
                'pricemonthDiscount' => $settingdata->pricemonthDiscount ?? 0,
                'priceyear' => $settingdata->priceyear ?? 0,
                'priceyearDiscount' => $settingdata->priceyearDiscount ?? 0,
            ];

            // Store in Redis for 30 minutes (1800 seconds)
            Redis::setex($redisKey, 1800, json_encode($data));

            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
    public function getUserPoints(Request $request)
    {
        // Get user_id from request (or from auth if logged in)
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['error' => 'user_id is required'], 400);
        }

        // Run the SQL query
        $result = DB::selectOne("
            SELECT 
              COALESCE((SELECT SUM(points) FROM user_durations WHERE user_id = ?), 0) AS total_points,
              COALESCE((SELECT SUM(spentcoins) FROM giftredeemcoins WHERE user_id = ?), 0) AS total_spent,
              COALESCE((SELECT SUM(points) FROM user_durations WHERE user_id = ?), 0)
                - COALESCE((SELECT SUM(spentcoins) FROM giftredeemcoins WHERE user_id = ?), 0)
                AS remaining_points
        ", [$userId, $userId, $userId, $userId]);

        $totalPoints = (int) $result->total_points;
        $totalSpent  = (int) $result->total_spent;

        // ➕ ADD 100 EXTRA POINTS
        $updatedTotalPoints = $totalPoints + 100;

        // Remaining = (total_points + 100) - spent
        $remainingPoints = $updatedTotalPoints - $totalSpent;

        // Return as JSON
        return response()->json([
            'user_id'          => $userId,
            'total_points'     => $updatedTotalPoints,   // updated total
            'total_spent'      => $totalSpent,
            'remaining_points' => $remainingPoints,
        ]);
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
  		public function breakingnews(Request $request)
        {
            $webtype = config('app.webtype');
            $today = date('Y-m-d');

            // ---------- Redis Keys ----------
            $breakingNewsKey = "breaking_news:$webtype:$today";
            $newsflashKey = "newsflashs:$webtype:$today";

            // ---------- Breaking News ----------
            $breakingNews = Redis::get($breakingNewsKey);

            if ($breakingNews) {
                $breakingNews = json_decode($breakingNews, true);
            } else {
                $selectFields = [
                    'news.id', 'news.title', 'news.slug'
                ];

                // Attempt to fetch today's breaking news
                $breakingNews = DB::table('news')
                    ->select($selectFields)
                    ->where('news.status', 'Active')
                    ->where('news.is_breaking', 1)
                    ->whereDate('news.created_at', $today)
                    ->groupBy('news.id')
                    ->orderByDesc('news.id')
                    ->limit(5)
                    ->get();

                // If no breaking news found, fallback to default
                if ($breakingNews->isEmpty()) {
                    $breakingNews = DB::table('news')
                        ->select($selectFields)
                        ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
                        ->where('news.status', 'Active')
                        ->where('news.catOrder', 0)
                        ->where('news.is_home', 0)
                        ->where('nc.category_id', 1)
                        ->groupBy('news.id')
                        ->orderByDesc('news.created_at')
                        ->limit(5)
                        ->get();
                }

                // Cache the result for 5 minutes
                Redis::setex($breakingNewsKey, 300, json_encode($breakingNews));
            }

            // ---------- Newsflash ----------
            $newsflash = Redis::get($newsflashKey);

            if ($newsflash) {
                $newsflash = json_decode($newsflash, true);
            } else {
                $titles = DB::table('newsflash')
                    ->where('status', 1)
                    ->whereDate('created_at', $today)
                    ->orderByDesc('id')
                    ->limit(10)
                    ->pluck('title');

                $newsflash = $titles
                    ->flatMap(fn($title) => explode('•••', $title))
                    ->map(fn($t) => trim($t))
                    ->implode(' ••• ');

                Redis::setex($newsflashKey, 300, json_encode($newsflash));
            }

            return response()->json([
                'breakingnews' => $breakingNews,
                'newsflash' => $newsflash,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function topbreakingnewsWeb(Request $request)
        {
            $webtype = config('app.webtype');
            $today = date('Y-m-d');

            // ---------- Redis Keys ----------
            $newsflashKey = "newsflashs:$webtype:$today";

            // ---------- Newsflash ----------
            $newsflash = Redis::get($newsflashKey);

            if ($newsflash) {
                $newsflash = json_decode($newsflash, true);
            } else {
                $titles = DB::table('newsflash')
                    ->where('status', 1)
                    ->whereDate('created_at', $today)
                    ->orderByDesc('id')
                    ->limit(10)
                    ->pluck('title');

                $newsflash = $titles
                    ->flatMap(fn($title) => explode('•••', $title))
                    ->map(fn($t) => trim($t))
                    ->implode(' ••• ');

                Redis::setex($newsflashKey, 300, json_encode($newsflash));
            }

            return response()->json([
                'newsflash' => $newsflash,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function gstvfasttrack(Request $request)
        {
            $newsdataKey = 'gujaratnewsapi_' . config('app.webtype');
            $newsdata = Cache::remember($newsdataKey, 300, function () {
                return DB::table('news as n')
                    ->join('news_category as nc', 'nc.news_id', '=', 'n.id')
                    ->join('categories as c', 'c.id', '=', 'nc.category_id')
                    ->select([
                        'n.id',
                        'n.created_at',
                        'n.slug',
                        'n.title',
                        'n.videoURL',
                        'n.featureImage',
                        DB::raw('GROUP_CONCAT(DISTINCT c.slug ORDER BY c.id) as category_slugs')
                    ])
                    ->where('n.status', 'Active')
                    ->where('n.catOrder', 0)
                    ->where('n.is_home', 0)
                    ->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                        ->from('news_category as filter_nc')
                        ->whereColumn('filter_nc.news_id', 'n.id')
                        ->where('filter_nc.category_id', 1); // Gujarat category
                    })
                    ->groupBy('n.id')
                    ->orderByDesc('n.created_at')
                    ->limit(10)
                    ->get();
            });

            $newsdata = collect($newsdata);

            return response()->json([
                'gstvfasttrack' => $newsdata
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
  
        public function poll(Request $request)
        {
            $ipAddress = $request->input('device_id');
            $userID = $request->input('userID');
            //$pollID = $request->input('pollID');
          	 $now = Carbon::now('Asia/Kolkata');
            // Redis Key
            $redisKey = "polls_active_" . config('app.webtype');

            // --- Check Redis ---
            if (Redis::exists($redisKey)) {
                $poll = json_decode(Redis::get($redisKey));
            } else {
                // Fetch poll (without votes)
                $poll = DB::table('polls')
                    ->where('polls.status', 'Active')
                    ->orderByDesc('id')
                    ->first();

                // Count Views
                if (empty($userID)) {
                    $this->viewCountService->incrementViewCount('pollcount', $poll->id);
                } else {
                    $this->viewCountService->incrementViewLoginCount('pollcount', $poll->id);
                }

                // Store in Redis
                Redis::setex($redisKey, 600, json_encode($poll));
            }

            // --- Fetch selectedAnswer fresh every time ---
            $selectedAnswer = DB::table('votes')
                ->where('pollID', $poll->id)
                ->where('IPaddress', $ipAddress)
                ->value('selectedAnswer');

            $poll->selectedAnswer = $selectedAnswer;
          	if (!empty($poll->expiredate)) {
                $expireTime = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    trim($poll->expiredate),
                    'Asia/Kolkata'
                );
                $poll->expirestatus = $now->lt($expireTime);

            } else {
                $poll->expirestatus = true;
            }


            // --- Final output format ---
            return response()->json([
                "poll" => [$poll]
            ]);
        }
        public function submitpoll(Request $request)
        {
            $ipAddress = $request->input('device_id');
            $pollID = $request->input('pollID');
            $userID = $request->input('userID'); // if empty → no coins
            $selectedAnswer = $request->input('selectedAnswer');

            // Check existing vote using USER or IP
            // $existingVote = Vote::where('pollID', $pollID)
            //     ->where(function ($q) use ($ipAddress) {
            //         // if (!empty($userID)) {
            //         //     $q->where('userID', $userID);
            //         // }
            //         $q->orWhere('IPaddress', $ipAddress);
            //     })
            //     ->first();
          
          
            // if ($existingVote) {

            //     $existingVote->selectedAnswer = $selectedAnswer;

            //     // Update userID only if empty
            //     if (empty($existingVote->userID) && !empty($userID)) {
            //         $existingVote->userID = $userID;
            //     }

            //     // Update coin only if empty OR 0
            //     // if (empty($existingVote->coin) && $coinValue > 0) {
            //     //     $existingVote->coin = $coinValue;
            //     // }
            //     $existingVote->save();
              
            //   	return response()->json([
            //         'status'        => true,
            //         'message'       => 'Vote updated successfully',
            //         'message_guj'   => 'તમારો મત સુધારવામાં આવ્યો છે',
            //         'vote_id'       => $existingVote->id,
            //         'user_id'       => $existingVote->userID,
            //         'poll_id'       => $pollID,
            //         'draw_message'  => 'draw'
            //     ]);

            // }
            $vote = new Vote([
                'pollID'        => $pollID,
                'selectedAnswer'=> $selectedAnswer,
                'userID'        => $userID,
                // 'coin'          => $coinValue,
                'IPaddress'     => $ipAddress
            ]);

            $vote->save();

            return response()->json([
                'status'        => true,
                'message'       => 'Vote submitted successfully',
                'message_guj'   => 'તમારો મત સફળતાપૂર્વક નોંધાયો છે',
                'vote_id'       => $vote->id,
                'user_id'       => $userID,
                'poll_id'       => $pollID,
                'draw_message'  => 'draw'
            ]);
        }
        public function submitpoll11(Request $request)
        {
            $ipAddress = $request->input('device_id');
            $pollID = $request->input('pollID');
            $userID = $request->input('userID'); // if empty → no coins
            $selectedAnswer = $request->input('selectedAnswer');

            // Check existing vote using USER or IP
            $existingVote = Vote::where('pollID', $pollID)
                ->where(function ($q) use ($ipAddress) {
                    // if (!empty($userID)) {
                    //     $q->where('userID', $userID);
                    // }
                    $q->orWhere('IPaddress', $ipAddress);
                })
                ->first();
          
			// $coinValue = !empty($userID) ? 10 : 0;
          
            if ($existingVote) {

                $existingVote->selectedAnswer = $selectedAnswer;

                // Update userID only if empty
                if (empty($existingVote->userID) && !empty($userID)) {
                    $existingVote->userID = $userID;
                }

                // Update coin only if empty OR 0
                // if (empty($existingVote->coin) && $coinValue > 0) {
                //     $existingVote->coin = $coinValue;
                // }
                $existingVote->save();
              
              	return response()->json([
                    'status'        => true,
                    'message'       => 'Vote updated successfully',
                    'message_guj'   => 'તમારો મત સુધારવામાં આવ્યો છે',
                    'vote_id'       => $existingVote->id,
                    'user_id'       => $existingVote->userID,
                    'poll_id'       => $pollID,
                    'draw_message'  => 'draw'
                ]);

            }
            $vote = new Vote([
                'pollID'        => $pollID,
                'selectedAnswer'=> $selectedAnswer,
                'userID'        => $userID,
                // 'coin'          => $coinValue,
                'IPaddress'     => $ipAddress
            ]);

            $vote->save();

            return response()->json([
                'status'        => true,
                'message'       => 'Vote submitted successfully',
                'message_guj'   => 'તમારો મત સફળતાપૂર્વક નોંધાયો છે',
                'vote_id'       => $vote->id,
                'user_id'       => $userID,
                'poll_id'       => $pollID,
                'draw_message'  => 'draw'
            ]);
        }
         public function submitdrawpoll(Request $request)
        {
            $mobile = $request->input('mobile');
            $firstname = $request->input('firstname');
            $lastname = $request->input('lastname');
            $address = $request->input('address');
            $pollID = $request->input('pollID');
            $voteId = $request->input('voteId');
            // Check if the mobile number already exists in the database
            $mobileExist = Drawpoll::where('mobile', $mobile)->where('pollID', $pollID)->first();
            if (!empty($mobileExist)) {
                $data = [
                    'mobile' => $mobile,
                    'firstname' => $firstname,
                    'lastname'=>$lastname,
                    'coin'=>10,
                    'pollID'=>$pollID,
                    'voteId'=>$voteId,
                    'address' => $address,
                    'updated_at'=>now()
                ];
                DB::table('drawpoll')->where('mobile', $mobile)->update(['firstname' => $firstname,'address' => $address,'lastname'=>$lastname,'coin'=>10,'pollID'=>$pollID,'voteId'=>$voteId,'updated_at'=>now()]);
                return response()->json(['success' => true,'data' => $data,'msg' => 'Poll vote submitted successfully'], 200);
            } else {
                // Mobile does not exist, send SMS and insert into database
                $data = [
                    'mobile' => $mobile,
                    'firstname' => $firstname,
                    'lastname'=>$lastname,
                    'coin'=>10,
                    'pollID'=>$pollID,
                    'voteId'=>$voteId,
                    'address' => $address,
                    'created_at'=>now()
                ];
                // $insertedId = DB::table('userregister')->insert($data);
                $insertedId = DB::table('drawpoll')->insertGetId($data);
                // DB::table('votes')->where('id', $voteId)->update(['userID' => $insertedId,'coin' => 10]);
                return response()->json(['success' => true,'data' => $data, 'msg' => 'Draw poll vote submitted successfully.'], 200);
            }
           
        }
        public function submitpoll_old(Request $request)
        {
            $ipAddress = $request->input('device_id');
            $pollID = $request->input('pollID');
            $userID = $request->input('userID');
            $selectedAnswer = $request->input('selectedAnswer');
            $existingVote = Vote::where('userID', $userID)
                                ->where('pollID',  $pollID)
                                ->first();

            if ($existingVote) {
                $existingVote->selectedAnswer = $selectedAnswer;
                $existingVote->save();
            }  else {
                $vote = new Vote([
                    'pollID' =>  $request->input('pollID'),
                    'selectedAnswer'=>  $selectedAnswer,
                    'userID'=>  $userID,
                    'coin'=>10,
                    'IPaddress'=> $ipAddress
                ]);
                
                $vote->save();
            }
            
            return response()->json(['status'=>true,'message' => 'Vote submitted successfully','message_guj' => 'You have already voted for this poll.']);
        } 
        public function pollresult()
        {
            try {
                // Define Redis key
            /* $redisKey = "poll_results_latest_10".config('app.webtype');
        
                // Check if poll results are cached in Redis
                if (Redis::exists($redisKey)) {
                    $pollresults = json_decode(Redis::get($redisKey));
                } else {*/
                    // Fetch latest 10 poll questions
                    $pollresults = DB::table('polls')
                        ->select('question', 'answerOption', 'id')
                        ->where('status','active')
                        ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                        ->orderByDesc('id')
                        ->limit(10)
                        ->get();
        
                  foreach ($pollresults as $poll) {
                        $totalVotes = DB::table('votes')
                            ->where('pollID', $poll->id)
                            ->count();

                        $results = DB::table('votes')
                            ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                            ->where('pollID', $poll->id)
                            ->groupBy('selectedAnswer')
                            ->get()
                            ->keyBy('selectedAnswer'); // index by answer text

                        // Now reorder results as per answerOption
                        $orderedResults = [];
                        $options = explode(':', $poll->answerOption);

                        foreach ($options as $option) {
                            $option = trim($option);
                            if (isset($results[$option])) {
                                $count = $results[$option]->answerCount;
                            } else {
                                $count = 0; // no votes yet
                            }
                            $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 2) : 0;

                            $orderedResults[] = (object)[
                                'selectedAnswer' => $option,
                                'answerCount'    => $count,
                                'percentage'     => $percentage,
                            ];
                        }

                        $poll->selectedresult = $orderedResults; // assign back
                    }

                    // Store poll results in Redis for 10 minutes (600 seconds)
                /*  Redis::setex($redisKey, 600, json_encode($pollresults));
                }*/
        
                return response()->json(['pollresults' => $pollresults]);
        
            } catch (\Exception $e) {
                return response()->json(['error' => 'Something went wrong'], 500);
            }
        }
        public function Iframe(Request $request)
        {
            try {
                // Define Redis key
                $redisKey = "active_iframe".config('app.webtype');

                // Check if iframe data is cached in Redis
                if (Redis::exists($redisKey)) {
                    $iframe = json_decode(Redis::get($redisKey));
                } else {
                    // Fetch active iframe records from the database
                    $iframe = DB::table('iframe')
                        ->select('title', 'url')
                        ->where('status', 'Active')
                        ->get();

                    // Store iframe data in Redis for 30 minutes (1800 seconds)
                    Redis::setex($redisKey, 1800, json_encode($iframe));
                }

                // If no active iframe data found, return a 404 response
                if ($iframe->isEmpty()) {
                    return response()->json(['error' => 'Iframe is not found'], 404, [], JSON_UNESCAPED_UNICODE);
                }

                return response()->json(['iframe' => $iframe], 200, [], JSON_UNESCAPED_UNICODE);

            } catch (\Exception $e) {
                return response()->json(['error' => 'Something went wrong'], 500);
            }
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
        public function getepapercat(Request $request)
        {
            try {
                // Define Redis key
                $redisKey = "active_getepapercat" . config('app.webtype');

                // Check if data is cached in Redis
                if (Redis::exists($redisKey)) {
                    $epapercat = collect(json_decode(Redis::get($redisKey)));
                } else {
                    // Fetch from DB
                    $epapercat = DB::table('epapercategory')
                        ->select('id','title','cattype','featureImage','icon','icon_dark','slug','engtitle')
                        ->where('status', 'Active')
                        ->where('cattype', 'Magazine')  
                        ->orderBy('catOrder', 'ASC')
                        ->get();
                    $epapercat->transform(function ($item) {
                        $item->url = config('app.front_url') . '/magazine/' . $item->slug;
                        return $item;
                    });
                    // Cache for 30 minutes
                    Redis::setex($redisKey, 1800, json_encode($epapercat));
                }

                // Handle empty
                if ($epapercat->isEmpty()) {
                    return response()->json(['error' => 'Epapercat is not found'], 404, [], JSON_UNESCAPED_UNICODE);
                }

                return response()->json(['epapercat' => $epapercat], 200, [], JSON_UNESCAPED_UNICODE);

            } catch (\Exception $e) {
                return response()->json(['error' => 'Something went wrong'], 500);
            }
        }
        public function magazine(Request $request)
        {
            $data = [];
            $page = (int) $request->input('pageNumber', 1);
            $slug = $request->input('epaper_cat', '');
            $date = $request->input('date', '');
            $perPage = (int) $request->input('per_page', 8);
            $offset = ($page - 1) * $perPage;

            $query = DB::table('epaper')
                ->join('epapercategory', 'epapercategory.id', '=', 'epaper.ecatID')
                ->where('epaper.status', 'Active')
                ->where('epapercategory.status', 'Active')
                ->where('epapercategory.cattype', 'Magazine');

            if ($date != "") {
                $formattedDate = date('Y-m-d', strtotime($date));
                $query->where('epaper.newspaperdate', $formattedDate);
            }

            if ($slug != "") {
                $query->where('epapercategory.slug', $slug);
            }

            $epapers = $query
                ->orderBy('epaper.created_at', 'DESC')
                ->select(
                    'epapercategory.title',
                    'epapercategory.cattype',
                    'epapercategory.slug as ecatslug',
                    'epaper.*'
                )
                ->paginate($perPage, ['*'], 'page', $page);

            // 🔁 Transform the data while keeping pagination intact
            $epapers->getCollection()->transform(function ($item) {
                if (!empty($item->Story_data)) {
                    $storyData = json_decode($item->Story_data, true);
                    $storyData = array_filter($storyData, function ($value) {
                        return !empty($value['image']);
                    });
                    $item->Story_data = array_values(array_map(function ($value) {
                        return $value['image'];
                    }, $storyData));
                }
                return $item;
            });

            $data['Magazine'] = $epapers;

            // 📚 Magazine List
            $magazinlist = DB::table('epapercategory')
                ->select('title', 'slug')
                ->where('status', 'Active')
                ->where('cattype', 'Magazine')
                ->get();

            $data['magazinlist'] = $magazinlist;

            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        }

        public function epaper(Request $request)
        {
            $page = (int) $request->input('pageNumber', 1);
            $perPage = (int) $request->input('per_page', 8);
            $date = $request->input('date', '');
            $redisKey = "epaperpage_{$page}_{$date}" . config('app.webtype');
            $epapercity = Redis::get($redisKey);

            $newspaperDate = !empty($date) ? date('Y-m-d', strtotime($date)) : null;

            if (!$epapercity) {
                $query = DB::table('epapercategory')
                    ->leftJoinSub(
                        DB::table('epaper as e1')
                            ->select('e1.*')
                            ->where('e1.status', 'Active'),
                        'epaper',
                        function ($join) {
                            $join->on('epapercategory.id', '=', 'epaper.ecatID');
                        }
                    )
                    ->where('epapercategory.status', 'Active')
                    ->where('epapercategory.cattype', 'Newspaper');

                if (!empty($newspaperDate)) {
                    $query->whereDate('epaper.newspaperdate', $newspaperDate);
                }

                $epapercity = $query
                    ->select(
                        'epapercategory.title',
                        'epapercategory.cattype',
                        'epapercategory.id',
                        'epapercategory.slug as ecatslug',
                        'epaper.*'
                    )
                    ->orderBy('epaper.created_at', 'DESC')
                    ->paginate($perPage, ['*'], 'page', $page);

                // ✅ Work on the collection without breaking pagination
                $filtered = $epapercity->getCollection()->filter(function ($item) {
                    return !empty($item->ecatID);
                });

                $transformed = $filtered->map(function ($item) {
                    if (!empty($item->Story_data)) {
                        $storyData = json_decode($item->Story_data, true);
                        $storyData = array_filter($storyData, function ($value) {
                            return !empty($value['image']);
                        });
                        $item->Story_data = array_values(array_map(function ($value) {
                            return $value['image'];
                        }, $storyData));
                    }
                    return $item;
                });

                // ✅ Set the transformed collection back
                $epapercity->setCollection($transformed->values());

                // ✅ Wrap in response structure
                $data['Newspaper'] = $epapercity;

                // ✅ Cache the whole pagination structure (as JSON)
                Redis::setex($redisKey, 900, json_encode($data));
            } else {
                $data = json_decode($epapercity, true);
            }

            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        }

        public function epaperdetail(Request $request)
        {
            $slug = $request->input('slug');
            $date = $request->input('date');
            $userId = 0;
            if ($request->has('user_id')) {
                $userId = $request->input('user_id');
            }
            try {
                $formattedDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format'], 400);
            }
            $redisKey = "epaper_detail_{$slug}_{$formattedDate}_{$userId}".config('app.webtype');
            $cachedData = Redis::get($redisKey);

            /*if ($cachedData) {
                return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
            }*/

            if (Redis::exists($cachedData)) {
                $cachedResponse = json_decode(Redis::get($cachedData), true);

                if (!empty($cachedResponse['epaper']['id'])) {
                    $this->viewCountService->incrementViewCount('epapercount', $cachedResponse['epaper']['id']);
                }
                return response()->json($cachedResponse);
            }

            $epaper = DB::table('epaper')
                ->select('epaper.*', 'epapercategory.slug as ecatslug')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM epapercategory WHERE FIND_IN_SET(epapercategory.id, epaper.ecatID)) as title')
                ->join('epapercategory', DB::raw('FIND_IN_SET(epapercategory.id, epaper.ecatID)'), '>', DB::raw('0'))
                ->where('epapercategory.slug', $slug)
                ->whereDate('epaper.newspaperdate', $formattedDate)
                ->orderBy('epaper.created_at', 'DESC')
                ->first();
            $this->viewCountService->incrementViewCount('epapercount', $epaper->id);
            if ($epaper && !empty($epaper->Story_data)) {
                $storyData = json_decode($epaper->Story_data, true);
                $storyData = array_filter($storyData, fn($value) => !empty($value['image']));
                $epaper->Story_data = array_values(array_map(fn($value) => $value['image'], $storyData));
            }
 
            
            // Fetch settings data from Redis or set it if not available
            $cacheKey = 'settingsappepaper' . config('app.webtype');
            $setting  = Redis::get($cacheKey);

            if (!$setting) {
                $setting = DB::table('settings')
                    ->select('fullcontent_status', 'fullcontent_login', 'logo','plan_news_count')
                    ->first();
                $setting = (array) $setting;
                Redis::set($cacheKey, json_encode($setting));

            } else {
                $setting = json_decode($setting, true);
            }
            
            $plan = Redis::get('plandtlapi'.config('app.webtype'));
            if (!$plan) {
                $plan = DB::table('plan')
                    ->where('status', 'Active')
                    ->get();
                
                Redis::set('plandtlapi'.config('app.webtype'), json_encode($plan)); // Store in Redis
            } else {
                $plan = json_decode($plan, true);
            }
 
            $planexist = 0;
            $plan_expire = '';
            if (!empty($setting) && isset($setting['fullcontent_login'])) {
                $planexist = $setting['fullcontent_login'] == 0 ? 1 : 0;
            }

            if ($userId != "" || $userId != null || $userId != 0) {
                $plandetals = DB::table('user_plan')
                    ->select('plan_status','plan_expire')
                    ->where('user_id', $userId)
                    ->where('plan_status', 'Active')
                    ->orderBy('id', 'desc')
                    ->first();
                if ($setting['fullcontent_login'] == 1) {
                    $planexist = 1;
                    if ($setting['fullcontent_status'] == 1 && empty($plandetals)) {
                        $planexist = 0;
                    }

                    if ($setting['fullcontent_status'] == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Inactive") {
                        $planexist = 0;
                        $plan_expire = $plandetals->plan_expire;
                    }

                    if ($setting['fullcontent_status'] == 1 && isset($plandetals->plan_status) && $plandetals->plan_status == "Active") {
                        $planexist = 1;
                        $plan_expire = $plandetals->plan_expire;
                    }
                }
            }
    
            $data = ['epaper' => $epaper, 'date' => $formattedDate,'planexist' =>  $planexist,'plan_expire' => $plan_expire];
            
            // Store in Redis with expiration time (1 hour)
            Redis::setex($redisKey, 3600, json_encode($data));

            // Increment viewer count
            // DB::table('epaper')
            //     ->where('newspaperdate', $formattedDate)
            //     ->whereIn('ecatID', function ($query) use ($slug) {
            //         $query->select('id')->from('epapercategory')->where('slug', $slug);
            //     })
            //     ->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);

            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function reedemPointsByUser(Request $request)
        { 
            $user_id = $request->input('user_id');
            $giftid =  $request->input('giftid');
            $spentcoins =  $request->input('spentcoins');
            $address =  $request->input('address');
            $mobilenumber =  $request->input('mobilenumber');
            $giftid =  $request->input('giftid');
            $drawcode =  $request->input('drawcode');
            $giftlist = [
                'user_id' => $user_id,
                'giftid' => $giftid,
                'spentcoins'=>$spentcoins,
                'address'=>$address,
                'mobilenumber'=>$mobilenumber,
                'drawcode'=>$drawcode,
                'status'=>"Active",
                'created_at'=>now()
            ];
            $inserted = DB::table('giftredeemcoins')->insert($giftlist);
            $data = [];
            if ($inserted) {
                $giftlist = DB::table('gift')->where('id', $giftid)->first();
                if ($giftlist) {
                    DB::table('gift')->where('id', $giftid)->update(['qty' => $giftlist->qty - 1]);
                } else {
                    $data = array('message' => 'Gift item not found');
                    return response()->json($data, 200,[], JSON_UNESCAPED_UNICODE);
                }
                if($drawcode != ""){
                    $data = array('message' => 'લકી ડ્રો માટે તમારુ રજિસ્ટ્રેશન સફળતાપૂર્વક થઈ ગયું છે. આ તમારો ટિકિટ નંબર "'.$drawcode.'"છે, જેને સાચવીને રાખવો. 1 જાન્યુઆરીએ અહીં લકી ડ્રોના વિજેતાઓના નંબર દર્શાવાશે, જેમાં તમે તમારો નંબર વિજેતા લિસ્ટમાં છે કે નહીં તે ચેક કરી શકશો.');
                } else {
                    $data = array('message' => 'Reedem added successfully.');
                }
                return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json($data, 404, [], JSON_UNESCAPED_UNICODE);
            }
            return response()->json($responseData);
        }
        public function categorysatrang()
        {
            $cacheKey = "categories_satrang".config('app.webtype');

            // Check if data exists in Redis
            $cachedData = Redis::get($cacheKey);

            if ($cachedData) {
                return response()->json(json_decode($cachedData), 200, [], JSON_UNESCAPED_UNICODE);
            }

            $categorychildQuery = DB::table('categories as c1')
                ->select('c1.title as category_name', 'c1.*', 'u1.name as authorName', 'c2.title as parent_category_name')
                ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
                ->where('c1.parentID', 63)
                ->orderBy('c1.parentID', 'ASC')
                ->orderBy('c1.catOrder', 'ASC')
                ->get();

            $iconBaseUrl = config('app.backendnewsurl') . '/public/uploads/category/icon/';
            foreach ($categorychildQuery as $item) {
                $item->icon = $iconBaseUrl . $item->icon;
            }

            $data['categorychildQuery'] = $categorychildQuery;

            // Store in Redis for 1 hour
            Redis::setex($cacheKey, 3600, json_encode($data));

            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function submitplanpayment(Request $request)
        {   
            $userId = $request->input('user_id');
            $plan = $request->input('plan'); // monthly,yearly
            $plan_amount = $request->input('plan_amount');
            $newPlanDuration = $request->input('plan') === "monthly" ? now()->addMonth() : now()->addYear();

            // Check if the user has an active plan
            $activePlan = DB::table('user_plan')
                ->where('user_id', $userId)
                ->where('plan_status', 'Active')
                ->orderBy('id', 'DESC')
                ->first();
        
            if ($activePlan) {
                // If an active plan exists, calculate the new plan's start date
                $currentExpiry = Carbon::parse($activePlan->plan_expire);
                $newPlanStartDate = $currentExpiry->isFuture() ? $currentExpiry : now();

                // Calculate the new plan's expiration date based on the start date
                $newPlanExpiry = $request->input('plan') === "monthly"
                    ? $newPlanStartDate->copy()->addMonth()
                    : $newPlanStartDate->copy()->addYear();
            } else {
                // If no active plan exists, use the current date as the start date
                $newPlanStartDate = now();
                $newPlanExpiry = $newPlanDuration;
            }
    
            // Insert the new plan
            $newPlan = Userplan::create([
                'user_id' => $userId,
                'plan' => $request->input('plan'),
                'plan_amount' => $request->input('plan_amount'),
                'plan_expire' => $newPlanExpiry->format('Y-m-d'),
                'plan_status' => 'Active',
            ]);
            
            if ($newPlan) {
                return response()->json(['message' => 'Plan added successfully!'], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(['message' => 'Failed to add plan!'], 500, [], JSON_UNESCAPED_UNICODE);
            }
        }
        public function paymenthistory(Request $request)
        {   
            $userId = $request->input('user_id');
            $paymentHistory = DB::table('user_plan')
                                ->where('user_id', $userId)
                                ->orderBy('created_at','DESC')
                                ->get();
            $data = [
                'paymenthistory' => $paymentHistory->isNotEmpty() ? $paymentHistory : [] // Use the collection directly
            ];
            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function rashifal(Request $request)
        {
            // Generate Redis key for today's rashifal data
            $redisKey = 'rashifal_' . Carbon::today()->format('Y-m-d').config('app.webtype');
        
            // Check if data exists in Redis
            $cachedData = Redis::get($redisKey);
            if ($cachedData) {
                return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
            }
        
            // Fetch data from the database
            $rashi = DB::table('rashifal')
                ->select('id', 'title', 'engtitle', 'rashiword', 'rashiicon')
                ->where('status', 'Active')
                ->get()
                ->map(function ($rashi) {
                    $rashi->rashiicon = config('app.backendnewsurl') . '/public/uploads/rashi/icon/' . $rashi->rashiicon;
        
                    // Fetch today's rashifaldata for the current rashi
                    $rashifaldata = DB::table('rashifaldata')
                        ->select('rashifaldata.authorname', 'rashifaldata.authoricon', 
                                'rashifaldata.rashidescription', 'rashifaldata.created_at')
                        ->where('rashifaldata.rashifal_id', $rashi->id)
                        ->whereDate('rashifaldata.created_at', Carbon::today()) 
                        ->orderBy('rashifaldata.id', 'DESC')
                        ->first();
        
                    if ($rashifaldata) {
                        if($rashifaldata->authoricon == ""){
                            $rashifaldata->authoricon = 'author.png';
                        }
                        $rashifaldata->authoricon = config('app.backendnewsurl') . '/public/uploads/rashi/author/' . $rashifaldata->authoricon;
                        $rashi->rashifaldata = $rashifaldata;
                    } else {
                        $rashi->rashifaldata = null;
                    }
        
                    return $rashi;
                });
        
            if ($rashi->isEmpty()) {
                return response()->json(['error' => 'Rashi is not found'], 404, [], JSON_UNESCAPED_UNICODE);
            }
        
            $responseData = ['rashi' => $rashi];
        
            // Store data in Redis for 1 day (86400 seconds)
            Redis::setex($redisKey, 86400, json_encode($responseData, JSON_UNESCAPED_UNICODE));
        
            return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function rashifaldata(Request $request)
        {
            $rashi_id = $request->input('rashi_id');
            $redisKey = 'rashifaldata_' . $rashi_id . '_' . Carbon::today()->format('Y-m-d').config('app.webtype');

            // Check if data exists in Redis
            $cachedData = Redis::get($redisKey);
            if ($cachedData) {
                // Increment viewer count in the database even if cached data is returned
                //DB::table('rashifaldata')->where('rashifal_id', $rashi_id)->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);

                return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
            }

            // Fetch data from the database
            $rashifaldata = DB::table('rashifaldata')
                ->select(
                    'rashifaldata.rashifal_id',
                    'rashifaldata.authorname',
                    'rashifaldata.authoricon',
                    'rashifaldata.rashidescription',
                    'rashifaldata.created_at',
                    'rashifal.title',
                    'rashifal.engtitle',
                    'rashifal.rashiword',
                    'rashifal.rashiicon'
                )
                ->leftJoin('rashifal', 'rashifaldata.rashifal_id', '=', 'rashifal.id')
                ->where('rashifal.status', 'Active') // Filter active records
                ->where('rashifaldata.rashifal_id', $rashi_id)
                ->whereDate('rashifaldata.created_at', Carbon::today()) // Filter only today's records
                ->orderBy('rashifaldata.id', 'DESC')
                ->first();

            if ($rashifaldata) {
                $rashifaldata->rashiicon = config('app.backendnewsurl') . '/public/uploads/rashi/icon/' . $rashifaldata->rashiicon;
                $rashifaldata->authoricon = config('app.backendnewsurl') . '/public/uploads/rashi/author/' . $rashifaldata->authoricon;

                // Store data in Redis for 24 hours (86400 seconds)
                Redis::setex($redisKey, 86400, json_encode(['rashifaldata' => $rashifaldata], JSON_UNESCAPED_UNICODE));
            }

            // Increment viewer count in the database
            //DB::table('rashifaldata')->where('rashifal_id', $rashi_id)->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);

            if (!$rashifaldata) {
                return response()->json(['error' => 'Rashi data is not found'], 404, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json(['rashifaldata' => $rashifaldata], 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function rashifalalldata(Request $request)
    {
        $rashi_id = $request->input('rashi_id');
        $redisKey = 'rashifalalldata_' . $rashi_id.config('app.webtype');

        // Check if data exists in Redis
        $cachedData = Redis::get($redisKey);
        if ($cachedData) {
            return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Fetch data from the database
        $rashifaldata = DB::table('rashifaldata')
            ->select(
                'rashifaldata.rashifal_id',
                'rashifaldata.authorname',
                'rashifaldata.authoricon',
                'rashifaldata.rashidescription',
                'rashifaldata.created_at',
                'rashifal.title',
                'rashifal.engtitle',
                'rashifal.rashiword',
                'rashifal.rashiicon'
            )
            ->leftJoin('rashifal', 'rashifaldata.rashifal_id', '=', 'rashifal.id')
            ->where('rashifal.status', 'Active') // Filter active records
            ->where('rashifaldata.rashifal_id', $rashi_id)
            ->orderBy('rashifaldata.id', 'DESC')
            ->get();

        if ($rashifaldata->isNotEmpty()) {
            $rashifaldata->transform(function ($news) {
                $news->rashiicon = config('app.backendnewsurl') . '/public/uploads/rashi/icon/' . $news->rashiicon;
                $news->authoricon = config('app.backendnewsurl') . '/public/uploads/rashi/author/' . $news->authoricon;
                return $news;
            });

            // Store data in Redis for 24 hours (86400 seconds)
            Redis::setex($redisKey, 86400, json_encode(['rashifaldata' => $rashifaldata], JSON_UNESCAPED_UNICODE));
        }

        if ($rashifaldata->isEmpty()) {
            return response()->json(['error' => 'Rashi data is not found'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['rashifaldata' => $rashifaldata], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function livecricket()
    {
        $redisKey = 'livecricket_data' . config('app.webtype');

        // Check if data exists in Redis
        $cachedData = Redis::get($redisKey);
        if ($cachedData) {
            return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
        }

        $data = array();

        // Fetch data from the database
        $livecricket = DB::table('upcoming_cricket')
            ->select(
                'upcoming_cricket.series_id',
                'upcoming_cricket.series',
                'upcoming_cricket.match_time',
                'upcoming_cricket.venue',
                'upcoming_cricket.match_type',
                'live_cricket.livecricket',
                'live_cricket.match_id',
                'live_cricket.scorecard'
            )
            ->join('live_cricket', function ($join) {
                $join->on('upcoming_cricket.series_id', '=', 'live_cricket.series_id')
                    ->on('upcoming_cricket.series', '=', 'live_cricket.series');
            })
            ->where('upcoming_cricket.status', '1')
            ->first();

        if (!$livecricket) {
            return response()->json([
                'status' => 0,
                'message' => 'Live Cricket is not found'
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Convert `livecricket` JSON string to an associative array
        $livecricket->livecricket = json_decode($livecricket->livecricket, true);
        if (isset($livecricket->scorecard)) {
            $livecricket->scorecard = json_decode($livecricket->scorecard, true);
        }

        $data['livecricket'] = $livecricket;
        $data['message'] = 'success';
        $data['status'] = 1;

        // Store data in Redis for 1 hour (3600 seconds)
        Redis::setex($redisKey, 3600, json_encode($data, JSON_UNESCAPED_UNICODE));

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
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
  public function searchresult(Request $request)
    {
        $page = (int) $request->input('pageNumber', 1);
        $txtSearch = trim($request->input('txtSearch', ''));
        $perPage = (int) $request->input('per_page', 4);

        // Return empty if no search keyword
        if ($txtSearch === '') {
            return response()->json(['news' => []]);
        }

        // Safe Redis key
        $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($txtSearch));
        $redisKey = "news_search_{$safeKey}_{$page}_" . config('app.webtype');

        // Try fetching from Redis
        $cached = Redis::get($redisKey);
        if (!empty($cached)) {
            $newsData = json_decode($cached);

            $news = new \Illuminate\Pagination\LengthAwarePaginator(
                collect($newsData->data),
                $newsData->total,
                $newsData->per_page,
                $newsData->current_page,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json(['news' => $news], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Execute DB query
        $news = DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.englishTitle',
                'news.slug',
                'news.featureImage',
                'news.created_at',
                DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID')
            )
            ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
            ->where('news.status', 'Active')
            ->whereNotIn('nc.category_id', [9])
            ->where(function ($q) use ($txtSearch) {
                $q->where('news.title', 'LIKE', "%{$txtSearch}%")
                ->orWhere('news.englishTitle', 'LIKE', "%{$txtSearch}%");
            })
            ->groupBy('news.id')
            ->orderByDesc('news.created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        // Only cache if data found
        if ($news->total() > 0) {
            // Cache for 10 minutes
            Redis::setex($redisKey, 600, json_encode($news));
        }

        return response()->json(['news' => $news], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function searchresult09(Request $request)
    {
        $pageNumber = $request->input('pageNumber');
        $txtSearch = $request->input('txtSearch');

        $perPage = $request->input('per_page', 4); // Default items per page
        $page = $request->input('page', $pageNumber); // Default to first page
        $news = Redis::get('news_searchpage_' . $txtSearch . '_' . $page.config('app.webtype'));
        if ($news) {
            $newsData = json_decode($news);
            $news = new \Illuminate\Pagination\LengthAwarePaginator(
                collect($newsData->data),
                $newsData->total,
                $newsData->per_page,
                $newsData->current_page,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
        } else { 
            
        $news = DB::table('news')
            ->select(
                'news.id',
                'news.title',
                'news.englishTitle',
                'news.slug',
                'news.featureImage',
                'news.created_at',
                DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID') // alias for catID
            )
            ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
            ->where('news.status', 'Active')
            ->whereNotIn('nc.category_id', [9]) // replaces: NOT FIND_IN_SET(?, catID)
            ->where(function ($query) use ($txtSearch) {
                $query->where('news.title', 'LIKE', "%{$txtSearch}%")
                    ->orWhere('news.englishTitle', 'LIKE', "%{$txtSearch}%");
                    // ->orWhere('description', 'LIKE', "%{$txtSearch}%");
            })
            ->groupBy('news.id')
            ->orderBy('news.created_at', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

            Redis::set('news_searchpage_' . $txtSearch . '_' . $page.config('app.webtype'), json_encode($news));  
        }
        // Prepare response data
        $data = [
            'news' => $news
        ];
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function podcast(Request $request)
    {
        $perPage = 6; // Define the number of news items per page
        $pageNumber = $request->input('pageNumber');
        // Generate Redis key for today's podcast data
        $redisKey = 'podcastapp_' .$pageNumber. Carbon::today()->format('Y-m-d') . config('app.webtype');

        // Check if data exists in Redis
        if (Redis::exists($redisKey)) {
            $cachedData = Redis::get($redisKey);
            return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
        }
 
        $authors = DB::table('podcast_author as a')
                ->where('a.status', 'Active')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('podcast as b')
                        ->whereRaw('b.authorid = a.id');
                })
                ->orderBy('a.id', 'DESC')
                ->paginate($perPage, ['*'], 'page', $pageNumber);

         $authors->transform(function ($news) {
            $news->authorimg = config('app.backendnewsurl') . '/public/uploads/podcast/' . $news->authorimg;
            return $news;
        });
        $responseData = ['podcast' => $authors];

        // Cache data for 1 day
        Redis::setex($redisKey, 86400, json_encode($responseData, JSON_UNESCAPED_UNICODE));
        return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function podcastdetail(Request $request)
    {
        $perPage = 10;
        $pageNumber = $request->input('pageNumber');
        $authorid = $request->input('authorid');

        $redisKey = 'podcastdetail_'.$authorid.$pageNumber.Carbon::today()->format('Y-m-d').config('app.webtype');

        if (Redis::exists($redisKey)) {
            $cachedData = Redis::get($redisKey);
            return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Fetch author details once
        $author = DB::table('podcast_author')
            ->where('id', $authorid)
            ->first();

        if (!$author) {
            return response()->json(['error' => 'Author not found'], 404);
        }

        $authorDetails = [
            'authorname' => $author->authorname,
            'authorengname' => $author->authorengname,
            'authorslug' => $author->authorslug,
            'authorimg' => config('app.backendnewsurl') . '/public/uploads/podcast/' . $author->authorimg,
            'authorsubtitle' => $author->authorsubtitle,
            'authordesc' => $author->authordesc,
        ];

        $podcastdata = DB::table('podcast')
            ->select('id', 'authorid', 'podcastTitle', 'podcastDescription', 'audioclip', 'audioclipimg', 'audiocliptime', 'userID', 'viewer', 'viewer_app', 'status', 'created_at', 'updated_at')
            ->where('status', 'Active')
            ->where('authorid', $authorid)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, ['*'], 'page', $pageNumber);

        $podcastdata->getCollection()->transform(function ($podcast) {
            $podcast->audioclipimg = config('app.backendnewsurl') . '/public/uploads/podcast/' . $podcast->audioclipimg;
            $podcast->audioclip = config('app.backendnewsurl') . '/public/uploads/podcast/' . $podcast->audioclip;
            return $podcast;
        });

        $responseData = [
            'author' => $authorDetails,
            'podcast' => $podcastdata
        ];

        Redis::setex($redisKey, 86400, json_encode($responseData, JSON_UNESCAPED_UNICODE));

        return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function pageShow($slug)
    {
        $currentPath = $slug;
        $pagesData = DB::table('pages')->where('slug', $currentPath)->where('status', 'Active')->first();
        $data['pages'] =  $pagesData;
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function videoDetail(Request $request)
    { 
        $slug =  $request->input('slug');
        $subslug = $request->input('subslug');
        $newslug = $subslug === 'undefined' ? $slug : $subslug;
        $catslug = $subslug === 'undefined' ? 'videos' : $slug;
        $videoslug = $subslug === 'undefined' ? 'videos' : 'videos/' . $slug;

        $ipAddress =  $request->input('device_id');
        $user_id = $request->input('user_id');
        $perPage = 10;
        $page = (int) $request->input('pageNumber');
        
        $newcategory = DB::table('categories')
            ->select('id', 'title as catTitle', 'slug as catSlug', 'metatitle', 'metadesc')
            ->where('slug', $catslug)
            ->where('status', 'Active')
            ->first();
       
        // Subqueries
        $likeSubquery = DB::table('videolike')
            ->selectRaw('video_id, 
                SUM(CASE WHEN status = "1" THEN 1 ELSE 0 END) as like_count,
                SUM(CASE WHEN status = "0" THEN 1 ELSE 0 END) as dislike_count')
            ->groupBy('video_id');

        
        $userLikeSubquery = DB::table('videolike')
            ->selectRaw("
                video_id,
                SUM(CASE WHEN status = '1' AND ipAddress = ? THEN 1 ELSE 0 END) as liked_by_user,
                SUM(CASE WHEN status = '0' AND ipAddress = ? THEN 1 ELSE 0 END) as disliked_by_user
            ", [$ipAddress, $ipAddress])
            ->groupBy('video_id');
        
        // Fetch the current video separately and prepend it to the list
        if($page==1) {
            $currentVideo = DB::table('news')
                ->select(
                    'news.slug','news.id','news.title','news.videoURL','news.metatitle','news.metadesc','news.status','news.created_at','news.img_credit_txt',
                    DB::raw('COALESCE(like_data.like_count, 0) as like_count'),
                    DB::raw('COALESCE(user_likes.liked_by_user, 0) as liked_by_user'),
                    DB::raw('COALESCE(user_likes.disliked_by_user, 0) as disliked_by_user'),
                    DB::raw('COALESCE(like_data.dislike_count, 0) as dislike_count')
                )
                ->leftJoinSub($likeSubquery, 'like_data', 'news.id', '=', 'like_data.video_id')
                ->leftJoinSub($userLikeSubquery, 'user_likes', 'news.id', '=', 'user_likes.video_id')
                ->where('news.slug', $newslug)
                ->where('news.status', 'Active')
                ->first();
        }
          
        $videolistkey = $page > 2 ? "videolist_{$catslug}_apipage_{$page}_" . config('app.webtype') : null;

        if ($videolistkey && Redis::exists($videolistkey)) {
            $cached = json_decode(Redis::get($videolistkey));
            $videos = new LengthAwarePaginator(
                collect($cached->data),
                $cached->total,
                $cached->per_page,
                $cached->current_page,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            $videos = DB::table('news')
                ->select(
                    'news.slug','news.id','news.title','news.videoURL','news.metatitle','news.metadesc','news.status','news.created_at','news.img_credit_txt',
                    DB::raw('COALESCE(like_data.like_count, 0) as like_count'),
                    DB::raw('COALESCE(user_likes.liked_by_user, 0) as liked_by_user'),
                    DB::raw('COALESCE(user_likes.disliked_by_user, 0) as disliked_by_user'),
                    DB::raw('COALESCE(like_data.dislike_count, 0) as dislike_count')
                )
                ->leftJoinSub($likeSubquery, 'like_data', 'news.id', '=', 'like_data.video_id')
                ->leftJoinSub($userLikeSubquery, 'user_likes', 'news.id', '=', 'user_likes.video_id')
                ->join('news_category', 'news.id', '=', 'news_category.news_id')
                ->where('news.status', 'Active')
                ->where(function ($query) use ($newcategory) {
                    $query->where('news.is_vertical_video', 9);
                    if (!empty($newcategory->id)) {
                        $query->orWhere('news_category.category_id', $newcategory->id);
                    }
                })
                ->where('news.slug', '!=', $newslug) // exclude current
                ->orderBy('news.created_at', 'DESC')
                ->distinct()
                ->paginate($perPage, ['*'], 'page', $page);

            if ($videolistkey) {
                Redis::set($videolistkey, json_encode([
                    'data' => $videos->items(),
                    'total' => $videos->total(),
                    'per_page' => $videos->perPage(),
                    'current_page' => $videos->currentPage()
                ]));
                Redis::expire($videolistkey, 900); // 15 minutes
            }
        }
        
        // Bookmark setup
        $videos->getCollection()->transform(function ($video) {
            $video->bookmark = 0;
            return $video;
        });
        
        $bookmarkedIds = [];
        if ($user_id) {
            $bookmarkedIds = DB::table('bookmark')
                ->where('user_id', $user_id)
                ->pluck('status', 'news_id') // [news_id => status]
                ->toArray();
        
            $videos->getCollection()->transform(function ($video) use ($bookmarkedIds) {
                $video->bookmark = (isset($bookmarkedIds[$video->id]) && $bookmarkedIds[$video->id] == 1) ? 1 : 0;
                return $video;
            });
        }
        
        // Prepend the current video to page 1 only
        if($page==1) {
            if (!empty($currentVideo)) { 
                $currentVideo->bookmark = 0;
                $bookmarkedIds1 = [];
                if ($user_id) { 
                    $bookmarkedIds1 = DB::table('bookmark')
                        ->where('user_id', $user_id)
                        ->where('news_id', $currentVideo->id)
                        ->first();
                    
                    if ($bookmarkedIds1 && $bookmarkedIds1->status == 1) {
                        $currentVideo->bookmark = 1;
                    }
                }
                $videos->getCollection()->prepend($currentVideo);
            }
        }
        // Plan Check
        $planexist = 0;
        if ($user_id) {
            $plandetals = DB::table('user_plan')
                ->where('user_id', $user_id)
                ->where('plan_status', 'Active')
                ->first();

            if ($plandetals) {
                $planexist = 1;
            }
        }
        
        $data = [
            'slug' => $newslug,
            'catSlug' => $catslug,
            'videoslug' => $videoslug,
            'videos' => $videos
        ];
         return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        // return $request->ajax()
        //     ? view($planexist ? 'videolist' : 'videolistplan', compact('data'))
        //     : view($planexist ? 'video' : 'videoplan', compact('data'));

         
    }
    public function getelectionresults()
    {
        // Fetch election results
        $electionResults = DB::table('electionresults')
            ->select('electionresults.party', 'electionresults.electiontitle', 'electionresults.totalseats', 'electionresults.stateId', 'state.Name as statename')
            ->join('state', 'electionresults.stateId', '=', 'state.id')
            ->where('electionresults.status', '=', 'Active')
            ->orderBy('electionresults.id', 'ASC')
            ->get();
            
        $partyDataArray = [];
        // Initialize the HTML string for the output
        $imageTags = '';

        // CSS Styles for the tabs
        $imageTags .= '
            <style>
                .electionbox {  
                    margin-bottom: 20px;
                    background: #fff;
                    padding: 10px;
                    border-left: 1px solid #dee2e6;
                    border-right: 1px solid #dee2e6;
                    border-bottom: 1px solid #dee2e6;
                }
                .chart-section { float: left; }
                #chartContainer a {color:#000;}
                // #chartContainer .active {
                //     font-weight: 600;
                // }
            </style>
        ';

        if ($electionResults->isNotEmpty()) {
            // Create the tab navigation and content wrapper
            $imageTags .= '
                <div class="" id="chartContainer">
                    <ul class="nav nav-tabs" role="tablist">';

            $partyDataArray = []; // Store each election's party data

            foreach ($electionResults as $index => $datas) {
                $activeClass = ($index === 0) ? 'active' : ''; // Set the first tab as active
                $imageTags .= '
                    <li class="nav-item">
                        <a class="nav-link ' . $activeClass . '" id="tab_' . $index . '" data-toggle="tab" href="#section_' . $index . '" role="tab">' . htmlspecialchars($datas->statename) . '</a>
                    </li>';

                // Decode party data and store in array for use in JavaScript
                $partyData = json_decode($datas->party, true);
                $partyDataArray[] = $partyData;
            }

            $imageTags .= '</ul>'; // Close the tab list

            // Create the tab content container
            $imageTags .= '<div class="tab-content electionbox">';

            foreach ($electionResults as $index => $datas) {
                $electionstarResults = DB::table('electionstar')
                    ->where('status', 'Active')
                    ->where('stateId', $datas->stateId)
                    ->orderBy('id', 'DESC')
                    ->get();
                $activeClass = ($index === 0) ? 'active' : '';

                $imageTags .= '
                    <div class="tab-pane fade show ' . $activeClass . '" id="section_' . $index . '" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6 chart-section text-center" id="chartSection_' . $index . '">
                                <h3 class="election_title">' . htmlspecialchars($datas->electiontitle) . '</h3>
                                <h4 class="election_city">' . htmlspecialchars($datas->statename) . ' (' . htmlspecialchars($datas->totalseats) . ')</h4>
                                <div style="width:300px; margin:0 auto;">
                                    <canvas id="electionChart_' . $index . '" width="100%" height="100"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6 chart-section text-center" style="margin-top:15px;">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Party</th>
                                            <th>Lead</th>
                                            <th>Win</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                
                foreach ($partyDataArray[$index]['partyname'] as $partykey => $partyVal) {
                    $dotcls = match ($partykey) {
                        0 => 'rgb(196, 75, 27)',
                        1 => 'rgba(54, 162, 235, 1)',
                        2 => 'rgba(255, 206, 86, 1)',
                        3 => 'rgba(75, 192, 192, 1)',
                        default => 'rgba(128, 128, 128, 1)',
                    };
                    $partylead = htmlspecialchars($partyDataArray[$index]['partylead'][$partykey] ?? 'N/A');
                    $partyseat = htmlspecialchars($partyDataArray[$index]['partyseat'][$partykey] ?? 'N/A');
                    $partycolor = htmlspecialchars($partyDataArray[$index]['partycolor'][$partykey] ?? '#cfcfcf');

                    $imageTags .= '
                                        <tr style="background-color:' . $partycolor . ';color:#fff;">
                                            <td><span class="dot" style="background-color:' . $dotcls . '"></span> ' . htmlspecialchars($partyVal) . '</td>
                                            <td>' . $partylead . '</td>
                                            <td>' . $partyseat . '</td>
                                        </tr>';
                }

                $imageTags .= '
                                    </tbody>
                                </table>
                            </div>';

                // Star candidates section
                if (!empty($electionstarResults) && count($electionstarResults)>0) {
                    $imageTags .= '
                            <div class="election">
                                <div class="container mt-4">
                                    <h2 class="election_starts">Star Candidates</h2>
                                    <div class="row mt-3">';
                    foreach ($electionstarResults as $stars) {
                        $imageTags .= '
                        <div class="col-md-6">
                            <div class="candidate-card">
                                <img src="' . htmlspecialchars($stars->image) . '" alt="Candidate Image">
                                <div class="candidate-details">
                                    <h4>' . htmlspecialchars($stars->title) . '</h4>
                                    <p>' . htmlspecialchars($stars->city) . '<br>' . htmlspecialchars($stars->partytype) . '</p>
                                    <span class="status" style="background-color:' . ($stars->party_status == "Won" ? 'green' : 'red') . ';">' . htmlspecialchars($stars->party_status) . '</span>
                                </div>
                            </div>
                        </div>';
                    }
                    $imageTags .= '
                                    </div>
                                </div>
                            </div>';
                }

                $imageTags .= '
                        </div>
                    </div>';
            }

            $imageTags .= '</div>'; // End tab-content
        }

        $partyDataJson = json_encode($partyDataArray);

        // Include scripts and the JavaScript to render the charts
        $imageTags .= '
        <link rel="stylesheet" type="text/css" href="' . asset('public/assets/css/styles.css') . '?v=' . time() . '">
        <script src="' . asset('public/assets/js/jquery.min.js') . '"></script>
        <script src="' . asset('public/assets/js/bootstrap.min.js') . '"></script>
        <script src="' . asset('public/assets/js/chart.js') . '"></script>
        <script src="' . asset('public/assets/js/chartjs-plugin-datalabels.js') . '"></script>
        <script>
            $(document).ready(function () {
                var partyDataArray = ' . $partyDataJson . ';

                // Function to render chart for a given index
                function renderChart(index) {
                    var canvasId = "electionChart_" + index;
                    var canvasElement = document.getElementById(canvasId);

                    if (canvasElement) {
                        var ctx = canvasElement.getContext("2d");
                        var partyData = partyDataArray[index];
                        var combinedData = partyData.partyname.map(function(_, i) {
                            var seats = parseInt(partyData.partyseat[i], 10) || 0; // Convert to number, default to 0
                            var leads = parseInt(partyData.partylead[i], 10) || 0; // Convert to number, default to 0
                            return seats + leads; // Sum the two
                        });

                        // Generate chart labels
                        var labels = partyData.partyname.map(function(name, i) {
                            return name + " (Win: " + partyData.partyseat[i] + ", Leads: " + partyData.partylead[i] + ")";
                        });

                        // Debugging: Log combined data
                        console.log("Combined Data:", combinedData);

                        // Create the chart
                        new Chart(ctx, {
                            type: "doughnut",
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: "Total (Win + Leads)",
                                        data: combinedData, // Use the corrected combined data
                                        backgroundColor: partyData.partycolor,
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: "50%",
                                rotation: -90,
                                circumference: 180,
                                plugins: {
                                    legend: { display: false },
                                    datalabels: { display: false }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    }
                }

                // Initialize the first chart
                renderChart(0);

                // Render chart on tab click
               $("a[data-toggle=\"tab\"]").on("shown.bs.tab", function (e) {
                    var targetIndex = $(e.target).attr("id").split("_")[1];
                    
                    // Remove active class from all nav-items and nav-links
                    $(".nav-item").removeClass("active");
                    $(".nav-link").removeClass("active");

                    // Add active class to the clicked nav-item and nav-link
                    $(e.target).closest(".nav-item").addClass("active");
                    $(e.target).addClass("active");

                    renderChart(targetIndex);
                });
            });
        </script>';

        return response($imageTags);
    }
    public function getelectionresultsApp()
    {
        // Fetch election results
        $electionResults = DB::table('electionresults')
            ->select('electionresults.party', 'electionresults.electiontitle', 'electionresults.totalseats', 'electionresults.stateId', 'state.Name as statename')
            ->join('state', 'electionresults.stateId', '=', 'state.id')
            ->where('electionresults.status', '=', 'Active')
            ->orderBy('electionresults.id', 'ASC')
            ->get();

        // Initialize to store all the HTML
        $imageTags = '';

        // Initialize an array to hold party data
        $partyDataArray = [];

        // CSS Styles for the carousel
        $imageTags .= '
            <style>
                .electionbox {  
                    margin-bottom: 20px;
                    padding: 10px;
                    border-left: 1px solid #dee2e6;
                    border-right: 1px solid #dee2e6;
                }
                .chart-section { float: left; }
                #chartContainer a {color:#000;}
                // #chartContainer .active {
                //     font-weight: 600;
                // }
                .election_title,#chartContainer a,.election_city{
                	font-size:40px !important;
                }
                .election_title{margin-top:25px;}
                .election_starts{font-size:35px !important;}
                .candidate-card img {
                    width: 130px !important;
                    height: 130px !important;
                }
                .candidate-details h4 { font-size: 27px !important;}
                .candidate-details p { font-size: 20px !important;}
                .status{font-size: 20px !important;}
            </style>
        ';

        // If there are election results, build the carousel
        if ($electionResults->isNotEmpty()) {
            $imageTags .= '
                <div class="" id="chartContainer">
                    <ul class="nav nav-tabs" role="tablist">';

            $partyDataArray = []; // Store each election's party data

            foreach ($electionResults as $index => $datas) {
                $activeClass = ($index === 0) ? 'active' : ''; // Set the first tab as active
                $imageTags .= '
                    <li class="nav-item">
                        <a class="nav-link ' . $activeClass . '" id="tab_' . $index . '" data-toggle="tab" href="#section_' . $index . '" role="tab">' . htmlspecialchars($datas->statename) . '</a>
                    </li>';

                // Decode party data and store in array for use in JavaScript
                $partyData = json_decode($datas->party, true);
                $partyDataArray[] = $partyData;
            }

            $imageTags .= '</ul>'; // Close the tab list

            // Create the tab content container
            $imageTags .= '<div class="tab-content electionbox">';

            foreach ($electionResults as $index => $datas) {
                $electionstarResults = DB::table('electionstar')
                ->where('status', 'Active')
                ->where('stateId', $datas->stateId)
                ->orderBy('id', 'DESC')
                ->get();
                $activeClass = ($index === 0) ? 'active' : '';

                $imageTags .= '
                    <div class="tab-pane fade show ' . $activeClass . '" id="section_' . $index . '" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6 chart-section text-center" id="chartSection_' . $index . '">
                                <h3 class="election_title">' . htmlspecialchars($datas->electiontitle) . '</h3>
                                <h4 class="election_city">' . htmlspecialchars($datas->statename) . ' (' . htmlspecialchars($datas->totalseats) . ')</h4>
                                <div style="height:300px;">
                                    <canvas id="electionChart_' . $index . '" width="100%" ></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6 chart-section text-center" style="margin-top:50px;">
                                <table class="table table-bordered" style="width:950px;display:inline-table;font-size:40px;">
                                    <thead>
                                        <tr>
                                            <th>Party</th>
                                            <th>Lead</th>
                                            <th>Win</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                foreach ($partyDataArray[$index]['partyname'] as $partykey => $partyVal) {
                    $dotcls = match ($partykey) {
                        0 => 'rgb(196, 75, 27)',
                        1 => 'rgba(54, 162, 235, 1)',
                        2 => 'rgba(255, 206, 86, 1)',
                        3 => 'rgba(75, 192, 192, 1)',
                        default => 'rgba(128, 128, 128, 1)',
                    };
                    $partylead = isset($partyDataArray[$index]['partylead'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partylead'][$partykey]) : 'N/A';
                    $partyseat = isset($partyDataArray[$index]['partyseat'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partyseat'][$partykey]) : 'N/A';
                    $partycolor = isset($partyDataArray[$index]['partycolor'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partycolor'][$partykey]) : '#cfcfcf';
                    $imageTags .= '
                                        <tr style="background-color:' . $partycolor . ';color:#fff;" >
                                            <td><span class="dot" style="background-color:' . $dotcls . '"></span> ' . htmlspecialchars($partyVal) . '</td>
                                            <td ">' . $partylead . '</td>
                                            <td ">' . $partyseat . '</td>
                                        </tr>';
                }

                $imageTags .= '
                                    </tbody>
                                </table>
                            </div>';

                // Star candidates section
                if (!empty($electionstarResults) && count($electionstarResults)>0) {
                    $imageTags .= '
                            <div class="election">
                                <div class="mt-4">
                                    <h2 class="election_starts">Star Candidates</h2>
                                    <div class="row mt-3">';
                    foreach ($electionstarResults as $stars) {
                        $imageTags .= '
                        <div class="col-md-6">
                            <div class="candidate-card">
                                <img src="' . htmlspecialchars($stars->image) . '" alt="Candidate Image">
                                <div class="candidate-details">
                                    <h4>' . htmlspecialchars($stars->title) . '</h4>
                                    <p>' . htmlspecialchars($stars->city) . '<br>' . htmlspecialchars($stars->partytype) . '</p>
                                    <span class="status" style="background-color:' . ($stars->party_status == "Won" ? 'green' : 'red') . ';">' . htmlspecialchars($stars->party_status) . '</span>
                                </div>
                            </div>
                        </div>';
                    }
                    $imageTags .= '
                                    </div>
                                </div>
                            </div>';
                }

                $imageTags .= '
                        </div>
                    </div>';
            }

            $imageTags .= '</div>'; // End carousel-inner
        }

        // Convert partyDataArray to JSON for use in JavaScript
        $partyDataJson = json_encode($partyDataArray);

        // Include scripts at the end
        $imageTags .= '
        <link rel="stylesheet" type="text/css" href="' . asset('public/assets/css/styles.css') . '?v=' . time() . '">
        <script src="' . asset('public/assets/js/jquery.min.js') . '"></script>
        <script src="' . asset('public/assets/js/bootstrap.min.js') . '"></script>
        <link rel="stylesheet" type="text/css" href="' . asset('public/assets/css/bootstrap.min.css') .'">
        <script src="' . asset('public/assets/js/chart.js') . '"></script>
        <script src="' . asset('public/assets/js/chartjs-plugin-datalabels.js') . '"></script>
        <script>
            $(document).ready(function () {
                var partyDataArray = ' . $partyDataJson . ';

                // Function to render chart for a given index
                function renderChart(index) {
                    var canvasId = "electionChart_" + index;
                    var canvasElement = document.getElementById(canvasId);

                    if (canvasElement) {
                        var ctx = canvasElement.getContext("2d");
                        var partyData = partyDataArray[index];
                        var combinedData = partyData.partyname.map(function(_, i) {
                            var seats = parseInt(partyData.partyseat[i], 10) || 0; // Convert to number, default to 0
                            var leads = parseInt(partyData.partylead[i], 10) || 0; // Convert to number, default to 0
                            return seats + leads; // Sum the two
                        });

                        // Generate chart labels
                        var labels = partyData.partyname.map(function(name, i) {
                            return name + " (Win: " + partyData.partyseat[i] + ", Leads: " + partyData.partylead[i] + ")";
                        });

                        // Debugging: Log combined data
                        console.log("Combined Data:", combinedData);

                        // Create the chart
                        new Chart(ctx, {
                            type: "doughnut",
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: "Total (Win + Leads)",
                                        data: combinedData, // Use the corrected combined data
                                        backgroundColor: partyData.partycolor,
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: "50%",
                                rotation: -90,
                                circumference: 180,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Customize tooltip text if needed
                                                return tooltipItem.label + ": " + tooltipItem.raw;
                                            }
                                        },
                                        bodyFont: {
                                            size: 20 // Set the font size for the tooltip text
                                        },
                                        titleFont: {
                                            size: 25 // Set the font size for the tooltip title
                                        }
                                    },
                                    legend: { display: false },
                                    datalabels: { display: false }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    }
                }

                // Initialize the first chart
                renderChart(0);

                // Render chart on tab click
               $("a[data-toggle=\"tab\"]").on("shown.bs.tab", function (e) {
                    var targetIndex = $(e.target).attr("id").split("_")[1];
                    
                    // Remove active class from all nav-items and nav-links
                    $(".nav-item").removeClass("active");
                    $(".nav-link").removeClass("active");

                    // Add active class to the clicked nav-item and nav-link
                    $(e.target).closest(".nav-item").addClass("active");
                    $(e.target).addClass("active");

                    renderChart(targetIndex);
                });
            });
        </script>';


        // Return the HTML as a response
        return response($imageTags);
    }
    public function userviewpoints(Request $request)
    {
       
            $userId = $request->input('user_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // If dates are not provided, default to the last 7 days
            if (!$startDate || !$endDate) {
                $endDate = now()->toDateString();
                $startDate = now()->subDays(6)->toDateString(); // 7 days including today
            }
            // Fetch data for the last 7 days
            $userDurations = DB::table('user_durations')
                ->where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'ASC') // Ensure data is sorted by date
                ->get();

            // Prepare data for the chart
            $chartDataSum = $userDurations->groupBy('date')->map(function ($items) {
                // return $items->sum('total_duration');
                return round($items->sum('total_duration') / 60, 2);
            });
             
            // Calculate total duration in minutes
            $totalMinutes = $chartDataSum->sum();

            // Convert total duration to hours and minutes if needed
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            $totalDurationFormatted = $hours > 0 
            ? sprintf('%d hours %d minutes', $hours, $minutes)
            : sprintf('%d minutes', $minutes);
 
             $data = [
                'chartData' => $chartDataSum,
                'totalDurationFormatted' => $totalDurationFormatted,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
       
    }

function epaperrigthsidebar()
{
    $cacheKey = 'getepaperhomeright2app_' . config('app.webtype');
    $data= array();
    if (Redis::exists($cacheKey)) {
        return response()->json(json_decode(Redis::get($cacheKey)), 200, [], JSON_UNESCAPED_UNICODE);
    } 
         $epaper = DB::table('epaper')
            ->select([
                'epaper.id',
                'epaper.newspaperdate', 
                'epaper.Story_data',
                'epapercategory.slug'
            ])
            ->join('epapercategory', 'epaper.ecatID', '=', 'epapercategory.id')
            ->where('epaper.status', 'Active')
            ->where('epapercategory.cattype', 'Newspaper')
            ->orderBy('epaper.id', 'DESC')
            ->limit(1)
            ->first(); // fetch single record

        if ($epaper) {
            // Decode Story_data JSON
            $storyData = json_decode($epaper->Story_data, true);

            // Get first image if exists
            $firstImage = $storyData[0]['image'] ?? null;

            // Generate URL and HTML
            $newspaperdt = date('d-m-Y',strtotime($epaper->newspaperdate));
            $url = config('app.front_url') . '/epaper/' . $epaper->slug . '/' . $newspaperdt;



            if ($firstImage) {
              //  $epaper->html = '<div class="epaperrigthside"><a href="' . $url . '"><img src="' . $firstImage . '" alt="Epaper Image"/></a></div>';
                
                $data = [
                    'first_image' => $firstImage,
                    'url' => $url,
                ];
            }
        }
        
       Redis::setex($cacheKey, 1800, json_encode($data, JSON_UNESCAPED_UNICODE));    
    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    
}
function getMagazineCategories()
{
    $cacheKey = 'magazine_categoriesapp_' . config('app.webtype');

    if (Redis::exists($cacheKey)) {
        return response()->json(json_decode(Redis::get($cacheKey)), 200, [], JSON_UNESCAPED_UNICODE);
    } 
    
    $data =  DB::table('epapercategory')
        ->select('title', 'cattype', 'slug', 'icon')
        ->where('status', 'Active')
        ->where('cattype', 'Magazine')
        ->orderBy('catOrder', 'ASC')
        ->get()
        ->map(function ($item) {
            return (object) $item; // Ensure the result is stdClass (like DB::get)
        });
    Redis::setex($cacheKey, 1800, json_encode($data, JSON_UNESCAPED_UNICODE));    
    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
}
public function getElectionResultsApi()
{
    $electionResults = DB::table('electionresults')
        ->select('electionresults.party', 'electionresults.electiontitle', 'electionresults.totalseats', 'electionresults.stateId', 'state.Name as statename')
        ->join('state', 'electionresults.stateId', '=', 'state.id')
        ->where('electionresults.status', '=', 'Active')
        ->orderBy('electionresults.id', 'ASC')
        ->get();

    $responseData = [];

    foreach ($electionResults as $data) {
        $partyData = json_decode($data->party, true);

        $electionstarResults = DB::table('electionstar')
            ->where('status', 'Active')
            ->where('stateId', $data->stateId)
            ->orderBy('id', 'DESC')
            ->get();

        $responseData[] = [
            'electiontitle' => $data->electiontitle,
            'statename' => $data->statename,
            'totalseats' => $data->totalseats,
            'partydata' => $partyData,
            'starcandidates' => $electionstarResults,
        ];
    }

    return response()->json([
        'status' => true,
        'data' => $responseData
    ]);
}
public function createSubscription(Request $request)
{
    $userId = $request->input('user_id');
    $plan = $request->input('plan_id');
    $plandtl = DB::table('plan')
                ->where('plan_subscription_id', $plan)
                ->first();
    $razorpayPlanId = $plandtl->plan_subscription_id;
    // map to Razorpay plan IDs (use your own plan IDs)
    // $razorpayPlanId = $request->plan === 'monthly'
    //     ? 'plan_RUUMLjYnKKUuKR'
    //     : 'plan_RUUMyuCKfRyfDt';

    try {
        // Create subscription on Razorpay
        $payload = [
            'plan_id' => $razorpayPlanId,
            'customer_notify' => 1, // send email/SMS
            // total_count optional, 0 = infinite (or set e.g. 12)
            'total_count' => 12,
        ];
        $api = new Api('rzp_test_RGayUf4jrYOXOT', 'wlQKkIhATwdrOmf7ModT2jn9');
        $subscription = $api->subscription->create($payload);

        // Save to DB in created state
        $sub = UserSubscription::create([
            'user_id' => $userId,
            'plan' => $request->plan,
            'razorpay_plan_id' => $razorpayPlanId,
            'razorpay_subscription_id' => $subscription->id,
            'amount' => $request->plan_amount,
            'status' => $subscription->status ?? 'created',
            'raw' => json_encode($subscription),
        ]);

        $data = array(
            'success' => true,
            'subscription_id' => $subscription->id,
            'razorpay_key' => env('RAZORPAY_KEY'),
            'subscription' => $subscription);
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        Log::error('Create subscription failed: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to create subscription', 'error' => $e->getMessage()], 500);
    }
}
public function webhook(Request $request)
{
    $payload = $request->getContent();
    $signature = $request->header('X-Razorpay-Signature');
    $webhookSecret = 'gstvwebhook@345'; // or env('RAZORPAY_WEBHOOK_SECRET');
     Log::info('🔔 Razorpay Webhook Received', [
            'signature' => $signature,
            'payload' => $payload
        ]);
    try {
        // Verify webhook signature
        $this->api->utility->verifyWebhookSignature($payload, $signature, $webhookSecret);

        $event = json_decode($payload, true);
        $eventType = $event['event'] ?? null;
        $payloadObj = $event['payload'] ?? [];
         Log::info('✅ Webhook Verified', ['event_type' => $eventType]);
        // handle events
        switch ($eventType) {
            case 'subscription.activated':
            case 'subscription.charged':
            case 'subscription.charged.success':
            case 'subscription.charged.failed':
            case 'subscription.cancelled':
            case 'payment.captured':
            case 'payment.authorized':
                Log::info("📦 Handling event type: {$eventType}");
                $this->handleRazorpayEvent($eventType, $payloadObj);
                break;

            default:
                Log::info('⚠️ Unhandled Razorpay event type: ' . $eventType);
        }

        return response()->json(['status' => 'ok']);
    } catch (Exception $e) {
        Log::error('❌ Webhook verification failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'invalid signature'], 400);
    }
}

protected function handleRazorpayEvent($eventType, $payload) 
{
    try {
         Log::info('🔔 Razorpay Webhook Triggered', [
                'event_type' => $eventType,
                'payload_keys' => array_keys($payload ?? []),
                'raw_payload' => json_encode($payload)
            ]);
        // 1️⃣ Subscription-related event
        if (isset($payload['subscription']['entity'])) {
            $sub = $payload['subscription']['entity'];
            $subId = $sub['id'] ?? null;
            $status = $sub['status'] ?? null;
            $payment = $payload['payment']['entity'] ?? [];
            Log::info('📬 Subscription event detected', [
                    'subscription_id' => $subId,
                    'status' => $status,
                    'payment_keys' => array_keys($payment ?? []),
                    'notes' => $payment['notes'] ?? null,
                ]);
            if ($subId) {
                $record = UserSubscription::where('razorpay_subscription_id', $subId)->first();

                if ($record) {
                    // ✅ Update subscription status/timestamps
                    $record->status = $status;
                    if (isset($sub['current_start'])) {
                        $record->activated_at = Carbon::createFromTimestamp($sub['current_start']);
                    }
                    if (isset($sub['current_end'])) {
                        $record->expires_at = Carbon::createFromTimestamp($sub['current_end']);
                    }
                    $record->raw = json_encode($payload);
                    Log::info('✅ Subscription record updated', [
                            'user_id' => $record->user_id ?? null,
                            'subscription_id' => $subId,
                            'status' => $record->status,
                        ]);
                    // 2️⃣ Detect plan type and amount
                    $amountPaise = $payment['amount'] ?? 0;
                    $amountInr = $amountPaise / 100;

                    // Priority: use notes.plan_type if available, else detect by amount
                    $planType = $payment['notes']['plan_type'] ?? (
                        $amountInr == 270.00 ? 'yearly' :
                        ($amountInr == 27.00 ? 'monthly' : 'monthly')
                    );

                    $record->plan = $planType;
                    $record->razorpay_payment_id = $payment['id'];
                    $record->amount = $amountInr;
                    $record->save();
                    Log::info('💰 Plan type detected', [
                            'amount_inr' => $amountInr,
                            'plan_type' => $planType,
                        ]);

                    // 3️⃣ Determine plan start & expiry dates
                    $activePlan = DB::table('user_plan')
                        ->where('user_id', $record->user_id)
                        ->where('plan_status', 'Active')
                        ->orderBy('id', 'DESC')
                        ->first();

                    $newPlanStartDate = $activePlan
                        ? (Carbon::parse($activePlan->plan_expire)->isFuture()
                            ? Carbon::parse($activePlan->plan_expire)
                            : now())
                        : now();

                    $newPlanExpiry = $planType === 'monthly'
                        ? $newPlanStartDate->copy()->addMonth()
                        : $newPlanStartDate->copy()->addYear();

                    $successEvents = [
                        'payment.captured',
                        'subscription.charged',
                        'subscription.charged.success',
                        'subscription.activated'
                    ];
                    if (in_array($eventType, $successEvents)) {
                        $existingPlan = \App\Models\Userplan::where('plan_payment_id', $payment['id'])->first();

                        if (!$existingPlan) {
                            $userPlan = \App\Models\Userplan::create([
                                'user_id'         => $record->user_id,
                                'plan'            => $planType,
                                'plan_amount'     => $amountInr,
                                'plan_expire'     => $newPlanExpiry->format('Y-m-d'),
                                'plan_order_id'   => $payment['order_id'] ?? null,
                                'plan_payment_id' => $payment['id'] ?? null,
                                'plan_status'     => 'Active',
                            ]);

                            Log::info('💾 User plan created successfully after verified webhook', [
                                'user_id'   => $record->user_id,
                                'plan_type' => $planType,
                                'amount'    => $amountInr,
                                'expires_on'=> $newPlanExpiry->format('Y-m-d'),
                                'event_type'=> $eventType
                            ]);
                        } 
                        else {
                             DB::table('user_plan')
                                ->where('plan_payment_id', $payment['id'])
                                ->update([
                                    'user_id'         => $record->user_id,
                                    'plan'            => $planType,
                                    'plan_amount'     => $amountInr,
                                    'plan_expire'     => $newPlanExpiry->format('Y-m-d'),
                                    'plan_order_id'   => $payment['order_id'] ?? null,
                                    'plan_payment_id' => $payment['id'] ?? null,
                                    'plan_status'     => 'Active',
                                    'updated_at'       => now(),
                                ]);
                            Log::info('⚠️ User plan already exists for this payment', [
                                'payment_id' => $payment['id']
                            ]);
                        }
                    } else {
                        Log::info('⏳ Skipped Userplan creation — not a success event', [
                            'event_type' => $eventType
                        ]);
                    }
                    if ($eventType == 'subscription.charged') {
                        // 5️⃣ Send confirmation email
                        $userdetails = DB::table('userregister')
                            ->select('firstname', 'email', 'mobile')
                            ->where('id', $record->user_id)
                            ->first();

                        if ($userdetails) {
                            Mail::to($userdetails->email)->send(
                                new PaymentSuccessMail(
                                    $userdetails->firstname,
                                    ucfirst($planType),
                                    $amountInr,
                                    $payment['order_id'],
                                    $payment['id'],
                                    $newPlanExpiry->format('d-m-Y')
                                )
                            );
                            Log::info('📧 Payment success email sent', ['email' => $userdetails->email]);
                        }
                    }
                } else {
                    Log::warning('⚠️ Subscription not found in DB', ['subscription_id' => $subId]);
                }
            }
        }

        // 6️⃣ Payment-only event (no subscription)
        if (isset($payload['payment']['entity'])) {
            $payment = $payload['payment']['entity'];
            Log::info('💰 Payment entity detected', ['payment_id' => $payment['id'] ?? null]);
            if ($eventType === 'payment.authorized' && !empty($payment['id'])) {
            try {
                Log::info('⚡ Attempting to manually capture payment', ['payment_id' => $payment['id']]);

                // Fetch payment details from Razorpay
                $razorpayPayment = $this->api->payment->fetch($payment['id']);

                // Capture full authorized amount
                $razorpayPayment->capture(['amount' => $razorpayPayment['amount']]);

                Log::info('✅ Payment manually captured successfully', [
                    'payment_id' => $payment['id'],
                    'amount' => $razorpayPayment['amount']
                ]);

            } catch (\Exception $e) {
                Log::error('❌ Manual capture failed', [
                    'payment_id' => $payment['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
            if (!empty($payment['subscription_id'])) {
                $record = UserSubscription::where('razorpay_subscription_id', $payment['subscription_id'])->first();

                if ($record) {
                    $record->razorpay_payment_id = $payment['id'];
                    $record->status = 'completed';
                    $record->raw = json_encode($payload);
                    $record->save();

                    Log::info('✅ Payment captured, subscription marked completed', [
                        'subscription_id' => $payment['subscription_id'],
                        'payment_id' => $payment['id'],
                    ]);
                } else {
                    Log::warning('⚠️ No subscription found for payment', [
                        'subscription_id' => $payment['subscription_id'],
                    ]);
                }
            }
        }
    } catch (Exception $e) {
        Log::error('❌ Error in handleRazorpayEvent', [
            'event_type' => $eventType,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'payload' => $payload ?? [],
        ]);
    }
}
public function adduserplansubscribe(Request $request)
{
    try {
        $existing = DB::table('user_plan')
            ->where('plan_payment_id', $request->plan_payment_id)
            ->first();
        if ($existing) {
            // Update
            DB::table('user_plan')
                ->where('plan_payment_id', $request->plan_payment_id)
                ->update([
                    'plan_order_id'    => $request->plan_order_id,
                    'plan_payment_id'  => $request->plan_payment_id,
                    'plan_status'      => 'Active',
                    'updated_at'       => now(),
                ]);

         } else {

                // Insert new record
                $insertId = DB::table('user_plan')->insertGetId([
                    'user_id'         => $request->user_id,
                    'plan_order_id'   => $request->plan_order_id,
                    'plan_payment_id' => $request->plan_payment_id,
                    'plan_status'     => 'Active',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]); 
            }

        return response()->json([
            'status'  => true,
            'message' => 'Plan subscribed successfully',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
    public function careersubmit(Request $request){
        
        // Save CV file
        $filename = null;
        if ($request->hasFile('cv')) {
            $cv = $request->file('cv');
            $filename = time() . '_' . $cv->getClientOriginalName();
            // $cvPath = public_path('uploads/career');
            $mediaPath =   dirname( dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/backend/public/uploads/career';
            $cv->move($mediaPath, $filename);
        }

        // Save to DB (optional)
        DB::table('career')->insert([
            'fullname'   => $request->fullname,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'post'       => $request->post,
            'experience' => $request->experience,
            'message'    => $request->message,
            'cv'         => $filename,
            'created_at' => now(),
        ]);
        $setting = Setting::first();
        Mail::to($setting->toemail)->cc([$setting->ccemail])->send(new CareerEmail($request->all(), $filename));
        return response()->json(['success' => true, 'message' => 'Application submitted successfully!']);
    }
}
