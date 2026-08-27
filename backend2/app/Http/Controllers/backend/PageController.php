<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class PageController extends Controller
{
    // Get all pages
    public function index()
    {
        return view('pages.alllist');
      	$page = Redis::get('page');
        if (!$page) { 
        	$page = DB::table('pages as c1')
                    ->select('c1.title as page_name', 'c1.*', 'u1.name as authorName', 'c2.title as parent_page_name')
                    ->leftJoin('pages as c2', 'c1.parentID', '=', 'c2.id')
                    ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
                    ->get();
          Redis::set('page', json_encode($page));
        } else {
            $page = json_decode($page);
        }
        $data =  array();
        $data['page'] =  $page;
        return view('pages.list', compact('data'));
    }

    // Add new page
    public function add()
    {
        $page = DB::table('pages')->where('parentID',0)->where('status','Active')->get();
        $data =  array();
        $data['page'] =  $page;
        return view('pages.add', compact('data'));
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required|string',
            'metatitle'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The Page title field is required.',
            'description.required' => 'The Page description field is required.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);
        
        $page = new Page([
            'title' => $request->get('title'),
            'slug' => Str::slug($request->get('title'), '-'),
            'userID'=> Auth::user()->id,
            'parentID'=> $request->get('parentID'),
            'metatitle'=> $request->get('metatitle'),
            'metakeyword' => $request->get('metakeyword'),
            'metadesc'=> $request->get('metadesc'),
            'description'=> $request->get('description')
        ]);
        
        $page->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'pages:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Page added: ' . $request->get('title'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/pages/')->with('success', 'Page has been added');
    }

    // Update Category
    public function edit($id)
    {
        $id = Route::current()->parameter('id');
        $page = Page::find($id); 
        $subpage = DB::table('pages')->where('parentID',0)->where('status','Active')->get();
        $data =  array();
        $data['subpage'] =  $subpage;
        $data['page'] =  $page;
        return view('pages.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required|string',
            'metatitle'=>'required'
        ];

        $customMessages = [
            'title.required' => 'The Page title field is required.',
            'description.required' => 'The Page description field is required.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);     

        $page = Page::find($request->pid);
        $page->title = $request->get('title');
        $page->slug = Str::slug($request->get('title'), '-');
        $page->userID = Auth::user()->id;
        $page->parentID = $request->get('parentID');
        $page->metatitle = $request->get('metatitle');
        $page->metakeyword = $request->get('metakeyword');
        $page->metadesc = $request->get('metadesc');
        $page->description = $request->get('description');
        		
        $page->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'pages:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Page updated: ' . $request->get('title'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        return redirect('/pages/')->with('success', 'Page has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $page = Page::find($id); 
        $pagetitle = $page->title;
        $page->status = $status;
        $page->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'pages:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Page published/Unpublished: ' . $pagetitle,
            'action' => $status,
            'contents' => json_encode($pagetitle),
        ]);
        return redirect('/pages/')->with('success', 'Page Status has been updated successfully');
    }

    // Dalete the page
    public function destroy($id)
    {
        $id = Route::current()->parameter('id');
        $page = Page::find($id); 
        $pagetitle = $page->title;
        $page->delete();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'pages:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Page deleted: ' . $pagetitle,
            'action' => 'Delete',
            'contents' => json_encode($pagetitle),
        ]);
        return redirect('/pages/')->with('success', 'Page has been deleted successfully');
    }
    public function pagesfetchData(Request $request)
    {
        $columns = ['id', 'title', 'author_name', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

        $cacheKey = 'pages:search_' . config('app.webtype') . ':' . $draw;

        $categoryData = Redis::get($cacheKey);
        
        if (!$categoryData) {
     
            $query = DB::table('pages as c1')
                        ->select('c1.title as page_name', 'c1.*', 'u1.name as author_name', 'c2.title as parent_page_name')
                        ->leftJoin('pages as c2', 'c1.parentID', '=', 'c2.id')
                        ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id');

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('c1.title', 'like', '%' . $searchValue . '%')
                        ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
                });
            }
            $categoryData = $query->get();
            Redis::setex($cacheKey, 300, json_encode($categoryData));
        } else {
            $categoryData = json_decode($categoryData);
        }

        if (is_array($categoryData)) {
            $totalRecords = count($categoryData);
            $filteredData = array_slice($categoryData, $start, $length);
            $filteredData = collect($filteredData);
        } else {
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
            // Generate the image URL for the category icon
            // Add the action button with the URL for viewing the category
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/pages/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/pages/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/pages/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/pages/destroy', $row->id) . '" method="POST" style="display: none;">
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
