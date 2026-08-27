<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class TagController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('tags.alllist');   
    }

    // New Category Add
    public function add()
    {
       return view('tags.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required',
        ];

        $customMessages = [
            'title.required' => 'The tag title field is required.',
        ];
    
        $request->validate($rules, $customMessages);   

        $tag = new Tag([
            'userID'=> Auth::user()->id,
            'title' => $request->get('title'),
            'status' => 'Active',
        ]);
        
        $tag->save();
      
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Tags added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/tags/')->with('success', 'Tag has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $tag = Tag::find($id); 
        $data =  array();
        $data['tag'] =  $tag;
        return view('tags.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required',
        ];

        $customMessages = [
            'title.required' => 'The tag title field is required.',
        ];
    
        $request->validate($rules, $customMessages);   

       
        $tag = Tag::find($request->mid);
        $tag->title = $request->get('title');
        $tag->userID = Auth::user()->id;
       
        $tag->update();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Tag updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        return redirect('/tags/')->with('success', 'Tag has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $tag = Tag::find($id); 
        $title = $tag->title;
        $tag->status = $status;
        $tag->update();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Tag published/Unpublished: ' . $title,
            'action' => $status,
            'contents' => json_encode($title),
        ]);

        return redirect('/tags/')->with('success', 'Tag Status has been updated successfully');
    }

    // Category Delete 
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $tag = Tag::find($id); 
        $title = $tag->title;
        $tag->delete();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Tag deleted: ' . $title,
            'action' => 'Delete',
            'contents' => json_encode($title),
        ]);
        return redirect('/tags/')->with('success', 'Tag has been deleted successfully');
    }
    public function tagsfetchData(Request $request)
    {
        $columns = ['id', 'tagsEnglishTitle','authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
        $status = $request->input('status', 'Active'); // Default to Active

        // Create a unique cache key based on the search query parameters
        $cacheKey = 'tags:search_' . config('app.webtype') . ':' . $draw;

        // Check if the data is cached
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Data found in cache, return it
            $data = json_decode($cachedData, true);
        } else {
            // Query the database if data is not cached
            $query = DB::table('tags as c1')
                ->select('c1.*', 'u1.name as authorName')
                ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id');
            
            // Apply search filter if any search value is provided
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('c1.tagsEnglishTitle', 'like', '%' . $searchValue . '%')
                    ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
                });
            }

            if (!empty($status)) {
                $query->where('c1.status', $status);
            }

            // Get the total number of filtered records
            $totalRecords = $query->count();

            // Apply pagination
            $filteredData = $query
            ->orderBy("c1.$orderColumn", $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

            $counter = $start + 1;
            // Map the data to add the 'action' column and other transformations
            $formattedData = $filteredData->map(function ($row) use (&$counter) {
                // Assign row number and increment counter
                $row->rowNumber = $counter++;
                // Add action buttons
                $row->action = '
                    <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/tags/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                        <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
                    </a>
                    <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/tags/edit', $row->id) . '">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/tags/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                        <i class="bi bi-trash"></i>
                    </a>
                    <form id="delete-form-' . $row->id . '" action="' . url('/tags/destroy', $row->id) . '" method="POST" style="display: none;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                    </form>';

                return $row;
            });

            // Prepare the data for response
            $data = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecords, // Send the total filtered records count
                "data" => $formattedData,
            ];

            // Cache the data for 5 minutes (300 seconds)
            Redis::setex($cacheKey, 300, json_encode($data));
        }

        // Return the data (either from cache or from database)
        return response()->json($data);
    }

}

