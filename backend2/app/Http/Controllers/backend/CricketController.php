<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UpcomingCricket;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class CricketController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('cricket.alllist');
    }
 
    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $Cricketdata = UpcomingCricket::find($id); 
        $authorname= $Cricketdata->series;
        $Cricketdata->status = $status;
        $Cricketdata->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'cricket published/Unpublished: ',
            'action' => $status,
            'contents' => json_encode($authorname),
        ]);
        return redirect('/cricket/')->with('success', 'cricket Status has been updated successfully');
    }
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $UpcomingCricket = UpcomingCricket::find($id); 
        $authorname= $UpcomingCricket->series;
        $UpcomingCricket->delete();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Rashi Data deleted: ' . $authorname,
            'action' => 'Delete',
            'contents' => json_encode($authorname),
        ]);

        return redirect('/cricket/')->with('success', 'cricket Data has been deleted successfully');
    }
    public function cricketfetchData(Request $request)
    {
        $columns = ['id', 'series','match_time','team_a','team_b', 'venue','match_type', 'date_wise', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
        $status = $request->input('status', 'Active'); // Default to Active

  
        $query = DB::table('upcoming_cricket as c1')
            ->where('c1.cricketdate', '>=', date('Y-m-d')) 
            ->orderBy('c1.id', 'ASC')
            ->take(10);
 
        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.series', 'like', '%' . $searchValue . '%')
                    ->orWhere('c1.venue', 'like', '%' . $searchValue . '%');
            });
        }
    
        // Execute the query
        $filteredData = $query->get();
        $totalRecords = count($filteredData); // Since we are taking 10, the total will always be ≤10.
    
        $counter = 1;
        // Map the data to add the 'action' column and other transformations
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Assign row number and increment counter
            $row->rowNumber = $counter++; 
            $row->action = '
             <a class="btn iconbtn showhideCls ' . ($row->status == '1' ? 'btn-success' : 'btn-warning') . '" 
            title="' . ($row->status == '1' ? 'Show' : 'Hidden') . '" 
            href="' . url('/cricket/updateStatus/' . ($row->status == '1' ? '0' : '1'), $row->id) . '">
                <i class="bi ' . ($row->status == '1' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/cricket/destroy', $row->id) . '" 
            onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { 
                document.getElementById(\'delete-form-' . $row->id . '\').submit(); 
            }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/cricket/destroy', $row->id) . '" method="POST" style="display: none;">
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

