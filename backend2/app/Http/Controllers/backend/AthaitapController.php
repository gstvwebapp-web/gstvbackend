<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Useraksana;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class AthaitapController extends Controller
{
    // Get all pages
   
    public function index()
    {
        return view('athaitap.alllist');   
        
    }
 
    public function updateStatus(Request $request)
    {
        // $athai_id = $request->input('athai_id');
        // $status = $request->input('status');

         $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');

        $Useraksana = Useraksana::find($id);

        if ($Useraksana) {
            $Useraksana->status = $status;
            $Useraksana->update();
        }
        return redirect('/athaitap/')->with('success', 'Athaitap Status has been updated successfully');
    } 
 
 
    public function athaitapfetchData(Request $request)
    {
        $columns = ['id', 'name', 'days', 'mobile', 'featureImage','video', 'created_at', 'status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        
        // If not cached, run the query
        $query = DB::table('user_aksana as e');

        // Total records
        $totalRecords = DB::table('user_aksana')->count();
        $filteredRecords = $query->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($subquery) use ($searchValue) {
                $subquery->where('name', 'like', "%$searchValue%")
                        ->orWhere('mobile', 'like', "%$searchValue%")
                        ->orWhere('address', 'like', "%$searchValue%");
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
            $row->featureImage = '<img src="' . config('app.backendnewsurl') . '/public/uploads/ekasana/' . $row->featureImage . '" width="120" />';
            if($row->video == ""){
                $row->video = "";
            } else {
                $row->video = '<video width="200" height="120" controls>
                        <source src="' . config('app.backendnewsurl') . '/public/uploads/ekasana/' . $row->video . '" type="video/mp4">
                        Your browser does not support the video tag.
                   </video>';
            }
            $row->created_at = Carbon::parse($row->created_at)->format('d M Y h:i A');
            

             $row->action = ' <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/athaitap/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>   <form action="' . url('/athaitap/destroy', $row->id) . '" method="POST" style="display:inline;">
                     ' . csrf_field() . method_field('DELETE') . '
                   
                    <button type="submit" onclick="return confirm(\'Are you sure you want to delete this record?\')" title="Delete" class="btn delCls iconbtn btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                   
                </form>';
            
        }
        

        // Prepare the response data
        $responseData = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        ]; 

        return response()->json($responseData);
    }
     
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $Useraksana = Useraksana::find($id); 
        $Useraksana->delete();
  
        return redirect('/athaitap/')->with('success', 'cricket Data has been deleted successfully');
    }
}
