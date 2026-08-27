<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Adsprice;
use App\Models\Podcastauthor;
use Illuminate\Support\Facades\Auth;
use Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use File;
use Image;
use Illuminate\Support\Facades\Redis;
use Imagick;
use App\Services\FirebaseService;

class AdspriceController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('adsprice.alllist');
    }

    // New Category Add
    public function add()
    {
        $adsprice = DB::table('adsprice')->where('status', 'Active')->get();
        $data =  array();
        $data['adsprice'] =  $adsprice;
        return view('adsprice.add', compact('data'));
    }
    public function store(Request $request)
    {
        // Validation rules and custom messages
        $rules = [
            'ads_position' => 'required',
            'price' => 'required',
        ];

        $customMessages = [
            'ads_position.required' => 'The title field is required.',
            'price.required' => 'The title field is required.'
        ];

        $request->validate($rules, $customMessages);
        $adsprice = new Adsprice([
            'ads_position' => $request->get('ads_position'),
            'price' => $request->get('price'),
            'userID'=>Auth::user()->id,
            'status'=> 'Active',
        ]);
        
        $adsprice->save(); 
        // Log the action
        \App\Models\Log::create([
            'userID' => Auth::user()->id,
            'message' => 'Ads Price added: ' . $request->get('ads_position'),
            'action' => 'Add',
            'contents' => json_encode($request->all())
        ]);

        return redirect('/adsprice/')->with('success', 'Podcast has been added');
    }

    
    
    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $adsprice = Adsprice::where('id', $id)->first(); 
        $data =  array();
        $data['adsprice'] =  $adsprice;
        return view('adsprice.edit', compact('data'));
    }

    public function update(Request $request)
    {
       $rules = [
            'ads_position' => 'required',
            'price' => 'required',
        ];

        $customMessages = [
            'ads_position.required' => 'The title field is required.',
            'price.required' => 'The price field is required.',
        ];

        $request->validate($rules, $customMessages); 
        
        $adsprice = Adsprice::find($request->get('adspriceid'));
        $adsprice->ads_position = $request->get('ads_position');
        $adsprice->price = $request->get('price');
        $adsprice->update();
       
        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Ads Price updated: ' . $request->get('ads_position'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/adsprice/')->with('success', 'Adsprice has been updated successfully.');
    }
  public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $adsprice = Adsprice::find($id); 
       
        $ads_position= $adsprice->ads_position;
        $adsprice->delete();

        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Ads price Data deleted: ' . $ads_position,
            'action' => 'Delete',
            'contents' => json_encode($ads_position),
        ]);

        return redirect('/adsprice/')->with('success', 'Adsprice has been deleted successfully');
    }
    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $adsprice = Adsprice::find($id); 
        $ads_position = $adsprice->ads_position;
        $adsprice->status = $status;
        $adsprice->update();
      
        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Ads price  published/Unpublished: ' . $ads_position,
            'action' => $status,
            'contents' => json_encode($ads_position),
        ]);
        return redirect('/adsprice/')->with('success', 'Adsprice Status has been updated successfully');
    }

    
    public function adspricefetchData(Request $request)
    {
        $columns = ['id', 'ads_position','price','authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a unique Redis cache key
        $cacheKey = 'adspricefetchData:search_' . config('app.webtype') . ':' . $draw;

        // Check if data exists in Redis
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Return cached data if exists
            return response()->json(json_decode($cachedData));
        }

        // Base query
        $query = DB::table('adsprice')
            ->select(
                'adsprice.*',
                'u1.name as authorName',
            )
            ->leftJoin('users as u1', 'adsprice.userID', '=', 'u1.id');
        
        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('ads_position', 'like', '%' . $searchValue . '%');
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination and order
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Map the data to add row numbers and action buttons
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
           	// $row->authorName = $row->authorName; 
            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" 
            title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" 
            href="' . url('/adsprice/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/adsprice/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/adsprice/destroy', $row->id) . '" 
            onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { 
                document.getElementById(\'delete-form-' . $row->id . '\').submit(); 
            }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/adsprice/destroy', $row->id) . '" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="DELETE">
            </form>';

            return $row;
        });

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ];

        // Store the result in Redis with a 5-minute expiration
        Redis::setex($cacheKey, 300, json_encode($response));

        // Return the response
        return response()->json($response);
    }


}

