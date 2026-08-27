<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Newsflash;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class NewsflashController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('newsflash.alllist');
    }

    // New Category Add
    public function add()
    {
        return view('newsflash.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required'
        ];

        $customMessages = [
            'title.required' => 'The title field is required.'
        ];
        
        $request->validate($rules, $customMessages);

        $newsflash = new Newsflash([
            'title' => $request->get('title'),
        ]);
        
        $newsflash->save();
     
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'News Flash added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        
        return redirect('/newsflash/')->with('success', 'News Flash has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $newsflash = Newsflash::find($id); 
        
        $data =  array();
        $data['newsflash'] =  $newsflash;
        return view('newsflash.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required'
        ];

        $customMessages = [
            'title.required' => 'The title field is required.'
        ];
        
        $request->validate($rules, $customMessages);
      
       
        $newsflash = Newsflash::find($request->sid);
        $newsflash->title = $request->get('title');
       
        $newsflash->update();
 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'News Flash updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/newsflash/')->with('success', 'News Flash has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $newsflash = Newsflash::find($id); 
        
        $title = $newsflash->title;
        $newsflash->status = $status;
        $newsflash->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'News Flash published/Unpublished: ' . $title,
            'action' => $status,
            'contents' => json_encode($title),
        ]);
        return redirect('/newsflash/')->with('success', 'News Flash Status has been updated successfully');
    }

    
    // Category Delete 
    public function destroy(Request $request)
    {
       
        $id = Route::current()->parameter('id');
        $newsflash = Newsflash::find($id); 
        $title = $newsflash->title;
        $newsflash->delete();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'News Flash deleted: ' . $title,
            'action' => 'Delete',
            'contents' => json_encode($title),
        ]);

        return redirect('/newsflash/')->with('success', 'News Flash has been deleted successfully');
    }
    
    
    public function newsflashfetchData(Request $request)
{
    $columns = ['id', 'title', 'action'];

    // Request parameters
    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20); // Default 20 records per page
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderColumn = $columns[$orderColumnIndex] ?? 'id';
    $orderDirection = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    // Redis cache key
      $query = DB::table('newsflash')->orderBy('id', 'desc');

        // Apply search filter if provided
        if (!empty($searchValue)) {
            $query->where('title', 'like', '%' . $searchValue . '%');
        }

        // Fetch data from the database
        $newsflashData = $query->get();

        // Cache the result in Redis for 5 minutes (300 seconds)
      

    // Pagination handling
    if (is_array($newsflashData)) {
        $totalRecords = count($newsflashData);
        $filteredData = array_slice($newsflashData, $start, $length);
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

       
        // Action buttons
        $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == '1' ? 'btn-success' : 'btn-warning') . '" 
               title="' . ($row->status == '1' ? 'Visible' : 'Hidden') . '" 
               href="' . url('/newsflash/updateStatus', [$row->id, $row->status == '1' ? '0' : '1']) . '">
                <i class="bi ' . ($row->status == '1' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/newsflash/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/newsflash/destroy', $row->id) . '" 
               onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { 
               document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/newsflash/destroy', $row->id) . '" method="POST" style="display: none;">
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

