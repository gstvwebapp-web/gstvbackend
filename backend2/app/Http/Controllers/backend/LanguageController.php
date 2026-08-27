<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Language;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class LanguageController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('language.alllist');
    }

    // New Category Add
    public function add()
    {
        return view('language.add');
    }
    public function store(Request $request)
    {
        
        $rules = [
            'title_eng' => 'required',
            'title_guj'=>'required'
        ];

        $customMessages = [
            'title_eng.required' => 'Englsih title field is required.',
            'title_guj.required' => 'Gujarati title field is required.',
        ];
        
        $request->validate($rules, $customMessages);
      	$status = 'Active';
        $language = new Language([
            'title_eng' => $request->get('title_eng'),
            'title_guj'=> $request->get('title_guj'),
          	'status' => $status,
        ]);
        
        $language->save();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Language added: ' . $request->get('title_eng'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/language/')->with('success', 'Language has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $language = Language::find($id); 
        
        $data =  array();
        $data['language'] =  $language;
        return view('language.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'title_eng' => 'required',
            'title_guj'=>'required'
        ];

        $customMessages = [
            'title_eng.required' => 'Englsih title field is required.',
            'title_guj.required' => 'Gujarati title field is required.',
        ];
        
        $request->validate($rules, $customMessages);
       
        $language = Language::find($request->lid);
        $language->title_eng = $request->get('title_eng');
        $language->title_guj = $request->get('title_guj');
        $language->update();
 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Language updated: ' . $request->get('title_eng'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/language/')->with('success', 'Language has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $language = Language::find($id); 
        $language->status = $status;
        $language->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Language published/Unpublished: ' . $id,
            'action' => $status,
            'contents' => json_encode($id),
        ]);
        return redirect('/language/')->with('success', 'Language has been updated successfully');
    } 

    // Category Delete 
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $language = Language::find($id); 
        $language->delete();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Language deleted: ' . $id,
            'action' => 'Delete',
            'contents' => json_encode($id),
        ]);

        return redirect('/language/')->with('success', 'Language has been deleted successfully');
    }
    
    
    public function languagefetchData(Request $request)
{
    $columns = ['id', 'title_eng', 'title_guj', 'action'];

    // Request parameters
    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20); // Default 20 records per page
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderColumn = $columns[$orderColumnIndex] ?? 'id';
    $orderDirection = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $query = DB::table('language')->orderBy('id', 'DESC');

    // Apply search filter if provided
    if (!empty($searchValue)) {
        $query->where('title_eng', 'like', '%' . $searchValue . '%');
    }

    // Fetch data from the database
    $languageData = $query->get();

    // Pagination handling
    if (is_array($languageData)) {
        $totalRecords = count($languageData);
        $filteredData = array_slice($languageData, $start, $length);
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
             <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/language/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/language/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/language/destroy', $row->id) . '" 
               onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { 
               document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/language/destroy', $row->id) . '" method="POST" style="display: none;">
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

