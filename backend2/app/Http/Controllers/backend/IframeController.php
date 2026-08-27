<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Iframe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class IframeController extends Controller
{
    // Get all pages
    public function index()
    {
        return view('iframe.alllist');
        $iframe = DB::table('iframe')->select('iframe.*', 'u1.name as authorName')->leftJoin('users as u1', 'iframe.userID', '=', 'u1.id')->orderBy('id','DESC')->get();
        $data =  array();
        $data['iframe'] =  $iframe;
        return view('iframe.list', compact('data'));
    }

    // Add new page
    public function add()
    {
        return view('iframe.add');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'url'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The title field is required.',
            'url.required' => 'The url field is required.',
        ];
    
        $request->validate($rules, $customMessages);   
       
        $iframe = new Iframe([
            'title' => $request->get('title'),
            'url'=> $request->get('url'),
            'userID' => Auth::user()->id
        ]);
        
        $iframe->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'iframefetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Iframe added: ' . $request->get('question'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/iframe/')->with('success', 'Iframe has been added');
    }

    // Update Category
    public function edit($id)
    {
        $id = Route::current()->parameter('id');
        $iframe = Iframe::find($id); 
        $data =  array();
        $data['iframe'] =  $iframe;
        return view('iframe.edit', compact('data'));
    }

    public function update(Request $request)
    {
        
        $rules = [
            'title' => 'required',
            'url'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The title field is required.',
            'url.required' => 'The answer field is required.',
        ];
    
        $request->validate($rules, $customMessages);   
        
        $iframe = Iframe::find($request->id);
        $iframe->title = $request->get('title');
        $iframe->userID = Auth::user()->id;
        $iframe->url = $request->get('url');
                		
        $iframe->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'iframefetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Iframe updated: ' . $request->get('question'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        return redirect('/iframe/')->with('success', 'Iframe has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $iframe = Iframe::find($id);
        $iframe->status = $status;
        $iframe->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'iframefetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Iframe published/Unpublished: ' . $iframe->title,
            'action' => $status,
            'contents' => json_encode($iframe->title),
        ]);
        return redirect('/iframe/')->with('success', 'Iframe Status has been updated successfully');
    }

    // Dalete the page
    public function destroy($id)
    {
        $id = Route::current()->parameter('id');
        $iframe = Iframe::find($id);
        $iframetitle = $iframe->question;  
        $iframe->delete();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'iframefetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Iframe deleted: ' . $iframetitle,
            'action' => 'Delete',
            'contents' => json_encode($iframetitle),
        ]);
        return redirect('/iframe/')->with('success', 'Iframe has been deleted successfully');
    }

    public function iframefetchData1(Request $request)
    {
        $columns = ['id', 'title','url', 'authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query = DB::table('iframe')->select('iframe.*', 'u1.name as authorName')->leftJoin('users as u1', 'iframe.userID', '=', 'u1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('url', 'like', '%' . $searchValue . '%')
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
            
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/iframe/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/iframe/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/iframe/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/iframe/destroy', $row->id) . '" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="DELETE">
            </form>';

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
    public function iframefetchData(Request $request)
{
    $columns = ['id', 'title','url', 'authorName', 'action'];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20); // Default 20 records per page
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderColumn = $columns[$orderColumnIndex] ?? 'id';
    $orderDirection = $request->input('order.0.dir', 'DESC');

    // Create a unique cache key based on the search, pagination, and sorting parameters
    $cacheKey = 'iframefetchData:search_' . config('app.webtype') . ':' . $draw;

    // Check if the data is cached
    $cachedData = Redis::get($cacheKey);

    if ($cachedData) {
        // Data found in cache, return it
        $data = json_decode($cachedData, true);
    } else {
        // Query for fetching iframe data with author name
        $query = DB::table('iframe')->select('iframe.*', 'u1.name as authorName')->leftJoin('users as u1', 'iframe.userID', '=', 'u1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('url', 'like', '%' . $searchValue . '%')
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
            // Assign row number and increment counter
            $row->rowNumber = $counter++;

            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/iframe/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/iframe/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/iframe/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/iframe/destroy', $row->id) . '" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="DELETE">
            </form>';

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
