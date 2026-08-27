<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\General;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Imports\NewsDataImport; 
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GeneralController extends Controller
{
    // Get all pages
    public function index(Request $request)
    {
        $general = DB::table('general')->where('type','Election')->orderBy('id', 'DESC')->get();
        $data =  array();
        $data['general'] =  $general;
        return view('general.list', compact('data'));
    }   
    public function generalfetchData(Request $request)
    {
        $columns = ['id', 'question', 'result', 'authorName', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = DB::table('polls')
            ->select('polls.*', 'u1.name as authorName')
            ->leftJoin('users as u1', 'polls.userID', '=', 'u1.id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('polls.question', 'like', '%' . $searchValue . '%')
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
            $results = DB::table('votes')
                ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                ->where('pollID', $row->id)
                ->groupBy('selectedAnswer')
                ->get();

            // Prepare results summary
            $finalresult = '';
            foreach ($results as $result) {
                $finalresult .= htmlspecialchars($result->selectedAnswer, ENT_QUOTES, 'UTF-8') . ' : ' . $result->answerCount . '<br/>';
            }

            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            $row->result = $finalresult;

            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/poll/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/poll/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/poll/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/poll/destroy', $row->id) . '" method="POST" style="display: none;">
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
}
