<?php

namespace App\Http\Controllers\Api\v4;

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

class HomeAppController extends Controller
{
    public function tophomecategory(Request $request)
    {
        $data = array(); 
        $pageNumber = $request->input('pageNumber', 1); // Default to 1 if not provided
        $itemsPerPage = 1;
        $cacheKey = "tophomecategory_page_{$pageNumber}".config('app.webtype');

        // Check Redis cache
        if (Redis::exists($cacheKey)) {
            return response()->json(json_decode(Redis::get($cacheKey)), 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Fetch category with pagination
        $category = DB::table('categories as c1')
            ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
            ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
            ->where('c1.status', 'Active')
            ->where('c1.setHome', 1)
            ->where('c1.id', '!=', 9)
            ->orderBy('c1.catOrder', 'ASC')
            ->paginate($itemsPerPage, ['*'], 'page', $pageNumber);
        
        if ($category->isEmpty()) {
            return response()->json(['message' => 'No categories found'], 404);
        }

        $selectedCategory = $category->first();
        $categoryId = $selectedCategory->id;
        $id = $category[0]->id;
        // Fetch news related to the category
        $newsData = DB::table('news')
            ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
            ->join('categories', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
            })
            ->whereRaw('FIND_IN_SET(?, news.catID) > 0', [$id])
            ->where('news.status', 'Active')
            ->where('news.is_home', '!=', 1)
            ->groupBy('news.id')
            ->orderBy('news.created_at', 'DESC')
            ->limit(4)
            ->get();

        // Prepare response data
       // $data['homecategory'][$category[0]->title.'|'.$category[0]->category_name_guj] =  $newsData;
       $data['homecategory'][$category[0]->title] =  $newsData;
        $currentPage = $category->currentPage();
        $nextPage = $category->currentPage() < $category->lastPage() ? $category->currentPage() + 1 : null; 
      $totalPages = $category->lastPage();
        $data['currentPage'] = $currentPage;
        $data['nextPage'] = $nextPage;
        $data['totalPages'] = $totalPages;

        // Store in Redis cache for 10 minutes
        Redis::setex($cacheKey, 600, json_encode($data));

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
                ->leftjoin('news', 'usernews.id', '=', 'news.usernews_userID') // Join the news table based on usernews_userID
                ->select('usernews.id','usernews.title','usernews.description','usernews.status','usernews.imgtype','usernews.created_at','usernews.featureImage as usersimg', 'news.featureImage', 'news.slug','news.videoURL', 'news.viewer', 'news.catID')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categoryNames')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')
                ->where('usernews.userID', $userId)
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
            ->where('usernews.id', $newsId)
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
    public function CategoryNews_old(Request $request)
    {
        $value = $request->query('page');
        $slug = $request->input('slug');
        $device_id = $request->input('device_id');
        $subslug = $request->input('subslug');
        $pageNumber = $request->input('pageNumber');
        $user_id = $request->input('user_id');
        
        $perPage = $slug === 'videos' ? $request->input('per_page', 4) : $request->input('per_page', 4); // Default to 10 items per page
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
                ->where(function ($query) use ($newcategory) {
                    if ($newcategory->id == 9) {
                        $query->orWhere('is_vertical_video', 9);
                    }
                    $query->orWhereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id]);
                })
                ->where('status', 'Active');
                $newsQuery = clone $news;
                $newsQuery->where('catOrder', '!=', 0)->orderBy('catOrder', 'ASC');
                $results = $newsQuery->get();

