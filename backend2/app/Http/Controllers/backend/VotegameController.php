<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Votegame;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class VotegameController extends Controller
{
    // Get all pages
    public function index()
    {
        return view('votegame.alllist');
        $votegame = DB::table('votegames')->select('votegames.*', 'u1.name as authorName')->leftJoin('users as u1', 'votegames.userID', '=', 'u1.id')->orderBy('id','DESC')->get();
        $data =  array();
        $data['votegame'] =  $votegame;
        return view('votegame.list', compact('data'));
    }

    // Add new page
    public function add()
    {
        return view('votegame.add');
    }

    public function store(Request $request)
    {
        $rules = [
            'question' => 'required',
            'answerOption'=>'required'
        ];

        $customMessages = [
            'question.required' => 'The question field is required.',
            'answerOption.required' => 'The answer field is required.',
        ];
    
        $request->validate($rules, $customMessages);   
       
        $votegame = new Votegame([
            'question' => $request->get('question'),
            'answerOption'=> $request->get('answerOption'),
            //'gameDetail'=> $request->get('gameDetail'),
            'whowin'=> $request->get('whowin'),
            'voteforwin'=> $request->get('voteforwin'),
            'totalvote'=> $request->get('totalvote'),
            'userID' => Auth::user()->id
        ]);
        
        $votegame->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'votegames:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Vote game added: ' . $request->get('question'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/votegame/')->with('success', 'Vote Game has been added');
    }

    // Update Category
    public function edit($id)
    {
        $id = Route::current()->parameter('id');
        $votegame = Votegame::find($id); 
        $data =  array();
        $data['votegame'] =  $votegame;
        return view('votegame.edit', compact('data'));
    }

    public function update(Request $request)
    {
        
        $rules = [
            'question' => 'required',
            'answerOption'=>'required'
        ];

        $customMessages = [
            'question.required' => 'The question field is required.',
            'answerOption.required' => 'The answer field is required.',
        ];
    
        $request->validate($rules, $customMessages);   
        
        $jsonOptions = $request->get('answerOption');
        $votegame = Votegame::find($request->pid);
        $votegame->question = $request->get('question');
       // $votegame->gameDetail = $request->get('gameDetail');
        $votegame->whowin = $request->get('whowin');
        $votegame->voteforwin = $request->get('voteforwin');
        $votegame->totalvote = $request->get('totalvote');
        $votegame->userID = Auth::user()->id;
        $votegame->answerOption = $jsonOptions;
                		
        $votegame->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'votegames:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Vote Game updated: ' . $request->get('question'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        return redirect('/votegame/')->with('success', 'Vote game has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $votegame = Votegame::find($id);
        $votetitle = $votegame->question; 
        $votegame->status = $status;
        $votegame->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'votegames:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Vote game published/Unpublished: ' . $votetitle,
            'action' => $status,
            'contents' => json_encode($votetitle),
        ]);
        return redirect('/votegame/')->with('success', 'Vote game Status has been updated successfully');
    }

    // Dalete the page
    public function destroy($id)
    {
        $id = Route::current()->parameter('id');
        $votegame = Votegame::find($id);
        $votetitle = $votegame->question;  
        $votegame->delete();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'votegames:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Vote game deleted: ' . $votetitle,
            'action' => 'Delete',
            'contents' => json_encode($votetitle),
        ]);
        return redirect('/votegame/')->with('success', 'Vote game has been deleted successfully');
    }
    public function votegamefetchData1(Request $request)
    {
        $columns = ['id', 'question', 'authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = DB::table('votegames')->select('votegames.*', 'u1.name as authorName')->leftJoin('users as u1', 'votegames.userID', '=', 'u1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('question', 'like', '%' . $searchValue . '%')
                ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
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
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/votegame/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/votegame/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/votegame/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/votegame/destroy', $row->id) . '" method="POST" style="display: none;">
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
    public function votegamefetchData(Request $request)
    {
        $columns = ['id', 'question', 'authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');

        // Create a unique cache key based on the search, pagination, and sorting parameters
        $cacheKey = 'votegames:search_' . config('app.webtype') . ':' . $draw;

        // Check if the data is cached
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Data found in cache, return it
            $data = json_decode($cachedData, true);
        } else {
            // Query the database if data is not cached
            $query = DB::table('votegames')
                ->select('votegames.*', 'u1.name as authorName')
                ->leftJoin('users as u1', 'votegames.userID', '=', 'u1.id');

            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('question', 'like', '%' . $searchValue . '%')
                        ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
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
                <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/votegame/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                    <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
                </a>
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/votegame/edit', $row->id) . '">
                    <i class="bi bi-pencil"></i>
                </a>
                <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/votegame/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                    <i class="bi bi-trash"></i>
                </a>
                <form id="delete-form-' . $row->id . '" action="' . url('/votegame/destroy', $row->id) . '" method="POST" style="display: none;">
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
