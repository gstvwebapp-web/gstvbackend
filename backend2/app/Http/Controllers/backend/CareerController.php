<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Userregister;
use App\Models\Career;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class CareerController extends Controller
{
    // Get all pages
   
    public function index()
    {
        return view('career.alllist');   
        
    }
 
    public function viewdata1($id)
    {
        $event = Event::where('id', $id)->first();
        $data = [
                'event' => $event
        ];
        return view('event.view', compact('data'));
    }
 
 
    public function careerfetchData(Request $request)
    {
        Redis::flushall();
        //  'firstname', 'email', 'mobile'
        $columns = ['id', 'fullname', 'email', 'phone', 'post', 'experience','message','cv', 'created_at','action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Define a unique Redis key based on the request parameters
        $redisKey = "career-fetch-data:draw-{$draw}:start-{$start}:length-{$length}:order-{$orderColumn}-{$orderDirection}:search-{$searchValue}";

        // Check if data exists in Redis
        $cachedData = Redis::get($redisKey);

        if ($cachedData) {
            return response()->json(json_decode($cachedData, true));
        }

        // If not cached, run the query
        $query = DB::table('career');

        // Total records
        $totalRecords = DB::table('career')->count();
        $filteredRecords = $query->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($subquery) use ($searchValue) {
                $subquery->where('fullname', 'like', "%$searchValue%")
                        ->orWhere('email', 'like', "%$searchValue%")
                        ->orWhere('post', 'like', "%$searchValue%")
                        ->orWhere('experience', 'like', "%$searchValue%")
                        ->orWhere('phone', 'like', "%$searchValue%");
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
            $row->created_at = Carbon::parse($row->created_at)->format('d M Y h:i A');  
            $row->cv = '<a target="_blank" href="' . config('app.backendnewsurl') . '/public/uploads/career/' . $row->cv . '"> <i class="bi bi-download"></i></a>';
            //$row->cv = Carbon::parse($row->cv)->format('d M Y h:i A');  
            $row->action = ' <form action="' . url('/career/destroy', $row->id) . '" method="POST" style="display:inline;">
                     ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" onclick="return confirm(\'Are you sure you want to delete this record?\')" title="Delete" class="btn delCls iconbtn btn-danger">
                        <i class="bi bi-trash"></i>
                    </button></form>';   
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
        $career = Career::where('id', $id)->first();
        $eventtitle = $career->title;
        $storydata = $career->cv;
      
        $imagePath  = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/career/'.$storydata;
        //$imagePath = public_path('/event/'.$lastElement);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
         
        $career->delete();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Career deleted: ' . $eventtitle,
            'action' => 'Delete',
            'contents' => json_encode($eventtitle),
        ]);

        return redirect('/career/')->with('success', 'Career has been deleted successfully');
    }
   
}
