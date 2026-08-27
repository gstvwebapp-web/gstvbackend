<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class CategoryController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('category.alllist');
        $categorylist = Redis::get('categorylist');
        if (!$categorylist) {     
            $categorylist = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'u1.name as authorName', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
          			->orderBy('c1.parentID','ASC')
                    ->orderBy('c1.catOrder','ASC')
                    ->get();
           Redis::set('categorylist', json_encode($categorylist));
        } else {
           $categorylist = json_decode($categorylist);
        }
        $data =  array();
        $data['category'] =  $categorylist;
        return view('category.list', compact('data'));
    }

    // New Category Add
    public function add()
    {
        $category = DB::table('categories')->where('parentID',0)->where('status','Active')->get();
        $data =  array();
        $data['category'] =  $category;
        return view('category.add', compact('data'));
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required',
            'icon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'metatitle'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The Category title field is required.',
            'icon.image' => 'The uploaded file must be an image.',
            'icon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'icon.max' => 'The image size must not exceed 2 MB.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
        Redis::del('categories');
        Redis::del('child_categories');
      	Redis::del('categorylist');
        $categories = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.slug', 'c1.id', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status', 'Active')
                    ->where('c1.setHome', 1)
                    ->get();
        $categoryCount = $categories->count();
        for($i=0;$i<=$categoryCount;$i++){
            Redis::del('categories_page_'.$i);
        }
        $request->validate($rules, $customMessages);

        $iconImg = '';
        $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/category/icon/';
        if ($files = $request->file('icon')) {            
            $iconImg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $iconImg);
        }
        $featureImageImg = '';
        if ($files = $request->file('featureImage')) {
            $featureImageImg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $featureImageImg);
        }
        
        $eventCat = 0;
        if($request->get('eventCat')==1) { $eventCat = 1; }
        $setHome = 0;
        if($request->get('setHome')==1) { $setHome = 1; }
        $settopvideos = 0;
        if($request->get('settopvideos')==1) { $settopvideos = 1; }
        $settopmenucat = 0;
        if($request->get('settopmenucat')==1) { $settopmenucat = 1; }
        $category = new Category([
            'title' => $request->get('title'),
            'category_name_guj' => $request->get('category_name_guj'),
            'slug' => Str::slug($request->get('title'), '-'),
            'userID'=> Auth::user()->id,
            'parentID'=> $request->get('parentID'),
            'icon'=> $iconImg,
            'featureImage'=>config('app.backendnewsurl') ."/public/uploads/category/icon/".$featureImageImg,
            'eventCat'=> $eventCat,
          	'setHome' => $setHome,
            'settopvideos' => $settopvideos,
            'settopmenucat' => $settopmenucat,
            'metatitle'=> $request->get('metatitle'),
            'metakeyword'=> $request->get('metakeyword'),
            'metadesc'=> $request->get('metadesc')
        ]);
        
        $category->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'categories:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        $category = Category::find($category->id); 
        $category->catOrder = $category->id;       
        $category->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Category added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/category/')->with('success', 'Category has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $category = Category::find($id); 
        $subcategory = DB::table('categories')->where('parentID',0)->where('status','Active')->get();
        $data =  array();
        $data['subcategory'] =  $subcategory;
        $data['category'] =  $category;
        return view('category.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required',
            'icon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'metatitle'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The Category title field is required.',
            'icon.image' => 'The uploaded file must be an image.',
            'icon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'icon.max' => 'The image size must not exceed 2 MB.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);
        Redis::del('categories');
        Redis::del('child_categories');
      	Redis::del('categorylist');
        $categories = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.slug', 'c1.id', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status', 'Active')
                    ->where('c1.setHome', 1)
                    ->get();
        $categoryCount = $categories->count();
        for($i=0;$i<=$categoryCount;$i++){
            Redis::del('categories_page_'.$i);
        }
        $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/category/icon/';
        if ($files = $request->file('icon')) {          
            if ($request->get('oldImg') != '') {
                $filePath = $destinationPath . $request->get('oldImg');
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $iconImg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $iconImg);
        } else {
            $iconImg =  $request->get('oldImg');
        }
        // if ($files = $request->file('featureImage')) {
        //     if ($request->get('oldfeatureImg') != '') {
        //         $filePath = $destinationPath . $request->get('oldfeatureImg');
        //         // if (file_exists($filePath)) {
        //         //     unlink($filePath);
        //         // }
        //     }
        //     $featureImageimg = 'img_'.time().'.'.$files->extension();   
        //     $files->move($destinationPath, $featureImageimg);
        // } else {
        //     $featureImageimg =  $request->get('oldfeatureImg');
        // }
        $eventCat = 0;
        if($request->get('eventCat')==1) { $eventCat = 1; }
        $setHome = 0;
        if($request->get('setHome')==1) { $setHome = 1; }
        $settopvideos = 0;
        if($request->get('settopvideos')==1) { $settopvideos = 1; }
        $settopmenucat = 0;
        if($request->get('settopmenucat')==1) { $settopmenucat = 1; }
        $category = Category::find($request->catid);
        $category->title = $request->get('title');
        $category->category_name_guj = $request->get('category_name_guj');
        $category->slug = Str::slug($request->get('title'), '-');
        //$category->userID = Auth::user()->id;
        $category->parentID = $request->get('parentID');
        $category->icon = $iconImg;
        // $category->featureImage = config('app.backendnewsurl') ."/public/uploads/category/icon/".$featureImageimg;
        $category->eventCat = $eventCat;
      	$category->setHome = $setHome;
        $category->settopvideos = $settopvideos;
        $category->settopmenucat = $settopmenucat;
        $category->metatitle = $request->get('metatitle');
        $category->metakeyword = $request->get('metakeyword');
        $category->metadesc = $request->get('metadesc');
       
        $category->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'categories:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Category updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/category/')->with('success', 'Category has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $category = Category::find($id); 
        $cattitle = $category->title;
        $category->status = $status;
        $category->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Category published/Unpublished: ' . $cattitle,
            'action' => $status,
            'contents' => json_encode($cattitle),
        ]);
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'categories:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return redirect('/category/')->with('success', 'Category Status has been updated successfully');
    }

    public function catorder(Request $request)
    {
        Redis::del('gstvsatrang');
        Redis::del('categories');
        Redis::del('child_categories');
        $categories = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.slug', 'c1.id', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status', 'Active')
                    ->where('c1.setHome', 1)
                    ->get();
        $categoryCount = $categories->count();
        for($i=0;$i<=$categoryCount;$i++){
            Redis::del('categories_page_'.$i);
        }
        $newOrder = $request->input('newOrder');
        foreach ($newOrder as $position => $itemId) {
            $category = Category::find($itemId);
            $category->catOrder = $position + 1;
            $category->update();
        }
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'categories:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return response()->json(['message' => 'Order updated successfully']);
    }

    // Category Delete 
    public function destroy(Request $request)
    {
        if($request->get('oldImg')) {
            $destinationPath=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/category/icon/';
            $imagePath = $destinationPath.$request->get('oldImg');
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $id = Route::current()->parameter('id');
        $category = Category::find($id); 
        $cattitle = $category->title;
        $category->delete();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'categories:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Category deleted: ' . $cattitle,
            'action' => 'Delete',
            'contents' => json_encode($cattitle),
        ]);

        return redirect('/category/')->with('success', 'Category has been deleted successfully');
    }
    
    public function categoryfetchData_1(Request $request)
    {
        $columns = ['id', 'icon', 'title', 'parentID', 'author_name', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

        // Base query with joins for parent category and author
        $query = DB::table('categories as c1')
            ->select(
                'c1.*', // Select all columns from 'categories'
                'u1.name as author_name', // Author name from the users table
                'c2.title as parent_category_name' // Parent category title from the categories table
            )
            ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id') // Join for parent category
            ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id') // Join for author info (user)
            ->orderBy('c1.parentID', 'ASC')
            ->orderBy('c1.catOrder', 'ASC');

        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.title', 'like', '%' . $searchValue . '%')
                    ->orWhere('u1.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('c2.title', 'like', '%' . $searchValue . '%');
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination
        $filteredData = $query->skip($start)
            ->take($length)
            ->get();
            $counter = $start + 1;
        // Map the data to add the 'action' column and other transformations
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            $imgurl = config('app.backendnewsurl') . '/public/uploads/category/icon/' . $row->icon;
            $row->icon = "<img src=" . $imgurl . " style='width:30px;'/>"; // Image
            // Generate the image URL for the category icon
            $row->icon_url = $row->icon ? config('app.backendnewsurl') . '/public/uploads/category/icon/' . $row->icon : null;
            $row->parentID = $row->parent_category_name;
            // Add the action button with the URL for viewing the category
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/category/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/category/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/category/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/category/destroy', $row->id) . '" method="POST" style="display: none;">
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
    public function categoryfetchData(Request $request)
    {
        $columns = ['id', 'icon', 'title', 'parentID', 'author_name', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

        $cacheKey = 'categories:search_' .config('app.webtype') . ':' . $draw;

        $categoryData = Redis::get($cacheKey);
        
        if (!$categoryData) {
            $query = DB::table('categories as c1')
                ->select(
                    'c1.*', // Select all columns from 'categories'
                    'u1.name as author_name', // Author name from the users table
                    'c2.title as parent_category_name' // Parent category title from the categories table
                )
                ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id') // Join for parent category
                ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id') // Join for author info (user)
                ->orderBy('c1.parentID', 'ASC')
                ->orderBy('c1.catOrder', 'ASC');
        
            // Apply search filter if any search value is provided
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('c1.title', 'like', '%' . $searchValue . '%')
                        ->orWhere('u1.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('c2.title', 'like', '%' . $searchValue . '%');
                });
            }
        
            // Execute the query
            $categoryData = $query->get();
        
            // Cache the result in Redis for 5 minutes (300 seconds)
            Redis::setex($cacheKey, 300, json_encode($categoryData));
        } else {
            // Decode the cached data
            $categoryData = json_decode($categoryData);
        }
        
        // Handle pagination
        if (is_array($categoryData)) {
            // If data is from Redis, paginate manually
            $totalRecords = count($categoryData);
            $filteredData = array_slice($categoryData, $start, $length);
        
            // Convert filtered data to a Laravel collection
            $filteredData = collect($filteredData);
        } else {
            // If data is from DB query, apply pagination to the query
            $totalRecords = $query->count();
            $filteredData = $query->skip($start)
                ->take($length)
                ->get();
        }
            $counter = $start + 1;
        // Map the data to add the 'action' column and other transformations
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            $imgurl = config('app.backendnewsurl') . '/public/uploads/category/icon/' . $row->icon;
            $row->icon = "<img src=" . $imgurl . " style='width:30px;'/>"; // Image
            // Generate the image URL for the category icon
            $row->icon_url = $row->icon ? config('app.backendnewsurl') . '/public/uploads/category/icon/' . $row->icon : null;
            $row->parentID = $row->parent_category_name;
            // Add the action button with the URL for viewing the category
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/category/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/category/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/category/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/category/destroy', $row->id) . '" method="POST" style="display: none;">
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

}

