<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Graphic;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class AdsgraphicsController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('graphic.alllist');
        $graphic = DB::table('graphic')
                    ->orderBy('id','DESC')
                    ->get();
        $data =  array();
        $data['graphic'] =  $graphic;
        return view('graphic.list', compact('data'));
    }

    // New Category Add
    public function add()
    {
        return view('graphic.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required',
            'icon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];

        $customMessages = [
            'title.required' => 'The Ads title field is required.',
            'icon.image' => 'The uploaded file must be an image.',
            'icon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'icon.max' => 'The image size must not exceed 2 MB.',
        ];
    
        $request->validate($rules, $customMessages);

        $iconImg = '';
        if ($files = $request->file('icon')) {
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/graphic/";
            //$destinationPath = public_path('/uploads/graphic/'); // upload path
            $iconImg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $iconImg);
        }
        $graphic = new Graphic([
            'title' => $request->get('title'),
            'images'=> $iconImg,
            'sitelink'=> $request->get('link')
        ]);
        
        $graphic->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'adsgraphicsfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Ads Graphic added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/graphic/')->with('success', 'Ads graphic has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $graphic = Graphic::find($id); 
        $data =  array();
        $data['graphic'] =  $graphic;
        return view('graphic.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required',
            'icon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];

        $customMessages = [
            'title.required' => 'The Category title field is required.',
            'icon.image' => 'The uploaded file must be an image.',
            'icon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'icon.max' => 'The image size must not exceed 2 MB.'
        ];
    
        $request->validate($rules, $customMessages);

       
        if ($files = $request->file('icon')) {
            //$destinationPath = public_path('/uploads/graphic/'); // upload path
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/graphic/";
            /*if($request->get('oldImg')!='') {
                unlink($destinationPath.$request->get('oldImg'));
            }*/
            $iconImg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $iconImg);
        } else {
            $iconImg =  $request->get('oldImg');
        }
        $graphic = Graphic::find($request->gid);
        $graphic->title = $request->get('title');
        $graphic->images = $iconImg;
        $graphic->sitelink = $request->get('link');
       
        $graphic->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'adsgraphicsfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Ads graphic updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/graphic/')->with('success', 'Ads graphic has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $graphic = Graphic::find($id); 
        $graphictitle = $graphic->title;
        $graphic->status = $status;
        $graphic->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'adsgraphicsfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Ads Graphic published/Unpublished: ' . $graphictitle,
            'action' => $status,
            'contents' => json_encode($graphictitle),
        ]);
        return redirect('/graphic/')->with('success', 'Ads graphic Status has been updated successfully');
    }
    public function adsgraphicsfetchData1(Request $request)
    {
        $columns = ['id', 'images', 'title', 'sitelink', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query = DB::table('graphic');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('images', 'like', '%' . $searchValue . '%')
                ->orWhere('title', 'like', '%' . $searchValue . '%');
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Map the data to add 'result' and 'action' columns
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $imgurl = config('app.backendnewsurl') . '/public/uploads/graphic/' . $row->images;

            // Check if image exists and create image HTML
            $imageHtml = '';
            if ($row->images) {
                $imageHtml = '<img src="' . $imgurl . '" style="width:30px;" />';
            }
            $row->images = $imageHtml;
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
 
            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == '1' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == '1' ? 'Show' : 'Hidden') . '" href="' . url('/graphic/updateStatus/' . ($row->status == '1' ? '0' : '1'), $row->id) . '">
                <i class="bi ' . ($row->status == '1' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/graphic/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>';

            return $row;
        });

        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ]);
    }
    public function adsgraphicsfetchData(Request $request)
    {
        $columns = ['id', 'images', 'title', 'sitelink', 'action'];
    
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
    
        // Create a unique cache key based on the search, pagination, and sorting parameters
        $cacheKey = 'adsgraphicsfetchData:search_' . config('app.webtype') . ':' . $draw;
    
        // Check if the data is cached
        $cachedData = Redis::get($cacheKey);
    
        if ($cachedData) {
            // Data found in cache, return it
            $data = json_decode($cachedData, true);
        } else {
            $query = DB::table('graphic');
    
            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('images', 'like', '%' . $searchValue . '%')
                    ->orWhere('title', 'like', '%' . $searchValue . '%');
                });
            }
    
            // Get the total number of filtered records
            $totalRecords = $query->count();
    
            // Apply pagination
            $filteredData = $query->orderBy($orderColumn, $orderDirection)
                ->skip($start)
                ->take($length)
                ->get();
    
            $counter = $start + 1;
    
            // Map the data to add 'result' and 'action' columns
            $formattedData = $filteredData->map(function ($row) use (&$counter) {
                $imgurl = config('app.backendnewsurl') . '/public/uploads/graphic/' . $row->images;
    
                // Check if image exists and create image HTML
                $imageHtml = '';
                if ($row->images) {
                    $imageHtml = '<img src="' . $imgurl . '" style="width:30px;" />';
                }
                $row->images = $imageHtml;
                // Assign row number and increment counter
                $row->rowNumber = $counter++;
    
                // Add action buttons
                $row->action = '
                <a class="btn iconbtn showhideCls ' . ($row->status == '1' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == '1' ? 'Show' : 'Hidden') . '" href="' . url('/graphic/updateStatus/' . ($row->status == '1' ? '0' : '1'), $row->id) . '">
                    <i class="bi ' . ($row->status == '1' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
                </a>
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/graphic/edit', $row->id) . '">
                    <i class="bi bi-pencil"></i>
                </a>';
    
                return $row;
            });
    
            // Prepare the data for response
            $data = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecords,
                "data" => $formattedData,
            ];
    
            // Cache the data for 5 minutes (300 seconds)
            Redis::setex($cacheKey, 300, json_encode($data));
        }
    
        // Return the data (either from cache or from database)
        return response()->json($data);
    }
}

