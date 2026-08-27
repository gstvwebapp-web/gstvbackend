<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Poll;
use App\Models\PollWinner;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Route;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\PollCount;

class PollController extends Controller
{
    // Get all pages
    public function index()
    {
        return view('poll.alllist');
        $poll = DB::table('polls')->select('polls.*', 'u1.name as authorName')->leftJoin('users as u1', 'polls.userID', '=', 'u1.id')->orderBy('id','DESC')->get();
        $data =  array();
        $data['poll'] =  $poll;
        return view('poll.list', compact('data'));
    }

    // Add new page
    public function add()
    {
        return view('poll.add');
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
       
        $poll = new Poll([
            'question' => $request->get('question'),
            'answerOption'=> $request->get('answerOption'),
            'pollDetail'=> $request->get('pollDetail'),
          	'pollmsg'=> $request->get('pollmsg'),
          	'expiredate'=> $request->get('expiredate'),
            'userID' => Auth::user()->id
        ]);
        
        $poll->save();
        
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Poll added: ' . $request->get('question'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/poll/')->with('success', 'Poll has been added');
    }

    // Update Category
    public function edit($id)
    {
        $id = Route::current()->parameter('id');
        $poll = Poll::find($id); 
        $data =  array();
        $data['poll'] =  $poll;
        return view('poll.edit', compact('data'));
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
        $poll = Poll::find($request->pid);
        $poll->question = $request->get('question');
        $poll->pollDetail = $request->get('pollDetail');
      	$poll->pollmsg = $request->get('pollmsg');
      	$poll->expiredate = $request->get('expiredate');
        $poll->userID = Auth::user()->id;
        $poll->answerOption = $jsonOptions;
                		
        $poll->update();
        
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Poll updated: ' . $request->get('question'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        return redirect('/poll/')->with('success', 'Poll has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $poll = Poll::find($id);
        $polltitle = $poll->question; 
        $poll->status = $status;
        $poll->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'polls:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Poll published/Unpublished: ' . $polltitle,
            'action' => $status,
            'contents' => json_encode($polltitle),
        ]);
        return redirect('/poll/')->with('success', 'Poll Status has been updated successfully');
    }

    // Dalete the page
    public function destroy($id)
    {
        $id = Route::current()->parameter('id');
        $poll = Poll::find($id);
        $polltitle = $poll->question;  
        $poll->delete();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'polls:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Poll deleted: ' . $polltitle,
            'action' => 'Delete',
            'contents' => json_encode($polltitle),
        ]);
        return redirect('/poll/')->with('success', 'Poll has been deleted successfully');
    }
  	public function pollfetchData(Request $request)
    {
        $columns = ['id', 'question', 'result','viewer','created_at', 'authorName', 'action'];
    
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
    
        // Generate a cache key based on the search and pagination parameters
        $cacheKey = 'polls:search_' . config('app.webtype') . ':' . $draw;
        // Attempt to retrieve data from Redis
        $pollData = Redis::get($cacheKey);
    
        if (!$pollData) {
            /*$query = DB::table('polls')
                ->select('polls.*', 'u1.name as authorName', DB::raw('SUM(pollcount.view_count + pollcount.logincount) as total_view_count'))
                ->leftJoin('users as u1', 'polls.userID', '=', 'u1.id')
                ->leftJoin('pollcount', 'polls.id', '=', 'pollcount.content_id')
                ->groupBy('polls.id', 'u1.name');*/ 
          	
          	// $query = DB::table('polls')
            //         ->select(
            //             'polls.*',
            //             'u1.name as authorName',
            //             DB::raw('SUM(pollcount.view_count + pollcount.logincount) as total_view_count'),
            //             DB::raw('IF(poll_winners.pollID IS NULL, 0, 1) as isWinner')   // ✅ new
            //         )
            //         ->leftJoin('users as u1', 'polls.userID', '=', 'u1.id')
            //         ->leftJoin('pollcount', 'polls.id', '=', 'pollcount.content_id')
            //         ->leftJoin('poll_winners', 'polls.id', '=', 'poll_winners.pollID')  // ✅ new
            //         ->groupBy('polls.id', 'u1.name', 'poll_winners.pollID');
            $query = DB::table('polls')
            ->select(
                'polls.*',
                'u1.name as authorName',
                DB::raw('SUM(pollcount.view_count + pollcount.logincount) as total_view_count'),
                DB::raw('IF(poll_winners.pollID IS NULL, 0, 1) as isWinner')   // ✅ new
            )
            ->leftJoin('users as u1', 'polls.userID', '=', 'u1.id')
            ->leftJoin('pollcount', 'polls.id', '=', 'pollcount.content_id')
            ->leftJoin('poll_winners', 'polls.id', '=', 'poll_winners.pollID')  // ✅ new
            ->groupBy('polls.id', 'u1.name', 'poll_winners.pollID');
            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('polls.question', 'like', '%' . $searchValue . '%')
                      ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
                });
            }
    
            // Get total records count
            $totalRecords = $query->count();
    
            // Apply pagination and sorting
            $filteredData = $query->orderBy($orderColumn, $orderDirection)
                                  ->skip($start)
                                  ->take($length)
                                  ->get();
    
            // Map the data to add 'result' and 'action' columns
            $counter = $start + 1;
            $formattedData = $filteredData->map(function ($row) use (&$counter) {
                $results = DB::table('votes')
                    ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                    ->where('pollID', $row->id)
                    ->groupBy('selectedAnswer')
                    ->get();
    
                // Prepare results summary
                $totalVotes = DB::table('votes')
                    ->where('pollID', $row->id)
                    ->count();
                
                $finalresult = '';
                $options = explode(':', $row->answerOption); // get options from poll table
                foreach ($options as $option) {
                    $option = trim($option);
                    $match = $results->firstWhere('selectedAnswer', $option);
                    $count = $match ? $match->answerCount : 0;
                    $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 2) : 0;
                    $finalresult .= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') . ' : ' . $count . ' <b>(' . $percentage . '%)</b><br/>';
                }
                 
                // Assign row number and increment counter
                $row->rowNumber = $counter++;
                $row->result = $finalresult;
                $row->created_at = Carbon::parse($row->created_at)->format('d M Y');
                //$row->viewer = $row->total_view_count ?? 0; // Direct from query
              	$viewcount = $row->total_view_count ?? 0; // Direct from query
              	$row->viewer = $totalVotes .' / '. $viewcount;
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
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                </form>
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/poll/viewuserresult', $row->id) . '">
                    <i class="bi bi-list"></i>
                </a>';
                $row->rowClass = $row->isWinner ? 'winner-row' : '';
                return $row;
            });
    
            // Cache the data in Redis for 5 minutes (300 seconds)
            Redis::setex($cacheKey, 300, json_encode([
                'totalRecords' => $totalRecords,
                'formattedData' => $formattedData,
            ]));
        } else {
            // Decode the cached data
            $cachedData = json_decode($pollData, true);
            $totalRecords = $cachedData['totalRecords'];
            $formattedData = collect($cachedData['formattedData']);
        }
    
        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ]);
    }
    public function pollfetchData13(Request $request)
    {
        $columns = ['id', 'question', 'result','viewer','created_at', 'authorName', 'action'];
    
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
    
        // Generate a cache key based on the search and pagination parameters
        $cacheKey = 'polls:search_' . config('app.webtype') . ':' . $draw;
        // Attempt to retrieve data from Redis
        $pollData = Redis::get($cacheKey);
    
        if (!$pollData) {
            /*$query = DB::table('polls')
                ->select('polls.*', 'u1.name as authorName', DB::raw('SUM(pollcount.view_count + pollcount.logincount) as total_view_count'))
                ->leftJoin('users as u1', 'polls.userID', '=', 'u1.id')
                ->leftJoin('pollcount', 'polls.id', '=', 'pollcount.content_id')
                ->groupBy('polls.id', 'u1.name');*/ 
          	
          	// $query = DB::table('polls')
            //         ->select(
            //             'polls.*',
            //             'u1.name as authorName',
            //             DB::raw('SUM(pollcount.view_count + pollcount.logincount) as total_view_count'),
            //             DB::raw('IF(poll_winners.pollID IS NULL, 0, 1) as isWinner')   // ✅ new
            //         )
            //         ->leftJoin('users as u1', 'polls.userID', '=', 'u1.id')
            //         ->leftJoin('pollcount', 'polls.id', '=', 'pollcount.content_id')
            //         ->leftJoin('poll_winners', 'polls.id', '=', 'poll_winners.pollID')  // ✅ new
            //         ->groupBy('polls.id', 'u1.name', 'poll_winners.pollID');
            $query = DB::table('polls')
    ->select(
        'polls.*',
        'u1.name as authorName',
        DB::raw('SUM(pollcount.view_count + pollcount.logincount) as total_view_count'),
        DB::raw('IF(poll_winners.pollID IS NULL, 0, 1) as isWinner')   // ✅ new
    )
    ->leftJoin('users as u1', 'polls.userID', '=', 'u1.id')
    ->leftJoin('pollcount', 'polls.id', '=', 'pollcount.content_id')
    ->leftJoin('poll_winners', 'polls.id', '=', 'poll_winners.pollID')  // ✅ new
    ->groupBy('polls.id', 'u1.name', 'poll_winners.pollID');
            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('polls.question', 'like', '%' . $searchValue . '%')
                      ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
                });
            }
    
            // Get total records count
            $totalRecords = $query->count();
    
            // Apply pagination and sorting
            $filteredData = $query->orderBy($orderColumn, $orderDirection)
                                  ->skip($start)
                                  ->take($length)
                                  ->get();
    
            // Map the data to add 'result' and 'action' columns
            $counter = $start + 1;
            $formattedData = $filteredData->map(function ($row) use (&$counter) {
                $results = DB::table('votes')
                    ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                    ->where('pollID', $row->id)
                    ->groupBy('selectedAnswer')
                    ->get();
    
                // Prepare results summary
                $totalVotes = DB::table('votes')
                    ->where('pollID', $row->id)
                    ->count();
                
                $finalresult = '';
                $options = explode(':', $row->answerOption); // get options from poll table
                foreach ($options as $option) {
                    $option = trim($option);
                    $match = $results->firstWhere('selectedAnswer', $option);
                    $count = $match ? $match->answerCount : 0;
                    $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 2) : 0;
                    $finalresult .= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') . ' : ' . $count . ' <b>(' . $percentage . '%)</b><br/>';
                }
                 
                // Assign row number and increment counter
                $row->rowNumber = $counter++;
                $row->result = $finalresult;
                $row->created_at = Carbon::parse($row->created_at)->format('d M Y');
                //$row->viewer = $row->total_view_count ?? 0; // Direct from query
              	$viewcount = $row->total_view_count ?? 0; // Direct from query
              	$row->viewer = $totalVotes .' / '. $viewcount;
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
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                </form>
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/poll/viewuserresult', $row->id) . '">
                    <i class="bi bi-list"></i>
                </a>';
                $row->rowClass = $row->isWinner ? 'winner-row' : '';
                return $row;
            });
    
            // Cache the data in Redis for 5 minutes (300 seconds)
            Redis::setex($cacheKey, 300, json_encode([
                'totalRecords' => $totalRecords,
                'formattedData' => $formattedData,
            ]));
        } else {
            // Decode the cached data
            $cachedData = json_decode($pollData, true);
            $totalRecords = $cachedData['totalRecords'];
            $formattedData = collect($cachedData['formattedData']);
        }
    
        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ]);
    }
    public function viewuserresult($id)
    {
        $poll = DB::table('polls')
            ->select('polls.question','polls.id')
            ->where('polls.id', $id)
            ->first();
        $winner = DB::table('poll_winners')
                  ->where('pollID', $id)
                  ->first();
        $data = [
            'poll' => $poll,
            'winner' => $winner,
            'id'=>$id,
        ];
        return view('poll.viewuserresult', compact('data'));
    }

    public function userpollfetchData(Request $request)
{
    $columns = ['id', 'authorName', 'selectedAnswer','mobile','created_at'];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20);
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderColumn = $columns[$orderColumnIndex] ?? 'id';
    $orderDirection = $request->input('order.0.dir', 'DESC');
    $pollid = $request->input('pollid');

    // -----------------------------------------
    // STEP 1: Build Grouped Subquery
    // -----------------------------------------
    $subQuery = DB::table('votes')
        ->leftJoin('drawpoll as u1', function($join) {
            $join->on('votes.pollID', '=', 'u1.pollID')
                 ->on('votes.id', '=', 'u1.voteId');
        })
        ->where('votes.pollID', $pollid)
        ->whereNotNull('u1.mobile')
        ->where('u1.mobile', '!=', '')
        ->groupBy('u1.mobile')
        ->select([
            DB::raw("MAX(votes.id) as id"),
           // DB::raw("MAX(CONCAT(u1.firstname,' ',u1.lastname)) as authorName"),
          	DB::raw("MAX(TRIM(CONCAT_WS(' ', u1.firstname, u1.lastname))) as authorName"),
            'u1.mobile as mobile',
            DB::raw("MAX(votes.selectedAnswer) as selectedAnswer"),
            DB::raw("MAX(votes.created_at) as created_at")
        ]);

    // Convert subquery to a table
    $baseQuery = DB::query()->fromSub($subQuery, 't1');

    // -----------------------------------------
    // Total Records
    // -----------------------------------------
    $totalRecords = $baseQuery->count();

    // -----------------------------------------
    // Apply Search
    // -----------------------------------------
    if (!empty($searchValue)) {
        $baseQuery->where(function ($q) use ($searchValue) {
            $q->where('authorName', 'like', "%{$searchValue}%")
              ->orWhere('mobile', 'like', "%{$searchValue}%")
              ->orWhere('selectedAnswer', 'like', "%{$searchValue}%");
        });
    }

    // Records filtered
    $recordsFiltered = $baseQuery->count();

    // -----------------------------------------
    // Apply Ordering + Pagination
    // -----------------------------------------
    $data = $baseQuery
        ->orderBy($orderColumn, $orderDirection)
        ->skip($start)
        ->take($length)
        ->get();

    // -----------------------------------------
    // Format Response
    // -----------------------------------------
    $counter = $start + 1;

    $formattedData = $data->map(function ($row) use (&$counter) {
        $row->rowNumber = $counter++;
        $row->created_at = Carbon::parse($row->created_at)->format('d M Y');
        return $row;
    });

    return response()->json([
        "draw" => intval($draw),
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $recordsFiltered,
        "data" => $formattedData
    ]);
}

