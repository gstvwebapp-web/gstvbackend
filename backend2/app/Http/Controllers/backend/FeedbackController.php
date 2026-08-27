<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class FeedbackController extends Controller
{
    // Get all pages
    public function index()
    {
      return view('feedback.alllist');
        $feedback = DB::table('feedback')->orderBy('id','DESC')->get();
        $data =  array();
        $data['feedback'] =  $feedback;
        return view('feedback.list', compact('data'));
    }
  	public function destroy($id)
    {
      $id = Route::current()->parameter('id');
      $feedback = Feedback::find($id);
      $feedback->delete();
      Log::create([
        'userID'=> Auth::user()->id,
        'message' => 'Feedback deleted',
        'action' => 'Delete',
        'contents' => json_encode($feedback),
      ]);
      return redirect('/feedback/')->with('success', 'Feedback has been deleted successfully');
    }
    public function feedbackfetchData1(Request $request)
    {
        $columns = ['id', 'name','email', 'phone','comment', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query = DB::table('feedback');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                ->orWhere('email', 'like', '%' . $searchValue . '%')
                ->orWhere('phone', 'like', '%' . $searchValue . '%')
                ->orWhere('comment', 'like', '%' . $searchValue . '%');
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
            $row->action = '<a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/feedback/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/feedback/destroy', $row->id) . '" method="POST" style="display: none;">
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
    public function feedbackfetchData(Request $request)
    {
        $columns = ['id', 'name', 'email', 'phone', 'comment', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a unique cache key based on the input parameters
        $cacheKey = "feedback_data:draw:$draw:start:$start";

        // Check if data exists in Redis
        if (Redis::exists($cacheKey)) {
            $response = Redis::get($cacheKey);
            return response()->json(json_decode($response, true));
        }

        $query = DB::table('feedback');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('comment', 'like', '%' . $searchValue . '%');
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

        // Map the data to add 'rowNumber' and 'action' columns
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            // Add action buttons
            $row->action = '<a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/feedback/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                    <i class="bi bi-trash"></i>
                </a>
                <form id="delete-form-' . $row->id . '" action="' . url('/feedback/destroy', $row->id) . '" method="POST" style="display: none;">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                    <input type="hidden" name="_method" value="DELETE">
                </form>';
            return $row;
        });

        // Prepare the response
        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ];

        // Store the response in Redis for 5 minutes
        Redis::setex($cacheKey, 300, json_encode($response));

        return response()->json($response);
    }

}
