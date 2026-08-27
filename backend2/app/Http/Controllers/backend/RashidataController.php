<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Rashifaldata;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;


class RashidataController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('rashidata.alllist');
    }

    // New Category Add
    public function add()
    {
        $rashifal = DB::table('rashifal')->where('status', 'Active')->get();
        $data =  array();
        $data['rashi'] =  $rashifal;
        return view('rashidata.add', compact('data'));
    }
    public function store(Request $request)
    {
        
        $rules = [
            'rashifal_id' => 'required',
            'authorname' => 'required',
            'authoricon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rashidescription'=>'required'
        ];

        $customMessages = [
            'rashifal_id.required' => 'Select Rashi field is required.',
            'authorname.required' => 'The author name field is required.',
            'authoricon.image' => 'The uploaded file must be an image.',
            'authoricon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'authoricon.max' => 'The image size must not exceed 2 MB.',
            'rashidescription.required' => 'The Rashi word field is required.',
        ];
        
        $request->validate($rules, $customMessages);

        $iconImg = '';
        if ($files = $request->file('authoricon')) {
            // $destinationPath = public_path('/uploads/category/icon/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/rashi/author/';
            $iconImg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $iconImg);
        }
        $rashi = new Rashifaldata([
            'rashifal_id' => $request->get('rashifal_id'),
            'authorname' => $request->get('authorname'),
            'rashidescription' => $request->get('rashidescription'),
            'slug' => Str::slug($request->get('authorname'), '-'),
            'user_id'=> Auth::user()->id,
            'authoricon'=> $iconImg,
        ]);
        
        $rashi->save(); 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi Data added: ' . $request->get('authorname'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/rashidata/')->with('success', 'Rashi data has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $Rashifaldata = Rashifaldata::find($id); 
        $data =  array();
        $data['rashidata'] =  $Rashifaldata;
        $rashi = DB::table('rashifal')->where('status', 'Active')->where('id',$Rashifaldata->rashifal_id)->get();
        $data['rashi'] =  $rashi;
        return view('rashidata.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'rashifal_id' => 'required',
            'authorname' => 'required',
            'authoricon' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rashidescription'=>'required'
        ];

        $customMessages = [
            'rashifal_id.required' => 'Select Rashi field is required.',
            'authorname.required' => 'The author name field is required.',
            'authoricon.image' => 'The uploaded file must be an image.',
            'authoricon.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'authoricon.max' => 'The image size must not exceed 2 MB.',
            'rashidescription.required' => 'The Rashi word field is required.',
        ];
        
        $request->validate($rules, $customMessages);
    
   
        if ($files = $request->file('authoricon')) {
            // $destinationPath = public_path('/uploads/category/icon/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/rashi/author/';
            if($request->get('oldImg')!='') {
                unlink($destinationPath.$request->get('oldImg'));
            }
            $iconImg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $iconImg);
        } else {
            $iconImg =  $request->get('oldImg');
        }
        $rashi = Rashifaldata::find($request->rashifalid);
        $rashi->rashifal_id = $request->get('rashifal_id');
        $rashi->authorname = $request->get('authorname');
        $rashi->rashidescription = $request->get('rashidescription');
        $rashi->slug = Str::slug($request->get('authorname'), '-');
        $rashi->user_id = Auth::user()->id;
        $rashi->authoricon = $iconImg;
        $rashi->update();
       
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi updated: ' . $request->get('authorname'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/rashidata/')->with('success', 'Rashi data has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $Rashifaldata = Rashifaldata::find($id); 
        $authorname = $Rashifaldata->authorname;
        $Rashifaldata->status = $status;
        $Rashifaldata->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi published/Unpublished: ' . $authorname,
            'action' => $status,
            'contents' => json_encode($authorname),
        ]);
        return redirect('/rashidata/')->with('success', 'Rashi Status has been updated successfully');
    }

    // Rashi Delete 
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $Rashifaldata = Rashifaldata::find($id); 
        if($Rashifaldata->authoricon) {
            $destinationPath=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/rashi/author/';
            $imagePath = $destinationPath.$Rashifaldata->authoricon;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $authorname= $Rashifaldata->authorname;
        $Rashifaldata->delete();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi Data deleted: ' . $authorname,
            'action' => 'Delete',
            'contents' => json_encode($authorname),
        ]);

        return redirect('/rashidata/')->with('success', 'Rashi Data has been deleted successfully');
    }
    public function rashidatafetchData(Request $request)
    {
        $columns = ['id', 'rashifal_id','authorname', 'authoricon','rashidescription', 'author_name','created_at', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
        $status = $request->input('status', 'Active'); // Default to Active

        $cacheKey = 'rashidata:search_' .config('app.webtype') . ':' . $draw;
 
        $query = DB::table('rashifaldata as c1')
            ->select(
                'c1.*', // Select all columns from 'categories'
                'u1.name as author_name', // Author name from the users table
                'r.title'
            )
            ->leftJoin('rashifal as r', 'c1.rashifal_id', '=', 'r.id')
            ->leftJoin('users as u1', 'c1.user_id', '=', 'u1.id') // Join for author info (user)
            ->orderBy('c1.id', 'DESC');
    
        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.authorname', 'like', '%' . $searchValue . '%')
                    ->orWhere('u1.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('c1.rashidescription', 'like', '%' . $searchValue . '%');
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
            $row->rashifal_id = $row->title;
            $row->viewer = $row->viewer + $row->viewer_app;
            $authoricon = $row->authoricon;
            if($row->authoricon == ""){
                $authoricon = 'author.png';
            }
            $imgurl = config('app.backendnewsurl') . '/public/uploads/rashi/author/' . $authoricon;
            $row->authoricon = "<img src=" . $imgurl . " style='width:30px;'/>"; // Image
            $row->viewer = Carbon::parse($row->created_at)->format('d M Y h:i A');
            // Generate the image URL for the rashi icon
            // $row->icon_url = $row->rashiicon ? config('app.backendnewsurl') . '/public/uploads/rashi/author/' . $row->rashiicon : null;
            // Add the action button with the URL for viewing the rashi
            // <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/rashidata/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
            //     <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            // </a>
            $row->action = '
            
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/rashidata/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/rashidata/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/rashidata/destroy', $row->id) . '" method="POST" style="display: none;">
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
    public function rashidatafetchData111(Request $request)
    {
        $columns = ['id', 'rashifal_id','authorname', 'authoricon','rashidescription', 'author_name','viewer', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
        $status = $request->input('status', 'Active'); // Default to Active

        $cacheKey = 'rashidata:search_' .config('app.webtype') . ':' . $draw;
 
        $query = DB::table('rashifaldata as c1')
            ->select(
                'c1.*', // Select all columns from 'categories'
                'u1.name as author_name', // Author name from the users table
                'r.title'
            )
            ->leftJoin('rashifal as r', 'c1.rashifal_id', '=', 'r.id')
            ->leftJoin('users as u1', 'c1.user_id', '=', 'u1.id') // Join for author info (user)
            ->orderBy('c1.id', 'DESC');
    
        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.authorname', 'like', '%' . $searchValue . '%')
                    ->orWhere('u1.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('c1.rashidescription', 'like', '%' . $searchValue . '%');
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
            $row->rashifal_id = $row->title;
            $row->viewer = $row->viewer + $row->viewer_app;
            $authoricon = $row->authoricon;
            if($row->authoricon == ""){
                $authoricon = 'author.png';
            }
            $imgurl = config('app.backendnewsurl') . '/public/uploads/rashi/author/' . $authoricon;
            $row->authoricon = "<img src=" . $imgurl . " style='width:30px;'/>"; // Image
            // Generate the image URL for the rashi icon
            // $row->icon_url = $row->rashiicon ? config('app.backendnewsurl') . '/public/uploads/rashi/author/' . $row->rashiicon : null;
            // Add the action button with the URL for viewing the rashi
            // <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/rashidata/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
            //     <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            // </a>
            $row->action = '
            
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/rashidata/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/rashidata/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/rashidata/destroy', $row->id) . '" method="POST" style="display: none;">
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

