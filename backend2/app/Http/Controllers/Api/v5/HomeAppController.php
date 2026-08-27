<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
use App\Models\Epapercategory;
use App\Models\Epaper;
use App\Models\Gift;
use App\Models\Usernews;
use App\Models\Giftredeemcoins;
use App\Models\Sidemenu;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HomeAppController extends Controller
{
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
          ->select('n.*')
          ->orderByDesc('n.created_at')
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

    public function electionresults()
    {
      $data =  array('election_status'=>0, 'height'=>400, 'election_url'=>'https://staging.gstv.in/electionresults');
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
            ->orderBy('news.created_at', 'DESC')
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
   
    
    public function CategorySetting()
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
                'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0, 'status' => '',
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
                    'news.*',
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
                ->select('id', 'slug', 'title', 'featureImage', 'videoURL', 'description', 'created_at')
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
    
        // Check if the response is cached
        if (Redis::exists($cacheKey)) {
            return response()->json(json_decode(Redis::get($cacheKey), true));
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
                        DB::raw("GROUP_CONCAT(DISTINCT nc.category_id ORDER BY nc.category_id) AS catID")
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
            $relatednews = DB::table('news')
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
                            ->get();
        }
    
        $setting = DB::table('settings')->first();
    
        if (!empty($newsDetail)) {
            $newsDetail->bookmark = 0;
            if (!empty($user_id)) {
                $bookmark = DB::table('bookmark')
                    ->where('user_id', $user_id)
                    ->where('news_id', $newsDetail->id)
                    ->first();
                $newsDetail->bookmark = $bookmark ? (int)$bookmark->status : 0;
            }
        }
    
        $response = [
            'newsDetail' => $newsDetail,
            'newsdescription' => $modifiedHtml,
            'relatednews' => $relatednews,
            'newsdefaulttxt' => $setting->newsdefaultapptxt ?? '',
        ];
    
        // Cache the response for 10 minutes
        Redis::setex($cacheKey, 600, json_encode($response));
    
        return response()->json($response, $newsDetail ? 200 : 404, [], JSON_UNESCAPED_UNICODE);
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
                'bookmark_type' => $bookmark_type
            ];
            DB::table('bookmark')->insert($dataNews);
        } else {
            DB::table('bookmark')->where('news_id', $news_id)->where('user_id', $user_id)->update(['status' => $status]);
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
        if (Redis::exists($likeKey)) {
            $likeCount = Redis::get($likeKey);
        } else {
            $likeCount = DB::table('videolike')->where('video_id', $video_id)->where('status', '1')->count();
            Redis::setex($likeKey, 600, $likeCount); // Cache for 10 minutes
        }

        if (Redis::exists($dislikeKey)) {
            $dislikeCount = Redis::get($dislikeKey);
        } else {
            $dislikeCount = DB::table('videolike')->where('video_id', $video_id)->where('status', '0')->count();
            Redis::setex($dislikeKey, 600, $dislikeCount); // Cache for 10 minutes
        }

        // Format like and dislike count
        $data['videolike'] = $this->formatCount($likeCount);
        $data['videodislike'] = $this->formatCount($dislikeCount);

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
        public function breakingnews11(Request $request)
        {
            $redisKey = "breaking_news".config('app.webtype');

            // Check if breaking news data exists in Redis
            if (Redis::exists($redisKey)) {
                $breakingNews = json_decode(Redis::get($redisKey), true);
            } else {
                // Fetch from DB if not in Redis
                $breakingNews = DB::table('news')
                    ->select(
                        'news.*',
                        'u1.name as authorName',
                        DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id SEPARATOR ", ") as categoryNames'),
                        DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR "/") as categorySlugs'),
                        DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID')
                    )
                    ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
                    ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
                    ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
                    ->where('news.status', 'Active')
                    ->where('news.is_breaking', 1)
                    ->whereRaw('DATE(news.created_at) = CURDATE()')
                    ->groupBy('news.id', 'u1.name') // group by non-aggregated fields
                    ->orderBy('news.id', 'DESC')
                    ->limit(5)
                    ->get();


                if ($breakingNews->isEmpty()) {
                    $breakingNews = DB::table('news')
                        ->select(
                            'news.*',
                            'u1.name as authorName',
                            DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id SEPARATOR ", ") as categoryNames'),
                            DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR "/") as categorySlugs'),
                            DB::raw('GROUP_CONCAT(DISTINCT categories.id ORDER BY categories.id) as catID') // Alias for catID
                        )
                        ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
                        ->leftJoin('categories', 'categories.id', '=', 'nc.category_id')
                        ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
                        ->where('news.status', 'Active')
                        ->where('news.catOrder', 0)
                        ->where('news.is_home', 0)
                        ->where('nc.category_id', 1) // This replaces: FIND_IN_SET(?, news.catID)
                        ->groupBy('news.id', 'u1.name')
                        ->orderByDesc('news.created_at')
                        ->limit(5)
                        ->get();
                }

                // Store in Redis for 5 minutes (300 seconds)
                Redis::setex($redisKey, 300, json_encode($breakingNews));
            }
          
          	// Newsflash

            $newsflashkey = 'newsflashs_' . date('Y-m-d').config('app.webtype'); // Unique key for daily news

            if (Redis::exists($newsflashkey)) {
                $newsflash = json_decode(Redis::get($newsflashkey));
            } else {
               $newsflash = DB::table('newsflash')
                    ->where('status', '1')
                    ->whereRaw('DATE(created_at) = CURDATE()')
                    ->orderBy('id', 'DESC')
                    ->limit(10)
                    ->pluck('title')
                    ->flatMap(fn($title) => explode('•••', $title))
                    ->map(fn($t) => trim($t))
                    ->implode(' ••• ');
                Redis::set($newsflashkey, json_encode($newsflash));
            }

            return response()->json(['breakingnews' => $breakingNews, 'newsflash' => $newsflash], 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function poll(Request $request)
        {
            $ipAddress = $request->input('device_id');
            
            // Define Redis key
        /* $redisKey = "polls_active_{$ipAddress}".config('app.webtype');

            // Check if the poll data is cached in Redis
            if (Redis::exists($redisKey)) {
                $polls = json_decode(Redis::get($redisKey));
            } else {*/
                // Fetch poll data from DB
                $polls = DB::table('polls')
                    ->leftJoin('votes', function ($join) use ($ipAddress) {
                        $join->on('polls.id', '=', 'votes.pollID')
                            ->where('votes.IPaddress', '=', $ipAddress);
                    })
                    ->where('polls.status', 'Active')
                    ->select('polls.*', 'votes.selectedAnswer')
                    ->orderByDesc('id')
                    ->get();

                // Store poll data in Redis for 10 minutes (600 seconds)
            /* Redis::setex($redisKey, 600, json_encode($polls));
            }*/

            if (empty($polls)) {
                return response()->json(['error' => 'Poll not found'], 404, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json(['poll' => $polls], 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function submitpoll(Request $request)
        {
            $ipAddress = $request->input('device_id');
            $pollID = $request->input('pollID');
            $selectedAnswer = $request->input('selectedAnswer');
            $existingVote = Vote::where('IPaddress', $ipAddress)
                                ->where('pollID',  $pollID)
                                ->first();

            if ($existingVote) {
                $existingVote->selectedAnswer = $selectedAnswer;
                $existingVote->save();
            }  else {
                $vote = new Vote([
                    'pollID' =>  $request->input('pollID'),
                    'selectedAnswer'=>  $selectedAnswer,
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
                            ->get();
        
                        foreach ($results as $result) {
                            $result->percentage = $totalVotes > 0 ? round(($result->answerCount / $totalVotes) * 100, 2) : 0;
                        }
        
                        $poll->selectedresult = $results; // Merge results into poll data
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
        public function magazine(Request $request) {
            $data = array(); 
            $epapermagazine = DB::table('epapercategory')
            ->leftJoinSub(
                DB::table('epaper')
                    ->select('epaper.*')
                    ->where('epaper.status', 'Active')
                    ->whereRaw('epaper.id IN (SELECT MAX(id) FROM epaper GROUP BY ecatID)')
                    ->orderBy('created_at', 'DESC'),
                'epaper',
                'epapercategory.id',
                '=',
                'epaper.ecatID'
            )
            ->where('epapercategory.status', 'Active')
            ->where('epapercategory.cattype', 'magazine')
            ->select('epapercategory.title', 'epapercategory.id', 'epapercategory.slug as ecatslug', 'epaper.*')
            ->get();

            // Filter out entries where ecatID is blank or NULL
            $epapermagazine = $epapermagazine->filter(function($item) {
                    return !empty($item->ecatID); // Ensure ecatID is not blank
            });
            $epapermagazine = $epapermagazine->map(function($item) {
                // Check if Story_data exists and is a valid JSON string
                if (!empty($item->Story_data)) {
                    // Decode the Story_data JSON string
                    $storyData = json_decode($item->Story_data, true);
        
                    // Remove empty strings from Story_data array and extract only image URLs
                    $storyData = array_filter($storyData, function($value) {
                        return !empty($value['image']); // Filter out empty 'image' fields
                    });
        
                    // Re-index the array after filtering, and only return image URLs
                    $item->Story_data = array_values(array_map(function($value) {
                        return $value['image']; // Only include the image URL
                    }, $storyData));
                }
        
                // Add Story_jsondata as a string without empty image entries
                // $item->Story_jsondata = json_encode($item->Story_data);
                return $item;
            });
            
            
            $data['epapermagazine'] =  $epapermagazine->values();
            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function epaper($newspaperDate = null)
        {   
            if (!empty($newspaperDate)) {
                $newspaperDate = date('Y-m-d', strtotime($newspaperDate));
            }
            $redisKey = "epaper_{$newspaperDate}".config('app.webtype');
            $epapercity = Redis::get($redisKey);

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
                    ->where('epapercategory.status', 'Active');
                   // ->where('epapercategory.cattype', 'Newspaper');

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
                    ->limit(8)
                    ->get();

                // Filter out empty categories
                $epapercity = $epapercity->filter(function ($item) {
                    return !empty($item->ecatID);
                })->values();

                // Process Story Data
                $epapercity = $epapercity->map(function($item) {
                    if (!empty($item->Story_data)) {
                        $storyData = json_decode($item->Story_data, true);
            
                        $storyData = array_filter($storyData, function($value) {
                            return !empty($value['image']);
                        });

                        $item->Story_data = array_values(array_map(function($value) {
                            return $value['image'];
                        }, $storyData));
                    }
                    return $item;
                });

                // Group by category type
                $data = $epapercity->groupBy('cattype');
                $data['date'] = $newspaperDate;

                // Store data in Redis for 15 minutes (900 seconds)
                Redis::setex($redisKey, 900, json_encode($data));
            } else {
                // Decode JSON from Redis
                $data = json_decode($epapercity, true);
            }

            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        }
        public function epaperdetail(Request $request)
        {
            $slug = $request->input('slug');
            $date = $request->input('date');

            try {
                $formattedDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format'], 400);
            }
            $redisKey = "epaper_detail_{$slug}_{$formattedDate}".config('app.webtype');
            $cachedData = Redis::get($redisKey);

            if ($cachedData) {
                return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
            }

            $epaper = DB::table('epaper')
                ->select('epaper.*', 'epapercategory.slug as ecatslug')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM epapercategory WHERE FIND_IN_SET(epapercategory.id, epaper.ecatID)) as title')
                ->join('epapercategory', DB::raw('FIND_IN_SET(epapercategory.id, epaper.ecatID)'), '>', DB::raw('0'))
                ->where('epapercategory.slug', $slug)
                ->whereDate('epaper.newspaperdate', $formattedDate)
                ->orderBy('epaper.created_at', 'DESC')
                ->first();

            if ($epaper && !empty($epaper->Story_data)) {
                $storyData = json_decode($epaper->Story_data, true);
                $storyData = array_filter($storyData, fn($value) => !empty($value['image']));
                $epaper->Story_data = array_values(array_map(fn($value) => $value['image'], $storyData));
            }

            $data = ['epaper' => $epaper, 'date' => $formattedDate];

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
    
}
