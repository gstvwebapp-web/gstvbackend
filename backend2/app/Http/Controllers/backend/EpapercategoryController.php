<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Epapercategory;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class EpapercategoryController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('epapercategory.alllist');
        $epapercategorylist = Redis::get('epapercategorylist');
        if (!$epapercategorylist) {     
            $epapercategorylist = DB::table('epapercategory as c1')
                    ->select('c1.title as category_name', 'c1.*', 'u1.name as authorName')
                    ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
          			->orderBy('c1.id','ASC')
                    ->get();
           Redis::set('epapercategorylist', json_encode($epapercategorylist));
        } else {
           $epapercategorylist = json_decode($epapercategorylist);
        }
        $data =  array();
        $data['epapercategory'] =  $epapercategorylist;
        return view('epapercategory.list', compact('data'));
    }

    // New Category Add
    public function add()
    {
        return view('epapercategory.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title' => 'required',
            'engtitle' => 'required',
            'metatitle'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The Category title field is required.',
            'engtitle.required' => 'The English title field is required.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
        
      	Redis::del('epapercategorylist');
        
        $request->validate($rules, $customMessages);

        
        $epapercategory = new Epapercategory([
            'title' => $request->get('title'),
            'cattype' => $request->get('cattype'),
            'engtitle' => $request->get('engtitle'),
            'slug' => Str::slug($request->get('engtitle'), '-'),
            'userID'=> Auth::user()->id,
            'metatitle'=> $request->get('metatitle'),
            'metakeyword'=> $request->get('metakeyword'),
            'metadesc'=> $request->get('metadesc')
        ]);
        
        $epapercategory->save();
        $epapercategory = Epapercategory::find($epapercategory->id); 
        $epapercategory->catOrder = $epapercategory->id;       
        $epapercategory->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'epapercategory:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Epaper Category added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/epapercategory/')->with('success', 'Epaper Category has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $epapercategory = Epapercategory::find($id); 
        $data =  array();
        $data['epapercategory'] =  $epapercategory;
        return view('epapercategory.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required',
            'engtitle' => 'required',
            'metatitle'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The Category title field is required.',
            'engtitle.required' => 'The English title field is required.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);
        Redis::del('epapercategorylist');
        
        $epapercategory = Epapercategory::find($request->catid);
        $epapercategory->title = $request->get('title');
        $epapercategory->cattype = $request->get('cattype');
        $epapercategory->engtitle = $request->get('engtitle');
        $epapercategory->slug = Str::slug($request->get('engtitle'), '-');
        $epapercategory->metatitle = $request->get('metatitle');
        $epapercategory->metakeyword = $request->get('metakeyword');
        $epapercategory->metadesc = $request->get('metadesc');
       
        $epapercategory->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'epapercategory:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Epaper Category updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/epapercategory/')->with('success', 'Epaper Category has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $epapercategory = Epapercategory::find($id); 
        $cattitle = $epapercategory->title;
        $epapercategory->status = $status;
        $epapercategory->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'epapercategory:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Epaper Category published/Unpublished: ' . $cattitle,
            'action' => $status,
            'contents' => json_encode($cattitle),
        ]);
        return redirect('/epapercategory/')->with('success', 'Epaper Category Status has been updated successfully');
    }

    public function catorder(Request $request)
    {
        Redis::del('epapercategorylist');
        
        $newOrder = $request->input('newOrder');
        foreach ($newOrder as $position => $itemId) {
            $epapercategory = Epapercategory::find($itemId);
            $epapercategory->catOrder = $position + 1;
            $epapercategory->update();
        }
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'epapercategory:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return response()->json(['message' => 'Order updated successfully']);
    }

    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $epapercategory = Epapercategory::find($id);
        
        if ($epapercategory) {
            $title = $epapercategory->title;         
            $epapercategory->delete();
            for ($k = 0; $k < 2000; $k++) {
                $cacheKey = 'epapercategory:search_' . config('app.webtype') . ':' . $k;
                Redis::del($cacheKey);
            }
            Redis::del('epapercategorylist');
            Log::create([
                'userID'=> Auth::user()->id,
                'message' => 'Epaper Category deleted: ' . $title,
                'action' => 'Delete',
                'contents' => json_encode($title),
            ]);
            return redirect('/epapercategory/')->with('success', 'Epaper Category has been deleted successfully');
        }
        return redirect('/epapercategory/')->with('error', 'Epaper Category not found');
    }
    public function epapercategoryfetchData11(Request $request)
    {
        $columns = ['id', 'cattype','category_name', 'authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query = DB::table('epapercategory as c1')
        ->select('c1.title as category_name', 'c1.*', 'u1.name as authorName')
        ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('cattype', 'like', '%' . $searchValue . '%')
                ->orWhere('category_name', 'like', '%' . $searchValue . '%')
                ->orWhere('authorName', 'like', '%' . $searchValue . '%');
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
            $partyy = '';
            if(isset($row->partyname) && isset($row->partyseat)){
                foreach($row->partyname as $index => $party){
                    $partyy .= $party .' : '. $row->partyseat[$index] .'<br/>';
                }
            }
            $row->partyname = $partyy;
            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/epapercategory/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/epapercategory/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/epapercategory/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/epapercategory/destroy', $row->id) . '" method="POST" style="display: none;">
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
    

    public function epapercategoryfetchData(Request $request)
    {
        $columns = ['id', 'cattype', 'category_name', 'authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a unique cache key
        $cacheKey = 'epapercategory:search_' . config('app.webtype') . ':' . $draw;

        // Check if the data is already cached
        if (Redis::exists($cacheKey)) {
            $cachedData = json_decode(Redis::get($cacheKey), true);

            return response()->json($cachedData);
        }

        $query = DB::table('epapercategory as c1')
            ->select('c1.title as category_name', 'c1.*', 'u1.name as authorName')
            ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('cattype', 'like', '%' . $searchValue . '%')
                ->orWhere('c1.title', 'like', '%' . $searchValue . '%')
                ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination and sorting
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Map the data to add 'result' and 'action' columns
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/epapercategory/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/epapercategory/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/epapercategory/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/epapercategory/destroy', $row->id) . '" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="DELETE">
            </form>';
            return $row;
        });

        // Prepare response data
        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ];

        // Cache the response for 10 minutes
        Redis::setex($cacheKey, 600, json_encode($response));

        return response()->json($response);
    }

}

