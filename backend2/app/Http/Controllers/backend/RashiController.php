<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Rashi;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class RashiController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('rashi.alllist');
    }

    // New Category Add
    public function add()
    {
        $data = array() ;
        return view('rashi.add', compact('data'));
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required',
            'engtitle' => 'required',
            'icon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rashiword'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The title field is required.',
            'engtitle.required' => 'The English title field is required.',
            'icon.image' => 'The uploaded file must be an image.',
            'icon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'icon.max' => 'The image size must not exceed 2 MB.',
            'rashiword.required' => 'The Rashi word field is required.',
        ];
        
        $request->validate($rules, $customMessages);

        $iconImg = '';
        if ($files = $request->file('rashiicon')) {
            // $destinationPath = public_path('/uploads/category/icon/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/rashi/icon/';
            $iconImg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $iconImg);
        }
        $rashi = new Rashi([
            'title' => $request->get('title'),
            'engtitle' => $request->get('engtitle'),
            'slug' => Str::slug($request->get('title'), '-'),
            'user_id'=> Auth::user()->id,
            'rashiicon'=> $iconImg,
            'rashiword'=> $request->get('rashiword')
        ]);
        
        $rashi->save(); 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/rashi/')->with('success', 'Rashi has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $rashi = Rashi::find($id); 
        $data =  array();
        $data['rashi'] =  $rashi;
        return view('rashi.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required',
            'engtitle' => 'required',
            'icon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rashiword'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The title field is required.',
            'engtitle.required' => 'The English title field is required.',
            'icon.image' => 'The uploaded file must be an image.',
            'icon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'icon.max' => 'The image size must not exceed 2 MB.',
            'rashiword.required' => 'The Rashi word field is required.',
        ];
        
        $request->validate($rules, $customMessages);
    
   
        if ($files = $request->file('rashiicon')) {
            // $destinationPath = public_path('/uploads/category/icon/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/rashi/icon/';
            if($request->get('oldImg')!='') {
                unlink($destinationPath.$request->get('oldImg'));
            }
            $iconImg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $iconImg);
        } else {
            $iconImg =  $request->get('oldImg');
        }
        $rashi = Rashi::find($request->rashiid);
        $rashi->title = $request->get('title');
        $rashi->engtitle = $request->get('engtitle');
        $rashi->slug = Str::slug($request->get('title'), '-');
        $rashi->user_id = Auth::user()->id;
        $rashi->rashiicon = $iconImg;
        $rashi->rashiword = $request->get('rashiword');
        $rashi->update();
       
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/rashi/')->with('success', 'Rashi has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $rashi = Rashi::find($id); 
        $rashititle = $rashi->title;
        $rashi->status = $status;
        $rashi->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi published/Unpublished: ' . $rashititle,
            'action' => $status,
            'contents' => json_encode($rashititle),
        ]);
        return redirect('/rashi/')->with('success', 'Rashi Status has been updated successfully');
    }

    // Rashi Delete 
    public function destroy(Request $request)
    {
        
        $id = Route::current()->parameter('id');
        $rashi = Rashi::find($id); 
        if($rashi->rashiicon) {
            $destinationPath=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/rashi/icon/';
            $imagePath = $destinationPath.$rashi->rashiicon;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $rashititle = $rashi->title;
        $rashi->delete();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi deleted: ' . $rashititle,
            'action' => 'Delete',
            'contents' => json_encode($rashititle),
        ]);

        return redirect('/rashi/')->with('success', 'Rashi has been deleted successfully');
    }
    
    public function rashifetchData(Request $request)
    {
        $columns = ['id', 'rashiicon', 'title','engtitle', 'rashiword', 'author_name', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
        $status = $request->input('status', 'Active'); // Default to Active

        $cacheKey = 'rashi:search_' .config('app.webtype') . ':' . $draw;
        $query = DB::table('rashifal as c1')
            ->select(
                'c1.*', // Select all columns from 'categories'
                'u1.name as author_name', // Author name from the users table
            )
            ->leftJoin('users as u1', 'c1.user_id', '=', 'u1.id') // Join for author info (user)
            ->orderBy('c1.id', 'DESC');
    
        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.title', 'like', '%' . $searchValue . '%')
                    ->orWhere('u1.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('c1.rashiword', 'like', '%' . $searchValue . '%');
            });
        }
    
        // Execute the query
        $rashiData = $query->get();
            
        // Handle pagination
        if (is_array($rashiData)) {
            // If data is from Redis, paginate manually
            $totalRecords = count($rashiData);
            $filteredData = array_slice($rashiData, $start, $length);
        
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
            $imgurl = config('app.backendnewsurl') . '/public/uploads/rashi/icon/' . $row->rashiicon;
            $row->rashiicon = "<img src=" . $imgurl . " style='width:30px;'/>"; // Image
            // Generate the image URL for the rashi icon
            $row->icon_url = $row->rashiicon ? config('app.backendnewsurl') . '/public/uploads/rashi/icon/' . $row->rashiicon : null;
            // Add the action button with the URL for viewing the rashi
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/rashi/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/rashi/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/rashi/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/rashi/destroy', $row->id) . '" method="POST" style="display: none;">
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

