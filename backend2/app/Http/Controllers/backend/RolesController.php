<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Route;
use Illuminate\Support\Facades\Redis;

class RolesController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('roles.alllist'); 
        $role = DB::table('roles')->get();
        $data =  array();
        $data['role'] =  $role;
        return view('roles.list', compact('data'));
    }

    // New Category Add
    public function add()
    {
       return view('roles.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required',
        ];

        $customMessages = [
            'title.required' => 'The role title field is required.',
        ];
    
        $request->validate($rules, $customMessages);  

        $viewRole = 0;
        if($request->get('viewRole')==1) { $viewRole = 1; }
        $addRole = 0;
        if($request->get('addRole')==1) { $addRole = 1; }
        $editRole = 0;
        if($request->get('editRole')==1) { $editRole = 1; }
        $deleteRole = 0;
        if($request->get('deleteRole')==1) { $deleteRole = 1; }
        $publishRole = 0;
        if($request->get('publishRole')==1) { $publishRole = 1; }

        $role = new Role([
            'title' => $request->get('title'),
            'viewRole' => $viewRole,
            'addRole' => $addRole,
            'editRole' => $editRole,
            'deleteRole' => $deleteRole,
            'publishRole' => $publishRole
        ]);
        
        $role->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'roles:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return redirect('/roles/')->with('success', 'Role has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $role = Role::find($id); 
        $data =  array();
        $data['role'] =  $role;
        return view('roles.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required'
        ];

        $customMessages = [
            'title.required' => 'The role title field is required.',
        ];
    
        $request->validate($rules, $customMessages);

        
        $role = Role::find($request->rid);
        $viewRole = 0;
        if($request->get('viewRole')==1) { $viewRole = 1; }
        $addRole = 0;
        if($request->get('addRole')==1) { $addRole = 1; }
        $editRole = 0;
        if($request->get('editRole')==1) { $editRole = 1; }
        $deleteRole = 0;
        if($request->get('deleteRole')==1) { $deleteRole = 1; }
        $publishRole = 0;
        if($request->get('publishRole')==1) { $publishRole = 1; }

        $role->title = $request->get('title');
        $role->viewRole = $viewRole;
        $role->addRole = $addRole;
        $role->editRole = $editRole;
        $role->deleteRole = $deleteRole;
        $role->publishRole = $publishRole;
       
        $role->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'roles:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
 
        return redirect('/roles/')->with('success', 'Role has been updated successfully.');
    }
    public function rolesfetchData1(Request $request)
    {
        $columns = ['id','title', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

     
        $query = DB::table('roles');

        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('title', 'like', '%' . $searchValue . '%');
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
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/roles/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>';

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
    public function rolesfetchData(Request $request)
{
    $columns = ['id', 'title', 'action'];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20); // Default 20 records per page
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderColumn = $columns[$orderColumnIndex] ?? 'id';
    $orderDirection = $request->input('order.0.dir', 'asc');
    $status = $request->input('status', 'Active'); // Default to Active

    // Create a unique cache key based on the search query parameters
    $cacheKey = 'roles:search_' . config('app.webtype') . ':' . $draw;

    // Check if the data is cached
    $cachedData = Redis::get($cacheKey);

    if ($cachedData) {
        // Data found in cache, return it
        $data = json_decode($cachedData, true);
    } else {
        // Query the database if data is not cached
        $query = DB::table('roles');

        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where('title', 'like', '%' . $searchValue . '%');
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
            // Add the action button with the URL for editing the role
            $row->action = '
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/roles/edit', $row->id) . '">
                    <i class="bi bi-pencil"></i>
                </a>';

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

