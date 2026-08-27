<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Module;
use App\Models\Deletenews;
use App\Models\Newsarchive;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    // Get all pages
    public function index()
    {
        return view('user.alllist');
      	$user = Redis::get('user');
        if (!$user) { 
        $user = DB::table('users')
                ->select('users.*', 'role.title as title')
                ->leftJoin('roles AS role', 'role.id', '=', 'users.roleID')->get();
          Redis::set('user', json_encode($user));
        } else {
            $user = json_decode($user);
        }
        $data =  array();
        $data['user'] =  $user;
        return view('user.list', compact('data'));
    }

    // Add new page
    public function add()
    {
        $category = DB::table('categories')
                    ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
                    ->select('categories.id', 'categories.title', 'categories.parentID', 'children.id as child_id', 'children.title as child_name')
                    ->orderBy('categories.id')
                    ->get();
        $uniqueParentCategories = $category->unique('id');
        $module = DB::table('modules')->orderBy('id')->get();
        $role = DB::table('roles')->orderBy('id')->get();
        $data =  array();
        $data['category'] =  $uniqueParentCategories;
        $data['module'] =  $module;
        $data['role'] =  $role;
        return view('user.add', compact('data'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'profileImg' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'moduleID'=>'required',
            'catID'=>'required',
            'roleID'=>'required'
        ];

        $customMessages = [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email address is already in use.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'profileImg.image' => 'The uploaded file must be an image.',
            'profileImg.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'profileImg.max' => 'The image size must not exceed 2 MB.',
            'roleID.required' => 'The role field is required.',
            'moduleID.required' => 'At least one module must be selected.',
            'catID.required' => 'At least one category must be selected.',
        ];
    
        $request->validate($rules, $customMessages);

        $profileImg = '';
        if ($files = $request->file('profileImg')) {
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/profile/";
            //$destinationPath = public_path('/uploads/profile/'); // upload path
            $profileImg = 'profile_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $profileImg);
        }
        
        $user = new User([
            'name' => $request->get('name'),
            'email'=> $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::random(50),
            'profileImg'=> $profileImg,
            'moduleID'=> implode(',',$request->get('moduleID')),
            'catID'=> implode(',',$request->get('catID')),
            'roleID'=> $request->get('roleID')
        ]);
        $user->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'users:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'User added: ' . $request->get('name'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/user/')->with('success', 'User has been added');
    }

    // Update Category
    public function edit($id)
    {
        $id = Route::current()->parameter('id');
        $user = User::find($id); 
        $category = DB::table('categories')
                    ->leftJoin('categories AS children', 'categories.id', '=', 'children.parentID')
                    ->select('categories.id', 'categories.title', 'categories.parentID', 'children.id as child_id', 'children.title as child_name')
                    ->orderBy('categories.id')
                    ->get();
        $uniqueParentCategories = $category->unique('id');
        $module = DB::table('modules')->orderBy('id')->get();
        $role = DB::table('roles')->orderBy('id')->get();
        $data =  array();
        $data['category'] =  $uniqueParentCategories;
        $data['module'] =  $module;
        $data['role'] =  $role;
        $data['user'] =  $user;
        return view('user.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email||unique:users,email,' . $request->uid,
            'profileImg' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'moduleID'=>'required',
            'catID'=>'required',
            'roleID'=>'required'
        ];

        $customMessages = [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email address is already in use.',
            'profileImg.image' => 'The uploaded file must be an image.',
            'profileImg.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'profileImg.max' => 'The image size must not exceed 2 MB.',
            'roleID.required' => 'The role field is required.',
            'moduleID.required' => 'At least one module must be selected.',
            'catID.required' => 'At least one category must be selected.',
        ];
    
        $request->validate($rules, $customMessages); 
        
        if ($files = $request->file('profileImg')) {
            //$destinationPath = public_path('/uploads/profile/'); // upload path
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/profile/";
            $oldImg = $request->get('oldImg'); 
            if ($oldImg !== null && $oldImg !== '' && Storage::exists($destinationPath.$oldImg))
            {
                unlink($destinationPath.$request->get('oldImg'));
            }
            $profileImg = 'profile_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $profileImg);
        } else {
            $profileImg =  $request->get('oldImg');
        }

        $user = User::find($request->uid);
        $user->name = $request->get('name');
        $user->profileImg = $profileImg;
        $user->catID = implode(',',$request->get('catID'));
        $user->moduleID = implode(',',$request->get('moduleID'));
        $user->email = $request->get('email');
        $user->roleID = $request->get('roleID');
        // ✅ Password update only if not empty
        if ($request->filled('password')) {
            $user->password = Hash::make($request->get('password'));
        }		
        $user->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'users:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'User updated: ' . $request->get('name'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/user/edit/'.$request->uid)->with('success', 'User has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $user = User::find($id); 
        $username = $user->name;
        $user->status = $status;
        $user->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'users:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'User published/Unpublished: ' . $username,
            'action' => $status,
            'contents' => json_encode($username),
        ]);
        return redirect('/user/')->with('success', 'User Status has been updated successfully');
    }

    // Dalete the page
    public function destroy($id,Request $request)
    {
        $destinationPath = public_path('/uploads/profile/'); // upload path
        $oldImg = $request->get('oldImg'); 
        if ($oldImg !== null && $oldImg !== '' && Storage::exists($destinationPath.$oldImg))
        {
            $imagePath = public_path('/uploads/profile/'.$request->get('oldImg'));
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $id = Route::current()->parameter('id');
        $user = User::find($id); 
        $username = $user->name;
        $user->delete();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'users:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'User deleted: ' . $username,
            'action' => 'Delete',
            'contents' => json_encode($username),
        ]);
        return redirect('/user/')->with('success', 'User has been deleted successfully');
    }

    public function profile($id)
    {
        $user = DB::table('users')
            ->select('users.*', 'role.title as title')
            ->leftJoin('roles AS role', 'role.id', '=', 'users.roleID')->where('users.id', $id)->get();
            $data = [
                'user' => $user,
                'id'=>$id,
            ];
        return view('user.allprofile', compact('data'));
    } 
  	
  	public function deletemediaImages(Request $request) {
        if ($request->isMethod('post')) {
            // Convert dates from d/m/Y to Y-m-d
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->format('Y-m-d');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Invalid start date format');
            }
    
            $endDate = $request->input('end_date') ? Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->format('Y-m-d') : null;
    
            // Build the query based on the provided dates
            $query = DB::table('medias');
    
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
    
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            } elseif (!$endDate && $startDate) {
                $query->where('created_at', '<', $startDate);
            }
    
            // Fetch the records that match the criteria
            $mediaRecords = $query->orderBy('id', 'DESC')->get();
    
            // Process each record and delete the associated files
            foreach ($mediaRecords as $media) {
                $featureImage = $media->photos;
                $exten = explode('.', $featureImage);
                $destinationPath = public_path('/media/');
    
                if (isset($exten[1]) && $exten[1] == 'mp4') {
                    $mp4Path = $destinationPath . $featureImage;
                    $gifPath = $destinationPath . $exten[0] . '.gif';
    
                    if (file_exists($mp4Path)) {
                        unlink($mp4Path);
                    }
                    if (file_exists($gifPath)) {
                        unlink($gifPath);
                    }
                } else {
                    $imgPath = $destinationPath . $featureImage;
                    $smallImgPath = $destinationPath . $exten[0] . '_small.' . $exten[1];
                    $medImgPath = $destinationPath . $exten[0] . '_medium.' . $exten[1];
    
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                    if (file_exists($smallImgPath)) {
                        unlink($smallImgPath);
                    }
                    if (file_exists($medImgPath)) {
                        unlink($medImgPath);
                    }
                }    
                // Delete the record from the database
                DB::table('medias')->where('id', $media->id)->delete();
            }
            Deletenews::create([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'type' => 'media'
            ]);
            return redirect()->back()->with('success', 'Media records deleted successfully.');
        }
    
        // Display the form if the request is not POST
        return view('user.deletemedia');
    }
    public function deletenewsImages(Request $request) {
        if ($request->isMethod('post')) {
            // Convert dates from d/m/Y to Y-m-d
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $request->input('start_date'))->format('Y-m-d');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Invalid start date format');
            }
    
            $endDate = $request->input('end_date') ? Carbon::createFromFormat('Y-m-d', $request->input('end_date'))->format('Y-m-d') : null;
    
            // Build the query based on the provided dates
            $query = DB::table('news');
    
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
    
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            } elseif (!$endDate && $startDate) {
                $query->where('created_at', '<', $startDate);
            }
    
            // Fetch the records that match the criteria
            $newsRecords = $query->orderBy('id', 'DESC')->get();
    
            // Process each record and delete the associated files
            foreach ($newsRecords as $news) {
                 

                Newsarchive::create([
                    'userID' => $news->userID,
                    'assignUserID' => $news->assignUserID,
                    'catID' => $news->catID,
                    'title' => $news->title,
                    'englishTitle' => $news->englishTitle,
                    'slug' => $news->slug,
                    'featureImage' => $news->featureImage,
                    'imageURL' => $news->imageURL,
                    'uploadVideo' => $news->uploadVideo,
                    'videoURL' => $news->videoURL,
                    'scheduleDate' => $news->scheduleDate,
                    'tags' => $news->tags,
                    'description' => $news->description,
                    'metatitle' => $news->metatitle,
                    'metakeyword' => $news->metakeyword,
                    'metadesc' => $news->metadesc,
                    'is_home' => $news->is_home,
                    'related_news' => $news->related_news,
                    'viewer' => $news->viewer,
                    'newsOrder' => $news->newsOrder,
                    'topnewsOrder' => $news->topnewsOrder,
                    'inshorts' => $news->inshorts,
                    'notification' => $news->notification,
                    'status' => $news->status,
                    'created_at' => $news->created_at,
                    'updated_at' => $news->updated_at,
                ]);
                 
                // Delete the records from the news table
                DB::table('news')->whereBetween('created_at', [$startDate, $endDate])->delete();
            }
            Deletenews::create([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'type' => 'news'
            ]);
            return redirect()->back()->with('success', 'News records deleted successfully.');
        }
    
        // Display the form if the request is not POST
        return view('user.deletenews');
    }
    public function userfetchData(Request $request)
    {
        $columns = ['id', 'name', 'email', 'title', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

        $cacheKey = 'users:search_' . config('app.webtype') . ':' . $draw;
        // Check if data exists in Redis cache
        $userData = Redis::get($cacheKey);

        if (!$userData) {
            // $query = DB::table('users')
            //     ->select('users.*', 'role.title as title')
            //     ->leftJoin('roles AS role', 'role.id', '=', 'users.roleID');
            $query = DB::table('users')
                ->select(
                    'users.*',
                    'role.title as title',
                    DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                    DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = CURDATE()) as today_news_count")
                )
                ->leftJoin('roles AS role', 'role.id', '=', 'users.roleID')
                ->orderByRaw("FIELD(users.status,'Active','Inactive')");

            // Apply search filter if any search value is provided
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('users.name', 'like', '%' . $searchValue . '%')
                       ->orWhere('role.title', 'like', '%' . $searchValue . '%');
                });
            }

            // Get all filtered data
            $userData = $query->get();

            // Cache the data in Redis for 5 minutes
            Redis::setex($cacheKey, 300, json_encode($userData));
        } else {
            $userData = json_decode($userData);
        }

        if (is_array($userData)) {
            $totalRecords = count($userData);
            $filteredData = array_slice($userData, $start, $length);
            $filteredData = collect($filteredData);
        } else {
            $totalRecords = $query->count();
            $filteredData = $query->skip($start)
                ->take($length)
                ->get();
        }

        $counter = $start + 1;

        // Map the data to add the 'action' column and other transformations
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $row->name = '<a href="' . url('/user/profile', $row->id) . '">' . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . '</a>';
            $row->action = '
                <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/user/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                    <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
                </a>
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/user/edit', $row->id) . '">
                    <i class="bi bi-pencil"></i>
                </a>
                <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/user/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                    <i class="bi bi-trash"></i>
                </a>
                <form id="delete-form-' . $row->id . '" action="' . url('/user/destroy', $row->id) . '" method="POST" style="display: none;">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                </form>';
            return $row;
        });

        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords, // Send the total filtered records count
            "data" => $formattedData,
        ]);
    }
   public function userprofilefetchData(Request $request)
    {
        $columns = ['id', 'title', 'categoryNames', 'viewer', 'created_at'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'newsOrder';
        $orderDirection = $request->input('order.0.dir', 'DESC');
        $id = $request->input('userid');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        if (!empty($startDate) && !empty($endDate)) {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        // Base query for news data
        $query = DB::table('news')
            ->select(
                'news.*',
                'u1.name as authorName',
                DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.id) as categoryIds'),
                DB::raw('IFNULL(newscount.view_count,0) as total_view_count')
            )
            ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
            ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
            ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
            ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id') // <-- JOIN newscount
            ->where('news.userID', $id)
            ->groupBy('news.id');

        // Apply date range filter
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('news.created_at', [$startDate, $endDate]);
        }

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('news.title', 'like', '%' . $searchValue . '%')
                    ->orWhere('categories.title', 'like', '%' . $searchValue . '%');
            });
        }

        // Count total records for userID (use distinct news.id)
        $totalRecords = DB::table('news')
            ->where('news.userID', $id)
            ->count();

        // Count filtered records (clone query before adding pagination or limits)
        $recordsFiltered = $query->get()->count(); 

        // =====================================================
        // Load Google Analytics cache (same as fetchData)
        // =====================================================

        $pageViews = [];
        $titleToAppCount = [];

        try {

            $pageViews365 = Cache::remember(
                'ga_page_views_365days',
                43200,
                fn() => $this->analyticsService->getPageTotalViews(365)
            );

            $pageViews30 = Cache::remember(
                'ga_page_views_30days',
                3600,
                fn() => $this->analyticsService->getPageTotalViews(30)
            );

            $pathTitleMap365 = Cache::remember(
                'ga_path_title_map_365days',
                43200,
                fn() => $this->analyticsService->getPathTitleMap(365)
            );

            $pathTitleMap30 = Cache::remember(
                'ga_path_title_map_30days',
                3600,
                fn() => $this->analyticsService->getPathTitleMap(30)
            );

            // Merge page views
            $pageViews = $pageViews365;

            foreach ($pageViews30 as $path => $count) {
                $pageViews[$path] = isset($pageViews[$path])
                    ? max($pageViews[$path], $count)
                    : $count;
            }

            $pathTitleMap = $pathTitleMap365;

            foreach ($pathTitleMap30 as $path => $title) {
                $pathTitleMap[$path] = $title;
            }

            // App views
            $appPathCounts = [];

            foreach ($pathTitleMap as $path => $title) {

                if (preg_match('#^/(GSTV|gstv)/#', $path)) {

                    $count = max(
                        $pageViews365[$path] ?? 0,
                        $pageViews30[$path] ?? 0
                    );

                    if (!isset($appPathCounts[$title])) {
                        $appPathCounts[$title] = 0;
                    }

                    $appPathCounts[$title] += $count;
                }
            }

            $titleToAppCount = $appPathCounts;

        } catch (\Exception $e) {

            Log::error(
                'Google Analytics fetch failed : '
                . $e->getMessage()
            );
        }

        // =====================================================
        // Same total logic as fetchData
        // =====================================================

        $computeRowTotal = function ($row) use ($pageViews, $titleToAppCount) {

            $isVideo = Str::contains(
                $row->categoryNames ?? '',
                'Videos'
            );

            $path = $isVideo
                ? "/{$row->categorySlugs}/{$row->slug}"
                : "/news/{$row->categorySlugs}/{$row->slug}";

            $gaWeb = $pageViews[$path] ?? 0;
            $gaApp = $titleToAppCount[$row->title] ?? 0;
            $dbCount = (int)($row->total_view_count ?? 0);

            return $gaWeb + $gaApp + $dbCount;
        };


        // Apply pagination
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Format data
        $formattedData = $filteredData->map(
            function ($row) use (&$counter, $computeRowTotal) {

                $row->rowNumber = $counter++;

                $catIDs = explode(',', $row->categoryIds);

                $baseUrl = config('app.front_url');

                $newsUrl =
                    $baseUrl . "/" .
                    $row->categorySlugs . "/" .
                    $row->slug;

                $newsvideoUrl =
                    $baseUrl . "/news/" .
                    $row->categorySlugs . "/" .
                    $row->slug;

                // SAME LOGIC AS fetchData
                $totalViews = $computeRowTotal($row);

                $row->viewer = number_format($totalViews);

                if (in_array('9', $catIDs)) {

                    $row->title =
                        '<a href="' .
                        $newsUrl .
                        '" target="_blank">' .
                        htmlspecialchars($row->title) .
                        '</a>';

                } else {

                    $row->title =
                        '<a href="' .
                        $newsvideoUrl .
                        '" target="_blank">' .
                        htmlspecialchars($row->title) .
                        '</a>';
                }

                $row->date = Carbon::parse(
                    $row->created_at
                )->format('d-m-Y');

                return $row;
            }
        );

        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $formattedData
        ]);
    }
    public function userprofilefetchData21052026(Request $request)
    {
        $columns = ['id', 'title', 'categoryNames', 'viewer', 'created_at'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'newsOrder';
        $orderDirection = $request->input('order.0.dir', 'DESC');
        $id = $request->input('userid');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        if (!empty($startDate) && !empty($endDate)) {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        // Base query for news data
        $query = DB::table('news')
            ->select(
                'news.*',
                'u1.name as authorName',
                DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT categories.id) as categoryIds'),
                'newscount.view_count as total_view_count'
            )
            ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
            ->leftJoin('news_category', 'news.id', '=', 'news_category.news_id')
            ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
            ->leftJoin('newscount', 'news.id', '=', 'newscount.content_id') // <-- JOIN newscount
            ->where('news.userID', $id)
            ->groupBy('news.id');

        // Apply date range filter
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('news.created_at', [$startDate, $endDate]);
        }

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('news.title', 'like', '%' . $searchValue . '%')
                    ->orWhere('categories.title', 'like', '%' . $searchValue . '%');
            });
        }

        // Count total records for userID (use distinct news.id)
        $totalRecords = DB::table('news')
            ->where('news.userID', $id)
            ->count();

        // Count filtered records (clone query before adding pagination or limits)
        $recordsFiltered = $query->get()->count(); 

        // Apply pagination
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Format the data
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $catIDs = explode(',', $row->categoryIds);
            $baseUrl = config('app.front_url');
            $newsUrl = $baseUrl."/".$row->categorySlugs."/".$row->slug;
            $newsvideoUrl = $baseUrl."/news/".$row->categorySlugs."/".$row->slug;
            $row->viewer = $row->total_view_count ?? 0;
            if (in_array('9', $catIDs)) {
                $row->title = '<a href="' . $newsUrl . '" target="_blank">' . htmlspecialchars($row->title) . '</a>';
            } else {
                $row->title = '<a href="' . $newsvideoUrl . '" target="_blank">' . htmlspecialchars($row->title) . '</a>';
            }
            $row->date = \Carbon\Carbon::createFromTimestamp(strtotime($row->created_at))->format('d-m-Y');
            return $row;
        });

        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,       // Total records without filters
            "recordsFiltered" => $recordsFiltered, // Filtered records count
            "data" => $formattedData,              // Paginated data
        ]);
    }

}
