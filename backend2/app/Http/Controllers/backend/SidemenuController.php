<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Sidemenu;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class SidemenuController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('sidemenu.alllist');
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
        return view('sidemenu.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'menu' => 'required',
            'menuicon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'menuaction'=>'required'
        ];

        $customMessages = [
            'menu.required' => 'The Menu title field is required.',
            'menuicon.image' => 'The uploaded file must be an image.',
            'menuicon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'menuicon.max' => 'The image size must not exceed 2 MB.',
            'menuaction.required' => 'The menu action field is required.',
        ];
        
        $request->validate($rules, $customMessages);

        $iconImg = '';
        if ($files = $request->file('icon')) {
            // $destinationPath = public_path('/uploads/category/icon/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/sidemenu/';
            $iconImg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $iconImg);
        }
      	$loginstatus = 0;
        if($request->get('loginstatus')==1) { $loginstatus = 1; }
        $sidemenu = new Sidemenu([
            'menu' => $request->get('menu'),
            'menuicon'=> $iconImg,
          	'loginstatus' => $loginstatus,
            'menuaction'=> $request->get('menuaction'),
        ]);
        
        $sidemenu->save();
        $sidemenu = Sidemenu::find($sidemenu->id); 
        $sidemenu->menuorder = $sidemenu->id;       
        $sidemenu->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'SideMenu added: ' . $request->get('menu'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/sidemenu/')->with('success', 'Side Menu has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $sidemenu = Sidemenu::find($id); 
        
        $data =  array();
        $data['sidemenu'] =  $sidemenu;
        return view('sidemenu.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'menu' => 'required',
            'menuicon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'menuaction'=>'required'
        ];

        $customMessages = [
            'menu.required' => 'The Menu title field is required.',
            'menuicon.image' => 'The uploaded file must be an image.',
            'menuicon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'menuicon.max' => 'The image size must not exceed 2 MB.',
            'menuaction.required' => 'The menu action field is required.',
        ];
        
        $request->validate($rules, $customMessages);
      
        if ($files = $request->file('icon')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/sidemenu/';
            if($request->get('oldImg')!='') {
                unlink($destinationPath.$request->get('oldImg'));
            }
            $iconImg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $iconImg);
        } else {
            $iconImg =  $request->get('oldImg');
        }
        $loginstatus = 0;
        if($request->get('loginstatus')==1) { $loginstatus = 1; }
        $sidemenu = Sidemenu::find($request->sid);
        $sidemenu->menu = $request->get('menu');
        $sidemenu->menuaction = $request->get('menuaction');
      	$sidemenu->loginstatus = $loginstatus;
        $sidemenu->menuicon = $iconImg;
       
        $sidemenu->update();
 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Side menu updated: ' . $request->get('menu'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/sidemenu/')->with('success', 'Side Menu has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $sidemenu = Sidemenu::find($id); 
      
        $menu = $sidemenu->menu;
        $sidemenu->menustatus = $status;
        $sidemenu->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Side Menu published/Unpublished: ' . $menu,
            'action' => $status,
            'contents' => json_encode($menu),
        ]);
        return redirect('/sidemenu/')->with('success', 'Menu Status has been updated successfully');
    }

    public function menuorder(Request $request)
    {
        
        $newOrder = $request->input('newOrder');
        foreach ($newOrder as $position => $itemId) {
            $sidemenu = Sidemenu::find($itemId);
            $sidemenu->menuorder = $position + 1;
            $sidemenu->update();
        }
        return response()->json(['message' => 'Order updated successfully']);
    }

    // Category Delete 
    public function destroy(Request $request)
    {
        if($request->get('oldImg')) {
            $destinationPath=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/sidemenu/';
            $imagePath = $destinationPath.$request->get('oldImg');
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $id = Route::current()->parameter('id');
        $sidemenu = Sidemenu::find($id); 
        $menu = $sidemenu->menu;
        $sidemenu->delete();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Side Menu deleted: ' . $menu,
            'action' => 'Delete',
            'contents' => json_encode($menu),
        ]);

        return redirect('/sidemenu/')->with('success', 'Side menu has been deleted successfully');
    }
    
    
    public function sidemenufetchData(Request $request)
{
    $columns = ['id', 'menuicon', 'menu', 'action'];

    // Request parameters
    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20); // Default 20 records per page
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderColumn = $columns[$orderColumnIndex] ?? 'id';
    $orderDirection = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    // Redis cache key
    $cacheKey = 'sidemenu:search_' . md5($searchValue);

    // Check if data exists in Redis cache
    $sidemenuData = Redis::get($cacheKey);

    if (!$sidemenuData || !is_array(json_decode($sidemenuData, true))) {
        $query = DB::table('sidemenu')->orderBy('menuorder', 'ASC');

        // Apply search filter if provided
        if (!empty($searchValue)) {
            $query->where('menu', 'like', '%' . $searchValue . '%');
        }

        // Fetch data from the database
        $sidemenuData = $query->get();

        // Cache the result in Redis for 5 minutes (300 seconds)
        Redis::setex($cacheKey, 300, json_encode($sidemenuData));
    } else {
        // Decode the cached data
        $sidemenuData = json_decode($sidemenuData);
    }

    // Pagination handling
    if (is_array($sidemenuData)) {
        $totalRecords = count($sidemenuData);
        $filteredData = array_slice($sidemenuData, $start, $length);
        $filteredData = collect($filteredData);
    } else {
        $totalRecords = $query->count();
        $filteredData = $query->skip($start)->take($length)->get();
    }

    // Row number counter for display
    $counter = $start + 1;

    // Format data
    $formattedData = $filteredData->map(function ($row) use (&$counter) {
        $row->rowNumber = $counter++;

        // Generate image URL for the menu icon
        $iconPath = $row->menuicon ? config('app.backendnewsurl') . '/public/uploads/sidemenu/' . $row->menuicon : null;
        $row->icon = $iconPath ? "<img src='{$iconPath}' style='width:30px;'/>" : null;
        $row->icon_url = $iconPath;

        // Action buttons
        $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->menustatus == '1' ? 'btn-success' : 'btn-warning') . '" 
               title="' . ($row->menustatus == '1' ? 'Visible' : 'Hidden') . '" 
               href="' . url('/sidemenu/updateStatus', [$row->id, $row->menustatus == '1' ? '0' : '1']) . '">
                <i class="bi ' . ($row->menustatus == '1' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/sidemenu/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/sidemenu/destroy', $row->id) . '" 
               onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { 
               document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/sidemenu/destroy', $row->id) . '" method="POST" style="display: none;">
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

