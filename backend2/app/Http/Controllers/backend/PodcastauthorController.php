<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Podcastauthor;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class PodcastauthorController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('podcastauthor.alllist');
       
    }

    // New Category Add
    public function add()
    {
        
        return view('podcastauthor.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'authorname' => 'required',
            'authorimg' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];

        $customMessages = [
            'authorname.required' => 'The author name field is required.',
            'authorimg.image' => 'The uploaded file must be an image.',
            'authorimg.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'authorimg.max' => 'The image size must not exceed 2 MB.'
        ];
        
        $request->validate($rules, $customMessages);

        $authorimg = '';
        if ($files = $request->file('authorimg')) {
             $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            $authorimg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $authorimg);
        }
        
        $podcastauthor = new Podcastauthor([
            'authorname' => $request->get('authorname'),
            'authorengname' => $request->get('authorengname'),
            'authorsubtitle' => $request->get('authorsubtitle'),
            'authordesc' => $request->get('authordesc'),
            'authorslug' => Str::slug($request->get('authorengname'), '-'),
            'userID'=> Auth::user()->id,
            'authorimg'=> $authorimg
        ]);
        
        $podcastauthor->save();
        

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Podcastauthor: ' . $request->get('authorname'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/podcastauthor/')->with('success', 'Podcast Author has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $podcastauthor = Podcastauthor::find($id); 
        $data =  array();
        $data['podcastauthor'] =  $podcastauthor;
        return view('podcastauthor.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'authorname' => 'required',
            'authorimg' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];

        $customMessages = [
            'authorname.required' => 'The author name field is required.',
            'authorimg.image' => 'The uploaded file must be an image.',
            'authorimg.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'authorimg.max' => 'The image size must not exceed 2 MB.'
        ];
        
        $request->validate($rules, $customMessages);

        
        if ($files = $request->file('authorimg')) {
            // $destinationPath = public_path('/uploads/category/icon/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            if ($request->get('oldImg') != '') {
                $filePath = $destinationPath . $request->get('oldImg');
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $authorimg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $authorimg);
        } else {
            $authorimg =  $request->get('oldImg');
        }
        
        $podcastauthor = Podcastauthor::find($request->podcastauthorid);
        $podcastauthor->authorname = $request->get('authorname');
        $podcastauthor->authorengname = $request->get('authorengname');
        $podcastauthor->authorsubtitle = $request->get('authorsubtitle');
        $podcastauthor->authordesc = $request->get('authordesc');
        $podcastauthor->authorslug = Str::slug($request->get('authorengname'), '-');
        $podcastauthor->authorimg = $authorimg;
       
        $podcastauthor->update();
 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Podcast author updated: ' . $request->get('authorname'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/podcastauthor/')->with('success', 'Podcast author has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $podcastauthor = Podcastauthor::find($id); 
        $authorname = $podcastauthor->authorname;
        $podcastauthor->status = $status;
        $podcastauthor->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Podcast author published/Unpublished: ' . $authorname,
            'action' => $status,
            'contents' => json_encode($authorname),
        ]);
        return redirect('/podcastauthor/')->with('success', 'Podcast author status has been updated successfully');
    }

    
    // Category Delete 
    public function destroy(Request $request)
    {
        if($request->get('oldImg')) {
            $destinationPath=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            $imagePath = $destinationPath.$request->get('oldImg');
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $id = Route::current()->parameter('id');
        $podcastauthor = Podcastauthor::find($id); 
        $authorname = $podcastauthor->authorname;
        $podcastauthor->delete();
        

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Podcast author deleted: ' . $authorname,
            'action' => 'Delete',
            'contents' => json_encode($authorname),
        ]);

        return redirect('/podcastauthor/')->with('success', 'Podcast author has been deleted successfully');
    }
    
    public function podcastauthorfetchData(Request $request)
    {
        $columns = ['id','authorname', 'authorengname', 'authorimg', 'author_name', 'viewer', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

        
          	
          	$query = DB::table('podcast_author as c1')
            ->select(
                'c1.*', 
                'u1.name as author_name'
            )
            ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id') // Join for author info (user)
            ->orderBy('c1.id', 'DESC');
    
        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.authorname', 'like', '%' . $searchValue . '%')
                    ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
            });
        }
    
        // Execute the query
        $podcastData = $query->get();
        // Handle pagination
        if (is_array($podcastData)) {
            // If data is from Redis, paginate manually
            $totalRecords = count($podcastData);
            $filteredData = array_slice($podcastData, $start, $length);
        
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
            $row->viewer = $row->viewer;
            $authorimg = $row->authorimg;
            if($row->authorimg == ""){
                $authorimg = 'author.png';
            }
            $imgurl = config('app.backendnewsurl') . '/public/uploads/podcast/' . $authorimg;
            $row->authorimg = "<img src=" . $imgurl . " style='width:30px;'/>"; // Image
          
          
           
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/podcastauthor/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/podcastauthor/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/podcastauthor/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/podcastauthor/destroy', $row->id) . '" method="POST" style="display: none;">
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