                if ($results->isEmpty()) {
                    $news->where('catOrder', 0)
                        ->orderBy('id', 'DESC');
                } else {
                    $news = $newsQuery;
                }
                //}
                $news = $news->paginate($perPage, ['*'], 'page', $page);
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
                ->where(function ($query) use ($newcategory) {
                    if ($newcategory->id == 9) {
                        $query->orWhere('is_vertical_video', 9);
                    }
                    $query->orWhereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id]);
                })
                ->where('status', 'Active')
                ->select('news.*', 'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user');
 
            $newsQuery = clone $news;
            $newsQuery->where('catOrder', '!=', 0)->orderBy('catOrder', 'ASC');
            $results = $newsQuery->get();

            if ($results->isEmpty()) {
                $news->where('catOrder', 0)
                    ->orderBy('id', 'DESC');
            } else {
                $news = $newsQuery;
            }
			
            // **Apply Pagination After Ordering**
            $news = $news->paginate($perPage, ['*'], 'page', $page);

        }
    
        $news->getCollection()->transform(function ($item) use ($user_id, $device_id) {
            $item->description = Str::limit($item->description, 120);
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
  public function CategoryNews(Request $request)
    {
        $value = $request->query('page');
        $slug = $request->input('slug');
        $device_id = $request->input('device_id');
        $subslug = $request->input('subslug');
        $pageNumber = $request->input('pageNumber');
        $user_id = $request->input('user_id');
        
        $perPage = $slug === 'videos' ? $request->input('per_page', 4) : $request->input('per_page', 4); // Default to 10 items per page
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
                ->where(function ($query) use ($newcategory) {
                    if ($newcategory->id == 9) {
                        $query->orWhere('is_vertical_video', 9);
                    }
                    $query->orWhereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id]);
                })
                ->where('status', 'Active');
                $news->orderBy('created_at', 'DESC');
                
                $news = $news->paginate($perPage, ['*'], 'page', $page);
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
                ->where(function ($query) use ($newcategory) {
                    if ($newcategory->id == 9) {
                        $query->orWhere('is_vertical_video', 9);
                    }
                    $query->orWhereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id]);
                })
                ->where('status', 'Active')
                ->select('news.*', 'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user');
                $news->orderBy('created_at', 'DESC');
                $news = $news->paginate($perPage, ['*'], 'page', $page);

        }
    
        $news->getCollection()->transform(function ($item) use ($user_id, $device_id) {
            $item->description = Str::limit($item->description, 120);
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
     public function CategoryNews33(Request $request)
{
    $value = $request->query('page');
    $slug = $request->input('slug');
    $device_id = $request->input('device_id');
    $subslug = $request->input('subslug');
    $pageNumber = $request->input('pageNumber');
    $user_id = $request->input('user_id');
    
    $perPage = $request->input('per_page', 6); // Default items per page
    $page = $request->input('page', $pageNumber); // Default to first page

    // Get category details
    $newcategory = DB::table('categories')
        ->select('id', 'title as catTitle', 'slug as catSlug', 'metatitle', 'metadesc')
        ->where('slug', $subslug === 'undefined' ? $slug : $subslug)
        ->where('status', 'Active')
        ->first();

    if (!$newcategory) {
        return response()->json(['error' => 'Category not found'], 404, JSON_UNESCAPED_UNICODE);
    }

    $categoryId = $newcategory->id;

    // Generate Redis cache key
    $redisKey = "category_news:{$categoryId}:page_{$page}:device_{$device_id}".config('app.webtype');

    // Check if data exists in Redis
    $cachedNews = Redis::get($redisKey);

    if ($cachedNews) {
        return response()->json(json_decode($cachedNews, true), 200);
    }

    // Fetch news data from database
    if (empty($device_id)) {
        $news = DB::table('news')
            ->where(function ($query) use ($newcategory) {
                if ($newcategory->id == 9) {
                    $query->orWhere('is_vertical_video', 9);
                }
                $query->orWhereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id]);
            })
            ->where('status', 'Active')
            ->orderBy('created_at', 'DESC')
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
            ->where(function ($query) use ($newcategory) {
                if ($newcategory->id == 9) {
                    $query->orWhere('is_vertical_video', 9);
                }
                $query->orWhereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id]);
            })
            ->where('status', 'Active')
            ->select('news.*', 'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user')
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    // Modify news collection
    $news->getCollection()->transform(function ($item) use ($user_id) {
        $item->description = Str::limit($item->description, 120);
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

    // Prepare response data
    $data = [
        'news' => $news,
        'catTitle' => $newcategory->catTitle,
        'catSlug' => $newcategory->catSlug,
        'catMetatitle' => $newcategory->metatitle,
        'catMetadesc' => $newcategory->metadesc
    ];

    // Store the response in Redis (set expiry time: 10 minutes)
    Redis::setex($redisKey, 600, json_encode($data));

    return response()->json($data, 200);
}
    public function CategorySetting()
{
    $backendNewsUrl = config('app.backendnewsurl');
    $iconBaseUrl = $backendNewsUrl . '/public/uploads/category/icon/';
    $logoBaseUrl = $backendNewsUrl . '/public/uploads/logo/';
    $sidemenuUrl = $backendNewsUrl . '/public/uploads/sidemenu/';

    // Fetch category data from Redis or set it if not available
    $category = Redis::get('categories'.config('app.webtype'));
    if (!$category) {
        $category = DB::table('categories as c1')
            ->select(
                'c1.id', 'c1.userID', 'c1.parentID', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug',
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
        
        Redis::set('categories'.config('app.webtype'), json_encode($category)); // Store in Redis
    } else {
        $category = json_decode($category, true);
    }

    // Fetch settings data from Redis or set it if not available
    $setting = Redis::get('settings'.config('app.webtype'));
    if (!$setting) {
        $setting = DB::table('settings')
            ->select('logo', 'newsdefaulttxt', 'epaper_url', 'announcement', 'announcement_link', 
                     'announcement_ios_link', 'announcement_status', 'election_status', 'election_link', 
                     'election_height', 'newfeatures', 'newfeatures_status')
            ->get()
            ->map(function ($item) use ($logoBaseUrl) {
                $item->logo = $logoBaseUrl . $item->logo;
                return $item;
            })
            ->toArray();
        
        Redis::set('settings'.config('app.webtype'), json_encode($setting)); // Store in Redis
    } else {
        $setting = json_decode($setting, true);
    }

    // Fetch side menu data from Redis or set it if not available
    $sidemenu = Redis::get('sidemenu'.config('app.webtype'));
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
        
        Redis::set('sidemenu'.config('app.webtype'), json_encode($sidemenu)); // Store in Redis
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
    /*$extraCategory = [
        [
            'id' => 0, 'userID' => 0, 'parentID' => 0, 'title' => 'Rashi', 
            'category_name' => 'Rashi', 'category_name_guj' => 'રાશિ', 'slug' => '',
            'featureImage' => '', 'icon' => $iconBaseUrl . 'default.jpg',
            'description' => '', 'metatitle' => '', 'metakeyword' => '', 'metadesc' => '',
            'catOrder' => 0, 'eventCat' => 0, 'setHome' => 0, 'status' => '',
            'created_at' => '', 'updated_at' => ''
        ]
    ];
    $category = array_merge($category, $extraCategory);
*/
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
    DB::table('webstory')->where('slug', $slug)->update([
        'viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')
    ]);

    return response()->json([
        'webstory' => $webstory,
        'webstorymore' => $webstorymore
    ], 200);
}
public function topnews()
{
    $data = array();
    
    // Define Redis keys
    $redisTopNewsKey = 'topnews'.config('app.webtype');
    $redisLiveNewsKey = 'livenews'.config('app.webtype');

    // Fetch top news from Redis or database
    if (Redis::exists($redisTopNewsKey)) {
        $topnews = collect(json_decode(Redis::get($redisTopNewsKey)));
    } else {
        $topnews = DB::table('news')
            ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
            ->leftJoin('categories', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
            })
            ->where('news.status', 'Active')
            ->where('news.catID', '!=', 9)
            ->where('news.topnewsOrder','!=', 0)
            ->where('news.is_home', 1)
            ->groupBy('news.id')
            ->orderBy('news.topnewsOrder', 'ASC')
            ->limit(10)
            ->get();

        // Store in Redis with a TTL of 10 minutes
        Redis::setex($redisTopNewsKey, 600, json_encode($topnews));
    }

    // Fetch live news from Redis or database
    if (Redis::exists($redisLiveNewsKey)) {
        $livenews = collect(json_decode(Redis::get($redisLiveNewsKey)));
    } else {
        $livenews = DB::table(DB::raw('(SELECT DISTINCT ln.lid, ln.livedesc, ln.livetitle, ln.created_at, ln.news_id
                        FROM livenews as ln ORDER BY ln.lid DESC) as subquery'))
            ->selectRaw('
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        "livetitle", subquery.livetitle,
                        "livenewsdate", subquery.created_at
                    )
                ) as live,
                subquery.news_id,
                n.slug,
                n.title,
                n.featureImage,
                n.videoURL,
                n.description,
                n.created_at,
                GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs,
                MAX(subquery.lid) as max_lid
            ')
            ->leftJoin('news as n', 'subquery.news_id', '=', 'n.id')
            ->leftJoin('categories', DB::raw('FIND_IN_SET(categories.id, n.catID)'), '>', DB::raw('0'))
            ->where('n.status', 'Active')
            ->where('n.is_live_news', 1)
            ->groupBy('subquery.news_id', 'n.slug', 'n.title', 'n.featureImage', 'n.videoURL')
            ->orderByDesc('max_lid')
            ->get()
            ->map(function ($row) {
                $row->live = json_decode($row->live, true);
                return $row;
            });

        // Store in Redis with a TTL of 10 minutes
        Redis::setex($redisLiveNewsKey, 600, json_encode($livenews));
    }

    // Set data array
    $data['topnews'] = $topnews;
    $data['livenews'] = $livenews;

    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
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
        
        DB::table('news')->where('slug', $slug)->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);
                  
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
                    'news.catID', 
                    DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs') // Use DISTINCT for unique slugs
                )
                ->join('categories', function ($join) {
                    // Use FIND_IN_SET to match categories only if necessary (can be inefficient)
                    $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
                })
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
    public function newsnextContent(Request $request){
        $perPage = 1; // Categories per page
        $newsPerPage = 1;
        $page = $request->input('pageNumber');
        $user_id = $request->input('user_id');
        $ipAddress = $request->input('device_id');
        $categoryIds = $request->has('categoryIds') ? explode(',', $request->input('categoryIds')) : [];
        $slug = $request->input('slug');
        $loadedSlugs = explode(',', $request->input('loadedSlugs', '')); // Get loaded slugs
        $lastSlug = end($loadedSlugs);
        // if (empty($categoryIds)) {
        //     return response()->json(['error' => 'Invalid categories'], 400);
        // }
        //$categoryIds = $request->has('categoryIds') ? explode(',', $request->input('categoryIds')) : [];

        $baseQuery = DB::table('news as n')
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
            ->where('n.status', 'Active')
            ->when(!empty($loadedSlugs), function ($query) use ($loadedSlugs) {
                $query->whereNotIn('n.slug', $loadedSlugs);
            })
            ->groupBy('n.id')
            ->orderBy('n.id', 'DESC')
            ->offset(($page - 1) * $newsPerPage)
            ->take($newsPerPage);

        if (!empty($categoryIds)) {
            $baseQuery->whereIn('categories.id', $categoryIds)->where('n.slug', '!=', $slug);
        } else {
            $baseQuery->where('n.slug', '=', $slug);
        }

        $newsDetail1 = $baseQuery->get()->map(fn($item) => (array) $item)->toArray();

        $newsDetail = !empty($newsDetail1) ? (object) $newsDetail1[0] : null;

        $data = $categories = $relatednews = array();
        $modifiedHtml = '';
        if ($newsDetail) { 
            if (strpos($newsDetail->catID, ',') !== false) {
                // Explode only if catID contains multiple values
                $categoryIds = explode(',', $newsDetail->catID);
            } else {
                $categoryIds = [$newsDetail->catID];
            }
            
            $categories = Category::whereIn('id', $categoryIds)
                ->orderByRaw("FIND_IN_SET(id, '{$newsDetail->catID}')") 
                ->get();
            
            $catid = $newsDetail->catID;
            $excludedId = $newsDetail->id;
            
            if (strpos($catid, ',') !== false) {
                $catIDs = explode(',', $catid);
            } else {
                $catIDs = [$catid];
            }
            
            $query = DB::table('news')
                ->select('id', 'catID', 'title', 'englishTitle', 'slug', 'featureImage', 'videoURL')
                ->where(function ($query) use ($catIDs) {
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
		if ($newsDetail) {
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
                      'news.catID', 
                      DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs') // Use DISTINCT for unique slugs
                  )
                  ->join('categories', function ($join) {
                      // Use FIND_IN_SET to match categories only if necessary (can be inefficient)
                      $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
                  })
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
    public function tagdetail(Request $request)
    {
        $slug = $request->input('slug');
        $device_id = $request->input('device_id');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 4); 
        $user_id = $request->input('user_id');

        $redisKey = "tagdetail:{$slug}:page:{$page}".config('app.webtype');

        // Check if data exists in Redis
        if (Redis::exists($redisKey)) {
            $news = json_decode(Redis::get($redisKey)); // Get from Redis
        } else {
            if (empty($device_id)) {
                $news = DB::table('news')
                    ->whereRaw('FIND_IN_SET(?, tags) > 0', [$slug])
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
                    ->whereRaw('FIND_IN_SET(?, tags) > 0', [$slug])
                    ->where('status', 'Active')
                    ->select('news.*', 'vl.like_count', 'vl.dislike_count', 'vl.liked_by_user', 'vl.disliked_by_user')
                    ->orderBy('newsOrder', 'DESC')
                    ->paginate($perPage, ['*'], 'page', $page);
            }

            // Store in Redis (Expire in 1 hour)
            Redis::setex($redisKey, 3600, json_encode($news));
        }

        // Transform data
        $news->getCollection()->transform(function ($item) use ($user_id) {
            $item->description = Str::limit($item->description, 120);
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

        $data = [
            'news' => $news,
            'catTitle' => $slug,
            'catSlug' => $slug,
            'catMetatitle' => str_replace("-", " ", $slug),
            'catMetadesc' => str_replace("-", " ", $slug),
        ];

        if (!$data['news']) {
            return response()->json($data, 404, ['error' => 'News not found'], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($data, 200, ['success' => 'Success'], JSON_UNESCAPED_UNICODE);
    }
    public function newsbookmark(Request $request)
    {
        $news_id = $request->input('news_id');
        $user_id = $request->input('user_id');
        $status = $request->input('status');
        $bookmark_type = $request->input('bookmark_type');

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
            ->leftJoin('categories', 'categories.id', '=', 'news.catID')
            ->select(
                'bookmark.news_id',
                'bookmark.bookmark_type',
                'news.title as newsTitle',
                'news.slug as newsSlug',
                'news.description',
                'news.featureImage',
                'news.videoURL',
                'categories.title as catTitle',
                'categories.slug as catSlug'
            )
            ->where('bookmark.user_id', $user_id)
            ->where('bookmark.status', '1')
            ->orderBy('bookmark.created_at', 'DESC')
            ->get();

        // Modify image URLs
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
        $redisKey = "breaking_news".config('app.webtype');

        // Check if breaking news data exists in Redis
        if (Redis::exists($redisKey)) {
            $breakingNews = json_decode(Redis::get($redisKey), true);
        } else {
            // Fetch from DB if not in Redis
            $breakingNews = DB::table('news')
                ->select('news.*', 'u1.name as authorName')
                ->selectRaw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categoryNames')
                ->selectRaw('(SELECT GROUP_CONCAT(slug SEPARATOR "/") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categorySlugs')      
                ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
                ->where('news.status', 'Active')
                ->where('news.is_breaking', 1)
                ->whereRaw('DATE(news.created_at) = CURDATE()') 
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->first();

            // Store in Redis for 5 minutes (300 seconds)
            Redis::setex($redisKey, 300, json_encode($breakingNews));
        }

        return response()->json(['breakingnews' => $breakingNews], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function poll(Request $request)
    {
        $ipAddress = $request->input('device_id');
        
        // Define Redis key
        $redisKey = "polls_active_{$ipAddress}".config('app.webtype');

        // Check if the poll data is cached in Redis
        if (Redis::exists($redisKey)) {
            $polls = json_decode(Redis::get($redisKey));
        } else {
            // Fetch poll data from DB
            $polls = DB::table('polls')
                ->leftJoin('votes', function ($join) use ($ipAddress) {
                    $join->on('polls.id', '=', 'votes.pollID')
                        ->where('votes.IPaddress', '=', $ipAddress);
                })
                ->where('polls.status', 'Active')
                ->select('polls.*', 'votes.selectedAnswer')
                ->get();

            // Store poll data in Redis for 10 minutes (600 seconds)
            Redis::setex($redisKey, 600, json_encode($polls));
        }

        if (empty($polls)) {
            return response()->json(['error' => 'Poll not found'], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['poll' => $polls], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function submitpoll(Request $request)
    {
        $submitedata = $request->all();
        $ipAddress = $request->input('device_id');
        $existingVote = Vote::where('IPaddress', $ipAddress)
                            ->where('pollID',  $request->input('pollID'))
                            ->first();

        if ($existingVote) {
            return response()->json(['status'=>false,'message' => 'You have already voted for this poll.','message_guj' => 'You have already voted for this poll.']);
        }
        $vote = new Vote([
            'pollID' =>  $request->input('pollID'),
            'selectedAnswer'=>  $request->input('selectedAnswer'),
            'IPaddress'=> $ipAddress
        ]);
        
        $vote->save();
        
        return response()->json(['status'=>true,'message' => 'Vote submitted successfully','message_guj' => 'You have already voted for this poll.']);
    } 
    public function pollresult()
    {
        try {
            // Define Redis key
            $redisKey = "poll_results_latest_10".config('app.webtype');
    
            // Check if poll results are cached in Redis
            if (Redis::exists($redisKey)) {
                $pollresults = json_decode(Redis::get($redisKey));
            } else {
                // Fetch latest 10 poll questions
                $pollresults = DB::table('polls')
                    ->select('question', 'answerOption', 'id')
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
                Redis::setex($redisKey, 600, json_encode($pollresults));
            }
    
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
        if (empty($newspaperDate)) {
            $newspaperDate = date('Y-m-d');
        }
        $newspaperDate = date('Y-m-d', strtotime($newspaperDate));

        // Unique Redis Key
        $redisKey = "epaper_{$newspaperDate}".config('app.webtype');

        // Check if data exists in Redis
        $epapercity = Redis::get($redisKey);

        if (!$epapercity) {
            // Fetch data from the database
            $epapercity = DB::table('epapercategory')
                ->leftJoinSub(
                    DB::table('epaper')
                        ->select('epaper.*')
                        ->where('epaper.status', 'Active') 
                        ->orderBy('epaper.created_at', 'DESC'),
                    'epaper',
                    function ($join) {
                        $join->on('epapercategory.id', '=', 'epaper.ecatID'); 
                    }
                )
                ->where('epapercategory.status', 'Active')
                ->where('epapercategory.cattype', 'Newspaper')
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

        // Convert date format using Carbon
        try {
            $formattedDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        // Generate Redis key
        $redisKey = "epaper_detail_{$slug}_{$formattedDate}".config('app.webtype');

        // Try to retrieve data from Redis
        $cachedData = Redis::get($redisKey);

        if ($cachedData) {
            return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Fetch data from database
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
        DB::table('epaper')
            ->where('newspaperdate', $formattedDate)
            ->whereIn('ecatID', function ($query) use ($slug) {
                $query->select('id')->from('epapercategory')->where('slug', $slug);
            })
            ->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);

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
            DB::table('rashifaldata')->where('rashifal_id', $rashi_id)->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);

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
        DB::table('rashifaldata')->where('rashifal_id', $rashi_id)->update(['viewer_app' => DB::raw('COALESCE(viewer_app, 0) + 1')]);

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
    $redisKey = 'livecricket_data'.config('app.webtype');

    // Check if data exists in Redis
    $cachedData = Redis::get($redisKey);
    if ($cachedData) {
        return response()->json(json_decode($cachedData, true), 200, [], JSON_UNESCAPED_UNICODE);
    }

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
        return response()->json(['error' => 'Live Cricket is not found'], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Store data in Redis for 1 hour (3600 seconds)
    Redis::setex($redisKey, 3600, json_encode($livecricket, JSON_UNESCAPED_UNICODE));

    return response()->json($livecricket, 200, [], JSON_UNESCAPED_UNICODE);
}

}
