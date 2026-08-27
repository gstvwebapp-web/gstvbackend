<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Electionresults;
use App\Models\Electionstar;
use Illuminate\Support\Facades\Auth;
use Route;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;


class ElectionresultsController extends Controller
{
    // Get all pages
    public function index()
    {
        return view('electionresults.alllist');
        $electionresults = DB::table('electionresults')
        ->select('electionresults.*', 'u1.name as authorName','s1.name as stateName')
        ->leftJoin('users as u1', 'electionresults.userID', '=', 'u1.id')
        ->leftJoin('state as s1', 'electionresults.stateId', '=', 's1.id')
        ->orderBy('id','DESC')->get();
        $data =  array();
        $data['electionresults'] =  $electionresults;
        return view('electionresults.list', compact('data'));
    }

    // Add new page
    public function add()
    {
        $state = DB::table('state')->where('status','1')->get();
        $data =  array();
        $data['state'] =  $state;
        return view('electionresults.add', compact('data'));
    }

    public function store(Request $request)
    {

        $rules = [
            'stateId' => 'required'
        ];

        $customMessages = [
            'stateId.required' => 'The state field is required.',
        ];
    
        $request->validate($rules, $customMessages);   
        $partydata = array(
            'partyname' => $request->get('partyname'),
            'partyseat' => $request->get('partyseat'),
            'partylead' => $request->get('partylead'),
            'partycolor' => $request->get('partycolor')
        );
        

        // Convert the associative array to JSON
        $json_data = json_encode($partydata);
        
       
        $electionresults = new Electionresults([
            'party'=> $json_data,
            'stateId'=> $request->get('stateId'),
            'electiontitle'=> $request->get('electiontitle'),
            'totalseats'=> $request->get('totalseats'),
            'userID' => Auth::user()->id
        ]);
        
        $electionresults->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'electionresultsfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Election results added: ' .  $json_data,
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/electionresults/all/')->with('success', 'Election Results has been added');
    }

    // Update Category
    public function edit($id)
    {
        $id = Route::current()->parameter('id');
        $state = DB::table('state')->where('status','1')->get();
        $electionresults = Electionresults::find($id); 
        $data =  array();
        $data['state'] =  $state;
        $data['electionresults'] =  $electionresults;
        return view('electionresults.edit', compact('data'));
    }