public function userpollLuckyDraw(Request $request)
{
    $pollid = $request->input('pollid');

    if (!$pollid) {
        return response()->json([
            'status' => 'error',
            'message' => 'Poll ID is required'
        ], 400);
    }

    // ✅ Check if winner already exists for this poll
    $existingWinner = DB::table('poll_winners')
        ->where('pollID', $pollid)
        ->first();

    if ($existingWinner) {
        return response()->json([
            'status' => 'error',
            'message' => 'Winner already selected for this poll',
            'winner' => $existingWinner
        ]);
    }

    // ✅ Exclude users who already won in ANY poll
    $alreadyWinners = DB::table('poll_winners')
        ->pluck('userID')
        ->toArray();

    // ✅ Pick only ONE random user from votes, excluding past winners
    /*$winner = DB::table('votes')
        ->leftJoin('userregister as u1', 'votes.userID', '=', 'u1.id')
        ->where('votes.pollID', $pollid)
        ->where('votes.userID', '!=', 0)
        ->whereNotIn('votes.userID', $alreadyWinners) // 🚀 Exclude past winners
        ->select(
            'votes.id as voteID',
            'votes.pollID',
            'votes.userID',
            'u1.firstname as authorName',
            'u1.mobile as mobile',
            'votes.selectedAnswer',
            'votes.created_at'
        )
        ->inRandomOrder()
        ->first();*/

	$winner = DB::table('votes as v')
    ->join('drawpoll as u1', 'v.id', '=', 'u1.voteId')  // ✅ join by voteId
    ->where('v.pollID', $pollid)
    ->whereNotIn('u1.id', $alreadyWinners)  // exclude winning voteIds
    ->select(
        'v.id as voteID',
        'v.pollID',
        'u1.id',
        DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"),
        'u1.mobile as mobile',
        'v.selectedAnswer',
        'v.created_at'
    )
    ->inRandomOrder()
    ->first();

    if ($winner) {
        // ✅ Insert into poll_winners
        $winnerId = DB::table('poll_winners')->insertGetId([
            'pollID'         => $winner->pollID,
            'userID'         => $winner->id,
            'voteID'         => $winner->voteID,
            'authorName'     => $winner->authorName,
            'mobile'         => $winner->mobile,
            'selectedAnswer' => $winner->selectedAnswer,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Format date for display
        $winner->created_at = Carbon::parse($winner->created_at)->format('d M Y');

        return response()->json([
            'status' => 'success',
            'winner_id' => $winnerId,
            'winner' => $winner
        ]);
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'No eligible users found for this poll'
        ]);
    }
}
 
