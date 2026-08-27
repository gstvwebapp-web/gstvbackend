<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Userregister;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class EventController extends Controller
{
    // Get all pages
   
    public function index()
    {
        return view('event.alllist');   
        
    }
 
    public function updateStatus1(Request $request)
    {
        $updated = Event::where('event_id', $request->event_id)
        ->update(['status' => $request->status]);

        if ($updated) {
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ]);
        }
    }
    public function updateStatus(Request $request)
    {
        $eventID = $request->input('event_id');
        $status = $request->input('status');

        $event = Event::find($eventID);

        if ($event) {
            $event->status = $status;
            $event->save();

            return response()->json([
                'success' => true,
                'message' => "Status updated to $status successfully."
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ]);
        }
    }
    public function viewdata($id)
    {
        $event = Event::where('event_id', $id)->first();
        $data = [
                'event' => $event
        ];
        return view('event.view', compact('data'));
    }
 
 
    public function eventfetchData(Request $request)
    {
        Redis::flushall();
        //  'firstname', 'email', 'mobile'
        $columns = ['event_id', 'event_title', 'event_city', 'event_location', 'event_start_date', 'event_end_date', 'status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'event_id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Define a unique Redis key based on the request parameters
        $redisKey = "event-fetch-data:draw-{$draw}:start-{$start}:length-{$length}:order-{$orderColumn}-{$orderDirection}:search-{$searchValue}";

        // Check if data exists in Redis
        $cachedData = Redis::get($redisKey);

        if ($cachedData) {
            return response()->json(json_decode($cachedData, true));
        }

        // If not cached, run the query
        $query = DB::table('event as e')
            ->leftJoin('userregister as u', 'e.userID', '=', 'u.id')
            ->select(
                'e.event_id as event_id',
                'e.event_title',
                'e.event_city',
                'e.event_location',
                'e.event_desc',
                'e.status',
                'e.event_start_date',
                'e.event_end_date',
                'e.event_img',
                'u.id as userID',
                'u.firstname',
                'u.lastname',
                'u.email',
                'u.mobile'
            );

        // Total records
        $totalRecords = DB::table('event')->count();
        $filteredRecords = $query->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($subquery) use ($searchValue) {
                $subquery->where('e.event_title', 'like', "%$searchValue%")
                        ->orWhere('e.event_city', 'like', "%$searchValue%")
                        ->orWhere('e.event_location', 'like', "%$searchValue%");
            });
        }

        // Get the data
        $data = $query->orderBy($orderColumn, $orderDirection)
                    ->offset($start)
                    ->limit($length)
                    ->get()
                    ->toArray();

        // Format the data
        $rowNumber = $start + 1;
        foreach ($data as &$row) {
            $row->rowNumber = $rowNumber++;
            $row->event_start_date = Carbon::parse($row->event_start_date)->format('d M Y h:i A');
            $row->event_end_date = Carbon::parse($row->event_end_date)->format('d M Y h:i A');
            $row->action = ' <form action="' . url('/events/destroy', $row->event_id) . '" method="POST" style="display:inline;">
                     ' . csrf_field() . method_field('DELETE') . '
                    <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/events/edit', $row->event_id) . '">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="submit" onclick="return confirm(\'Are you sure you want to delete this record?\')" title="Delete" class="btn delCls iconbtn btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                    <a class="btn iconbtn editCls btn-primary" title="View" href="' . url('/events/view', $row->event_id) . '">
                                <i class="bi bi-view-list"></i>
                            </a>
                </form>';
        }

        // Prepare the response data
        $responseData = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        ];

        // Store the data in Redis with a 60-minute expiration time
        Redis::setex($redisKey, 3600, json_encode($responseData));

        return response()->json($responseData);
    }
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $event = Event::where('event_id', $id)->first();
        $eventtitle = $event->title;
        $storydata = $event->event_img;
      
        $imagePath  = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/events/'.$storydata;
        //$imagePath = public_path('/event/'.$lastElement);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
         
        $event->delete();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Web Story deleted: ' . $eventtitle,
            'action' => 'Delete',
            'contents' => json_encode($eventtitle),
        ]);

        return redirect('/events/')->with('success', 'Event has been deleted successfully');
    }
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $event = Event::where('event_id', $id)->first();
        $data =  array();
        $data['event'] =  $event;
        return view('event.edit', compact('data'));
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'event_title' => 'required|string',
            'event_eng_title' => 'required|string',
            'event_city' => 'required|string',
            'event_location' => 'required|string',
            'event_desc' => 'required|string',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date',
        ]);

        $event = Event::where('event_id', $request->event_id)->firstOrFail();

        $event->event_title = $request->event_title;
        $event->event_eng_title = $request->event_eng_title;
        $event->event_city = $request->event_city;
        $event->event_location = $request->event_location;
        $event->event_desc = $request->event_desc;
        $event->status = $request->status;
        $event->event_slug = $this->generateUniqueSlug($request->event_eng_title, $event->event_id);

        // Combine start date & time
        $event->event_start_date = $request->event_start_date . ' ' . $request->event_start_time . ':00';
        $event->event_end_date = $request->event_end_date . ' ' . $request->event_end_time . ':00';

        // Handle image deletion
        if ($request->delete_image == 1 && $event->event_img != '') {

            $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))). '/backend/public/uploads/events';
            if ($event->event_img && file_exists($mediaPath.'/' . $event->event_img)) {
                unlink($mediaPath.'/' . $images);
            }
            $event->event_img = null;
        }

        

        // Handle new image upload
        if ($request->hasFile('event_img')) {
            $image = $request->file('event_img');
            $filename = time() .$image->getClientOriginalName();
            $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/events';
           
            $image->move($mediaPath, $filename);
            $event->event_img = $filename;
        }

        $event->save();

        return redirect('/events/')->with('success', 'Event has been updated successfully.');
    }
    private function generateUniqueSlug($title, $event_id = null)
    {
        // 1. Make basic slug
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
        $slug = trim($slug, '-');

        $original_slug = $slug;
        $counter = 1;

        // 2. Check uniqueness
        while (Event::where('event_slug', $slug)
                    ->when($event_id, function ($query) use ($event_id) {
                        return $query->where('event_id', '!=', $event_id);
                    })->exists()) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
