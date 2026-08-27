<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Podcast;
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

class PodcastController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('podcast.alllist');
    }

    // New Category Add
    public function add()
    {
        $podcastauthor = DB::table('podcast_author')->where('status', 'Active')->orderBy('id','DESC')->get();
        $data =  array();
        $data['podcastauthor'] =  $podcastauthor;
        return view('podcast.add', compact('data'));
    }
    public function store(Request $request)
    {
        // Validation rules and custom messages
        $rules = [
            'podcastTitle' => 'required',
            'audioclip.*' => 'image|mimes:jpeg,png,jpg,gif|max:20480'
        ];

        $customMessages = [
            'podcastTitle.required' => 'The title field is required.',
            'audioclip.image' => 'The uploaded file must be an image.',
            'audioclip.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'audioclip.max' => 'The image size must not exceed 2 MB.'
        ];

        $request->validate($rules, $customMessages);

        $audioclipimg = '';
        if ($files = $request->file('audioclipimg')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            $audioclipimg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $audioclipimg);
        }
     	$audioclip = '';
        if ($files = $request->file('audioclip')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            $audioclip = 'audio_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $audioclip);
        }
        $filePath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/'.$audioclip;
        $durationFormatted = '00:00';
        if (file_exists($filePath)) {
            $command = "ffprobe -i " . escapeshellarg($filePath) . " -show_entries format=duration -v quiet -of csv=\"p=0\"";
            $durationInSeconds = exec($command);
            if (is_numeric($durationInSeconds)) {
                $durationFormatted = gmdate("i:s", (int)$durationInSeconds);
            }
        }
        $podcast = new Podcast([
            'authorid' => $request->get('authorid'),
            'podcastTitle' => $request->get('podcastTitle'),
            'podcastDescription' => $request->get('podcastDescription'),
            'audiocliptime' =>$durationFormatted,
            'audioclip' => $audioclip,
            'userID'=> Auth::user()->id,
            'audioclipimg'=> $audioclipimg,
        ]);
        
        $podcast->save(); 
        // Log the action
        \App\Models\Log::create([
            'userID' => Auth::user()->id,
            'message' => 'Podcast added: ' . $request->get('podcastTitle'),
            'action' => 'Add',
            'contents' => json_encode($request->all())
        ]);

        return redirect('/podcast/')->with('success', 'Podcast has been added');
    }

    
    
    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $podcast = Podcast::where('id', $id)->first();
        
        $podcastauthor = DB::table('podcast_author')->where('status', 'Active')->orderBy('id','DESC')->get();
        $data =  array();
        $data['podcast'] =  $podcast;
        $data['podcastauthor'] =  $podcastauthor;
        return view('podcast.edit', compact('data'));
    }

    public function update(Request $request)
    {
       $rules = [
            'podcastTitle' => 'required',
            'audioclip.*' => 'image|mimes:jpeg,png,jpg,gif|max:20480'
        ];

        $customMessages = [
            'podcastTitle.required' => 'The title field is required.',
            'audioclip.image' => 'The uploaded file must be an image.',
            'audioclip.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'audioclip.max' => 'The image size must not exceed 2 MB.'
        ];

        $request->validate($rules, $customMessages);
    
       	if ($files = $request->file('audioclipimg')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            if($request->get('oldImg')!='') {
                unlink($destinationPath.$request->get('oldImg'));
            }
            $audioclipimg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $audioclipimg);
        } else {
            $audioclipimg =  $request->get('oldImg');
        }
      
      	if ($files = $request->file('audioclip')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            if($request->get('oldclip')!='') {
                unlink($destinationPath.$request->get('oldclip'));
            }
            $audioclip = 'audio_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $audioclip);
        } else {
            $audioclip =  $request->get('oldclip');
        }
        
        // $filePath = base_path('backend/public/uploads/podcast/'.$audioclip);
        $filePath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/'.$audioclip;
        $durationFormatted = '00:00';
        if (file_exists($filePath)) {
            $command = "ffprobe -i " . escapeshellarg($filePath) . " -show_entries format=duration -v quiet -of csv=\"p=0\"";
            $durationInSeconds = exec($command);
            if (is_numeric($durationInSeconds)) {
                $durationFormatted = gmdate("i:s", (int)$durationInSeconds);
            }
        }
        
        $podcast = Podcast::find($request->get('podcastid'));
        $podcast->authorid = $request->get('authorid');
        $podcast->podcastTitle = $request->get('podcastTitle');
        $podcast->podcastDescription = $request->get('podcastDescription');
        $podcast->audioclip = $audioclip;
        $podcast->audiocliptime = $durationFormatted;
        $podcast->audioclipimg = $audioclipimg;
        
       
        $podcast->update();
       
        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Podcast updated: ' . $request->get('podcastTitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/podcast/')->with('success', 'Podcast has been updated successfully.');
    }
  public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $podcast = Podcast::find($id); 
        if($podcast->audioclipimg) {
            $destinationPath=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            $imagePath = $destinationPath.$podcast->audioclipimg;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    	if($podcast->audioclip) {
            $destinationPath=dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/podcast/';
            $imagePath1 = $destinationPath.$podcast->audioclip;
            if (file_exists($imagePath1)) {
                unlink($imagePath1);
            }
        }
        $podcastTitle= $podcast->podcastTitle;
        $podcast->delete();

        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Podcast Data deleted: ' . $podcastTitle,
            'action' => 'Delete',
            'contents' => json_encode($podcastTitle),
        ]);

        return redirect('/podcast/')->with('success', 'Podcast has been deleted successfully');
    }
    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $podcast = Podcast::find($id); 
        $podcastTitle = $podcast->podcastTitle;
        $podcast->status = $status;
        $podcast->update();
      
        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Podcast  published/Unpublished: ' . $podcastTitle,
            'action' => $status,
            'contents' => json_encode($podcastTitle),
        ]);
        return redirect('/podcast/')->with('success', 'Podcast Status has been updated successfully');
    }

    
    public function podcastfetchData(Request $request)
    {
        $columns = ['id', 'authorid', 'podcastTitle','audioclipimg','authorName', 'viewer', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a unique Redis cache key
        $cacheKey = 'podcastfetchData:search_' . config('app.webtype') . ':' . $draw;

        // Check if data exists in Redis
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Return cached data if exists
            return response()->json(json_decode($cachedData));
        }

        // Base query
        $query = DB::table('podcast')
            ->select(
                'podcast.*',
                'u1.name as authorName',
          		'p.authorname'
            )
          	->leftJoin('podcast_author as p', 'podcast.authorid', '=', 'p.id')
            ->leftJoin('users as u1', 'podcast.userID', '=', 'u1.id');
        
        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('podcastTitle', 'like', '%' . $searchValue . '%');
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
            $row->viewer = $row->viewer + $row->viewer_app;
          	$row->authorName = $row->authorName;
            
			$audioclipimg = $row->audioclipimg;
            if($row->audioclipimg == ""){
                $audioclipimg = 'author.png';
            }
            $imgurl = config('app.backendnewsurl') . '/public/uploads/podcast/' . $audioclipimg;
            $row->audioclipimg = "<img src=" . $imgurl . " style='width:30px;'/>";
            

            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" 
            title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" 
            href="' . url('/podcast/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/podcast/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/podcast/destroy', $row->id) . '" 
            onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { 
                document.getElementById(\'delete-form-' . $row->id . '\').submit(); 
            }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/podcast/destroy', $row->id) . '" method="POST" style="display: none;">
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