public function userpollcount()
{
    return view('poll.userpollcount');
}

public function userpollcountfetchData(Request $request)
{
    $columns = ['rowNumber','mobile', 'authorName', 'total_polls', 'total_coins'];

    $draw   = $request->input('draw');
    $start  = $request->input('start', 0);
    $length = $request->input('length', 20);
    $search = $request->input('search.value');
    
    $startDate = $request->input('startDate');
    $endDate   = $request->input('endDate');

    // --- Base Query ---
    $query = DB::table('drawpoll as dp')
        ->select(
            'dp.mobile',
            DB::raw("MAX(CONCAT(dp.firstname, ' ', dp.lastname)) AS authorName"),
            DB::raw("COUNT(*) AS total_polls"),          // ✔ Count rows from drawpoll only
            DB::raw("SUM(dp.coin) AS total_coins")       // ✔ Sum coins from drawpoll
        )
        ->groupBy('dp.mobile')
        ->distinct('dp.mobile')  
        ->havingRaw("SUM(dp.coin) > 0");

    // --- Date Filter ---
    if (!empty($startDate) && !empty($endDate)) {
        $query->whereBetween(DB::raw('DATE(dp.created_at)'), [$startDate, $endDate]);
    }

    // --- Searching ---
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('dp.mobile', 'like', "%{$search}%")
            ->orWhere('dp.firstname', 'like', "%{$search}%")
            ->orWhere('dp.lastname', 'like', "%{$search}%");
        });
    }

    // --- Ordering (matching SQL) ---
    $query->orderBy('total_polls', 'DESC')
        ->orderBy('total_coins', 'DESC');
    
    

    // Total records before filtering
    $recordsTotal = DB::table('votes')
        ->select('pollID')
        ->distinct()
        ->count();

    // Total after filtering
    $recordsFiltered = $query->get()->count();

    // --- Pagination ---
    $data = $query->skip($start)->take($length)->get();

    // --- Add row numbers ---
    $rowNumber = $start + 1;
    $finalData = [];

    foreach ($data as $row) {
        $finalData[] = [
            'rowNumber'    => $rowNumber++,
            'mobile'       => $row->mobile,
            'authorName'   => $row->authorName,
            'total_polls'  => $row->total_polls,
            'total_coins'  => $row->total_coins
        ];
    }

    // --- DataTables Response ---
    return response()->json([
        'draw'            => intval($draw),
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $finalData
    ]);
}
public function exportUserPollpoint(Request $request)
{ 
    $startDate = $request->input('startDate');  
    $endDate   = $request->input('endDate');
    $search    = $request->input('search');  

    // --- CORRECT QUERY (drawpoll only) ---
    $query = DB::table('drawpoll as dp')
        ->select(
            'dp.mobile',
            DB::raw("MAX(CONCAT(dp.firstname, ' ', dp.lastname)) AS authorName"),
            DB::raw("COUNT(*) AS total_polls"),          // ✔ Count rows from drawpoll only
            DB::raw("SUM(dp.coin) AS total_coins")       // ✔ Sum coins from drawpoll
        )
        ->groupBy('dp.mobile')
        ->distinct('dp.mobile')  
        ->havingRaw("SUM(dp.coin) > 0");

    // --- Date Filter ---
    if (!empty($startDate) && !empty($endDate)) {
        $query->whereBetween(DB::raw('DATE(dp.created_at)'), [$startDate, $endDate]);
    }

    // --- Searching ---
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('dp.mobile', 'like', "%{$search}%")
              ->orWhere('dp.firstname', 'like', "%{$search}%")
              ->orWhere('dp.lastname', 'like', "%{$search}%");
        });
    }

    // --- Ordering ---
    $query->orderBy('total_polls', 'DESC')
          ->orderBy('total_coins', 'DESC');

    // Fetch final records
    $data = $query->get();

    // ===================== CSV EXPORT ===================== //

    $filename = "poll_users_coins_" . time() . ".csv";

    $headers = [
        "Content-type" => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = ['Mobile', 'Name', 'Poll Count', 'Poll Point'];

    $callback = function () use ($data, $columns) {
        $file = fopen('php://output', 'w');

        // UTF-8 for Gujarati support
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

        // Header
        fputcsv($file, $columns);

        // Rows
        foreach ($data as $row) {
            fputcsv($file, [
                $row->mobile,
                $row->authorName,
                $row->total_polls,
                $row->total_coins
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
  public function exportUserPoll(Request $request)
{
    $pollid = $request->pollid;

    // -----------------------------------------
    // STEP 1: Build Same SubQuery (Grouped Data)
    // -----------------------------------------
    $subQuery = DB::table('votes')
        ->leftJoin('drawpoll as u1', function($join) {
            $join->on('votes.pollID', '=', 'u1.pollID')
                 ->on('votes.id', '=', 'u1.voteId');
        })
        ->where('votes.pollID', $pollid)
        ->whereNotNull('u1.mobile')
        ->where('u1.mobile', '!=', '')
        ->groupBy('u1.mobile')
        ->select([
            DB::raw("MAX(votes.id) as id"),
            //DB::raw("MAX(CONCAT(u1.firstname,' ',u1.lastname)) as Name"),
          	DB::raw("MAX(TRIM(CONCAT_WS(' ', u1.firstname, u1.lastname))) as Name"),
            'u1.mobile as Mobile',
            DB::raw("MAX(votes.selectedAnswer) as Answer"),
            DB::raw("MAX(votes.created_at) as Date")
        ]);

    // Convert subquery to table
    $data = DB::query()->fromSub($subQuery, 't1')
            ->orderBy('id', 'DESC')    // Sort by latest vote
            ->get();

    // -----------------------------------------
    // STEP 2: Export CSV
    // -----------------------------------------
    $filename = "poll_users_" . time() . ".csv";

    $headers = [
        "Content-type" => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = ['Name', 'Mobile', 'Answer', 'Date'];

    $callback = function () use ($data, $columns) {
        $file = fopen('php://output', 'w');

        // UTF-8 BOM
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($file, $columns);

        foreach ($data as $row) {
            fputcsv($file, [
                $row->Name,
                $row->Mobile,
                $row->Answer,
                $row->Date,
            ]);
        }

        fclose($file);
    };

    if (ob_get_length()) {
        ob_end_clean();
    }

    return response()->stream($callback, 200, $headers);
}
}
