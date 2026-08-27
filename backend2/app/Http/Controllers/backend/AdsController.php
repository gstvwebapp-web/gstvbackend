<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Ads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Route;
use App\Models\Log;

class AdsController extends Controller
{
    // Get all pages
    public function index()
    {
        return view('ads.alllist');
        $ads = DB::table('ads')->get();
        $data =  array();
        $data['ads'] =  $ads;
        return view('ads.list', compact('data'));
    }

    // Add new page
    public function add()
    {
        return view('ads.add');
    }

    public function store(Request $request)
    {
            
        $data = $request->all(); 
        unset($data['_token']);
         $jsonData = json_encode($data);
         Ads::create([
            'adsdata' => $jsonData,
            'status' => '1',
        ]);
    //    redirect('/ads/')->with('success', 'Ads has been added');
    return redirect()->route('ads.index')->with('success', 'Ads has been added');
    }

    // Update Category
    public function edit($id)
    {
        $id = Route::current()->parameter('id');
        $ads = Ads::find($id); 
        $data =  array();
        $data['ads'] =  $ads;
        return view('ads.edit', compact('data'));
    }

    public function update(Request $request)
    {
        
        $data = $request->all(); 
        unset($data['_token']);
         $jsonData = json_encode($data);
 
        $ads = Ads::find($request->adsid);
        $ads->adsdata = $jsonData;
        $ads->status = '1';
                		
        $ads->update(); 
        return redirect()->route('ads.index')->with('success', 'Ads has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $ads = Ads::find($id);
        $ads->status = $status;
        $ads->update();
         return redirect()->route('ads.index')->with('success', 'Ads Status has been updated successfully');
    }

    // Dalete the page
    public function destroy($id)
    {
        $id = Route::current()->parameter('id');
        $poll = Poll::find($id);
        $polltitle = $poll->question;  
        $poll->delete();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Poll deleted: ' . $polltitle,
            'action' => 'Delete',
            'contents' => json_encode($polltitle),
        ]);
        return redirect('/poll/')->with('success', 'Poll has been deleted successfully');
    }    
   
    public function adsfetchData(Request $request)
    {
        $columns = ['id', 'adsdata', 'created_at', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = DB::table('ads');
        
        if (!empty($searchValue)) {
            $query->where('adsdata', 'like', '%' . $searchValue . '%')
                ->orWhere('created_at', 'like', '%' . $searchValue . '%');
        }

        $totalRecords = $query->count();

        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        if ($filteredData->isEmpty()) {
            \Log::info("No data fetched: " . $query->toSql());
        }

        $counter = $start + 1;

        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $row->adsdata = strlen($row->adsdata) > 100  ? substr($row->adsdata, 0, 100) . '... '  : $row->adsdata;
            $row->created_At = \Carbon\Carbon::createFromTimestamp(strtotime($row->created_at))->format('d-m-Y h:i A');
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/ads/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/ads/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>';
            return $row;
        });

        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ]);
    }

    
}
