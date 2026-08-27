<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class ModuleController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('module.alllist');    
        $module = DB::table('modules')->get();
        $data =  array();
        $data['module'] =  $module;
        return view('module.list', compact('data'));
    }

    // New Category Add
    public function add()
    {
       return view('module.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required',
            'moduleLink' => 'required'
        ];

        $customMessages = [
            'title.required' => 'The module title field is required.',
            'moduleLink.required' => 'The module link field is required.',
        ];
    
        $request->validate($rules, $customMessages);   

        $module = new Module([
            'title' => $request->get('title'),
            'moduleLink' => $request->get('moduleLink')
        ]);
        
        $module->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'modules:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Module added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/module/')->with('success', 'Module has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $module = Module::find($id); 
        $data =  array();
        $data['module'] =  $module;
        return view('module.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required',
            'moduleLink' => 'required'
        ];

        $customMessages = [
            'title.required' => 'The module title field is required.',
            'moduleLink.required' => 'The module link field is required.',
        ];
    
        $request->validate($rules, $customMessages);   

       
        $module = Module::find($request->mid);
        $module->title = $request->get('title');
        $module->moduleLink = $request->get('moduleLink');
       
        $module->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'modules:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Module updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        return redirect('/module/')->with('success', 'Module has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $module = Module::find($id); 
        $moduletitle = $module->title;
        $module->status = $status;
        $module->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'modules:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Module published/Unpublished: ' . $moduletitle,
            'action' => $status,
            'contents' => json_encode($moduletitle),
        ]);

        return redirect('/module/')->with('success', 'Module Status has been updated successfully');
    }

    // Category Delete 
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $module = Module::find($id); 
        $moduletitle = $module->title;
        $module->delete();

        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'modules:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Module deleted: ' . $moduletitle,
            'action' => 'Delete',
            'contents' => json_encode($moduletitle),
        ]);
        return redirect('/module/')->with('success', 'Module has been deleted successfully');
    }
    public function modulefetchData1(Request $request)
    {
        $columns = ['id', 'title', 'moduleLink', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

     
        $query = DB::table('modules');

        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('title', 'like', '%' . $searchValue . '%')
                    ->orWhere('moduleLink', 'like', '%' . $searchValue . '%');
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
            // Generate the image URL for the category icon
            // Add the action button with the URL for viewing the category
            $row->action = '
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/module/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/module/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/module/destroy', $row->id) . '" method="POST" style="display: none;">
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
    public function modulefetchData(Request $request)
    {
        $columns = ['id', 'title', 'moduleLink', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

        $cacheKey = 'modules:search_'. config('app.webtype') . ':' . $draw;

        // Retrieve cached data or query the database
        $moduleData = Redis::get($cacheKey);

        if (!$moduleData) {
            $query = DB::table('modules');

            // Apply search filter if any search value is provided
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('title', 'like', '%' . $searchValue . '%')
                        ->orWhere('moduleLink', 'like', '%' . $searchValue . '%');
                });
            }

            // Fetch data and cache it
            $moduleData = $query->get();
            Redis::setex($cacheKey, 300, json_encode($moduleData));
        } else {
            $moduleData = json_decode($moduleData, true); // Decode Redis data as an array
        }

        // Handle pagination
        if (is_array($moduleData)) {
            // Manual pagination for data from Redis
            $totalRecords = count($moduleData);
            $filteredData = array_slice($moduleData, $start, $length);
            $filteredData = collect($filteredData); // Convert to Laravel collection
        } else {
            // If data is from the DB query, apply pagination
            $totalRecords = DB::table('modules')->count();
            $filteredData = DB::table('modules')
                ->skip($start)
                ->take($length)
                ->get();
        }

        $counter = $start + 1;

        // Map the data to add the 'action' column and other transformations
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Ensure row is an object
            if (is_array($row)) {
                $row = (object) $row; // Convert array to object
            }

            // Assign row number and increment counter
            $row->rowNumber = $counter++;

            // Add the action button
            $row->action = '
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/module/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/module/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/module/destroy', $row->id) . '" method="POST" style="display: none;">
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