    public function update(Request $request)
    {
        
        $rules = [
            'stateId' => 'required'
        ];

        $customMessages = [
            'stateId.required' => 'The state field is required.',
        ];
    
        $request->validate($rules, $customMessages);   
        $partydata = array(
            'partyname' => $request->get('partyname'),
            'partyseat' => $request->get('partyseat'),
            'partylead' => $request->get('partylead'),
            'partycolor' => $request->get('partycolor'),
        );
        

        // Convert the associative array to JSON
        $json_data = json_encode($partydata);   
        
        $electionresults = Electionresults::find($request->pid);

        $electionresults->party = $json_data;
        $electionresults->stateId = $request->get('stateId');
        $electionresults->electiontitle = $request->get('electiontitle');
        $electionresults->totalseats = $request->get('totalseats');
        $electionresults->userID = Auth::user()->id;
                		
        $electionresults->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'electionresultsfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Election results updated: ' .  $json_data,
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/electionresults/edit/'.$request->pid)->with('success', 'Election Results has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $electionresults = Electionresults::find($id);
        $electionresults->status = $status;
        $electionresults->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'electionresultsfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Electionresults published/Unpublished: ' . $status,
            'action' => $status,
            'contents' => json_encode($status),
        ]);
        return redirect('/electionresults/all/')->with('success', 'Election Results Status has been updated successfully.');
    }

    // Dalete the page
    public function destroy($id)
    {
        $id = Route::current()->parameter('id');
        $electionresults = Electionresults::find($id);
        $electionresults->delete();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'electionresultsfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Electionresults deleted: Delete',
            'action' => 'Delete',
            'contents' => json_encode('Election result Delete'),
        ]);
        return redirect('/electionresults/all/')->with('success', 'Election Results has been deleted successfully.');
    }
    public function addelectionstar()
    {
        $electionstar = DB::table('electionstar')->where('status', 'Active')->get();
        $data =  array();
        $state = DB::table('state')->where('status','1')->get();
        $data['state'] =  $state;
        $data['electionstar'] =  $electionstar;
        return view('electionresults.addelectionstar', compact('data'));
    }
    public function electionstar()
    {
        return view('electionresults.electionstaralllist');
        $electionstar = DB::table('electionstar')
        ->orderBy('created_at', 'DESC')
        ->get();   
        $data =  array();
        $data['electionstar'] =  $electionstar;
        return view('electionresults.electionstarlist', compact('data'));
    }
    public function submitelectionstar(Request $request)
    {
        // Validation rules and custom messages
        $rules = [
            'title' => 'required',
            'party_status' => 'required',
            'city' => 'required',
            'files.*' => 'image|mimes:jpeg,png,jpg,gif|max:20480'
        ];

        $customMessages = [
            'title.required' => 'The Category title field is required.',
            'party_status.required' => 'The English title field is required.',
            'city.required' => 'The Meta title field is required.',
            'files.image' => 'The uploaded file must be an image.',
            'files.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'files.max' => 'The image size must not exceed 2 MB.'
        ];

        $request->validate($rules, $customMessages);

        // Get the file data
        $webimgs = $_FILES['files'];
        $jsonDataArray = [];

        // Get current date details for folder structure
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/election/$year/$month/$day/";

        // Ensure the upload directory exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true); // Create directory with proper permissions
        }

        // Define the path for file access
        $path = config('app.backendnewsurl') . '/public/uploads/election/' . $year . '/' . $month . '/' . $day;

        // Generate a random code for filenames
        $randomCode = Str::random(8) . time();
        $pdfname = "";

        // Handle PDF file upload if exists
        if (isset($_FILES['files']['name']) && $_FILES['files']['error'] == 0) {
            $pdf = $_FILES['files'];
            if (is_uploaded_file($pdf['tmp_name'])) {
                $pdfname = $path . '/' . pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.png';
                move_uploaded_file($pdf['tmp_name'], $destinationPath . pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.png');
            }
        }
  
        // Store the data in the database
        $Electionstar = new Electionstar([
            'stateId' => $request->get('stateId'),
            'title' => $request->get('title'),
            'city' => $request->get('city'),
            'partytype' => $request->get('partytype'),
            'image' => $pdfname,
            'party_status' => $request->get('party_status'),
            'status' => 'Active',
            'metakeyword' => now()
        ]);
        $Electionstar->save();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'electionstarfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return redirect('/electionresults/electionstar')->with('success', 'Electionstar has been added');
    }
    
    public function editelectionstar()
    {
        $id = Route::current()->parameter('id');
        $Electionstarre = Electionstar::where('id', $id)->first();
        $electionstar = DB::table('electionstar')->where('id', $Electionstarre->id)->first();
        $data =  array();
        $state = DB::table('state')->where('status','1')->get();
        $data['state'] =  $state;
        $data['electionstar'] =  $electionstar; 
        return view('electionresults.editelectionstar', compact('data'));
    }
    public function updateelectionstar(Request $request){
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/election/$year/$month/$day/";
        $randomCode = Str::random(8).time();
        $path = config('app.backendnewsurl').'/public/uploads/election/'.$year.'/'.$month.'/'.$day;
        $pdfname= "";
        if($_FILES['files']['name'] != ""){
            $pdf = $_FILES['files'];
            $pdfname = $path.'/'.pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.png';
            // echo pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.png';
            // echo $pdfname = $path.'/'.''.pathinfo($pdf['name'], PATHINFO_EXTENSION).'_'.$randomCode.'.png'; exit;
            move_uploaded_file($pdf['tmp_name'], $destinationPath . pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.png');
        } 
        if($_FILES['files']['name'] == "" && $request->get('exitsfiles') != ""){
            $pdfname = $request->get('exitsfiles');
        }

        $electionstar = Electionstar::find($request->get('id'));
        $electionstar->stateId = $request->get('stateId');
        $electionstar->title = $request->get('title');
        $electionstar->city = $request->get('city');
        $electionstar->partytype = $request->get('partytype');
        $electionstar->updated_at = now();
        $electionstar->image = $pdfname;
        $electionstar->party_status = $request->get('party_status');
       
        $electionstar->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'electionstarfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return redirect('/electionresults/electionstar')->with('success', 'Electionstar has been updated successfully.');
    }
    public function updateelectionstarStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $electionstar = Electionstar::find($id); 
        $electionstar->status = $status;
        $electionstar->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'electionstarfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return redirect('/electionresults/electionstar')->with('success', 'Electionstar Status has been updated successfully');
    }
    public function destroyelectionstar(Request $request)
    {
        $id = Route::current()->parameter('id');
        $Electionstar = Electionstar::find($id);

        if ($Electionstar) {
            // Extract year, month, day from created_at
            $createdDate = Carbon::parse($Electionstar->created_at);
            $year = $createdDate->year;
            $month = $createdDate->month;
            $day = $createdDate->day;

            // Base directory for epaper files
            $baseDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/election/$year/$month/$day/";

            // Handle image deletion
                $webimage = explode('/', $Electionstar->image);
                $lastElement = end($webimage);
                $filenameWithoutExt = pathinfo($lastElement, PATHINFO_FILENAME);
                $imagePath = $baseDir . $lastElement;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            
            $Electionstar->delete();
            for ($k = 0; $k < 2000; $k++) {
                $cacheKey = 'electionstarfetchData:search_' . config('app.webtype') . ':' . $k;
                Redis::del($cacheKey);
            }
            return redirect('/electionresults/electionstar/')->with('success', 'Electionstar has been deleted successfully');
        }

        return redirect('/electionresults/electionstar/')->with('error', 'Electionstar not found');
    }
    public function electionresultsfetchData1(Request $request)
    {
        $columns = ['id', 'stateName','electiontitle', 'totalseats','partyname','authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query = DB::table('electionresults')
        ->select('electionresults.*', 'u1.name as authorName','s1.name as stateName')
        ->leftJoin('users as u1', 'electionresults.userID', '=', 'u1.id')
        ->leftJoin('state as s1', 'electionresults.stateId', '=', 's1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('stateName', 'like', '%' . $searchValue . '%')
                ->orWhere('electiontitle', 'like', '%' . $searchValue . '%')
                ->orWhere('totalseats', 'like', '%' . $searchValue . '%')
                ->orWhere('partyname', 'like', '%' . $searchValue . '%');
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
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/electionresults/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/electionresults/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/electionresults/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/electionresults/destroy', $row->id) . '" method="POST" style="display: none;">
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
    public function electionresultsfetchData(Request $request)
    {
        $columns = ['id', 'stateName', 'electiontitle', 'totalseats', 'partyname', 'authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a unique cache key
        $cacheKey = 'electionresultsfetchData:search_' . config('app.webtype') . ':' . $draw;

        // Check if the data is cached
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Return cached data
            return response()->json(json_decode($cachedData, true));
        }

        // Query data from the database
        $query = DB::table('electionresults')
            ->select('electionresults.*', 'u1.name as authorName', 's1.name as stateName')
            ->leftJoin('users as u1', 'electionresults.userID', '=', 'u1.id')
            ->leftJoin('state as s1', 'electionresults.stateId', '=', 's1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('s1.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('electiontitle', 'like', '%' . $searchValue . '%')
                    ->orWhere('totalseats', 'like', '%' . $searchValue . '%')
                    ->orWhere('partyname', 'like', '%' . $searchValue . '%');
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
            $row->rowNumber = $counter++;
            $partyy = '';
            if (isset($row->partyname) && isset($row->partyseat)) {
                foreach ($row->partyname as $index => $party) {
                    $partyy .= $party . ' : ' . $row->partyseat[$index] . '<br/>';
                }
            }
            $row->partyname = $partyy;

            $row->action = '
                <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/electionresults/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                    <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
                </a>
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/electionresults/edit', $row->id) . '">
                    <i class="bi bi-pencil"></i>
                </a>
                <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/electionresults/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                    <i class="bi bi-trash"></i>
                </a>
                <form id="delete-form-' . $row->id . '" action="' . url('/electionresults/destroy', $row->id) . '" method="POST" style="display: none;">
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

        // Cache the response data
        Redis::setex($cacheKey, 300, json_encode($response)); // Cache for 5 minutes

        return response()->json($response);
    }
    public function electionstarfetchData1(Request $request)
    {
        $columns = ['id', 'image','title', 'city','partytype','party_status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query = DB::table('electionstar');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('title', 'like', '%' . $searchValue . '%')
                ->orWhere('city', 'like', '%' . $searchValue . '%')
                ->orWhere('partytype', 'like', '%' . $searchValue . '%')
                ->orWhere('party_status', 'like', '%' . $searchValue . '%');
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
            $row->image = '<img src="'.$row->image.'" style="height:50px;"/>';
            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/electionresults/updateelectionstarStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/electionresults/editelectionstar', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/electionresults/destroyelectionstar', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/electionresults/destroyelectionstar', $row->id) . '" method="POST" style="display: none;">
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
    public function electionstarfetchData(Request $request)
    {
        $columns = ['id', 'image', 'title', 'city', 'partytype', 'party_status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a cache key based on parameters
        $cacheKey = 'electionstarfetchData:search_' . config('app.webtype') . ':' . $draw;

        // Attempt to retrieve cached data
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Return cached response
            return response()->json(json_decode($cachedData, true));
        }

        $query = DB::table('electionstar');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('title', 'like', '%' . $searchValue . '%')
                    ->orWhere('city', 'like', '%' . $searchValue . '%')
                    ->orWhere('partytype', 'like', '%' . $searchValue . '%')
                    ->orWhere('party_status', 'like', '%' . $searchValue . '%');
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
            $row->image = '<img src="' . $row->image . '" style="height:50px;"/>';
            // Add action buttons
            $row->action = '
                <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/electionresults/updateelectionstarStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                    <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
                </a>
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/electionresults/editelectionstar', $row->id) . '">
                    <i class="bi bi-pencil"></i>
                </a>
                <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/electionresults/destroyelectionstar', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                    <i class="bi bi-trash"></i>
                </a>
                <form id="delete-form-' . $row->id . '" action="' . url('/electionresults/destroyelectionstar', $row->id) . '" method="POST" style="display: none;">
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

        // Cache the response for 5 minutes (300 seconds)
        Redis::set($cacheKey, json_encode($response), 'EX', 300);

        // Return response
        return response()->json($response);
    }
    public function getelectionresultsAppChart()
    {
        // Fetch election results
        $electionResults = DB::table('electionresults')
            ->select('electionresults.party', 'electionresults.electiontitle', 'electionresults.totalseats', 'electionresults.stateId', 'state.Name as statename')
            ->join('state', 'electionresults.stateId', '=', 'state.id')
            ->where('electionresults.status', '=', 'Active')
            ->orderBy('electionresults.id', 'ASC')
            ->get();

        // Initialize to store all the HTML
        $imageTags = '';

        // Initialize an array to hold party data
        $partyDataArray = [];

        // CSS Styles for the carousel
        $imageTags .= '
            <style>
                .electionbox {  
                    margin-bottom: 20px;
                    padding: 10px;
                    border-left: 1px solid #dee2e6;
                    border-right: 1px solid #dee2e6;
                }
                .chart-section { float: left; }
                #chartContainer a {color:#000;}
                // #chartContainer .active {
                //     font-weight: 600;
                // }
                .election_title,#chartContainer a,.election_city{
                	font-size:40px !important;
                }
                .election_title{margin-top:25px;}
                .election_starts{font-size:35px !important;}
                .candidate-card img {
                    width: 130px !important;
                    height: 130px !important;
                }
                .candidate-details h4 { font-size: 27px !important;}
                .candidate-details p { font-size: 20px !important;}
                .status{font-size: 20px !important;}
            </style>
        ';

        // If there are election results, build the carousel
        if ($electionResults->isNotEmpty()) {
            $imageTags .= '
                <div class="" id="chartContainer">
                    <ul class="nav nav-tabs" role="tablist">';

            $partyDataArray = []; // Store each election's party data

            foreach ($electionResults as $index => $datas) {
                $activeClass = ($index === 0) ? 'active' : ''; // Set the first tab as active
                $imageTags .= '
                    <li class="nav-item">
                        <a class="nav-link ' . $activeClass . '" id="tab_' . $index . '" data-toggle="tab" href="#section_' . $index . '" role="tab">' . htmlspecialchars($datas->statename) . '</a>
                    </li>';

                // Decode party data and store in array for use in JavaScript
                $partyData = json_decode($datas->party, true);
                $partyDataArray[] = $partyData;
            }

            $imageTags .= '</ul>'; // Close the tab list

            // Create the tab content container
            $imageTags .= '<div class="tab-content electionbox">';

            foreach ($electionResults as $index => $datas) {
                $electionstarResults = DB::table('electionstar')
                ->where('status', 'Active')
                ->where('stateId', $datas->stateId)
                ->orderBy('id', 'DESC')
                ->get();
                $activeClass = ($index === 0) ? 'active' : '';

                $imageTags .= '
                    <div class="tab-pane fade show ' . $activeClass . '" id="section_' . $index . '" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6 chart-section text-center" id="chartSection_' . $index . '">
                                <h3 class="election_title">' . htmlspecialchars($datas->electiontitle) . '</h3>
                                <h4 class="election_city">' . htmlspecialchars($datas->statename) . ' (' . htmlspecialchars($datas->totalseats) . ')</h4>
                                <div style="height:300px;">
                                    <canvas id="electionChart_' . $index . '" width="100%" ></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6 chart-section text-center" style="margin-top:50px;">
                                <table class="table table-bordered" style="width:950px;display:inline-table;font-size:40px;">
                                    <thead>
                                        <tr>
                                            <th>પાર્ટી</th>
                                            <th>આગળ</th>
                                            <th>જીત્યા</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                foreach ($partyDataArray[$index]['partyname'] as $partykey => $partyVal) {
                    $dotcls = match ($partykey) {
                        0 => 'rgb(196, 75, 27)',
                        1 => 'rgba(54, 162, 235, 1)',
                        2 => 'rgba(255, 206, 86, 1)',
                        3 => 'rgba(75, 192, 192, 1)',
                        default => 'rgba(128, 128, 128, 1)',
                    };
                    $partylead = isset($partyDataArray[$index]['partylead'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partylead'][$partykey]) : 'N/A';
                    $partyseat = isset($partyDataArray[$index]['partyseat'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partyseat'][$partykey]) : 'N/A';
                    $partycolor = isset($partyDataArray[$index]['partycolor'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partycolor'][$partykey]) : '#cfcfcf';
                    $imageTags .= '
                                        <tr style="background-color:' . $partycolor . ';color:#fff;" >
                                            <td><span class="dot" style="background-color:' . $dotcls . '"></span> ' . htmlspecialchars($partyVal) . '</td>
                                            <td ">' . $partylead . '</td>
                                            <td ">' . $partyseat . '</td>
                                        </tr>';
                }

                $imageTags .= '
                                    </tbody>
                                </table>
                            </div>';

                // Star candidates section
                if (!empty($electionstarResults) && count($electionstarResults)>0) {
                    $imageTags .= '
                            <div class="election">
                                <div class="mt-4">
                                    <h2 class="election_starts">સ્ટાર ઉમેદવારો</h2>
                                    <div class="row mt-3">';
                    foreach ($electionstarResults as $stars) {
                        $imageTags .= '
                        <div class="col-md-6">
                            <div class="candidate-card">
                                <img src="' . htmlspecialchars($stars->image) . '" alt="Candidate Image">
                                <div class="candidate-details">
                                    <h4>' . htmlspecialchars($stars->title) . '</h4>
                                    <p>' . htmlspecialchars($stars->city) . '<br>' . htmlspecialchars($stars->partytype) . '</p>
                                    <span class="status" style="background-color:' . ($stars->party_status == "Won" ? 'green' : 'red') . ';">' . htmlspecialchars($stars->party_status) . '</span>
                                </div>
                            </div>
                        </div>';
                    }
                    $imageTags .= '
                                    </div>
                                </div>
                            </div>';
                }

                $imageTags .= '
                        </div>
                    </div>';
            }

            $imageTags .= '</div>'; // End carousel-inner
        }

        // Convert partyDataArray to JSON for use in JavaScript
        $partyDataJson = json_encode($partyDataArray);

        // Include scripts at the end
        $imageTags .= '
        <link rel="stylesheet" type="text/css" href="' . asset('public/assets/css/styles.css') . '?v=' . time() . '">
        <script src="' . asset('public/assets/js/jquery.min.js') . '"></script>
        <script src="' . asset('public/assets/js/bootstrap.min.js') . '"></script>
        <link rel="stylesheet" type="text/css" href="' . asset('public/assets/css/bootstrap.min.css') .'">
        <script src="' . asset('public/assets/js/chart.js') . '"></script>
        <script src="' . asset('public/assets/js/chartjs-plugin-datalabels.js') . '"></script>
        <script>
            $(document).ready(function () {
                var partyDataArray = ' . $partyDataJson . ';

                // Function to render chart for a given index
                function renderChart(index) {
                    var canvasId = "electionChart_" + index;
                    var canvasElement = document.getElementById(canvasId);

                    if (canvasElement) {
                        var ctx = canvasElement.getContext("2d");
                        var partyData = partyDataArray[index];
                        var combinedData = partyData.partyname.map(function(_, i) {
                            var seats = parseInt(partyData.partyseat[i], 10) || 0; // Convert to number, default to 0
                            var leads = parseInt(partyData.partylead[i], 10) || 0; // Convert to number, default to 0
                            return seats + leads; // Sum the two
                        });

                        // Generate chart labels
                        var labels = partyData.partyname.map(function(name, i) {
                            return name + " (Win: " + partyData.partyseat[i] + ", Leads: " + partyData.partylead[i] + ")";
                        });

                        // Debugging: Log combined data
                        console.log("Combined Data:", combinedData);

                        // Create the chart
                        new Chart(ctx, {
                            type: "doughnut",
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: "Total (Win + Leads)",
                                        data: combinedData, // Use the corrected combined data
                                        backgroundColor: partyData.partycolor,
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: "50%",
                                rotation: -90,
                                circumference: 180,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Customize tooltip text if needed
                                                return tooltipItem.label + ": " + tooltipItem.raw;
                                            }
                                        },
                                        bodyFont: {
                                            size: 20 // Set the font size for the tooltip text
                                        },
                                        titleFont: {
                                            size: 25 // Set the font size for the tooltip title
                                        }
                                    },
                                    legend: { display: false },
                                    datalabels: { display: false }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    }
                }

                // Initialize the first chart
                renderChart(0);

                // Render chart on tab click
               $("a[data-toggle=\"tab\"]").on("shown.bs.tab", function (e) {
                    var targetIndex = $(e.target).attr("id").split("_")[1];
                    
                    // Remove active class from all nav-items and nav-links
                    $(".nav-item").removeClass("active");
                    $(".nav-link").removeClass("active");

                    // Add active class to the clicked nav-item and nav-link
                    $(e.target).closest(".nav-item").addClass("active");
                    $(e.target).addClass("active");

                    renderChart(targetIndex);
                });
            });
        </script>';


        // Return the HTML as a response
        return response($imageTags);
    }
     public function getelectionresultsApp1()
    {
        // Fetch election results
        $electionResults = DB::table('electionresults')
            ->select('electionresults.party', 'electionresults.electiontitle', 'electionresults.totalseats', 'electionresults.stateId', 'state.Name as statename')
            ->join('state', 'electionresults.stateId', '=', 'state.id')
            ->where('electionresults.status', '=', 'Active')
            ->orderBy('electionresults.id', 'ASC')
            ->get();

        // Initialize to store all the HTML
        $imageTags = '';

        // Initialize an array to hold party data
        $partyDataArray = [];

        // CSS Styles for the carousel
        $imageTags .= '
            <style>
                .electionbox {  
                    margin-bottom: 20px;
                    padding: 10px;
                    border-left: 1px solid #dee2e6;
                    border-right: 1px solid #dee2e6;
                }
                .chart-section { float: left; }
                #chartContainer a {color:#000;}
                // #chartContainer .active {
                //     font-weight: 600;
                // }
                .election_title,#chartContainer a,.election_city{
                	font-size:40px !important;
                }
                .election_title{margin-top:25px;}
                .election_starts{font-size:35px !important;}
                .candidate-card img {
                    width: 130px !important;
                    height: 130px !important;
                }
                .candidate-details h4 { font-size: 27px !important;}
                .candidate-details p { font-size: 20px !important;}
                .status{font-size: 20px !important;}
            </style>
        ';

        // If there are election results, build the carousel
        if ($electionResults->isNotEmpty()) {
            $imageTags .= '
                <div class="" id="chartContainer">
                    <ul class="nav nav-tabs" role="tablist">';

            $partyDataArray = []; // Store each election's party data

            foreach ($electionResults as $index => $datas) {
                $activeClass = ($index === 0) ? 'active' : ''; // Set the first tab as active
                $imageTags .= '
                    <li class="nav-item">
                        <a class="nav-link ' . $activeClass . '" id="tab_' . $index . '" data-toggle="tab" href="#section_' . $index . '" role="tab">' . htmlspecialchars($datas->statename) . '</a>
                    </li>';

                // Decode party data and store in array for use in JavaScript
                $partyData = json_decode($datas->party, true);
                $partyDataArray[] = $partyData;
            }

            $imageTags .= '</ul>'; // Close the tab list

            // Create the tab content container
            $imageTags .= '<div class="tab-content electionbox">';

            foreach ($electionResults as $index => $datas) {
                $electionstarResults = DB::table('electionstar')
                ->where('status', 'Active')
                ->where('stateId', $datas->stateId)
                ->orderBy('id', 'DESC')
                ->get();
                $activeClass = ($index === 0) ? 'active' : '';

                $imageTags .= '
                    <div class="tab-pane fade show ' . $activeClass . '" id="section_' . $index . '" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6 chart-section text-center" id="chartSection_' . $index . '">
                                <h3 class="election_title">' . htmlspecialchars($datas->electiontitle) . '</h3>
                                <h4 class="election_city">' . htmlspecialchars($datas->statename) . ' (' . htmlspecialchars($datas->totalseats) . ')</h4>
                                <div style="height:300px;">
                                    <canvas id="electionChart_' . $index . '" width="100%" ></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6 chart-section text-center" style="margin-top:50px;">
                                <table class="table table-bordered" style="width:950px;display:inline-table;font-size:40px;">
                                    <thead>
                                        <tr>
                                            <th>પાર્ટી</th>
                                            <th>આગળ</th>
                                            <th>જીત્યા</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                foreach ($partyDataArray[$index]['partyname'] as $partykey => $partyVal) {
                    $dotcls = match ($partykey) {
                        0 => 'rgb(196, 75, 27)',
                        1 => 'rgba(54, 162, 235, 1)',
                        2 => 'rgba(255, 206, 86, 1)',
                        3 => 'rgba(75, 192, 192, 1)',
                        default => 'rgba(128, 128, 128, 1)',
                    };
                    $partylead = isset($partyDataArray[$index]['partylead'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partylead'][$partykey]) : 'N/A';
                    $partyseat = isset($partyDataArray[$index]['partyseat'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partyseat'][$partykey]) : 'N/A';
                    $partycolor = isset($partyDataArray[$index]['partycolor'][$partykey]) ? htmlspecialchars($partyDataArray[$index]['partycolor'][$partykey]) : '#cfcfcf';
                    $imageTags .= '
                                        <tr style="background-color:' . $partycolor . ';color:#fff;" >
                                            <td><span class="dot" style="background-color:' . $dotcls . '"></span> ' . htmlspecialchars($partyVal) . '</td>
                                            <td ">' . $partylead . '</td>
                                            <td ">' . $partyseat . '</td>
                                        </tr>';
                }

                $imageTags .= '
                                    </tbody>
                                </table>
                            </div>';

                // Star candidates section
                if (!empty($electionstarResults) && count($electionstarResults)>0) {
                    $imageTags .= '
                            <div class="election">
                                <div class="mt-4">
                                    <h2 class="election_starts">સ્ટાર ઉમેદવારો</h2>
                                    <div class="row mt-3">';
                    foreach ($electionstarResults as $stars) {
                        $imageTags .= '
                        <div class="col-md-6">
                            <div class="candidate-card">
                                <img src="' . htmlspecialchars($stars->image) . '" alt="Candidate Image">
                                <div class="candidate-details">
                                    <h4>' . htmlspecialchars($stars->title) . '</h4>
                                    <p>' . htmlspecialchars($stars->city) . '<br>' . htmlspecialchars($stars->partytype) . '</p>
                                    <span class="status" style="background-color:' . ($stars->party_status == "Won" ? 'green' : 'red') . ';">' . htmlspecialchars($stars->party_status) . '</span>
                                </div>
                            </div>
                        </div>';
                    }
                    $imageTags .= '
                                    </div>
                                </div>
                            </div>';
                }

                $imageTags .= '
                        </div>
                    </div>';
            }

            $imageTags .= '</div>'; // End carousel-inner
        }

        // Convert partyDataArray to JSON for use in JavaScript
        $partyDataJson = json_encode($partyDataArray);

        // Include scripts at the end
        $imageTags .= '
        <link rel="stylesheet" type="text/css" href="' . asset('public/assets/css/styles.css') . '?v=' . time() . '">
        <script src="' . asset('public/assets/js/jquery.min.js') . '"></script>
        <script src="' . asset('public/assets/js/bootstrap.min.js') . '"></script>
        <link rel="stylesheet" type="text/css" href="' . asset('public/assets/css/bootstrap.min.css') .'">
        <script src="' . asset('public/assets/js/chart.js') . '"></script>
        <script src="' . asset('public/assets/js/chartjs-plugin-datalabels.js') . '"></script>
        <script>
            $(document).ready(function () {
                var partyDataArray = ' . $partyDataJson . ';

                // Function to render chart for a given index
                function renderChart(index) {
                    var canvasId = "electionChart_" + index;
                    var canvasElement = document.getElementById(canvasId);

                    if (canvasElement) {
                        var ctx = canvasElement.getContext("2d");
                        var partyData = partyDataArray[index];
                        var combinedData = partyData.partyname.map(function(_, i) {
                            var seats = parseInt(partyData.partyseat[i], 10) || 0; // Convert to number, default to 0
                            var leads = parseInt(partyData.partylead[i], 10) || 0; // Convert to number, default to 0
                            return seats + leads; // Sum the two
                        });

                        // Generate chart labels
                        var labels = partyData.partyname.map(function(name, i) {
                            return name + " (Win: " + partyData.partyseat[i] + ", Leads: " + partyData.partylead[i] + ")";
                        });

                        // Debugging: Log combined data
                        console.log("Combined Data:", combinedData);

                        // Create the chart
                        new Chart(ctx, {
                            type: "doughnut",
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: "Total (Win + Leads)",
                                        data: combinedData, // Use the corrected combined data
                                        backgroundColor: partyData.partycolor,
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: "50%",
                                rotation: -90,
                                circumference: 180,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                // Customize tooltip text if needed
                                                return tooltipItem.label + ": " + tooltipItem.raw;
                                            }
                                        },
                                        bodyFont: {
                                            size: 20 // Set the font size for the tooltip text
                                        },
                                        titleFont: {
                                            size: 25 // Set the font size for the tooltip title
                                        }
                                    },
                                    legend: { display: false },
                                    datalabels: { display: false }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    }
                }

                // Initialize the first chart
                renderChart(0);

                // Render chart on tab click
               $("a[data-toggle=\"tab\"]").on("shown.bs.tab", function (e) {
                    var targetIndex = $(e.target).attr("id").split("_")[1];
                    
                    // Remove active class from all nav-items and nav-links
                    $(".nav-item").removeClass("active");
                    $(".nav-link").removeClass("active");

                    // Add active class to the clicked nav-item and nav-link
                    $(e.target).closest(".nav-item").addClass("active");
                    $(e.target).addClass("active");

                    renderChart(targetIndex);
                });
            });
        </script>';


        // Return the HTML as a response
        return response($imageTags);
    }
    public function getelectionresultsApp()
    {
        try {
            // ── Fetch all active groups ───────────────────────────────────────
            $groups = DB::table('election_groups')
                ->where('status', 'Active')
                ->orderBy('id', 'asc')
                ->get(['id', 'main_title', 'status']);

            if ($groups->isEmpty()) {
                return response('<html><body style="font-family:sans-serif;padding:20px;color:#888;">No election data available.</body></html>');
            }

            $groupIds = $groups->pluck('id')->toArray();

            $modules = DB::table('election_modules as em')
                ->select(
                    'em.id',
                    'em.group_id',
                    'em.title as election_title',
                    'em.total_seats',
                    DB::raw("IFNULL(s.name, '') as state_name")
                )
                ->leftJoin('state as s', 'em.state_id', '=', 's.id')
                ->whereIn('em.group_id', $groupIds)
                ->where(function ($q) {
                    $q->where('em.status', 'Active')->orWhereNull('em.status');
                })
                ->orderBy('em.id', 'asc')
                ->get();

            $moduleIds = $modules->pluck('id')->toArray();

            $allColumns = count($moduleIds)
                ? DB::table('election_columns')
                    ->select('id', 'election_id', 'column_name',
                             DB::raw("IFNULL(is_hidden, 0) as is_hidden"))
                    ->whereIn('election_id', $moduleIds)
                    ->orderBy('sort_order', 'asc')
                    ->get()
                : collect();

            $allRows = count($moduleIds)
                ? DB::table('election_rows')
                    ->whereIn('election_id', $moduleIds)
                    ->orderBy('id', 'ASC')
                    ->get(['id', 'election_id', 'party_name',
                           DB::raw("IFNULL(color_code,'#e67e22') as color_code")])
                : collect();

            $rowIds    = $allRows->pluck('id')->toArray();
            $allValues = count($rowIds)
                ? DB::table('election_values')
                    ->whereIn('row_id', $rowIds)
                    ->get(['row_id', 'column_id', 'value'])
                : collect();

            $columnsByElection = $allColumns->groupBy('election_id');
            $rowsByElection    = $allRows->groupBy('election_id');
            $valuesByRow       = $allValues->groupBy('row_id')
                                           ->map(fn($v) => $v->keyBy('column_id'));
            $modulesByGroup    = $modules->groupBy('group_id');

            $partyColors = [];  // no longer needed — using row's own color_code

            $html = '<!DOCTYPE html>
                <html lang="gu">
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;700&display=swap" rel="stylesheet">
                <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: "Noto Sans Gujarati", "Hind", Arial, sans-serif;
                    background: #f0f2f5;
                    padding: 12px;
                    font-size: 14px;
                }
                .page-title {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    font-size: 17px;
                    font-weight: 700;
                    color: #1a1a2e;
                    margin-bottom: 14px;
                    padding: 0 2px;
                }
                .page-title .dot {
                    width: 12px; height: 12px;
                    border-radius: 50%;
                    background: #850e00;
                    flex-shrink: 0;
                }
                .election-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 15px;
                }
                .election-card {
                    background: #fff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 1px 6px rgba(0,0,0,0.08);
                }
                .election-card-header {
                    padding: 10px 14px;
                    border-left: 4px solid #850e00;
                    background: #fff;
                }
                .election-card-header .title {
                    font-size: 15px; font-weight: 700; color: #1a1a2e;
                        width: 100%;float: left;
                }
                .election-card-header .seats {
                    font-size: 12px; color: #666; font-weight: bold;
                }
                table { width: 100%; border-collapse: collapse; }
                thead tr { background: #f8f9fa; }
                thead th {
                    padding: 5px;
                    font-size: 11px; font-weight: 700;
                    color: #000; text-transform: uppercase;
                    letter-spacing: 0.5px;
                    border-bottom: 1px solid #eee;
                    text-align: left;
                }
                thead th:not(:first-child) { text-align: center; }
                tbody tr { border-bottom: 1px solid #f0f0f0; }
                tbody tr:last-child { border-bottom: none; }
                tbody td { padding: 5px; font-size: 12px; font-weight: 600; }
                tbody td:not(:first-child) { text-align: center; }
                .party-name { font-weight: 700; font-size: 10px; }
                .val-normal { font-weight: 600; }
                </style>
                </head>
                <body><div>';

            foreach ($groups as $group) {
                $groupModules = $modulesByGroup->get($group->id, collect());
                if ($groupModules->isEmpty()) continue;

                $html .= '<div class="page-title"><span class="dot"></span>'
                       . htmlspecialchars($group->main_title) . '</div><div class="election-grid">';

                foreach ($groupModules as $module) {
                    $cols = $columnsByElection->get($module->id, collect());
                    $rows = $rowsByElection->get($module->id, collect());

                    // Only visible columns
                    $visibleCols = $cols->filter(fn($c) => !(int)$c->is_hidden)->values();

                    $html .= '<div class="election-card">';
                    $html .= '<div class="election-card-header">'
                           . '<span class="title">' . htmlspecialchars($module->election_title) . '</span>'
                           . '<span class="seats">(Total Seat - ' . $module->total_seats . ')</span>'
                           . '</div>';
                    $html .= '<table>';

                    // Header — all headings in black
                    $html .= '<thead><tr><th style="color:#000;">PARTY</th>';
                    $totalVis = $visibleCols->count();
                    foreach ($visibleCols as $ci => $col) {
                        $html .= '<th style="color:#000;">'
                               . htmlspecialchars($col->column_name) . '</th>';
                    }
                    $html .= '</tr></thead>';

                    // Rows — every cell in the same row color_code
                    $html .= '<tbody>';
                    foreach ($rows as $row) {
                        $rowValues = $valuesByRow->get($row->id, collect());
                        $color     = !empty($row->color_code) ? $row->color_code : '#e67e22';

                        $html .= '<tr>';
                        // Party name cell
                        $html .= '<td class="party-name" style="color:' . htmlspecialchars($color) . ';">'
                               . htmlspecialchars($row->party_name) . '</td>';

                        // All value cells — same color as party name
                        foreach ($visibleCols as $col) {
                            $valEntry = $rowValues->get($col->id);
                            $val      = $valEntry ? htmlspecialchars($valEntry->value) : '';
                            $html    .= '<td class="val-normal" style="color:' . htmlspecialchars($color) . ';">'
                                      . $val . '</td>';
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</tbody></table></div>';
                }
            }

            $html .= '</div></div></body></html>';
            return response($html);

        } catch (\Exception $e) {
            return response('<html><body style="font-family:sans-serif;padding:20px;color:red;">Error: '
                . htmlspecialchars($e->getMessage()) . '</body></html>');
        }
    }
    public function sudoku()
    {
        $data =  array();
        return view('sudoku', compact('data'));
    }
    public function helpfun()
    {
        $data =  array();
        return view('help', compact('data'));
    }
}
